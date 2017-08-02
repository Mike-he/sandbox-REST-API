FROM debian:8
MAINTAINER Feng Li<feng.li@sandbox3.cn>

# Update distribution
COPY data/sources.list /etc/apt/sources.list
RUN apt-get update
RUN apt-get upgrade -y

# Install tools
RUN apt-get install -y vim cron wget git nginx php5-fpm php5-mysql php5-curl php5-common php5-redis php5-gd libxrender1 fonts-wqy-zenhei \
  && rm -fr /var/lib/apt/lists/*

# Copy composer on system and install it globally
COPY data/composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer
  
# Remove default nginx conf
RUN rm -rf /etc/nginx/sites-available/default

# Copy Nginx conf
COPY data/sandbox.conf /etc/nginx/conf.d/sandbox.conf

# Copy php-fpm conf
COPY data/www.conf /etc/php5/fpm/pool.d/www.conf

# Copy cron jobs
COPY data/crontab /etc/crontab

# Copy pdf bin
COPY data/pdf_bin/* /usr/bin/
RUN chmod +x /usr/bin/wkhtmltopdf /usr/bin/wkhtmltoimage

# Copy startup script
COPY data/entrypoint.sh /root/entrypoint.sh
RUN chown root:root /root/entrypoint.sh
RUN chmod +x /root/entrypoint.sh

# Copy code
RUN mkdir /var/www/sandbox-REST-API
COPY / /var/www/sandbox-REST-API/

WORKDIR /var/www
VOLUME /var/www
EXPOSE 9000 80

# Run startup script
ENTRYPOINT ["/root/entrypoint.sh"]
