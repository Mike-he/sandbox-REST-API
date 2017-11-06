#!/bin/bash

if [ -z "$TZ" ]; then
   TZ=Asia/Shanghai
fi
ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime && echo "${TZ}" > /etc/timezone

# Copy pdf bin
cp data/pdf_bin/* /usr/bin/ && chmod +x /usr/bin/wkhtmltopdf

# Copy composer on system and install it globally
cp data/composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer

HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)
setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX app/cache app/logs
setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX app/cache app/logs

# Startup
/etc/init.d/cron start
/etc/init.d/php5-fpm start 
/etc/init.d/nginx start 

# Keep container alive
tail -f /var/www/sandbox-REST-API/app/logs/dev.log
