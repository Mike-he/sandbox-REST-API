#!/bin/bash

cd /var/www/sandbox-REST-API

git pull

# Copy pdf bin
cp data/pdf_bin/* /usr/bin/ && chmod +x /usr/bin/wkhtmltopdf /usr/bin/wkhtmltoimage

# Copy composer on system and install it globally
cp data/composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer

# Remove default nginx conf
rm -rf /etc/nginx/sites-available/default

# Copy Nginx conf
cp data/sandbox.conf /etc/nginx/conf.d/sandbox.conf

# Copy php-fpm conf
cp data/www.conf /etc/php5/fpm/pool.d/www.conf

# Copy cron jobs
cp data/crontab /etc/crontab

if [ ! -z "$ENV" ]; then
 cp app/config/parameters_${ENV}.yml.dist app/config/parameters.yml
 php app/console doc:mig:mig
fi

# Update vendor of sandbox_app
#composer dump-autoload --optimize

# Clean all caches for sandbox_app
php app/console cache:clear --env=prod
php app/console cache:clear --env=dev

chmod o+rwx app/cache -R
chmod o+rwx app/logs -R
chmod o+rwx /data/openfire -R

cp -r web/image/ /data/openfire/

# Startup
/etc/init.d/cron start
/etc/init.d/php5-fpm start 
/etc/init.d/nginx start 

# Keep container alive
top -bc
