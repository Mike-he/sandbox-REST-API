#!/bin/bash

if [ -z "$TZ" ]; then
   TZ=Asia/Shanghai
fi
ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime && echo "${TZ}" > /etc/timezone

cd /var/www/

# Enable write permission for folder
chmod 777 web/

if [ ! -z "$ENV" ]; then
 cp app/config/parameters_${ENV}.yml.dist app/config/parameters.yml
fi

if [ ! -z "$DOC_MIG" ]; then
  if [ "$DOC_MIG" == true ]; then
     php app/console doc:mig:mig -q
  fi
fi

# Update vendor of sandbox_app
# composer dump-autoload --optimize

# Clean all caches for sandbox_app
php app/console cache:clear --env=prod

touch app/logs/prod.log

chmod o+rwx app/cache -R
chmod o+rwx app/logs -R

if [ ! -z "$CRON_JOB" ]; then
  if [ "$CRON_JOB" == true ]; then
      cp data/crontab /var/spool/cron/crontabs/root && chmod +x /var/spool/cron/crontabs/root
      /etc/init.d/cron start
  fi
fi

mkdir /var/log/nginx/error  /var/log/nginx/access  /var/log/app
touch /var/log/nginx/error/error.log /var/log/nginx/access/access.log /var/log/app/error.log

# Startup
/etc/init.d/php5-fpm start 
/etc/init.d/nginx start 

# Keep container alive
tail -f /var/www/app/logs/prod.log
