FROM registry.cn-hangzhou.aliyuncs.com/sandbox3/php:5.6-debian

MAINTAINER sandbox3 <account@sandbox3.cn>

# Copy startup script
COPY data/*.sh /root/
RUN chown root:root /root/*.sh && chmod +x /root/*.sh

# Copy pdf bin
COPY data/pdf_bin/* /usr/bin/
RUN chmod +x /usr/bin/wkhtmltopdf

# Copy composer on system and install it globally
COPY data/composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

# Remove default nginx conf
RUN rm -rf /etc/nginx/sites-available/default

# Copy Nginx conf
COPY data/sandbox.conf /etc/nginx/conf.d/sandbox.conf

# Copy php-fpm conf
COPY data/www.conf /etc/php5/fpm/pool.d/www.conf

COPY data/crontab /root/crontab

RUN rm -rf data/*

# Copy code
COPY . /var/www/

WORKDIR /var/www

EXPOSE 80

# Run startup script
ENTRYPOINT ["/root/entrypoint.sh"]