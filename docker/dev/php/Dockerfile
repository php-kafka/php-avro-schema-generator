FROM php:7.3-cli-alpine3.10

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER 1
ARG HOST_USER_ID
ARG HOST_USER

COPY dev/php/files/bin/ /usr/local/bin/

# SYS: Install required packages
RUN apk --no-cache upgrade && \
    apk --no-cache add bash git sudo make autoconf gcc g++

RUN if [ ! -z "$HOST_USER_ID" ]; then \
        adduser -u $HOST_USER_ID -D -H $HOST_USER && \
        echo "ALL ALL=NOPASSWD: ALL" >> /etc/sudoers; \
    fi

# PHP: Install php extensions
RUN mkdir /phpIni && \
    pecl channel-update pecl.php.net && \
    pecl install pcov && \
    php-ext-enable pcov

# COMPOSER: install binary
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# COMPOSER: install dependencies
RUN composer global require hirak/prestissimo

USER $HOST_USER

WORKDIR /var/www/html

CMD tail -f /dev/null

