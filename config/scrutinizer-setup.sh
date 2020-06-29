#!/usr/bin/env bash

wget -O phive.phar https://phar.io/releases/phive.phar
wget -O phive.phar.asc https://phar.io/releases/phive.phar.asc
gpg --keyserver pool.sks-keyservers.net --recv-keys 0x9D8A98B29B2D5D79
gpg --verify phive.phar.asc phive.phar
chmod +x phive.phar
sudo mv phive.phar /usr/local/bin/phive
composer self-update
composer --version
composer global require hirak/prestissimo --no-plugins
composer install --prefer-dist --no-interaction
chmod -R +x ./bin
sudo mkdir tmp
sudo chmod -R 777 ./tmp

