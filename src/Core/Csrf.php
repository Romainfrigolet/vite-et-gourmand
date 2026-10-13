<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Protection contre la falsification de requête inter-sites (CSRF).
 * Un jeton aléatoire est stocké en session et doit accompagner
 * chaque requête POST (champ caché _csrf ou en-tête X-CSRF-Token).
 */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function verify(?string $token): bool
    {
        return is_string($token)
            && !empty($_SESSION['_csrf'])
            && hash_equals($_SESSION['_csrf'], $token); // comparaison à temps constant
    }
}
