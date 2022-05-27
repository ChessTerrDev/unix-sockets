FROM alpine:latest

WORKDIR /app

# Essentials
RUN echo "UTC" > /etc/timezone
RUN apk add --no-cache zip unzip curl

# Installing PHP
RUN apk add --no-cache php8 \
    php8-sockets \
    php8-phar \
    php8-iconv \
    php8-cli \
    php8-curl \
    php8-mbstring \
    php8-openssl


# Installing composer
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN rm -rf composer-setup.php

# Building process
COPY . .
RUN composer install --no-dev

CMD ["php8", "worker.php"]




