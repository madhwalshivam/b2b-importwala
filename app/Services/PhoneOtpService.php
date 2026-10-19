<?php

namespace App\Services;

use App\Core\Database;
use App\Services\Sms\LogSmsSender;
use App\Services\Sms\SmsSenderInterface;

class PhoneOtpService
{
    public const SEND_LIMIT = 3;
    public const WINDOW_SECONDS = 900;
    public const RESEND_COOLDOWN = 60;

    public static function otpRequired(): bool
    {
        return (string)\App\Models\Setting::get('require_phone_otp', '0') === '1';
    }

    public static function send(string $phone): array
    {
        AuthSchema::ensure();
        $bucket = 'otp:' . $phone;
        if (RateLimiter::tooMany($bucket, self::SEND_LIMIT, self::WINDOW_SECONDS)) {
            return ['ok' => false, 'error' => 'Too many codes sent to this number. Please wait 15 minutes.'];
        }

        $db = Database::getInstance();
        $recent = $db->prepare("SELECT created_at FROM phone_otps WHERE phone = ? ORDER BY id DESC LIMIT 1");
        $recent->execute([$phone]);
        $last = $recent->fetchColumn();
        if ($last && (time() - strtotime((string)$last)) < self::RESEND_COOLDOWN) {
            $wait = self::RESEND_COOLDOWN - (time() - strtotime((string)$last));
            return ['ok' => false, 'error' => 'Please wait ' . max(1, $wait) . ' seconds before requesting another code.'];
        }

        $code = (string)random_int(100000, 999999);
        $db->prepare("INSERT INTO phone_otps (phone, otp_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))")
            ->execute([$phone, password_hash($code, PASSWORD_DEFAULT)]);

        $sender = self::sender();
        $sent = $sender->send($phone, 'Your ImportWale verification code is ' . $code . '. It expires in 10 minutes.');
        if (!$sent) {
            return ['ok' => false, 'error' => 'We could not send the verification code. Please try again.'];
        }

        $result = ['ok' => true, 'cooldown' => self::RESEND_COOLDOWN];
        if (self::exposeCodeLocally()) {
            $result['debug_code'] = $code;
        }
        return $result;
    }

    public static function verify(string $phone, string $code): array
    {
        AuthSchema::ensure();
        $code = preg_replace('/\D+/', '', $code) ?? '';
        if (strlen($code) !== 6) {
            return ['ok' => false, 'error' => 'Enter the 6-digit code we sent to your mobile.'];
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM phone_otps WHERE phone = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
        $stmt->execute([$phone]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return ['ok' => false, 'error' => 'That code has expired. Request a new one.'];
        }
        if ((int)$row['attempts'] >= 5) {
            return ['ok' => false, 'error' => 'Too many incorrect attempts. Request a new code.'];
        }
        if (!password_verify($code, $row['otp_hash'])) {
            $db->prepare("UPDATE phone_otps SET attempts = attempts + 1 WHERE id = ?")->execute([$row['id']]);
            return ['ok' => false, 'error' => 'That code is incorrect.'];
        }

        $db->prepare("DELETE FROM phone_otps WHERE phone = ?")->execute([$phone]);
        return ['ok' => true];
    }

    private static function sender(): SmsSenderInterface
    {
        $class = env('SMS_SENDER_CLASS', '');
        if (is_string($class) && $class !== '' && class_exists($class) && is_subclass_of($class, SmsSenderInterface::class)) {
            return new $class();
        }
        return new LogSmsSender();
    }

    private static function exposeCodeLocally(): bool
    {
        return env('APP_ENV', 'local') === 'local' && filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
    }
}
