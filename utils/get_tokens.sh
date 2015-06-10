#!/bin/bash
vagrant ssh -c 'echo "select * from ofUserSharedSecret \G" | mysql -u sandbox_rest_api --password=sandbox_rest_api sandbox_rest_api_db'

