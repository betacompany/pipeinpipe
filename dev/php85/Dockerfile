FROM php:8.5-fpm

# ext/mysql no longer exists; mysqli is what the code has to move to
RUN docker-php-ext-install mysqli

# The image ships without a php.ini. Use the production one, as prod's Ubuntu
# package and Ubuntu's php8.5-fpm do: among others request_order = "GP" (no
# cookies in $_REQUEST, which the code echoes back) and output_buffering = 4096.
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY php/php.ini /usr/local/etc/php/conf.d/zz-dev.ini
