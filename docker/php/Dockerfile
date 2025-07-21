## Основной образ
FROM php:8.1-fpm 
## Устанавливаем основные зависимости в операционную систему
RUN apt-get update && apt-get install -y \
    git \
    zip \
    curl \
    sudo \
    unzip 
## Расширение для работы с базой данных
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql pgsql

RUN pecl install xdebug && docker-php-ext-enable xdebug
ADD xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

## Устанавливаем композер
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
## Создаем переменную для ид пользователя
ARG HOST_UID
ARG USR_NAME
## Команда для добавления пользователя
RUN adduser --disabled-password --uid 1000 user
USER user
# CMD ["composer", "install"]