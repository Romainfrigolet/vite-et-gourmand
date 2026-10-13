<?php

declare(strict_types=1);

namespace App\Core;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;

/**
 * Accès à la base NoSQL (MongoDB) via l'extension native ext-mongodb.
 * Utilisée pour les statistiques de l'espace administrateur
 * (nombre de commandes et chiffre d'affaires par menu).
 */
final class Mongo
{
    private static ?Manager $manager = null;

    public static function manager(): Manager
    {
        if (self::$manager === null) {
            self::$manager = new Manager(
                Env::get('MONGO_URI', 'mongodb://127.0.0.1:27017'),
                ['serverSelectionTimeoutMS' => 3000]
            );
        }
        return self::$manager;
    }

    public static function database(): string
    {
        return Env::get('MONGO_DB', 'vite_gourmand');
    }

    /** Insère ou remplace un document identifié par $filter. */
    public static function upsert(string $collection, array $filter, array $document): void
    {
        $bulk = new BulkWrite();
        $bulk->update($filter, ['$set' => $document], ['upsert' => true]);
        self::manager()->executeBulkWrite(self::database() . '.' . $collection, $bulk);
    }

    public static function deleteAll(string $collection): void
    {
        $bulk = new BulkWrite();
        $bulk->delete([]);
        self::manager()->executeBulkWrite(self::database() . '.' . $collection, $bulk);
    }

    /** Exécute un pipeline d'agrégation et retourne les documents sous forme de tableaux. */
    public static function aggregate(string $collection, array $pipeline): array
    {
        $command = new Command([
            'aggregate' => $collection,
            'pipeline'  => $pipeline,
            'cursor'    => new \stdClass(),
        ]);
        $cursor = self::manager()->executeReadCommand(self::database(), $command);
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
        return $cursor->toArray();
    }
}
