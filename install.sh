#!/bin/bash
DIR=$(sed -n 's/.*install_dir *= *\([^ ]*.*\)/\1/p' < config.ini);;
#change working directory to $DIR
#check if Git is installed
#check if cURL is installed
#check if composer is installed
#check if PayPal API is installed

cd $DIR
if [ ! -f composer.phar ]; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    sudo apt-get install php5-curl
fi

if [ ! -d CoffeeBean/PayPal/REST-API-SDK ]; then
    git clone --depth=1 https://github.com/danbadds38/rest-api-sdk-php.git CoffeeBean/PayPal/REST-API-SDK
fi
