<?php

namespace App\Services;

use App\Exceptions\MailDeliveryException;
use App\Models\User;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Throwable;

class UserMailer
{
    public static function send(User $fromUser, string $to, Mailable $mailable): void
    {
        try {
            if (! $fromUser->hasSmtpConfig()) {
                Mail::to($to)->send($mailable);

                return;
            }

            $mailer = self::buildMailer($fromUser);
            $mailer->to($to)->send($mailable);
        } catch (Throwable $e) {
            if ($e instanceof MailDeliveryException) {
                throw $e;
            }

            throw MailDeliveryException::fromException($e);
        }
    }

    public static function buildMailer(User $user): Mailer
    {
        if (! $user->hasSmtpConfig()) {
            throw new InvalidArgumentException('User does not have an SMTP configuration.');
        }

        $config = [
            'transport' => 'smtp',
            'host' => $user->smtp_host,
            'port' => $user->smtp_port,
            'encryption' => $user->smtp_encryption,
            'username' => $user->smtp_username,
            'password' => $user->smtp_password,
        ];

        $transport = app('mail.manager')->createSymfonyTransport($config);

        $mailer = new Mailer(
            'user-smtp',
            app('view'),
            $transport,
            app('events')
        );

        if ($user->smtp_from_address) {
            $mailer->alwaysFrom($user->smtp_from_address, $user->smtp_from_name);
        }

        return $mailer;
    }
}
