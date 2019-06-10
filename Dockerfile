FROM php:7.3-cli

RUN apt update \
   && apt-get install -y --no-install-recommends libzip-dev zip unzip git curl \
   && docker-php-ext-install zip > /dev/null \
   && docker-php-ext-configure zip --with-libzip \
   && pecl install xdebug \
   && docker-php-ext-enable xdebug \
   && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
   && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_host=docker.for.mac.localhost" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "export PHP_IDE_CONFIG=\"serverName=package_import-io-api\"" >> /root/.bashrc

WORKDIR /app
