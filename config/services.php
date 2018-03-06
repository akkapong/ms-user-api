<?php

use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Crypt;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Mvc\Model\Metadata\Files as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Logger\Formatter\Line as FormatterLine;

use Phalcon\Mvc\Router;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Http\Client\Request as Curl;

use Core\Validations\MyValidations;

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

/**
 * Register the global configuration as config
 */
$di->set('config', $config);
$di->set('message', $message);
$di->set('status', $status);

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->set('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);
    return $url;
}, true);

/**
 * Setting up the view component
 */
$di->set('view', function () use ($config) {
    $view = new View();
    $view->disable();
    return $view;
}, true);

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db', function () use ($config) {
    return new DbAdapter(array(
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname
    ));
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->set('modelsMetadata', function () use ($config) {
    return new MetaDataAdapter(array(
        'metaDataDir' => $config->application->cacheDir . 'metaData/'
    ));
});

/**
 * Initialise the mongo DB connection.
 */
$di->set('mongo', function () use ($config) {
    $m = $config->database->connections->mongo;

    if (!$m->username || !$m->password) {
        $dsn = 'mongodb://' . $m->host;
    } else {
        $dsn = sprintf(
            'mongodb://%s:%s@%s',
            $m->username,
            $m->password,
            $m->host
        );
    }

    $options = ['ssl' => false ];

    $mongo = new Phalcon\Db\Adapter\MongoDB\Client($dsn, $options);

    return $mongo->selectDatabase($m->database);
}, true);

// Collection Manager is required for MongoDB
$di->set('collectionManager', function () {
    return new Phalcon\Mvc\Collection\Manager();
}, true);

/**
 * Start the session the first time some component request the session service
 */
$di->set('session', function () {
    $session = new SessionAdapter();
    $session->start();
    return $session;
});

/**
 * Crypt service
 */
$di->set('crypt', function () use ($config) {
    $crypt = new Crypt();
    $crypt->setKey($config->application->cryptSalt);
    return $crypt;
});

/**
 * Dispatcher use a default namespace
 */
$di->set('dispatcher', function () {
    // print_r(new \Core\Services\Services()); exit;
    
    // $dispatcher = new Dispatcher();
    // $dispatcher->setDefaultNamespace('Core\Controllers');
    // return $dispatcher;

    // Create/Get an EventManager
    $eventsManager = new Phalcon\Events\Manager();

    // Attach a listener
    $eventsManager->attach("dispatch", function ($event, $dispatcher, $exception) {
        // The controller exists but the action not
        if ($event->getType() == 'beforeNotFoundAction') {
            $dispatcher->forward(array(
                'namespace'  => 'Core\Controllers',
                'controller' => 'error',
                'action'     => 'page404'
            ));
            return false;
        }

        // Alternative way, controller or action doesn't exist
        if ($event->getType() == 'beforeException') {
            switch ($exception->getCode()) {
                case Phalcon\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                case Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                    $dispatcher->forward(array(
                        'namespace'  => 'Core\Controllers',
                        'controller' => 'error',
                        'action'     => 'page404'
                    ));
                    return false;
            }
        }
    });

    $dispatcher = new Phalcon\Mvc\Dispatcher();

    // Bind the EventsManager to the dispatcher
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
});

/**
 * Loading routes from the routes.php file
 */
// $di->set('router', function () {
//     return require __DIR__ . '/routes.php';
// });
$di->set('router', function ()
{
    $router = new Router();
    require 'routes.php';
    return $router;
});

// Register a "response" service in the container
$di->set('response', function () {
    $response = new Response();
    return $response;
});

// Register a "request" service in the container
$di->set('request', function () {
    $request = new Request();
    return $request;
});

// Register a "mongoLibrary" library in the container
$di->set('mongoLibrary', function () {
    return new \Core\Libraries\MongoLibrary();
});

// Register a "curl" service in the container
$di->set('curl', function () {
    $curl = Curl::getProvider();
    return $curl;
});

// Register a "myValidate" service in the container
$di->set('myValidate', function () {
    $myValidate = new MyValidations();
    return $myValidate;
});


/**
 * Logger service
 */
$di->set('logger', function ($filename = null, $format = null) use ($config) {
    $format   = $format ?: $config->get('logger')->format;
    $filename = trim($filename ?: $config->get('logger')->filename, '\\/');
    $path     = rtrim($config->get('logger')->path, '\\/') . DIRECTORY_SEPARATOR;

    $formatter = new FormatterLine($format, $config->get('logger')->date);
    $logger    = new FileLogger($path . $filename);

    $logger->setFormatter($formatter);
    $logger->setLogLevel($config->get('logger')->logLevel);

    return $logger;
});
