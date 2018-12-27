#!/usr/bin/env bash
docker rm gcc_dbg
docker run -p 8000:80  -e FACTER_PHP_DEBUG=true --name gcc_dbg -ti -v /root/Dev/gcc/app:/app -v /root/Dev/gcc/tools:/tools -v /root/Dev/gcc/tests:/tests gcc:latest