# Docker && symfony 6 && codeception dev test


**Docker container set-up for Symfony 6.0:**
* nginx
* PHP 8.0
* MariaDB 10.6
* Phpmyadmin 5.1
* codeception

### Installation:

1. Bring up the containers: `docker-compose up -d --build`

2. docker-compose exec phpserver bash

3. composer install

4. jwt configuration :

   mkdir config/jwt

   chmod -R 777 config/jwt
5. create database

    bin/console doctrine:schema:update --force

Generate the SSL keys:

	php bin/console lexik:jwt:generate-keypair	

##  check postman json file for api

### Managing your Symfony application:
**Available tools:**
* Composer: `./docker/scripts/composer`
* Symfony Console: `./docker/scripts/console`
* Symfony cli: `./docker/scripts/symfony`
* PHP Code Sniffer: `./docker/scripts/phpcs`  vendor/bin/php-cs-fixer fix src
* PhpStan: `./docker/scripts/phpstan`


## codeception

to run api test :
* go inside container
  docker-compose exec phpserver bash


* php vendor/bin/codecept run api -d
