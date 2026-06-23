<?php

use App\Services\FcmService;
use App\Services\MidtransService;
use App\Services\RuntimeMailConfigService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('midtrans:reconcile {--limit=50}', function (MidtransService $midtrans): int {
    $limit = (int) $this->option('limit');
    $limit = max(1, $limit);

    try {
        $result = $midtrans->reconcilePendingOrders($limit);
    } catch (RuntimeException $exception) {
        $this->error($exception->getMessage());

        return 1;
    }

    $this->info('Midtrans reconciliation completed.');
    $this->line('Processed: '.$result['processed']);
    $this->line('Updated: '.$result['updated']);
    $this->line('Skipped: '.$result['skipped']);
    $this->line('Failed: '.$result['failed']);

    return 0;
})->purpose('Reconcile pending Midtrans transactions with local orders');

Artisan::command('integrations:test-smtp {to?}', function (RuntimeMailConfigService $mailConfig): int {
    if (! $mailConfig->isConfigured()) {
        $this->error('SMTP is not configured.');

        return 1;
    }

    $mailConfig->applyFromSettings();

    $to = trim((string) ($this->argument('to') ?: config('mail.from.address', '')));
    if ($to === '') {
        $this->error('Recipient email is required. Provide {to} or configure smtp.from_address.');

        return 1;
    }

    try {
        Mail::raw('SEGreens SMTP test email sent at '.now()->toDateTimeString(), function ($message) use ($to): void {
            $message->to($to)
                ->subject('SEGreens SMTP Test');
        });
    } catch (Throwable $exception) {
        $this->error($exception->getMessage());

        return 1;
    }

    $this->info('SMTP test email sent to '.$to.'.');

    return 0;
})->purpose('Send a test email using runtime SMTP configuration');

Artisan::command('integrations:test-fcm {token?}', function (FcmService $fcm): int {
    $token = trim((string) $this->argument('token'));

    try {
        $fcm->assertConfigured();
    } catch (RuntimeException $exception) {
        $this->error($exception->getMessage());

        return 1;
    }

    if ($token === '') {
        $this->info('FCM configuration is valid. Provide {token} to perform a push send test.');

        return 0;
    }

    try {
        $fcm->sendToToken(
            token: $token,
            title: 'SEGreens FCM Test',
            body: 'FCM configuration is working.',
            data: [
                'type' => 'integration_test',
                'sent_at' => now()->toIso8601String(),
            ],
        );
    } catch (RuntimeException $exception) {
        $this->error($exception->getMessage());

        return 1;
    }

    $this->info('FCM test notification sent.');

    return 0;
})->purpose('Validate FCM configuration and optionally send a test push notification');

Schedule::command('midtrans:reconcile --limit=50')
    ->everyMinute()
    ->withoutOverlapping();
