FROM php:8.5-fpm

# The code uses mysqli; ext/mysql is gone since PHP 7
RUN docker-php-ext-install mysqli

# The image ships without a php.ini. Use the production one, as Ubuntu's
# php8.5-fpm on the server does: among others request_order = "GP" (no cookies
# in $_REQUEST, which the code echoes back) and output_buffering = 4096.
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY php/php.ini /usr/local/etc/php/conf.d/zz-dev.ini
