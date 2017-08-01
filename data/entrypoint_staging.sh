#!/bin/bash

cd /var/www/sandbox-REST-API

cp app/config/parameters_staging.yml.dist app/config/parameters.yml

# Update vendor of sandbox_app
composer dump-autoload --optimize

# Clean all caches for sandbox_app
php app/console cache:clear --env=prod
php app/console cache:clear --env=dev
php app/console cache:clear --env=test

HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)
setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX /var/www/cache /var/www/logs
setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX /var/www/cache /var/www/logs

# Apply all rights on cache folder again
chmod o+rwx /var/www/cache -R
chmod o+rwx /var/www/logs -R
chmod o+rwx /data/openfire -R

# Startup
/etc/init.d/cron start
/etc/init.d/php5-fpm start 
/etc/init.d/nginx start 

# Keep container alive
top -bc
