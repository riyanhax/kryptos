FROM php:5.6-apache

RUN apt-get update \
 && apt-get install -y git zlib1g-dev libmcrypt-dev \
 && docker-php-ext-install zip pdo_mysql mcrypt \
 && a2enmod rewrite \
 && sed -i 's!/var/www/html!/var/www/web!g' /etc/apache2/sites-available/000-default.conf \
 && curl -sS https://getcomposer.org/installer \
  | php -- --install-dir=/usr/local/bin --filename=composer

#ADD ./000-default.conf /etc/apache2/sites-enabled/000-default.conf

WORKDIR /var/www

EXPOSE 80

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
