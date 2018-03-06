<?php
/*
 * Define custom routes. File gets included in the router service definition.
 */
// $router = new Phalcon\Mvc\Router();

// $router->addGet("/basic", "Index::basic");
// $router->addGet("/basic-list", "Index::basicList");
// $router->addGet("/test-mongo", "test::mongoInsert");

// return $router;

use Phalcon\Mvc\Router\Group as RouterGroup;

$router->removeExtraSlashes(true);

$router->setDefaults(array(
    'namespace'  => 'Core\Controllers',
    'controller' => 'error',
    'action'     => 'page404'
));

//==========Route for api==========
$api = new RouterGroup(array(
    'namespace' => 'Users\Controllers'
));

//==== Start : user Section ====//
$api->addGet('/users', [
    'controller' => 'user',
    'action'     => 'getUser',
]);

$api->addGet('/users/{id}', [
    'controller' => 'user',
    'action'     => 'getUserdetail',
]);

$api->addPost('/users', [
    'controller' => 'user',
    'action'     => 'postUser',
]);

$api->addPut('/users/{id}', [
    'controller' => 'user',
    'action'     => 'putUser',
]);

$api->addDelete('/users/{id}', [
    'controller' => 'user',
    'action'     => 'deleteUser',
]);

$api->addPost('/users/login', [
    'controller' => 'user',
    'action'     => 'postLogin',
]);

$api->addPut('/users/{id}/change/password', [
    'controller' => 'user',
    'action'     => 'putChangepassword',
]);
//==== End : user Section ====//



$router->mount($api);

return $router;
