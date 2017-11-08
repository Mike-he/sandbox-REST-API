FROM debian:8
MAINTAINER Feng Li<feng.li@sandbox3.cn>

# Update distribution
COPY data/sources.list /etc/apt/sources.list
RUN apt-get update && apt-get upgrade -y

# Install tools
RUN apt-get install -y vim cron wget nginx php5-fpm php5-mysql php5-curl php5-common php5-redis php5-gd libxrender1 fonts-wqy-zenhei php5-mcrypt \
  && rm -fr /var/lib/apt/lists/*

# Copy startup script
COPY data/*.sh /root/
RUN chown root:root /root/*.sh && chmod +x /root/*.sh

# Copy code
RUN mkdir /var/www/sandbox-REST-API

COPY . /var/www/sandbox-REST-API/

WORKDIR /var/www
EXPOSE 80

# Run startup script
ENTRYPOINT ["/root/entrypoint.sh"]