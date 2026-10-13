<?php

/**
 * Reconstruit la collection MongoDB des statistiques à partir de MySQL.
 * À lancer après l'import de data.sql :   php bin/sync-stats.php
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/vendor/autoload.php';

App\Core\Env::load(BASE_PATH . '/.env');
date_default_timezone_set('Europe/Paris');

$count = App\Repositories\StatsRepository::rebuild();
echo "$count commande(s) synchronisée(s) dans MongoDB (collection " . App\Repositories\StatsRepository::COLLECTION . ").\n";
