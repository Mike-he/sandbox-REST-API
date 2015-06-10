# EZLinz REST API

This project use composer to manage dependencies
and symfony2 as the framework

all these API will require authentification with  Basic HTTP Auth
with username = "sharedsecret"  and password = "device's uii"

## Useful commands
  * to refresh tokens: run the script `utils/refresh_token.sh`
  * to see list of available sharedsecret/uii run the script 
  * to ssh into the virtual machine `vagrant ssh`

you're ready to roll


## Dev environement with Vagrant

All you should need to do is execute `vagrant up` from your working copy root.

## Pre-requisites

* [VirtualBox](http://www.virtualbox.org/) for full machine virtualization
* [Vagrant](http://www.vagrantup.com/) for automatic creation and provisioning of guest VMs






## Dev environement  (manual, deprecated)

you can run a local server without apache by simply doing

```
php app/console server:run
```

it will create a server listening on port `8000`  so after you can access to your api calls using

```
http://127.0.0.1:8000/app_dev.php/YOUR_API_CALL_HERE
```

# Production
## Installation instruction

  * make sure to have PHP > 5.5.0 (see BST knowledgebase article on how to install PHP > 5.5.0 on CentOS)
  * be sure to have the php.ini configured correctly , same for your apache vhost (see BST knowledge base article on how to configure them in "normal php website" guidelines)
  * import all the sql files  in `contrib` folder in your database
  * from the root directory of the project install composer `curl -sS https://getcomposer.org/installer | php`
  * run `php composer.phar install -vvv` * configure a vhost to point on the **web** folder (important the web folder, not the root of this directory) * change the group owner of app folder to the apache user of the system (it's www-data Debian)  `chgrp REPLACE_BY_USER -R app/`
  * edit `app/config/parameters.yml` to put the local database username/password


to switch to production mode run the command (after you've finished to deployed)

```
 php app/console --env=prod cache:clear
```

## Api documentation

check redmine (TODO: add link to the article on redmine)
