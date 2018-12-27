#!/usr/bin/env bash

cd tests
./composer_restore.sh

cd ../
cd app
chmod +x composer_restore.sh
./composer_restore.sh

cd docs
chmod +x composer_restore.sh
./composer_restore.sh

cd ../
cd ../
cd client
chmod +x composer_restore.sh
./composer_restore.sh