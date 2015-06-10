#!/bin/bash
STAMP="/home/vagrant/.30-virtualhost"

ROOT=
echo $STAMP
if [ ! -f $STAMP ]; then
  export DEBIAN_FRONTEND="noninteractive" ; set -e #-x

  cat > /etc/apache2/sites-available/default << _EOF
    <VirtualHost *:80>
        ServerName localhost
        DocumentRoot $WWW_ROOT

        CustomLog /var/log/apache2/$PROJECT_NAME combined
        ErrorLog /var/log/apache2/$PROJECT_NAME-error.log

        <Directory $WWW_ROOT>
            Options Indexes FollowSymLinks
            Order allow,deny
            Allow from all
            Require all granted
            Satisfy Any
        </Directory>
    </VirtualHost>
_EOF

  service apache2 stop
  a2enmod rewrite
  mkdir -p /var/log/xdebug
  chown -R vagrant: /var/lock/apache2/ /var/log/apache2/ /var/log/xdebug
  cat > /etc/php5/conf.d/99-xdebug-remote.ini << _EOF
[xdebug]
xdebug.default_enable = 1
xdebug.idekey = "sublime.xdebug"
xdebug.remote_enable = 1
xdebug.remote_autostart = 0
xdebug.remote_handler=dbgp
xdebug.remote_log="/var/log/xdebug/xdebug.log"
xdebug.remote_host=10.0.2.2
_EOF
  sed -i 's/www-data/vagrant/g' /etc/apache2/envvars
  echo -e "error_reporting=E_ALL\ndisplay_errors=On" > /etc/php5/conf.d/99-errors-on.ini
  service apache2 start

  touch $STAMP
fi
