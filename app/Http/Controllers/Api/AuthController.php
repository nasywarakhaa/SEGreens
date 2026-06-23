<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RefreshTokenRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\ResendVerificationRequest;
use App\Http\Resources\Api\UserResource;
use App\Jobs\SendVerifyEmailNotificationJob;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use App\Services\BrandAssetService;
use App\Services\RuntimeMailConfigService;
use App\Services\SystemSettingService;
use App\Support\ApiResponse;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class AuthController extends Controller
{
    private const RESET_OTP_TTL_MINUTES = 10;

    public function register(
        RegisterRequest $request,
        SystemSettingService $settings,
        RuntimeMailConfigService $mailConfig,
    ): JsonResponse {
        $requireVerification = $settings->getBool('app', 'require_email_verification', true);
        $canSendMail = $mailConfig->isConfigured();

        $payload = $request->validated();
        $payload['username'] = $this->generateUniqueUsername((string) $payload['email']);
        $payload['email_verified_at'] = $requireVerification ? null : now();

        $user = User::query()->create($payload);
        $user->refresh();

        if ($requireVerification && $canSendMail) {
            $mailConfig->applyFromSettings();
            try {
                SendVerifyEmailNotificationJob::dispatch((string) $user->id);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return ApiResponse::success([
            'user' => new UserResource($user),
            'token' => $this->issueTokenPair($user),
            'email_verification_required' => $requireVerification,
        ], 'Registration completed successfully.', 201);
    }

    public function login(LoginRequest $request, SystemSettingService $settings): JsonResponse
    {
        $identifier = trim((string) $request->input('identifier'));
        $identifierLower = strtolower($identifier);

        $user = User::query()
            ->where(function ($query) use ($identifier, $identifierLower): void {
                $query
                    ->whereRaw('LOWER(email) = ?', [$identifierLower])
                    ->orWhereRaw('LOWER(username) = ?', [$identifierLower])
                    ->orWhere('phone_number', $identifier);
            })
            ->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return ApiResponse::error('Invalid credentials.', 401);
        }

        $statusCode = $user->status_code?->value ?? (int) $user->status_code;
        if ($statusCode !== UserStatus::Active->value) {
            return ApiResponse::error('Account is not active.', 403);
        }

        $requireVerification = $settings->getBool('app', 'require_email_verification', true);
        if ($requireVerification && ! $user->hasVerifiedEmail()) {
            return ApiResponse::error('Email verification is required.', 403);
        }

        return ApiResponse::success([
            'user' => new UserResource($user),
            'token' => $this->issueTokenPair($user),
            'email_verification_required' => $requireVerification,
        ]);
    }

    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        $tokenString = $request->string('refresh')->toString();
        $refreshToken = PersonalAccessToken::findToken($tokenString);

        if (! $refreshToken || ! $refreshToken->can('refresh')) {
            return ApiResponse::error('Invalid refresh token.', 401);
        }

        if ($refreshToken->expires_at && $refreshToken->expires_at->isPast()) {
            $refreshToken->delete();

            return ApiResponse::error('Refresh token expired.', 401);
        }

        $user = $refreshToken->tokenable;

        if (! $user instanceof User) {
            $refreshToken->delete();

            return ApiResponse::error('Invalid refresh token.', 401);
        }

        $statusCode = $user->status_code?->value ?? (int) $user->status_code;
        if ($statusCode !== UserStatus::Active->value) {
            return ApiResponse::error('Account is not active.', 403);
        }

        $refreshToken->delete();

        return ApiResponse::success([
            'token' => $this->issueTokenPair($user),
        ], 'Token refreshed.');
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $accessToken = $request->user()?->currentAccessToken();

        if (! $user instanceof User || ! $accessToken) {
            return ApiResponse::error('Unauthorized.', 401);
        }

        PersonalAccessToken::query()
            ->where('tokenable_type', $user->getMorphClass())
            ->where('tokenable_id', (string) $user->getKey())
            ->where('name', 'refresh_token')
            ->delete();

        $accessToken->delete();

        return ApiResponse::success(null, 'Logged out.');
    }

    public function resendVerification(
        ResendVerificationRequest $request,
        RuntimeMailConfigService $mailConfig,
    ): JsonResponse {
        if (! $mailConfig->isConfigured()) {
            return ApiResponse::error('SMTP is disabled.', 422);
        }

        $mailConfig->applyFromSettings();

        $user = $request->user();
        if (! $user instanceof User) {
            $email = strtolower((string) $request->input('email'));
            if ($email === '') {
                return ApiResponse::error('The email field is required.', 422, [
                    'email' => ['The email field is required.'],
                ]);
            }

            $user = User::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();
        }

        if (! $user instanceof User) {
            return ApiResponse::success(null, 'If an account with that email exists, a verification email has been sent.');
        }

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(null, 'Email address is already verified.');
        }

        try {
            SendVerifyEmailNotificationJob::dispatch((string) $user->id);
        } catch (Throwable $exception) {
            report($exception);

            return ApiResponse::error('Unable to send verification email right now.', 503);
        }

        return ApiResponse::success(null, 'Verification email sent successfully.');
    }

    public function verifyEmail(Request $request, string $id, string $hash): HttpResponse
    {
        $ignoredQuery = array_values(array_filter(
            array_keys($request->query()),
            static fn (string $key): bool => ! in_array($key, ['signature', 'expires'], true),
        ));

        $hasValidSignature = $request->hasValidSignatureWhileIgnoring($ignoredQuery)
            || $request->hasValidRelativeSignatureWhileIgnoring($ignoredQuery);

        if (! $hasValidSignature) {
            return $this->verificationResult(
                request: $request,
                success: false,
                apiMessage: 'Invalid signature.',
                statusCode: 403,
                pageTitle: 'Tautan Verifikasi Tidak Valid',
                pageDescription: 'Tautan verifikasi tidak valid atau sudah kedaluwarsa. Silakan minta kirim ulang email verifikasi.',
            );
        }

        $user = User::query()->find($id);
        if (! $user instanceof User) {
            return $this->verificationResult(
                request: $request,
                success: false,
                apiMessage: 'Invalid verification link.',
                statusCode: 404,
                pageTitle: 'Akun Tidak Ditemukan',
                pageDescription: 'Akun untuk tautan verifikasi ini tidak ditemukan.',
            );
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return $this->verificationResult(
                request: $request,
                success: false,
                apiMessage: 'Invalid verification link.',
                statusCode: 403,
                pageTitle: 'Tautan Verifikasi Tidak Valid',
                pageDescription: 'Link verifikasi tidak cocok dengan akun yang dituju.',
            );
        }

        if ($user->hasVerifiedEmail()) {
            return $this->verificationResult(
                request: $request,
                success: true,
                apiMessage: 'Email address is already verified.',
                statusCode: 200,
                pageTitle: 'Email Sudah Terverifikasi',
                pageDescription: 'Alamat email Anda sudah aktif sebelumnya. Anda bisa langsung lanjut menggunakan aplikasi.',
                pageState: 'info',
            );
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return $this->verificationResult(
            request: $request,
            success: true,
            apiMessage: 'Email address verified successfully.',
            statusCode: 200,
            pageTitle: 'Email Berhasil Diverifikasi',
            pageDescription: 'Verifikasi berhasil. Akun Anda sudah aktif dan siap digunakan.',
            pageState: 'success',
        );
    }

    public function forgotPassword(Request $request, RuntimeMailConfigService $mailConfig): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        return $this->sendPasswordResetOtp((string) $validated['email'], $mailConfig);
    }

    public function resendForgotPasswordOtp(Request $request, RuntimeMailConfigService $mailConfig): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        return $this->sendPasswordResetOtp((string) $validated['email'], $mailConfig);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $normalizedEmail = strtolower((string) $validated['email']);
        $passwordResetRow = $this->passwordResetQuery()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if (! $passwordResetRow) {
            return ApiResponse::error('Invalid or expired reset OTP.', 422);
        }

        $expiresAt = CarbonImmutable::parse((string) $passwordResetRow->created_at)
            ->addMinutes(self::RESET_OTP_TTL_MINUTES);
        if ($expiresAt->isPast()) {
            $this->passwordResetQuery()
                ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
                ->delete();

            return ApiResponse::error('Invalid or expired reset OTP.', 422);
        }

        if (! Hash::check((string) $validated['token'], (string) $passwordResetRow->token)) {
            return ApiResponse::error('Invalid or expired reset OTP.', 422);
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if (! $user instanceof User) {
            return ApiResponse::error('Invalid or expired reset OTP.', 422);
        }

        $user->forceFill([
            'password' => $validated['password'],
            'remember_token' => Str::random(60),
        ])->save();

        $this->passwordResetQuery()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->delete();

        PersonalAccessToken::query()
            ->where('tokenable_type', $user->getMorphClass())
            ->where('tokenable_id', (string) $user->getKey())
            ->delete();

        return ApiResponse::success(null, 'Password reset completed successfully.');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::error('Unauthorized.', 401);
        }

        $user->forceFill([
            'password' => $request->string('password')->toString(),
            'remember_token' => Str::random(60),
        ])->save();

        PersonalAccessToken::query()
            ->where('tokenable_type', $user->getMorphClass())
            ->where('tokenable_id', (string) $user->getKey())
            ->delete();

        return ApiResponse::success(null, 'Password changed. Please login again.');
    }

    protected function sendPasswordResetOtp(string $email, RuntimeMailConfigService $mailConfig): JsonResponse
    {
        if (! $mailConfig->isConfigured()) {
            return ApiResponse::error('SMTP is disabled.', 422);
        }

        $normalizedEmail = strtolower(trim($email));
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if (! $user instanceof User) {
            return ApiResponse::success(null, 'If an account with that email exists, a password reset OTP has been sent.');
        }

        $mailConfig->applyFromSettings();

        $otp = (string) random_int(100000, 999999);
        $this->passwordResetQuery()->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ],
        );

        try {
            $user->notify(new PasswordResetOtpNotification($otp, self::RESET_OTP_TTL_MINUTES));
        } catch (Throwable $exception) {
            report($exception);

            return ApiResponse::error('Unable to send reset OTP right now.', 503);
        }

        return ApiResponse::success(null, 'Password reset OTP sent successfully.');
    }

    protected function passwordResetQuery()
    {
        return DB::table('password_reset_tokens');
    }

    /**
     * @return array{token_type: string, access: string, refresh: string}
     */
    protected function issueTokenPair(User $user): array
    {
        return [
            'token_type' => 'Bearer',
            'access' => $user->createToken('access_token', ['access'], now()->addMonth())->plainTextToken,
            'refresh' => $user->createToken('refresh_token', ['refresh'], now()->addYear())->plainTextToken,
        ];
    }

    private function generateUniqueUsername(string $email): string
    {
        $base = strtolower(Str::before($email, '@'));
        $base = preg_replace('/[^a-z0-9._]+/', '', $base) ?? '';
        $base = trim($base, '.');
        $base = $base !== '' ? $base : 'user';
        $base = (string) Str::of($base)->limit(40, '');

        $counter = 0;
        while (true) {
            $suffix = $counter === 0 ? '' : '_'.$counter;
            $maxBaseLength = max(1, 50 - strlen($suffix));
            $candidate = substr($base, 0, $maxBaseLength).$suffix;

            $exists = User::query()
                ->where('username', $candidate)
                ->exists();

            if (! $exists) {
                return $candidate;
            }

            $counter++;
        }
    }

    private function verificationResult(
        Request $request,
        bool $success,
        string $apiMessage,
        int $statusCode,
        string $pageTitle,
        string $pageDescription,
        string $pageState = 'error',
    ): HttpResponse {
        if ($request->expectsJson() || $request->wantsJson()) {
            return $success
                ? ApiResponse::success(null, $apiMessage, $statusCode)
                : ApiResponse::error($apiMessage, $statusCode);
        }

        $appName = app(BrandAssetService::class)->getBrandName();

        return response()->view('email.verification-status', [
            'state' => $pageState,
            'title' => $pageTitle,
            'description' => $pageDescription,
            'appName' => $appName,
            'logoUrl' => app(BrandAssetService::class)->getLogoUrl(),
        ], $statusCode);
    }
}
