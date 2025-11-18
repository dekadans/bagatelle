# syntax=docker/dockerfile:1

# Create a stage for installing app dependencies defined in Composer.
FROM composer:lts as deps

WORKDIR /app

RUN --mount=type=bind,source=composer.json,target=composer.json \
    --mount=type=bind,source=composer.lock,target=composer.lock \
    --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-interaction

################################################################################

FROM dunglas/frankenphp as final

ENV SERVER_NAME=:80

WORKDIR /app

ARG USER=appuser

RUN \
	useradd ${USER}; \
	setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
	chown -R ${USER}:${USER} /config/caddy /data/caddy

#RUN install-php-extensions \
#	gd \
#	intl \
#	zip \
#	opcache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
# RUN cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

USER ${USER}

COPY --from=deps app/vendor/ /app/vendor

COPY . /app

# Temporary! :)
COPY .env.example .env

