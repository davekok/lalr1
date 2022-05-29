FROM php:8.1.6
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
RUN docker-php-ext-install pcntl && pecl install pcov && docker-php-ext-enable pcov
