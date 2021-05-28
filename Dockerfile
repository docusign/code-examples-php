FROM composer:2 as composer_stage

RUN rm -rf /var/www && mkdir -p /var/www/html
WORKDIR /var/www/html




FROM php:7.4-fpm-alpine

# Install dev dependencies
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    curl-dev \
    imagemagick-dev \
    libtool \
    libxml2-dev

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

# Install and enable php extensions
RUN docker-php-ext-enable \
    imagick \
    xdebug
RUN docker-php-ext-configure zip 
RUN docker-php-ext-install \
    curl \
    iconv \
    pdo \
    pdo_mysql \
    pcntl \
    tokenizer \
    xml \
    gd \
    zip \
    bcmath

WORKDIR /var/www/html
COPY src src/
COPY --from=composer_stage /usr/bin/composer /usr/bin/composer
COPY composer.json /var/www/html/
# This are production settings, I'm running with 'no-dev', adjust accordingly 
# if you need it
RUN composer install

CMD ["php-fpm"]

EXPOSE 8080