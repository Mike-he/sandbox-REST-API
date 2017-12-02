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
      # Copy cron jobs
      cp data/crontab /etc/crontab
      /etc/init.d/cron start
  fi
fi

# Startup
/etc/init.d/php5-fpm start 
/etc/init.d/nginx start 

# Keep container alive
tail -f /app/logs/prod.log
