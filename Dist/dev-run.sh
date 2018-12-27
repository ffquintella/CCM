#!/usr/bin/env bash

docker run -v /Users/felipe/Dev/gcc/app:/app -v /Users/felipe/Dev/gcc/scripts:/scripts  -p 8443:443 -p 8083:80 -e XDEBUG_CONFIG="idekey=PHPSTORM remote_host=10.25.11.13 profiler_enable=On remote_enable=On" -e FACTER_PHP_DEBUG="true" --name gcc-dev --rm -ti gcc /bin/bash