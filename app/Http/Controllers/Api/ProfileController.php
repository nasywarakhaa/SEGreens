<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateFcmTokenRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\Api\ProfileResource;
use App\Jobs\SendVerifyEmailNotificationJob;
use App\Models\User;
use App\Services\RuntimeMailConfigService;
use App\Services\SystemSettingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;
use Throwable;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return $this->profileResponse($request->user());
    }

    public function update(
        UpdateProfileRequest $request,
        SystemSettingService $settings,
        RuntimeMailConfigService $mailConfig,
    ): JsonResponse {
        $user = $request->user();

        $validated = $request->validated();
        $newEmail = $validated['email'] ?? null;
        $emailChanged = is_string($newEmail) && strcasecmp((string) $user->email, $newEmail) !== 0;
        $requireVerification = $settings->getBool('app', 'require_email_verification', true);

        if ($emailChanged) {
            $validated['email_verified_at'] = $requireVerification ? null : now();
        }

        $user->update($validated);

        if ($emailChanged && $requireVerification && $mailConfig->isConfigured()) {
            $mailConfig->applyFromSettings();

            try {
                SendVerifyEmailNotificationJob::dispatch((string) $user->id);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $message = $emailChanged && $requireVerification
            ? 'Profile updated. Please verify your new email address.'
            : 'Profile updated.';

        return $this->profileResponse($user->fresh(), $message);
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', File::image()->max(5120)],
        ]);

        $user = $request->user();
        $path = (string) $request->file('avatar')->store('users/avatars', 'public');
        $user->update(['avatar' => $path]);

        return $this->profileResponse($user->fresh(), 'Avatar updated.');
    }

    public function updateFcmToken(UpdateFcmTokenRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['fcm_token' => $request->input('fcm_token')]);

        return $this->profileResponse($user->fresh(), 'FCM token updated.');
    }

    private function profileResponse(User $user, string $message = 'OK'): JsonResponse
    {
        $user->load([
            'addresses' => fn ($query) => $query
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderBy('id'),
        ]);

        return ApiResponse::success(new ProfileResource($user), $message);
    }
}
