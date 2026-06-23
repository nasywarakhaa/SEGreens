<?php

namespace App\Services;

use Illuminate\Mail\MailManager;

class RuntimeMailConfigService
{
    public function __construct(
        private readonly SystemSettingService $settings,
        private readonly MailManager $mailManager,
    ) {}

    public function isConfigured(): bool
    {
        if ($this->settings->getBool('smtp', 'enabled', false)) {
            return (string) $this->settings->get('smtp', 'host') !== ''
                && (string) $this->settings->get('smtp', 'from_address') !== '';
        }

        return config('mail.default') === 'smtp'
            && (string) config('mail.mailers.smtp.host') !== ''
            && (string) config('mail.from.address') !== '';
    }

    public function applyFromSettings(): void
    {
        if (! $this->settings->getBool('smtp', 'enabled', false)) {
            return;
        }

        $encryption = strtolower((string) $this->settings->get('smtp', 'encryption', ''));
        $scheme = match ($encryption) {
            'ssl', 'smtps' => 'smtps',
            default => null,
        };

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $this->settings->get('smtp', 'host'),
            'mail.mailers.smtp.port' => (int) $this->settings->get('smtp', 'port', 587),
            'mail.mailers.smtp.username' => $this->settings->get('smtp', 'username'),
            'mail.mailers.smtp.password' => $this->settings->get('smtp', 'password'),
            'mail.mailers.smtp.timeout' => max(1, (int) $this->settings->get('smtp', 'timeout', config('mail.mailers.smtp.timeout', 10))),
            'mail.mailers.smtp.scheme' => $scheme,
            'mail.from.address' => $this->settings->get('smtp', 'from_address', config('mail.from.address')),
            'mail.from.name' => $this->settings->get('smtp', 'from_name', config('mail.from.name')),
        ]);

        // Ensure updated runtime SMTP config is used immediately.
        $this->mailManager->forgetMailers();
    }
}
