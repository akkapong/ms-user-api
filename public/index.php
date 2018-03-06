<?php

error_reporting(E_ALL);


try {

    /**
     * Define some useful constants
     */
    define('BASE_DIR', dirname(__DIR__));
    define('APP_DIR', BASE_DIR . '/app');
    define('STORAGE_DIR', BASE_DIR . '/storage');

    /**
     * Read auto-loader
     */
    include BASE_DIR . '/config/loader.php';

    /**
     * Read the configuration
     */
    $config  = include BASE_DIR . '/config/config.php';
    $message = include BASE_DIR . '/config/message.php';
    $status  = include BASE_DIR . '/config/status.php';

    /**
     * Read services
     */
    include BASE_DIR . '/config/services.php';

    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);

    echo $application->handle()->getContent();

} catch (Exception $e) {
    echo $e->getMessage(), '<br>';
    echo nl2br(htmlentities($e->getTraceAsString()));
}
