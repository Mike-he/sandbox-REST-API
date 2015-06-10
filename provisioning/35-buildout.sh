#!/bin/bash
STAMP="/home/vagrant/.$(basename $0)"
echo $STAMP
if [ ! -f $STAMP ]; then
  set -e

  pushd /vagrant > /dev/null
  if [ ! -s /vagrant/composer.phar ]; then
    curl -sS https://getcomposer.org/installer | php
  fi
  /vagrant/composer.phar --no-interaction install --no-progress

  popd > /dev/null
  touch $STAMP
fi