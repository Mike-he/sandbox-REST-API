# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "puphpet/debian75-x32"
  config.vm.provision :shell, :inline => <<-END
    export PROJECT_NAME="sandbox_rest_api"
    export ROOT_DBUSER_PASSWORD="root"
    export DBUSER=$PROJECT_NAME
    export DBUSER_PASSWORD=$PROJECT_NAME
    export DBNAME=$PROJECT_NAME"_db"
    export WWW_ROOT="/vagrant/web"
    set -e
    for s in /vagrant/provisioning/??-*.sh ; do $s ; done
END
  config.vm.network :forwarded_port, host: 8042, guest: 80 #Apache server
  config.vm.hostname = "ezlinx-rest-api.dev"
  config.vm.box_check_update = false
  config.vm.synced_folder "./", "/vagrant", id: "vagrant-root",
      owner: "vagrant",
      group: "www-data",
      mount_options: ["dmode=775,fmode=764"]
end
