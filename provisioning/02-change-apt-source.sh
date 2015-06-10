#!/bin/bash
STAMP="/home/vagrant/.02-change-apt-source"
echo $STAMP
if [ ! -f $STAMP ]; then
  sudo cat << _EOF > /etc/apt/sources.list
deb http://ftp.cn.debian.org/debian wheezy main
deb http://security.debian.org/ wheezy/updates main
deb http://ftp.cn.debian.org/debian wheezy-updates main
_EOF

  touch $STAMP
fi
