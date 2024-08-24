ARG PHP_VERSION=8.3

FROM php:${PHP_VERSION}-cli

COPY --from=composer /usr/bin/composer /usr/bin/composer

ADD https://phar.phpunit.de/phpunit-10.phar /usr/local/bin/phpunit
RUN chmod +x /usr/local/bin/phpunit

ADD https://cs.symfony.com/download/php-cs-fixer-v3.phar /usr/local/bin/php-cs-fixer
RUN chmod +x /usr/local/bin/php-cs-fixer

ADD https://github.com/phpstan/phpstan/releases/download/1.11.4/phpstan.phar /usr/local/bin/phpstan
RUN chmod +x /usr/local/bin/phpstan


RUN apt-get update && apt-get install -y \
		libzip-dev \
        zip
RUN docker-php-ext-install zip

RUN pecl install xdebug && docker-php-ext-enable xdebug

WORKDIR /app
