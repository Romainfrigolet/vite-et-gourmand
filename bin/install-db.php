<?php

/**
 * Installe la base de données à partir des fichiers SQL du projet,
 * en utilisant les paramètres de connexion du fichier .env (ou des variables d'environnement).
 * Pratique lorsque le client « mysql » n'est pas installé sur le poste.
 *
 *   php bin/install-db.php            → exécute database/schema.sql puis database/data.sql
 *   php bin/install-db.php --no-data  → structure uniquement
 *   php bin/install-db.php --if-empty → n'installe que si la base est vide (utilisé au démarrage du conteneur)
 *
 * ATTENTION : schema.sql supprime puis recrée toutes les tables.
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/vendor/autoload.php';

use App\Core\Env;

Env::load(BASE_PATH . '/.env');

$dbName = Env::get('DB_NAME', 'vite_gourmand');
$dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', Env::get('DB_HOST', '127.0.0.1'), Env::get('DB_PORT', '3306'));
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
$ca = \App\Core\Database::caFile(Env::get('DB_SSL_CA'));
if ($ca !== null) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = $ca;
}
$pdo = new PDO($dsn, Env::get('DB_USER', 'root'), Env::get('DB_PASSWORD', ''), $options);

if (in_array('--if-empty', $argv, true)) {
    $exists = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = :db AND table_name = :t');
    $exists->execute(['db' => $dbName, 't' => 'menu']);
    if ((int) $exists->fetchColumn() > 0) {
        echo "Base « $dbName » déjà installée : rien à faire.\n";
        exit(0);
    }
}

$files = ['schema.sql'];
if (!in_array('--no-data', $argv, true)) {
    $files[] = 'data.sql';
}

foreach ($files as $file) {
    $sql = (string) file_get_contents(BASE_PATH . '/database/' . $file);
    // Le nom de la base peut différer chez l'hébergeur (ex. « defaultdb ») : on l'adapte
    $sql = str_replace(['CREATE DATABASE IF NOT EXISTS vite_gourmand', 'USE vite_gourmand;'],
                       ["CREATE DATABASE IF NOT EXISTS `$dbName`", "USE `$dbName`;"], $sql);
    // Suppression des commentaires puis découpage sur les « ; » de fin de ligne
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    $statements = array_filter(array_map('trim', preg_split('/;\s*$/m', (string) $sql)));
    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }
    echo "✔ $file exécuté (" . count($statements) . " instructions)\n";
}

echo "Base « $dbName » prête. Pensez à lancer : php bin/sync-stats.php\n";
