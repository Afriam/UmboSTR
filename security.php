<?php
declare(strict_types=1);

/** Small helpers shared by the Register and Admin modules. */
final class Security
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_name('umbo_sid');
        session_start();
    }

    public static function csrfToken(): string
    {
        self::startSession();
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        self::startSession();
        return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
    }

    public static function clientIp(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }

    public static function clean(mixed $value, int $max): string
    {
        $text = trim((string) $value);
        $text = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);
        return mb_substr($text, 0, $max);
    }

    public static function noCacheHeaders(): void
    {
        header('Cache-Control: no-store');
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
    }
}
