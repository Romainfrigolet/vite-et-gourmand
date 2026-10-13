<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class ContactRepository
{
    public static function create(string $titre, string $description, string $email): void
    {
        Database::run(
            'INSERT INTO contact_message (titre, description, email) VALUES (:t, :d, :e)',
            ['t' => $titre, 'd' => $description, 'e' => mb_strtolower($email)]
        );
    }
}
