# version 2.3.1

sudo cp -r web/image/ /data/openfire/  # synchronous images files

sudo php app/console doc:mig:exe 20161115023300  # add room_city data
sudo php app/console doc:mig:exe 20161116082531  # add admin_permission data
sudo php app/console doc:mig:exe 20161117104305  # update room_types's type data
sudo php app/console doc:mig:exe 20161118105827  # update admin_permission's name data
sudo php app/console doc:mig:exe 20161125081731  # add room_city english name
sudo php app/console doc:mig:exe 20161129070108  # add position & building service icons
