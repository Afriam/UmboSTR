<?php
declare(strict_types=1);

/**
 * MODEL (Admin)
 * Admin account (data/admin.json) and login throttling (data/attempts.json).
 * Only a password HASH is stored, never the plain password.
 */
class AdminModel
{
    private const MAX_FAILURES = 5;
    private const LOCK_SECONDS = 900; // 15 minutes

    private string $accountFile;
    private string $attemptsFile;

    public function __construct()
    {
        $this->accountFile  = __DIR__ . '/../data/admin.json';
        $this->attemptsFile = __DIR__ . '/../data/attempts.json';
    }

    private function account(): ?array
    {
        if (!is_file($this->accountFile)) {
            return null;
        }
        $data = json_decode((string) file_get_contents($this->accountFile), true);
        return is_array($data) && isset($data['username'], $data['password_hash']) ? $data : null;
    }

    private function saveAccount(array $account): bool
    {
        return file_put_contents($this->accountFile, json_encode($account, JSON_PRETTY_PRINT), LOCK_EX) !== false;
    }

    public function accountExists(): bool
    {
        return $this->account() !== null;
    }

    public function verify(string $username, string $password): bool
    {
        $account = $this->account();
        if ($account === null) {
            return false;
        }
        // Check both values every time so the response time does not reveal which one was wrong.
        $userOk = hash_equals((string) $account['username'], $username);
        $passOk = password_verify($password, (string) $account['password_hash']);
        if (!($userOk && $passOk)) {
            return false;
        }
        if (password_needs_rehash((string) $account['password_hash'], PASSWORD_DEFAULT)) {
            $account['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            $this->saveAccount($account);
        }
        return true;
    }

    public function verifyPassword(string $password): bool
    {
        $account = $this->account();
        return $account !== null && password_verify($password, (string) $account['password_hash']);
    }

    public function changePassword(string $newPassword): bool
    {
        $account = $this->account();
        if ($account === null) {
            return false;
        }
        $account['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->saveAccount($account);
    }

    /* ---------- login throttling (per IP address) ---------- */
    private function attempts(): array
    {
        $data = is_file($this->attemptsFile) ? json_decode((string) file_get_contents($this->attemptsFile), true) : [];
        return is_array($data) ? $data : [];
    }

    private function saveAttempts(array $data): void
    {
        @file_put_contents($this->attemptsFile, json_encode($data), LOCK_EX);
    }

    /** Seconds the visitor must still wait, or 0 if login is allowed. */
    public function lockedFor(string $ip): int
    {
        $recent = array_values(array_filter($this->attempts()[$ip] ?? [], fn ($t) => $t > time() - self::LOCK_SECONDS));
        if (count($recent) < self::MAX_FAILURES) {
            return 0;
        }
        return max(0, $recent[0] + self::LOCK_SECONDS - time());
    }

    public function recordFailure(string $ip): void
    {
        $data = $this->attempts();
        $data[$ip] = array_values(array_filter($data[$ip] ?? [], fn ($t) => $t > time() - self::LOCK_SECONDS));
        $data[$ip][] = time();
        $this->saveAttempts($data);
    }

    public function clearFailures(string $ip): void
    {
        $data = $this->attempts();
        unset($data[$ip]);
        $this->saveAttempts($data);
    }
}
