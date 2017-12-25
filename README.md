# Sandbox REST API

This project use composer to manage dependencies and symfony2 as the framework

All these API will require authentification with  Basic HTTP Auth
with username = "token" and password = "id of client"

## Useful commands
  * to start vagrant ENV `vagrant up` 
  * to ssh into the virtual machine `vagrant ssh`
  * to close vagrant ENV `vagrant halt`

## Pre-requisites

* [VirtualBox](http://www.virtualbox.org/) for full machine virtualization
* [Vagrant](http://www.vagrantup.com/) for automatic creation and provisioning of guest VMs

## Build Local Environment

* run the command "bin/homestead make"
* modify the file 'homestead.yml'
* execute 'vagrant up' from project directory

## API Documentations

[http://gitlab.sandbox3.cn/Sandbox/Sandbox-API-Docs](http://gitlab.sandbox3.cn/Sandbox/Sandbox-API-Docs)

TEST