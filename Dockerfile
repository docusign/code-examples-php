FROM composer:2 as composer_stage

RUN rm -rf /var/www && mkdir -p /var/www/html
WORKDIR /var/www/html

FROM php:8.2-fpm-alpine

# Install dev dependencies
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    curl-dev \
    imagemagick-dev \
    libtool \
    libxml2-dev \
    linux-headers # Add linux-headers here

# Install production dependencies
RUN apk add --no-cache \
    bash \
    curl \
    g++ \
    gcc \
    git \
    imagemagick \
    libc-dev \
    libpng-dev \
    make \
    yarn \
    openssh-client \
    rsync \
    zlib-dev \
    libzip-dev

# Install PECL and PEAR extensions
RUN pecl install \
    imagick \
    xdebug

# Workaround for iconv with PHP8
RUN apk add gnu-libiconv=1.15-r3 --update-cache --repository http://dl-cdn.alpinelinux.org/alpine/v3.13/community/ --allow-untrusted
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so

# Install and enable PHP extensions
RUN docker-php-ext-enable \
    imagick \
    xdebug
RUN docker-php-ext-configure zip
RUN docker-php-ext-install \
    curl \
    pdo \
    pdo_mysql \
    pcntl \
    xml \
    gd \
    zip \
    bcmath

WORKDIR /var/www/html
COPY src src/
COPY --from=composer_stage /usr/bin/composer /usr/bin/composer
COPY composer.json /var/www/html/
# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader

CMD ["php-fpm"]

EXPOSE 9000
