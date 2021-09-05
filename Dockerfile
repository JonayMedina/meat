FROM php:7.2-fpm-alpine


RUN apk add icu-dev 
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-configure intl && docker-php-ext-install intl

RUN apk add --no-cache --virtual .persistent-deps \
    icu-libs \
    libxml2-dev 
RUN docker-php-ext-configure soap --enable-soap \ 
     && docker-php-ext-install -j$(nproc) \
         soap

RUN apk add --no-cache freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev && \
  docker-php-ext-configure gd \
    --with-gd \
    --with-freetype-dir=/usr/include/ \
    --with-png-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ && \
  NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) && \
  docker-php-ext-install -j${NPROC} gd && \
  apk del --no-cache freetype-dev libpng-dev libjpeg-turbo-dev

RUN apk add --no-cache libzip-dev && docker-php-ext-configure zip --with-libzip=/usr/include && docker-php-ext-install zip

RUN docker-php-ext-install exif

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/php-cli.ini /usr/local/etc/php/php-cli.ini

WORKDIR /app

COPY . .


WORKDIR /var/www

RUN chown -R www-data:www-data /var/www