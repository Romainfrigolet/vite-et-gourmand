<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\UserRepository;

/**
 * Authentification et contrôle des rôles.
 * Hiérarchie : administrateur ⊃ employe ; utilisateur = client.
 */
final class Auth
{
    public const ROLE_USER = 'utilisateur';
    public const ROLE_EMPLOYEE = 'employe';
    public const ROLE_ADMIN = 'administrateur';

    private static ?array $user = null;
    private static bool $loaded = false;

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', (int) $user['utilisateur_id']);
        self::$user = null;
        self::$loaded = false;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$user = null;
        self::$loaded = true;
    }

    /** Utilisateur connecté, rechargé depuis la base à chaque requête (un compte désactivé est déconnecté immédiatement). */
    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = Session::get('user_id');
            if ($id) {
                $user = UserRepository::findById((int) $id);
                if ($user && (int) $user['actif'] === 1) {
                    self::$user = $user;
                } else {
                    Session::forget('user_id');
                }
            }
        }
        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        return self::user() ? (int) self::user()['utilisateur_id'] : null;
    }

    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === self::ROLE_ADMIN;
    }

    /** Employé ou administrateur (l'administrateur peut tout faire comme un employé). */
    public static function isStaff(): bool
    {
        return in_array(self::role(), [self::ROLE_EMPLOYEE, self::ROLE_ADMIN], true);
    }

    public static function isCustomer(): bool
    {
        return self::role() === self::ROLE_USER;
    }
}
