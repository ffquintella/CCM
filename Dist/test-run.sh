#!/usr/bin/env bash

docker run -e FACTER_HTTP_TIMEOUT=25 -p 8080:80 --name ccm-test --rm -ti ffquintella/ccm /bin/bash