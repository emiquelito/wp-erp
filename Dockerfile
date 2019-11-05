FROM wordpress

ADD . /wp-erp

RUN set -x && \
    apt-get update && apt-get install -y unzip git sudo && \
    cd /usr/src/wordpress/wp-content/plugins/ && \
    sudo chown -R www-data wp-erp && \
    cd wp-erp && \
    curl --output composer-setup.php https://getcomposer.org/installer && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm -f composer-setup.php && \
    mkdir /var/www/.composer && chown www-data:www-data /var/www/.composer && \
    sudo -u www-data composer install && \
    sudo -u www-data composer dump-autoload -o
