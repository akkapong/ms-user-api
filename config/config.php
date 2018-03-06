<?php

use Phalcon\Config;
use Phalcon\Logger;

$database = include BASE_DIR . '/config/database.php';
$session = include BASE_DIR . '/config/session.php';

return new Config([
    'application' => [
        // 'controllersDir'  => APP_DIR . '/controllers/',
        // 'modelsDir'       => APP_DIR . '/models/',
        // 'repositoriesDir' => APP_DIR . '/Repositories/',
        // 'servicesDir'     => APP_DIR . '/Services/',
        // 'viewsDir'        => APP_DIR . '/views/',
        'cacheDir'        => STORAGE_DIR . '/framework/cache/',
        'baseUri'         => getenv('BASE_URL', 'http://localhost.dev'),
        'publicUrl'       => 'vokuro.phalconphp.com',
        'cryptSalt'       => 'eEAfR|_&G&f,+vU]:jFr!!A&+71w1Ms9~8_4L!<@[N@DyaIP_2My|:+.u>/6m,$D'
    ],
    'database' => $database,
    'mail' => [
        'fromName'  => 'Vokuro',
        'fromEmail' => 'phosphorum@phalconphp.com',
        'smtp'      => [
            'server'   => 'smtp.gmail.com',
            'port'     => 587,
            'security' => 'tls',
            'username' => '',
            'password' => ''
        ]
    ],
    'amazon' => [
        'AWSAccessKeyId' => '',
        'AWSSecretKey' => ''
    ],
    'logger' => [
        'path'     => STORAGE_DIR . '/logs/',
        'format'   => '%date% [%type%] %message%',
        'date'     => 'D j H:i:s',
        'logLevel' => Logger::DEBUG,
        'filename' => 'application.log',
    ],
    'session' => $session,

]);
