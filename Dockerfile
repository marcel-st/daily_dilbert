FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl ca-certificates tar \
    && a2enmod headers \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . /var/www/html/
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
COPY apache-cache.conf /etc/apache2/conf-available/apache-cache.conf

RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && a2enconf apache-cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]