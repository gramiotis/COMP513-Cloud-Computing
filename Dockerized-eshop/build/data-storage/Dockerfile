FROM php:8.1-apache
RUN a2enmod rewrite
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer\
	&& apt-get update && apt-get install -y --no-install-recommends openssl libssl-dev libcurl4-openssl-dev \
	&& pecl install mongodb \
        && echo "extension=mongodb.so" >> /usr/local/etc/php/php.ini \
        && apt install -y git \
        && mkdir /var/www/html/config \
        && cd /var/www/html/config \
        && pwd \
        && echo Composer now configuring MongoDB PHP Driver... \ 
        && composer require mongodb/mongodb \
        && echo Composer finished! Produced: \
        && ls \
        && echo Copying PHP Files into server dir...
ADD ./data_storage_scripts/ /var/www/html/
