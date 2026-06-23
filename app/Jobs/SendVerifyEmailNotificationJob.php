<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\RuntimeMailConfigService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendVerifyEmailNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public readonly string $userId) {}

    public function handle(RuntimeMailConfigService $mailConfig): void
    {
        $user = User::query()->find($this->userId);
        if (! $user || $user->hasVerifiedEmail()) {
            return;
        }

        if ($mailConfig->isConfigured()) {
            $mailConfig->applyFromSettings();
        }

        $user->sendEmailVerificationNotification();
    }
}
