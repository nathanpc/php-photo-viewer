FROM alpine:3 AS build

RUN apk update && apk add \
    php83 \
    composer \
	php83-apache2 \
	php83-mbstring \
	php83-exif \
	php83-pecl-imagick \
	imagemagick \
	&& rm -rf /var/cache/apk/*

WORKDIR /var/www/localhost/htdocs

RUN rm -rf *
RUN mkdir -p thumbs photos
COPY composer.json .
COPY *.php .
COPY *.png .
COPY .htaccess .

RUN composer install

EXPOSE 80

ENTRYPOINT ["/usr/sbin/httpd", "-D", "FOREGROUND"]
