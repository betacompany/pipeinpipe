FROM php:8.5-cli

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN apt-get update \
	&& apt-get install -y --no-install-recommends git unzip \
	&& rm -rf /var/lib/apt/lists/*

# PHPCompatibility 10 is still alpha; develop has the most complete PHP 8.x sniffs
ENV COMPOSER_HOME=/opt/composer
ENV PATH=/opt/composer/vendor/bin:$PATH
RUN composer global config minimum-stability dev \
	&& composer global config prefer-stable true \
	&& composer global config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true \
	&& composer global require --no-interaction --no-progress \
		squizlabs/php_codesniffer \
		phpcompatibility/php-compatibility:dev-develop

# short_open_tag can't be changed at runtime; without it the code inside
# "<? ... ?>" blocks would be invisible to the tokenizer and go unchecked
RUN printf 'short_open_tag=On\nmemory_limit=-1\n' > /usr/local/etc/php/conf.d/zz-tools.ini
