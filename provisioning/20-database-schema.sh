#!/bin/bash
STAMP="/home/vagrant/.20-database-schema"
echo $STAMP
if [ ! -f $STAMP ]; then
  export DEBIAN_FRONTEND="noninteractive" ; set -e -x

  mysql -u root --password="$ROOT_DBUSER_PASSWORD" << _EOF
    create database ${DBNAME};
    create user '${DBUSER}'@'localhost' identified by '${DBUSER_PASSWORD}';
    grant all privileges on ${DBNAME}.* to '${DBUSER}'@'localhost';
_EOF

  mysql -u "$DBUSER" --password="$DBUSER_PASSWORD" --execute="show databases"

  touch $STAMP
fi