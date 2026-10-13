# =====================================================================
#  Image de production de Vite & Gourmand : PHP 8.3 + Apache
#  Utilisée en local (docker compose) et pour le déploiement (Render).
# =====================================================================
FROM php:8.3-apache

# Extensions PHP : PDO MySQL (base relationnelle) et MongoDB (base NoSQL)
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libssl-dev \
    && docker-php-ext-install pdo_mysql \
    && pecl install mongodb-1.20.1 \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite headers expires \
    && apt-get purge -y --auto-remove libssl-dev \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

# Configuration PHP de production (erreurs masquées, sessions sécurisées)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"

# Seul le dossier public/ est exposé par Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .
RUN mkdir -p storage/logs storage/mails public/uploads \
    && chown -R www-data:www-data storage public/uploads

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
