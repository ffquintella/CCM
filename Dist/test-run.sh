#!/usr/bin/env bash

docker run -e FACTER_REDIS_SERVER=1.2.3.4 -p 8080:80 -p 8443:443 --name ccm-test --rm -ti ffquintella/ccm /bin/bash