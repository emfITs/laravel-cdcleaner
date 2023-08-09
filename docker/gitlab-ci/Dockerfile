FROM mysql:latest

ENV MYSQL_DATABASE cloning_dev
ENV MYSQL_ROOT_PASSWORD cloning!Pass
ENV DB_HOST mysql
ENV DB_USERNAME root
ENV DB_PASSWORD cloning!Pass
ENV DB_DATABASE cloning_dev

FROM php:8.1

LABEL maintainer="Marcel Nowocyn"

# RUN [[ ! -e /.dockerenv ]] && exit 0

# set -xe

# Install git (the php image doesn't have it) which is required by composer
RUN apt-get update -yqq
RUN apt-get install git -yqq
RUN apt-get install libzip-dev -yqq
RUN apt-get install libldap2-dev -yqq
RUN apt-get install libpng-dev -yqq
RUN apt-get install nodejs -yqq

RUN curl --silent --show-error "https://getcomposer.org/installer" | php -- --install-dir=/usr/local/bin --filename=composer

# Install mysql driver
# Here you can install any other extension that you need
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/
RUN docker-php-ext-install pdo
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install zip
RUN docker-php-ext-install ldap
RUN docker-php-ext-install gd

RUN composer global require "laravel/envoy=~2.8.5"


