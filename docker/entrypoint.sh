#!/bin/sh
# Démarrage du conteneur :
#  1. Apache écoute sur le port imposé par l'hébergeur ($PORT, ex. Render) ou 80
#  2. Les statistiques MongoDB sont reconstruites depuis MySQL (opération idempotente)
set -e

PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Attente de la base MySQL (utile avec docker compose) puis synchronisation MongoDB
i=0
until php bin/sync-stats.php 2>/dev/null; do
    i=$((i+1))
    if [ "$i" -ge 15 ]; then
        echo "Synchronisation MongoDB impossible pour le moment (elle sera faite à la prochaine commande)."
        break
    fi
    sleep 3
done

exec "$@"
