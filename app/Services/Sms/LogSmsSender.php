<?php

namespace App\Services\Sms;

/**
 * Stub used when no SMS provider is configured.
 * Records that a message was queued without writing the body.
 */
class LogSmsSender implements SmsSenderInterface
{
    public function send(string $phone, string $message): bool
    {
        $tail = substr(preg_replace('/\D+/', '', $phone), -4);
        error_log('SMS stub: message queued for number ending ' . $tail . ' (no provider configured)');
        return true;
    }
}
