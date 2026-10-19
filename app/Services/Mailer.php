<?php

namespace App\Services;

/**
 * Sends HTML mail through SMTP (.env / config/mailer.php) when configured,
 * otherwise falls back to PHP mail(). Failures are logged, never thrown.
 */
class Mailer
{
    public static function send(string $to, string $subject, string $htmlBody): bool
    {
        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $cfgPath = dirname(__DIR__, 2) . '/config/mailer.php';
        $cfg = is_file($cfgPath) ? require $cfgPath : [];
        $host = trim((string)($cfg['host'] ?? env('MAIL_HOST', '')));

        try {
            if ($host !== '' && class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
                return self::viaPhpMailer($cfg, $to, $subject, $htmlBody);
            }
            if ($host !== '') {
                return self::viaSmtp($cfg, $to, $subject, $htmlBody);
            }
            return self::viaPhpMail($cfg, $to, $subject, $htmlBody);
        } catch (\Throwable $e) {
            error_log('Mail send failed: ' . $e->getMessage());
            return false;
        }
    }

    private static function from(array $cfg): array
    {
        $email = trim((string)($cfg['from_email'] ?? env('MAIL_FROM_ADDRESS', 'no-reply@importwale.com')));
        $name = trim((string)($cfg['from_name'] ?? env('MAIL_FROM_NAME', 'ImportWale')));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = 'no-reply@importwale.com';
        }
        if ($name === '') {
            $name = 'ImportWale';
        }
        return [$email, $name];
    }

    private static function viaPhpMailer(array $cfg, string $to, string $subject, string $htmlBody): bool
    {
        [$fromEmail, $fromName] = self::from($cfg);
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string)$cfg['host'];
        $mail->SMTPAuth = trim((string)($cfg['username'] ?? '')) !== '';
        $mail->Username = (string)($cfg['username'] ?? '');
        $mail->Password = (string)($cfg['password'] ?? '');
        $port = (int)($cfg['port'] ?? 587);
        $enc = strtolower((string)($cfg['encryption'] ?? 'tls'));
        $mail->Port = $port;
        if ($enc === 'ssl' || $port === 465) {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = trim(html_entity_decode(strip_tags($htmlBody), ENT_QUOTES, 'UTF-8'));
        $mail->send();
        return true;
    }

    private static function viaSmtp(array $cfg, string $to, string $subject, string $htmlBody): bool
    {
        [$fromEmail, $fromName] = self::from($cfg);
        $host = trim((string)$cfg['host']);
        $port = (int)($cfg['port'] ?? 587);
        $enc = strtolower((string)($cfg['encryption'] ?? 'tls'));
        $user = (string)($cfg['username'] ?? '');
        $pass = (string)($cfg['password'] ?? '');
        $useSsl = $enc === 'ssl' || $port === 465;
        $remote = ($useSsl ? 'ssl://' : 'tcp://') . $host . ':' . $port;

        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client($remote, $errno, $errstr, 20, STREAM_CLIENT_CONNECT);
        if (!$fp) {
            throw new \RuntimeException("SMTP connect failed: {$errstr} ({$errno})");
        }
        stream_set_timeout($fp, 20);

        $read = static function () use ($fp): string {
            $data = '';
            while (!feof($fp)) {
                $line = fgets($fp, 2048);
                if ($line === false) {
                    break;
                }
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            return $data;
        };
        $cmd = static function (string $command, string $expect) use ($fp, $read): void {
            fwrite($fp, $command . "\r\n");
            $resp = $read();
            if (!str_starts_with($resp, $expect)) {
                throw new \RuntimeException('SMTP error: ' . trim($resp));
            }
        };

        $banner = $read();
        if (!str_starts_with($banner, '220')) {
            fclose($fp);
            throw new \RuntimeException('SMTP banner: ' . trim($banner));
        }

        $ehloHost = preg_replace('/[^A-Za-z0-9.-]/', '', (string)($_SERVER['SERVER_NAME'] ?? 'localhost')) ?: 'localhost';
        $cmd('EHLO ' . $ehloHost, '250');

        if (!$useSsl && ($enc === 'tls' || $enc === 'starttls' || $port === 587)) {
            $cmd('STARTTLS', '220');
            $crypto = STREAM_CRYPTO_METHOD_TLS_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                $crypto = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                    $crypto |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
                }
            }
            if (!stream_socket_enable_crypto($fp, true, $crypto)) {
                fclose($fp);
                throw new \RuntimeException('SMTP STARTTLS failed');
            }
            $cmd('EHLO ' . $ehloHost, '250');
        }

        if ($user !== '') {
            $cmd('AUTH LOGIN', '334');
            $cmd(base64_encode($user), '334');
            $cmd(base64_encode($pass), '235');
        }

        $cmd('MAIL FROM:<' . $fromEmail . '>', '250');
        $cmd('RCPT TO:<' . $to . '>', '250');
        $cmd('DATA', '354');

        $headers = [
            'Date: ' . date('r'),
            'From: ' . sprintf('"%s" <%s>', addcslashes($fromName, '"'), $fromEmail),
            'To: <' . $to . '>',
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'X-Mailer: ImportWale',
        ];
        $stuffed = preg_replace('/^\./m', '..', str_replace(["\r\n", "\r"], "\n", $htmlBody));
        $stuffed = str_replace("\n", "\r\n", $stuffed);
        $body = implode("\r\n", $headers) . "\r\n\r\n" . $stuffed . "\r\n.";
        $cmd($body, '250');
        fwrite($fp, "QUIT\r\n");
        fclose($fp);
        return true;
    }

    private static function viaPhpMail(array $cfg, string $to, string $subject, string $htmlBody): bool
    {
        [$fromEmail, $fromName] = self::from($cfg);
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= 'From: ' . sprintf('"%s" <%s>', addcslashes($fromName, '"'), $fromEmail) . "\r\n";
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $ok = @mail($to, $encodedSubject, $htmlBody, $headers, '-f' . $fromEmail);
        if (!$ok) {
            throw new \RuntimeException('PHP mail() returned false');
        }
        return true;
    }
}
