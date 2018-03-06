<?php

use Phalcon\Loader;

$loader = new Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerNamespaces([
    'Phalcon' => BASE_DIR . '/../../vendor/phalcon/incubator/Library/',
    'Users'   => APP_DIR . '/Users',
    
    // 'Core\Models'       => APP_DIR . '/../core/Models/',
    // 'Core\Controllers'  => APP_DIR . '/../core/Controllers/',
    // 'Core\Collections'  => APP_DIR . '/../core/Collections/',
    // 'Core\Repositories' => APP_DIR . '/./core/Repositories/',
    // 'Core\Services'     => APP_DIR . '/../core/Services/',
    
    // 'Users\Models'       => APP_DIR . '/Users/Models/',
    // 'Users\Controllers'  => APP_DIR . '/Users/Controllers/',
    // 'Users\Collections'  => APP_DIR . '/Users/Collections/',
    // 'Users\Repositories' => APP_DIR . '/Users/Repositories/',
    // 'Users\Services'     => APP_DIR . '/Users/Services/',

    // 'Users'             => APP_DIR . '/Users',

    // 'Core\Libraries'       => APP_DIR . '/library/',
]);

$loader->register();

// Use composer autoloader to load vendor classes
require_once BASE_DIR . '/vendor/autoload.php';

/**
 * Environment variables
 */
$env = getenv('ENVIRONMENT');

if (empty($env)) {
    $env = 'docker';
} 

$dotenv = new Dotenv\Dotenv(BASE_DIR, ".$env.env");
$dotenv->load();

if (getenv('APP_ENV') !== 'production') {
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}