FROM php:8.2-cli-alpine3.19

ARG HOST_USER_ID
ARG HOST_USER

# Create sanitized username as an ENV variable so it persists throughout the build
ENV CLEAN_USER=default
RUN if [ ! -z "$HOST_USER" ]; then \
    export CLEAN_USER=$(echo "$HOST_USER" | sed 's/[^a-zA-Z0-9._-]/_/g') && \
    echo "export CLEAN_USER=$CLEAN_USER" >> /etc/profile; \
fi

COPY php/files/bin/ /usr/local/bin/

# SYS: Install required packages
RUN apk --no-cache upgrade && \
    apk --no-cache add bash git sudo make autoconf gcc g++

RUN if [ ! -z "$HOST_USER_ID" ]; then \
    adduser -u $HOST_USER_ID -D -H $CLEAN_USER && \
    echo "ALL ALL=NOPASSWD: ALL" >> /etc/sudoers; \
fi

# Create a directory with proper permissions for test files
RUN mkdir -p /tmp/test && \
    chmod 777 /tmp/test && \
    chown $CLEAN_USER:$CLEAN_USER /tmp/test

# PHP: Install php extensions
RUN mkdir /phpIni && \
    pecl channel-update pecl.php.net && \
    pecl install pcov && \
    php-ext-enable pcov

# COMPOSER: install binary
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

USER $CLEAN_USER

WORKDIR /var/www/html

CMD tail -f /dev/null
