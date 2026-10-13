<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Cycle de vie d'une commande et transitions autorisées.
 *
 * en_attente → accepte → en_preparation → en_livraison → livre
 *     livre → attente_retour_materiel (si matériel prêté) → terminee
 *     livre → terminee (sans prêt de matériel)
 * L'annulation est possible tant que la commande n'est pas livrée.
 */
final class OrderStatus
{
    public const LABELS = [
        'en_attente'              => 'En attente de validation',
        'accepte'                 => 'Acceptée',
        'en_preparation'          => 'En préparation',
        'en_livraison'            => 'En cours de livraison',
        'livre'                   => 'Livrée',
        'attente_retour_materiel' => 'En attente du retour de matériel',
        'terminee'                => 'Terminée',
        'annulee'                 => 'Annulée',
    ];

    public const BADGES = [
        'en_attente'              => 'text-bg-warning',
        'accepte'                 => 'text-bg-dark',
        'en_preparation'          => 'text-bg-primary',
        'en_livraison'            => 'text-bg-primary',
        'livre'                   => 'text-bg-success',
        'attente_retour_materiel' => 'text-bg-danger',
        'terminee'                => 'text-bg-success',
        'annulee'                 => 'text-bg-secondary',
    ];

    private const TRANSITIONS = [
        'en_attente'              => ['accepte'],
        'accepte'                 => ['en_preparation'],
        'en_preparation'          => ['en_livraison'],
        'en_livraison'            => ['livre'],
        'livre'                   => ['attente_retour_materiel', 'terminee'],
        'attente_retour_materiel' => ['terminee'],
        'terminee'                => [],
        'annulee'                 => [],
    ];

    public static function label(string $status): string
    {
        return self::LABELS[$status] ?? $status;
    }

    public static function badge(string $status): string
    {
        return '<span class="badge ' . (self::BADGES[$status] ?? 'text-bg-secondary') . '">' . e(self::label($status)) . '</span>';
    }

    /** Statuts suivants possibles, en tenant compte du prêt de matériel. */
    public static function next(string $current, bool $materialLent): array
    {
        $next = self::TRANSITIONS[$current] ?? [];
        if ($current === 'livre') {
            $next = $materialLent ? ['attente_retour_materiel'] : ['terminee'];
        }
        return $next;
    }

    public static function canTransition(string $from, string $to, bool $materialLent): bool
    {
        return in_array($to, self::next($from, $materialLent), true);
    }

    /** Le client peut modifier / annuler tant qu'aucun employé n'a accepté la commande. */
    public static function editableByCustomer(string $status): bool
    {
        return $status === 'en_attente';
    }

    /** L'équipe peut annuler une commande tant qu'elle n'est pas livrée. */
    public static function cancellableByStaff(string $status): bool
    {
        return in_array($status, ['en_attente', 'accepte', 'en_preparation'], true);
    }

    public static function editableByStaff(string $status): bool
    {
        return in_array($status, ['en_attente', 'accepte', 'en_preparation'], true);
    }
}
