Eggdigital Phalcon Boilerplate
======

Phalcon is a web framework delivered as a C extension providing high performance and lower resource consumption.

This is a sample application for the Phalcon Framework. We expect to implement as many features as possible to showcase the framework and its potential.

Please write us if you have any feedback.

Thanks.

NOTE
----
The master branch will always contain the latest stable version. If you wish to check older versions or newer ones currently under development, please switch to the relevant branch.

Get Started
-----------

#### Requirements

To run this application on your machine, you need at least:

* docker-compose
* >= PHP 7.1
* Apache Web Server with `mod_rewrite enabled`, and `AllowOverride Options` (or `All`) in your `httpd.conf` or or Nginx Web Server
* Latest Phalcon Framework extension installed/enabled
* MySQL >= 5.1.5


Application flow pattern:
---------------------
![alt text](http://esbenp.github.io/img/service-repository-pattern.png)

http://esbenp.github.io/2016/04/11/modern-rest-api-laravel-part-1

Run the docker for development:
---------------------
First you need to copy `.env.example` to `.env` for setup environment of appplication

You can now build, create, start, and attach to containers to the environment for your application. To build the containers use following command inside the project root:

```bash
docker-compose build
```

To start the application and run the containers in the background, use following command inside project root:

```bash
docker-compose up -d
```


Installing Dependencies via Composer
------------------------------------
Eggdigital Phalcon Boilerplate's dependencies must be installed using Composer. Install composer in a common location or in your project:

Run the composer installer:

```bash
docker exec -it eggphalconboilerplate_app_1 composer install
```
or
```bash
docker exec -it eggphalconboilerplate_app_1 composer update
```