<?php

namespace App\Exceptions;

use Exception;

class MailDeliveryException extends Exception
{
    public static function fromException(\Throwable $previous): self
    {
        return new self(
            'Could not send email. Please check your SMTP settings or mail server.',
            0,
            $previous
        );
    }
}
