# Version 2.3.3

sudo cp -r web/image/ /data/openfire/  # synchronous images files

sudo php app/console doc:mig:exe 20161207025409  # add permission group data
sudo php app/console doc:mig:exe 20161209160419  # change permission group's name data
sudo php app/console doc:mig:exe 20161214161831  # add lease expire in time in parameter table
sudo php app/console doc:mig:exe 20161219165153  # add event order permission
sudo php app/console doc:mig:exe 20161220101253  # change permission names

# install wkhtmltopdf
sudo cp ./pdf_bin/* /usr/bin/
sudo yum install wqy-zenhei-fonts.noarch -y

# update parameter
sudo cp app/config/parameter_production.yml app/config/parameter.yml

# cron jobs
30 1 * * * php /var/www/Sandbox/sandbox-REST-API/app/console sandbox:api-bundle:check_lease_bills
30 1 * * * php /var/www/Sandbox/sandbox-REST-API/app/console sandbox:api-bundle:check_lease_expire_in

## version 2.3.1
#
#sudo cp -r web/image/ /data/openfire/  # synchronous images files
#
#sudo php app/console doc:mig:exe 20161115023300  # add room_city data
#sudo php app/console doc:mig:exe 20161116082531  # add admin_permission data
#sudo php app/console doc:mig:exe 20161117104305  # update room_types's type data
#sudo php app/console doc:mig:exe 20161118105827  # update admin_permission's name data
#sudo php app/console doc:mig:exe 20161125081731  # add room_city english name
#sudo php app/console doc:mig:exe 20161129070108  # add position & building service icons
