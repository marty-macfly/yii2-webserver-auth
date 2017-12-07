FROM php:cli
RUN apt-get update
# Bcmath & opcache
RUN apt-get install -y --no-install-recommends net-tools && docker-php-ext-install bcmath
# Multi-langue
RUN apt-get install -y --no-install-recommends libicu-dev && docker-php-ext-install intl
# Capatcha
RUN apt-get install -y --no-install-recommends libfreetype6 libjpeg62-turbo libfreetype6-dev libjpeg62-turbo-dev libpng-dev && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && docker-php-ext-install gd
RUN apt-get install -y --no-install-recommends libmagick++-dev && pecl install imagick && docker-php-ext-enable imagick
# Composer
RUN apt-get install -y --no-install-recommends libssl-dev wget git unzip && wget -O composer-setup.php https://getcomposer.org/installer && php composer-setup.php --filename=composer --install-dir=/usr/bin && rm -f composer-setup.php
RUN apt-get autoremove -y
COPY . /home/
WORKDIR /home
CMD ["php", "yii", "serve", "0.0.0.0"]
