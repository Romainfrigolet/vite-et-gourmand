<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Limitation des tentatives de connexion (protection contre la force brute).
 * Au-delà de 5 échecs en 15 minutes pour un même e-mail ou une même IP,
 * la connexion est temporairement bloquée.
 */
final class RateLimiter
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_MINUTES = 15;

    public static function tooManyAttempts(string $email): bool
    {
        $row = Database::fetch(
            'SELECT COUNT(*) AS nb FROM login_attempt
             WHERE (email = :email OR ip_address = :ip)
               AND attempted_at > (NOW() - INTERVAL ' . self::WINDOW_MINUTES . ' MINUTE)',
            ['email' => mb_strtolower($email), 'ip' => client_ip()]
        );
        return (int) $row['nb'] >= self::MAX_ATTEMPTS * 2
            || self::countForEmail($email) >= self::MAX_ATTEMPTS;
    }

    private static function countForEmail(string $email): int
    {
        $row = Database::fetch(
            'SELECT COUNT(*) AS nb FROM login_attempt
             WHERE email = :email AND attempted_at > (NOW() - INTERVAL ' . self::WINDOW_MINUTES . ' MINUTE)',
            ['email' => mb_strtolower($email)]
        );
        return (int) $row['nb'];
    }

    public static function hit(string $email): void
    {
        Database::run(
            'INSERT INTO login_attempt (email, ip_address) VALUES (:email, :ip)',
            ['email' => mb_strtolower($email), 'ip' => client_ip()]
        );
    }

    public static function clear(string $email): void
    {
        Database::run('DELETE FROM login_attempt WHERE email = :email', ['email' => mb_strtolower($email)]);
    }
}
