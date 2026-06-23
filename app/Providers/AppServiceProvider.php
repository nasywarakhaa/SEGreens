<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use App\Models\Store;
use App\Models\SystemSetting;
use App\Services\BrandAssetService;
use App\Services\SystemSettingService;
use Filament\Actions\Action;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            $appName = app(BrandAssetService::class)->getBrandName();

            $expiresInMinutes = max(1, (int) config('auth.verification.expire', 60));

            return (new MailMessage)
                ->subject($appName.' - Verifikasi Alamat Email')
                ->greeting('Halo!')
                ->line('Terima kasih sudah mendaftar di '.$appName.'.')
                ->line('Silakan verifikasi alamat email Anda untuk mengaktifkan akun.')
                ->action('Verifikasi Email', $url)
                ->line('Tautan verifikasi ini berlaku selama '.$expiresInMinutes.' menit.')
                ->line('Jika Anda tidak membuat akun ini, Anda bisa abaikan email ini.')
                ->salutation("Salam,\nTim {$appName}");
        });

        ResetPassword::createUrlUsing(function (object $user, string $token): string {
            $baseUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');

            return sprintf(
                '%s/reset-password?token=%s&email=%s',
                $baseUrl,
                rawurlencode($token),
                rawurlencode($user->getEmailForPasswordReset()),
            );
        });

        SchemaComponent::configureUsing(function (SchemaComponent $component): SchemaComponent {
            if (method_exists($component, 'translateLabel')) {
                $component->translateLabel();
            }

            return $component;
        });

        Column::configureUsing(function (Column $column): Column {
            if (method_exists($column, 'translateLabel')) {
                $column->translateLabel();
            }

            return $column;
        });

        BaseFilter::configureUsing(function (BaseFilter $filter): BaseFilter {
            if (method_exists($filter, 'translateLabel')) {
                $filter->translateLabel();
            }

            return $filter;
        });

        Action::configureUsing(function (Action $action): Action {
            if (method_exists($action, 'translateLabel')) {
                $action->translateLabel();
            }

            return $action;
        });

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        SystemSetting::saved(function () {
            app(SystemSettingService::class)->clearCache();
        });

        SystemSetting::deleted(function () {
            app(SystemSettingService::class)->clearCache();
        });

        Store::saved(function () {
            app(BrandAssetService::class)->clearCache();
        });

        Store::deleted(function () {
            app(BrandAssetService::class)->clearCache();
        });

        RateLimiter::for('api-mobile-public', function (Request $request): Limit {
            $perMinute = (int) config('api.rate_limits.public_per_minute', 120);
            $by = 'public:'.$request->ip();

            return Limit::perMinute(max(1, $perMinute))->by($by);
        });

        RateLimiter::for('api-mobile-auth', function (Request $request): Limit {
            $perMinute = (int) config('api.rate_limits.authenticated_per_minute', 180);
            $userId = (string) ($request->user()?->id ?? 'guest');
            $by = 'auth:'.$userId.'|'.$request->ip();

            return Limit::perMinute(max(1, $perMinute))->by($by);
        });

        RateLimiter::for('api-auth-sensitive', function (Request $request): Limit {
            $perMinute = (int) config('api.rate_limits.auth_sensitive_per_minute', 20);
            $identifier = strtolower((string) (
                $request->input('identifier')
                ?? $request->input('email')
                ?? ''
            ));

            $by = 'auth-sensitive:'.$request->ip().'|'.$identifier;

            return Limit::perMinute(max(1, $perMinute))->by($by);
        });

        RateLimiter::for('api-midtrans-webhook', function (Request $request): Limit {
            $perMinute = (int) config('api.rate_limits.midtrans_webhook_per_minute', 120);

            return Limit::perMinute(max(1, $perMinute))->by('midtrans-webhook:'.$request->ip());
        });
    }
}
