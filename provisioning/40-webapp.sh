#!/bin/bash
STAMP="/home/vagrant/.40-webapp"
echo $STAMP
if [ ! -f $STAMP ]; then
  cat << _EOF >> /vagrant/app/config/parameters.yml
parameters:
    database_driver: pdo_mysql
    database_host: 127.0.0.1
    database_port: '3306'
    database_name: $DBNAME
    database_user: $DBUSER
    database_password:$DBUSER_PASSWORD
    mailer_transport: smtp
    mailer_host: 127.0.0.1
    mailer_user: null
    mailer_password: null
    locale: en
    secret: ae4f49befc969ff5feada55bcc88e3c0
    database_path: null
_EOF

  touch $STAMP
fi
