#!/bin/bash

if [ -z "$TZ" ]; then
   TZ=Asia/Shanghai
fi
ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime && echo "${TZ}" > /etc/timezone

cd /var/www/sandbox-REST-API

# Enable write permission for folder
chmod 777 web/

# Copy pdf bin
cp data/pdf_bin/* /usr/bin/ && chmod +x /usr/bin/wkhtmltopdf

# Copy composer on system and install it globally
cp data/composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer

# Remove default nginx conf
rm -rf /etc/nginx/sites-available/default

# Copy Nginx conf
cp data/sandbox.conf /etc/nginx/conf.d/sandbox.conf

# Copy php-fpm conf
cp data/www.conf /etc/php5/fpm/pool.d/www.conf

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
tail -f /var/log/nginx/access.log /var/log/nginx/error.log
