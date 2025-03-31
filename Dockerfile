FROM alpine:3 AS build

# Install required software.
RUN apk update && apk add \
    php83 \
    composer \
	php83-apache2 \
	php83-mbstring \
	php83-exif \
	php83-pecl-imagick \
	imagemagick \
	imagemagick-jpeg \
	&& rm -rf /var/cache/apk/*

# Switch to our website root.
WORKDIR /var/www/localhost/htdocs

# Clean up and copy files over.
RUN rm -rf *
RUN mkdir -p thumbs photos
COPY composer.json .
COPY *.php .
COPY *.png .
COPY .htaccess .

# Fix permissions.
RUN chown apache:apache thumbs

# Ensure we have all the PHP things ready.
RUN composer install

# Run the application.
EXPOSE 80
ENTRYPOINT ["/usr/sbin/httpd", "-D", "FOREGROUND"]
