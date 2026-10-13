<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Mongo;
use MongoDB\BSON\UTCDateTime;

/**
 * Statistiques de commandes stockées dans MongoDB (base NoSQL).
 *
 * MySQL reste la source de vérité ; chaque commande créée ou modifiée est copiée
 * (dénormalisée) dans la collection « commandes_stats ». Les agrégations de l'espace
 * administrateur (nombre de commandes et chiffre d'affaires par menu) sont calculées
 * par MongoDB, sans solliciter la base transactionnelle.
 */
final class StatsRepository
{
    public const COLLECTION = 'commandes_stats';

    /** Copie une commande dans MongoDB. Un échec n'empêche jamais la commande d'aboutir. */
    public static function sync(int $commandeId): void
    {
        try {
            $row = CommandeRepository::forStats($commandeId);
            if ($row) {
                Mongo::upsert(self::COLLECTION, ['commande_id' => $commandeId], self::document($row));
            }
        } catch (\Throwable $e) {
            log_error('Synchronisation MongoDB impossible pour la commande ' . $commandeId . ' : ' . $e->getMessage());
        }
    }

    /** Reconstruit entièrement la collection à partir de MySQL (script bin/sync-stats.php). */
    public static function rebuild(): int
    {
        Mongo::deleteAll(self::COLLECTION);
        $rows = CommandeRepository::allForStats();
        foreach ($rows as $row) {
            Mongo::upsert(self::COLLECTION, ['commande_id' => (int) $row['commande_id']], self::document($row));
        }
        return count($rows);
    }

    private static function document(array $row): array
    {
        return [
            'commande_id'     => (int) $row['commande_id'],
            'numero'          => $row['numero_commande'],
            'menu_id'         => (int) $row['menu_id'],
            'menu_titre'      => $row['menu_titre'],
            'date_commande'   => new UTCDateTime(strtotime($row['date_commande']) * 1000),
            'date_prestation' => $row['date_prestation'],
            'nombre_personne' => (int) $row['nombre_personne'],
            'prix_menu'       => (float) $row['prix_menu'],
            'remise'          => (float) $row['remise'],
            'prix_livraison'  => (float) $row['prix_livraison'],
            'prix_total'      => (float) $row['prix_total'],
            // Chiffre d'affaires « menu » de la commande : prix du menu remise déduite (hors livraison)
            'chiffre_affaires' => round((float) $row['prix_menu'] - (float) $row['remise'], 2),
            'statut'          => $row['statut'],
        ];
    }

    /** Filtre commun : commandes non annulées, éventuellement sur un menu et une période. */
    private static function match(?int $menuId, ?string $from, ?string $to): array
    {
        $match = ['statut' => ['$ne' => 'annulee']];
        if ($menuId) {
            $match['menu_id'] = $menuId;
        }
        $range = [];
        if ($from) {
            $range['$gte'] = new UTCDateTime(strtotime($from . ' 00:00:00') * 1000);
        }
        if ($to) {
            $range['$lte'] = new UTCDateTime(strtotime($to . ' 23:59:59') * 1000);
        }
        if ($range) {
            $match['date_commande'] = $range;
        }
        return $match;
    }

    /**
     * Nombre de commandes et chiffre d'affaires par menu.
     * Le chiffre d'affaires retenu est celui des menus (hors frais de livraison), remise déduite.
     *
     * @return array<int, array{menu_id: int, menu_titre: string, nb_commandes: int, chiffre_affaires: float, panier_moyen: float}>
     */
    public static function byMenu(?int $menuId = null, ?string $from = null, ?string $to = null): array
    {
        $rows = Mongo::aggregate(self::COLLECTION, [
            ['$match' => self::match($menuId, $from, $to)],
            ['$group' => [
                '_id'              => '$menu_id',
                'nb_commandes'     => ['$sum' => 1],
                'chiffre_affaires' => ['$sum' => '$chiffre_affaires'],
                'total_encaisse'   => ['$sum' => '$prix_total'],
            ]],
            ['$sort' => ['nb_commandes' => -1]],
        ]);

        // Les titres à jour des menus proviennent de la base relationnelle
        $titles = array_column(MenuRepository::options(), 'titre', 'menu_id');

        $stats = array_map(static fn (array $r): array => [
            'menu_id'          => (int) $r['_id'],
            'menu_titre'       => (string) ($titles[(int) $r['_id']] ?? 'Menu #' . $r['_id']),
            'nb_commandes'     => (int) $r['nb_commandes'],
            'chiffre_affaires' => round((float) $r['chiffre_affaires'], 2),
            'total_encaisse'   => round((float) $r['total_encaisse'], 2),
            'panier_moyen'     => $r['nb_commandes'] > 0 ? round((float) $r['chiffre_affaires'] / $r['nb_commandes'], 2) : 0.0,
        ], $rows);

        usort($stats, static fn (array $a, array $b): int => [$b['nb_commandes'], $a['menu_titre']] <=> [$a['nb_commandes'], $b['menu_titre']]);
        return $stats;
    }
}
