#!/bin/bash

cd /var/www/sandbox-REST-API

git pull origin master

cp app/config/parameters_staging.yml.dist app/config/parameters.yml

# Update vendor of sandbox_app
composer dump-autoload --optimize

# Clean all caches for sandbox_app
php app/console cache:clear --env=prod
php app/console cache:clear --env=dev

HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)
setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX app/cache app/logs
setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX app/cache app/logs

chmod o+rwx app/cache -R
chmod o+rwx app/logs -R

mkdir /data
mkdir /data/openfire
chmod o+rwx /data/openfire -R
cp -r /var/www/sandbox-REST-API/web/image/ /data/openfire/

# Startup
/etc/init.d/cron start
/etc/init.d/php5-fpm start
/etc/init.d/nginx start

# Keep container alive
top -bc