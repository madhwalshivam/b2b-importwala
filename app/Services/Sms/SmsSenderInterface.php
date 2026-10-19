<?php

namespace App\Services\Sms;

interface SmsSenderInterface
{
    /**
     * Send a plain SMS. Return true when the provider accepted it.
     * Implementations must not log the message body (it may contain an OTP).
     */
    public function send(string $phone, string $message): bool;
}
