FROM alpine:3.9

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/home/cocorico \
    HOME=/home/cocorico

RUN echo 'http://dl-cdn.alpinelinux.org/alpine/v3.9/main' >> /etc/apk/repositories \
    && apk --update add \
        curl \
        git \
        grep \
        imagemagick \
        mysql-client \
        musl \
        nginx \
        php7 \
        php7-common \
        php7-bcmath \
        php7-ctype \
        php7-curl \
        php7-dom \
        php7-exif \
        php7-fileinfo \
        php7-fpm \
        php7-gd \
        php7-iconv \
        php7-imagick \
        php7-intl \
        php7-json \
        php7-mbstring \
        php7-mcrypt \
        php7-mongodb \
        php7-mysqlnd \
        php7-opcache \
        php7-openssl \
        php7-pdo \
        php7-pdo_mysql \
        php7-pdo_pgsql \
        php7-pdo_sqlite \
        php7-pecl-apcu \
        php7-phar \
        php7-posix \
        php7-simplexml \
        php7-session \
        php7-soap \
        php7-sockets \
        php7-tokenizer \
        php7-xml \
        php7-xmlreader \
        php7-xmlwriter \
        php7-zip \
        shadow \
        supervisor \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=1.10.21 \
    #&& composer global require hirak/prestissimo \
    && groupadd cocorico && useradd -g cocorico --create-home cocorico \
    && mkdir -p /data/db \
    && rm -rf /var/cache/apk/*

ADD files /

WORKDIR /cocorico

VOLUME /cocorico /data/db /var/lib/mysql

EXPOSE 80 3306 27017

ENTRYPOINT sh /entrypoint.sh