FROM php:8.2-apache

RUN apt-get update \
  && apt-get install -y --no-install-recommends default-mysql-client \
  && docker-php-ext-install mysqli pdo pdo_mysql \
  && a2enmod rewrite headers \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html

COPY docker/bootstrap.php /usr/local/bin/gestor-bootstrap.php
COPY docker/entrypoint.sh /usr/local/bin/gestor-entrypoint.sh

RUN chmod +x /usr/local/bin/gestor-entrypoint.sh \
  && chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["gestor-entrypoint.sh"]
CMD ["apache2-foreground"]
