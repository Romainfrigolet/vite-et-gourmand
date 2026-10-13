<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Validator;

/**
 * Lecture et validation du formulaire de prestation (création et modification de commande),
 * puis calcul du prix définitif côté serveur.
 */
final class OrderForm
{
    public const FIELDS = ['date_prestation', 'heure_livraison', 'adresse_prestation', 'code_postal_prestation',
                           'ville_prestation', 'nombre_personne'];

    public static function input(): array
    {
        $data = [];
        foreach (self::FIELDS as $field) {
            $data[$field] = (string) input($field, '');
        }
        return $data;
    }

    /**
     * @return array{errors: array, data: array, prices: ?array}
     */
    public static function process(array $data, array $menu, bool $enforceDelay = true): array
    {
        $min = (int) $menu['nombre_personne_minimum'];
        $v = Validator::make($data)
            ->required('date_prestation', 'Date de la prestation')->date('date_prestation', 'Date de la prestation')
            ->required('heure_livraison', 'Heure de livraison')->time('heure_livraison', 'Heure de livraison')
            ->required('adresse_prestation', 'Adresse de la prestation')->max('adresse_prestation', 255, 'Adresse')
            ->required('code_postal_prestation', 'Code postal')->postalCode('code_postal_prestation')
            ->required('ville_prestation', 'Ville')->max('ville_prestation', 100, 'Ville')
            ->required('nombre_personne', 'Nombre de personnes')->integer('nombre_personne', 'Nombre de personnes', $min, 1000);

        if (!$v->fails()) {
            $earliest = (new \DateTimeImmutable('today'))->modify('+' . (int) $menu['delai_commande_jours'] . ' days');
            if ($enforceDelay && $data['date_prestation'] < $earliest->format('Y-m-d')) {
                $v->addError('date_prestation', sprintf(
                    'Ce menu doit être commandé %d jours à l\'avance : la prestation doit avoir lieu le %s au plus tôt.',
                    (int) $menu['delai_commande_jours'],
                    $earliest->format('d/m/Y')
                ));
            } elseif ($data['date_prestation'] < date('Y-m-d')) {
                $v->addError('date_prestation', 'La date de prestation ne peut pas être dans le passé.');
            }
            $hour = substr($data['heure_livraison'], 0, 5);
            if ($hour < '08:00' || $hour > '22:00') {
                $v->addError('heure_livraison', 'Nos livraisons ont lieu entre 8h00 et 22h00.');
            }
        }

        if ($v->fails()) {
            return ['errors' => $v->errors(), 'data' => $data, 'prices' => null];
        }

        $location = DeliveryService::locate($data['adresse_prestation'], $data['code_postal_prestation'], $data['ville_prestation']);
        if (!$location['ok']) {
            return ['errors' => ['ville_prestation' => $location['error']], 'data' => $data, 'prices' => null];
        }

        $data['distance_km'] = $location['distance_km'];
        $data['heure_livraison'] = substr($data['heure_livraison'], 0, 5);
        $prices = PricingService::quote($menu, (int) $data['nombre_personne'], $location['bordeaux'], $location['distance_km']);
        $prices['bordeaux'] = $location['bordeaux'];
        $prices['distance_km'] = $location['distance_km'];

        return ['errors' => [], 'data' => $data, 'prices' => $prices];
    }
}
