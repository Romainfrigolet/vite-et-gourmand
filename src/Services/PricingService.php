<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Règles de gestion du prix d'une commande (cahier des charges) :
 *  - prix du menu = prix par personne × nombre de personnes
 *  - le nombre de personnes ne peut être inférieur au minimum du menu
 *  - remise de 10 % si le nombre de personnes ≥ minimum + 5
 *  - livraison gratuite à Bordeaux, sinon 5 € + 0,59 € par kilomètre
 * Le calcul est TOUJOURS refait côté serveur : le prix affiché en JavaScript n'est qu'indicatif.
 */
final class PricingService
{
    public const DISCOUNT_RATE = 0.10;
    public const DISCOUNT_EXTRA_PEOPLE = 5;
    public const DELIVERY_BASE = 5.00;
    public const DELIVERY_PER_KM = 0.59;

    /**
     * @return array{prix_menu: float, remise: float, prix_livraison: float, prix_total: float, remise_appliquee: bool}
     */
    public static function quote(array $menu, int $people, bool $inBordeaux, float $distanceKm): array
    {
        $min = (int) $menu['nombre_personne_minimum'];
        if ($people < $min) {
            throw new \InvalidArgumentException("Ce menu se commande pour $min personnes minimum.");
        }

        $menuPrice = round((float) $menu['prix_par_personne'] * $people, 2);
        $discountApplies = $people >= $min + self::DISCOUNT_EXTRA_PEOPLE;
        $discount = $discountApplies ? round($menuPrice * self::DISCOUNT_RATE, 2) : 0.0;
        $delivery = self::deliveryPrice($inBordeaux, $distanceKm);

        return [
            'prix_menu'        => $menuPrice,
            'remise'           => $discount,
            'prix_livraison'   => $delivery,
            'prix_total'       => round($menuPrice - $discount + $delivery, 2),
            'remise_appliquee' => $discountApplies,
        ];
    }

    public static function deliveryPrice(bool $inBordeaux, float $distanceKm): float
    {
        if ($inBordeaux) {
            return 0.0;
        }
        return round(self::DELIVERY_BASE + self::DELIVERY_PER_KM * max(0, $distanceKm), 2);
    }
}
