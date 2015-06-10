#!/bin/bash
vagrant ssh -c 'echo " update  ofUserSharedSecret set creationDate =  UNIX_TIMESTAMP() *1000 ;" | mysql -u sandbox_rest_api --password=sandbox_rest_api sandbox_rest_api_db' && echo ok
