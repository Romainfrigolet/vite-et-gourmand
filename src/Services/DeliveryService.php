<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Détermine si l'adresse de prestation est à Bordeaux et, sinon, la distance depuis Bordeaux.
 *
 * 1. Géocodage de l'adresse via l'API publique de la Base Adresse Nationale (gratuite, sans clé)
 * 2. Distance « à vol d'oiseau » (formule de haversine) entre la mairie de Bordeaux et l'adresse
 * 3. Si l'API est indisponible : repli sur une table locale des principales communes de Gironde
 */
final class DeliveryService
{
    /** Point de départ : Hôtel de ville de Bordeaux */
    private const ORIGIN = [44.8378, -0.5792];

    private const BORDEAUX_POSTCODES = ['33000', '33100', '33200', '33300', '33800'];

    private const GEOCODERS = [
        'https://data.geopf.fr/geocodage/search',
        'https://api-adresse.data.gouv.fr/search/',
    ];

    /** Repli hors ligne : coordonnées des centres-villes [lat, lon] */
    private const FALLBACK = [
        'pessac' => [44.8067, -0.6311], 'merignac' => [44.8386, -0.6436], 'talence' => [44.8025, -0.5885],
        'begles' => [44.8086, -0.5478], 'le bouscat' => [44.8650, -0.5996], 'bruges' => [44.8833, -0.6111],
        'cenon' => [44.8578, -0.5317], 'floirac' => [44.8364, -0.5283], 'lormont' => [44.8792, -0.5217],
        'eysines' => [44.8836, -0.6500], 'gradignan' => [44.7725, -0.6156], 'villenave-d-ornon' => [44.7806, -0.5658],
        'blanquefort' => [44.9103, -0.6364], 'le haillan' => [44.8725, -0.6772], 'saint-medard-en-jalles' => [44.8967, -0.7172],
        'saint-medard-d-eyrans' => [44.7194, -0.5131], 'leognan' => [44.7297, -0.5992], 'cestas' => [44.7431, -0.6797],
        'artigues-pres-bordeaux' => [44.8603, -0.4942], 'bassens' => [44.9019, -0.5175], 'ambares-et-lagrave' => [44.9253, -0.4858],
        'libourne' => [44.9153, -0.2439], 'arcachon' => [44.6586, -1.1689], 'la teste-de-buch' => [44.6311, -1.1450],
        'andernos-les-bains' => [44.7442, -1.0947], 'saint-emilion' => [44.8936, -0.1553], 'langon' => [44.5531, -0.2492],
        'creon' => [44.7739, -0.3481], 'carbon-blanc' => [44.8939, -0.5047], 'martignas-sur-jalle' => [44.8411, -0.7756],
    ];

    /**
     * @return array{ok: bool, bordeaux?: bool, distance_km?: float, ville?: string, error?: string}
     */
    public static function locate(string $address, string $postcode, string $city): array
    {
        if (self::isBordeaux($postcode, $city)) {
            return ['ok' => true, 'bordeaux' => true, 'distance_km' => 0.0, 'ville' => 'Bordeaux'];
        }

        $coords = self::geocode("$address $postcode $city", $postcode) ?? self::fallback($city);
        if ($coords === null) {
            return [
                'ok'    => false,
                'error' => "Nous n'avons pas pu localiser cette adresse. Vérifiez la ville et le code postal, ou contactez-nous.",
            ];
        }

        return [
            'ok'          => true,
            'bordeaux'    => false,
            'distance_km' => round(self::haversine(self::ORIGIN, $coords), 1),
            'ville'       => $city,
        ];
    }

    public static function isBordeaux(string $postcode, string $city): bool
    {
        return self::slug($city) === 'bordeaux' && in_array(trim($postcode), self::BORDEAUX_POSTCODES, true);
    }

    private static function geocode(string $query, string $postcode): ?array
    {
        $context = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true]]);
        foreach (self::GEOCODERS as $endpoint) {
            $url = $endpoint . '?' . http_build_query(['q' => $query, 'limit' => 1, 'postcode' => $postcode]);
            $raw = @file_get_contents($url, false, $context);
            if ($raw === false) {
                continue;
            }
            $json = json_decode($raw, true);
            $coords = $json['features'][0]['geometry']['coordinates'] ?? null; // [lon, lat]
            if (is_array($coords) && count($coords) === 2) {
                return [(float) $coords[1], (float) $coords[0]];
            }
        }
        return null;
    }

    private static function fallback(string $city): ?array
    {
        return self::FALLBACK[self::slug($city)] ?? null;
    }

    /** Distance en km entre deux points GPS (formule de haversine). */
    public static function haversine(array $a, array $b): float
    {
        $earthRadius = 6371.0;
        [$lat1, $lon1] = array_map('deg2rad', $a);
        [$lat2, $lon2] = array_map('deg2rad', $b);
        $h = sin(($lat2 - $lat1) / 2) ** 2 + cos($lat1) * cos($lat2) * sin(($lon2 - $lon1) / 2) ** 2;
        return 2 * $earthRadius * asin(min(1.0, sqrt($h)));
    }

    /** « Saint-Médard-d'Eyrans » → « saint-medard-d-eyrans » */
    public static function slug(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'î' => 'i',
                                'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c', '’' => "'"]);
        $value = preg_replace("/['\s]+/", '-', $value);
        $value = preg_replace('/^(le|la)-/', '$1 ', (string) $value);
        return trim((string) $value, '-');
    }
}
