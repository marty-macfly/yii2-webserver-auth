FROM php:cli
RUN apt-get update
# Bcmath & opcache
RUN apt-get install -y --no-install-recommends net-tools && docker-php-ext-install bcmath
# Multi-langue
RUN apt-get install -y --no-install-recommends libicu52 libicu-dev && docker-php-ext-install intl && apt-get remove -y libicu-dev
# Capatcha
RUN apt-get install -y --no-install-recommends libfreetype6 libjpeg62-turbo libpng12-0 libfreetype6-dev libjpeg62-turbo-dev libpng12-dev && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && docker-php-ext-install gd && apt-get remove -y libfreetype6-dev libjpeg62-turbo-dev libpng12-dev
RUN apt-get install -y --no-install-recommends libmagick++-6.q16-5 libmagick++-dev && pecl install imagick && docker-php-ext-enable imagick && apt-get remove -y libmagick++-dev
# Composer
RUN apt-get install -y --no-install-recommends libssl1.0.0 libssl-dev wget git unzip && wget -O composer-setup.php https://getcomposer.org/installer && php composer-setup.php --filename=composer --install-dir=/usr/bin && rm -f composer-setup.php
RUN apt-get autoremove -y
COPY . /home/
WORKDIR /home
CMD ["php", "yii", "serve", "0.0.0.0"]
