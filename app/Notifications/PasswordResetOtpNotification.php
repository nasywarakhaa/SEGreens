<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $otp,
        public readonly int $expiresInMinutes = 10,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = $this->appName();

        return (new MailMessage)
            ->subject($appName.' - Kode OTP Reset Password')
            ->greeting('Halo!')
            ->line('Kami menerima permintaan reset password untuk akun Anda di '.$appName.'.')
            ->line('Kode OTP Anda: '.$this->otp)
            ->line('Kode OTP berlaku selama '.$this->expiresInMinutes.' menit.')
            ->line('Jangan bagikan kode OTP ini kepada siapa pun.')
            ->line('Jika Anda tidak meminta reset password, abaikan email ini.')
            ->salutation("Salam,\nTim {$appName}");
    }

    private function appName(): string
    {
        $name = trim((string) (config('mail.from.name') ?: config('app.name', '')));
        if ($name === '' || strcasecmp($name, 'Laravel') === 0) {
            return 'SEGreens';
        }

        return $name;
    }
}
