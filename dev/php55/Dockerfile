# Prod baseline: the exact php5-fpm package the server runs (Ubuntu 14.04,
# 5.5.9+dfsg-1ubuntu4.29). The official php:5.5 images use a manifest format
# current Docker can no longer pull. amd64 like prod, emulated on Apple Silicon.
FROM ubuntu:14.04

RUN apt-get update \
	&& apt-get install -y --no-install-recommends php5-fpm php5-cli php5-mysql \
	&& rm -rf /var/lib/apt/lists/*

# Listen on TCP for the nginx container instead of the prod unix socket
RUN sed -i 's|^listen = .*|listen = 9000|' /etc/php5/fpm/pool.d/www.conf

COPY php/php.ini /etc/php5/fpm/conf.d/99-dev.ini
COPY php/php.ini /etc/php5/cli/conf.d/99-dev.ini

EXPOSE 9000
CMD ["php5-fpm", "--nodaemonize"]
