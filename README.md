# Sandbox REST API

This project use composer to manage dependencies
and symfony2 as the framework

all these API will require authentification with  Basic HTTP Auth
with username = "token"  and password = "id of client"

## Useful commands
  * to refresh tokens: run the script `utils/refresh_token.sh`
  * to see list of available sharedsecret/uii run the script 
  * to ssh into the virtual machine `vagrant ssh`


## Pre-requisites

* [VirtualBox](http://www.virtualbox.org/) for full machine virtualization
* [Vagrant](http://www.vagrantup.com/) for automatic creation and provisioning of guest VMs

## Dev envirorment with Vagrant

All you should need to do is execute `vagrant up` from your working copy root.


## Api documentation

http://gitlab.sandbox3.cn/Sandbox/Sandbox-API-Docs