# Version 2.5.x

# cron jobs
sudo crontab -e
# */5 * * * * php /var/www/Sandbox/sandbox-REST-API/app/console sandbox:api-bundle:add_group_user_to_doors
# 30 0 * * * php /var/www/Sandbox/sandbox-REST-API/app/console sandbox:api-bundle:remove_group_user_to_doors


## Version 2.3.8

#sudo php app/console sandbox:api-bundle:init_finance_dashboard

#sudo crontab -e
# 10 0 1 * * php /var/www/Sandbox/sandbox-REST-API/app/console create:short_rent_invoice
# 15 0 1 * * php /var/www/Sandbox/sandbox-REST-API/app/console sandbox:api-bundle:set_finance_dashboard

#sudo cp app/config/parameters_production.yml.dist app/config/parameters.yml

#sudo cp -r web/image/ /data/openfire/  # synchronous images files

# https://crm.sandbox3.cn/admin/invoices/sync  (sync order number)


## Version 2.3.3
#
#sudo cp -r web/image/ /data/openfire/  # synchronous images files
#
#sudo php app/console doc:mig:exe 20161207025409  # add permission group data
#sudo php app/console doc:mig:exe 20161209160419  # change permission group's name data
#sudo php app/console doc:mig:exe 20161214161831  # add lease expire in time in parameter table
#sudo php app/console doc:mig:exe 20161219165153  # add event order permission
#sudo php app/console doc:mig:exe 20161220101253  # change permission names
#
## install wkhtmltopdf
#sudo cp ./pdf_bin/* /usr/bin/
#sudo yum install wqy-zenhei-fonts.noarch -y
#
## update parameter
#sudo cp app/config/parameter_production.yml app/config/parameter.yml

# cron jobs
# sudo crontab -e
#1 9 * * * php /var/www/Sandbox/sandbox-REST-API/app/console sandbox:api-bundle:check_lease_bills
#1 9 * * * php /var/www/Sandbox/sandbox-REST-API/app/console sandbox:api-bundle:check_lease_expire_in
#1 2 * * * php /var/www/Sandbox/sandbox-REST-API/app/console sandbox:api-bundle:check_lease_maturity

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
