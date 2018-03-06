<?php
namespace Users\Controllers;

use Core\Controllers\ControllerBase;

use Users\Schemas\UserSchema;
use Users\Collections\UserCollection;
use Users\Services\UserService;


/**
 * Display the default index page.
 */
class UserController extends ControllerBase
{
    //==== Start: Define variable ====//
    private $module = 'users';
    private $userService;
    private $modelName;
    private $schemaName;

    private $getDetailRule = [
        [
            'type'   => 'required',
            'fields' => ['id'],
        ]
    ];

    private $createRule = [
        [
            'type'   => 'required',
            'fields' => ['username', 'ref_type'],
        ],
    ];

    private $updateRule = [
        [
            'type'   => 'required',
            'fields' => ['id'],
        ],
    ];

    private $deleteRule = [
        [
            'type'   => 'required',
            'fields' => ['id'],
        ],
    ];

    private $userLogin = [
        [
            'type'   => 'required',
            'fields' => ['username', 'password', 'ref_type'],
        ]
    ];

    private $userChangePass = [
        [
            'type'   => 'required',
            'fields' => ['id', 'old', 'new'],
        ]
    ];
    //==== End: Define variable ====//

    //==== Start: Support method ====//
    //Method for initial some variable
    public function initialize()
    {
        $this->userService = new UserService();
        $this->modelName   = UserCollection::class;
        $this->schemaName  = UserSchema::class;
    }

    //==== End: Support method ====//

    //==== Start: Main method ====//
    public function getUserAction()
    {
        //get input
        $params = $this->getUrlParams();

        $limit  = (isset($params['limit']))?$params['limit']:null;
        $offset = (isset($params['offset']))?$params['offset']:null;

        //validate input
        //TODO: add validate here

        //get data in service
        $result = $this->userService->getUser($params, $limit, $offset);

        if (!$result['success']) {
            //process error
            return $this->responseError($result['message'], '/users');
        }

        // print_r(count($result['data'])); exit;

        //return data
        $encoder = $this->createEncoder($this->modelName, $this->schemaName);

        //get total
        $total  = (isset($result['total']))?$result['total']:null;

        return $this->response($encoder, $result['data'], $limit, $offset, $total);

        
    }

    public function getUserdetailAction(string $id)
    {
        //get data in service
        $result = $this->userService->getUserDetail($id);

        if (!$result['success']) {
            //process error
            return $this->responseError($result['message'], '/users/'.$id);
        }

        //return data
        $encoder = $this->createEncoder($this->modelName, $this->schemaName);

        return $this->response($encoder, $result['data']);

        
    }

    public function postUserAction()
    {
        //get input
        $params = $this->getPostInput();

        //validate input
        //TODO: add validate here


        //define default
        $default = [];

        // Validate input
        $params = $this->myValidate->validateApi($this->createRule, $default, $params);

        if (isset($params['validate_error'])) {
            //Validate error
            return $this->responseError($params['validate_error'], '/users');
        }

        //CREATE data by input
        $result = $this->userService->createUser($params);

        //Check response error
        if (!$result['success'])
        {
            //process error
            return $this->responseError($result['message'], '/users');
        }

        //return data
        $encoder = $this->createEncoder($this->modelName, $this->schemaName);

        return $this->response($encoder, $result['data']);
    }

    public function putUserAction(string $id)
    {
        //get input
        $inputs       = $this->getPostInput();

        $inputs['id'] = $id;
        
        //define default
        $default      = [];

        // Validate input
        $params = $this->myValidate->validateApi($this->updateRule, $default, $inputs);

        if (isset($params['validate_error']))
        {
            //Validate error
            return $this->responseError($params['validate_error'], '/users');
        }

        //UPDATE data by input
        $result = $this->userService->updateUser($params);

        //Check response error
        if (!$result['success'])
        {
            //process error
            return $this->responseError($result['message'], '/users');
        }

        //return data
        $encoder = $this->createEncoder($this->modelName, $this->schemaName);

        return $this->response($encoder, $result['data']);
    }

    public function deleteUserAction(string $id)
    {
        //update member data
        $result  = $this->userService->deleteUser($id);

        //Check response error
        if (!$result['success'])
        {
            //process error
            return $this->responseError($result['message'], '/users');
        }

        //return data
        $encoder = $this->createEncoder($this->modelName, $this->schemaName);

        return $this->response($encoder, $result['data']);
    }

    //Method for user login
    public function postLoginAction()
    {
        //get input
        $inputs = $this->getPostInput();

        //define default
        $default = [
            'status' => 'active'
        ];

        // Validate input
        $params = $this->myValidate->validateApi($this->userLogin, $default, $inputs);

        if (isset($params['validate_error']))
        {
            //Validate error
            return $this->responseError($params['validate_error'], '/users');
        }

        //process user login
        $result = $this->userService->checkLogin($params);

        //Check response error
        if (!$result['success'])
        {
            //process error
            return $this->responseError($result['message'], '/users');
        }

        //return data
        $encoder = $this->createEncoder($this->modelName, $this->schemaName);

        return $this->response($encoder, $result['data']);
    }

    //Method for change password
    public function putChangepasswordAction(string $id)
    {
        //get input
        $inputs       = $this->getPostInput();
        $inputs['id'] = $id;

        //define default
        $default = [];

        // Validate input
        $params = $this->myValidate->validateApi($this->userChangePass, $default, $inputs);

        if (isset($params['validate_error']))
        {
            //Validate error
            return $this->responseError($params['validate_error'], '/users');
        }

        //update user data
        $result   = $this->userService->changePassword($params['id'], $params['old'], $params['new']);

        //Check response error
        if (!$result['success'])
        {
            //process error
            return $this->responseError($result['message'], '/users');
        }

        //clear cache witch prefix
        // $this->cacheService->deleteCacheByPrefix($this->service);

        $encoder = $this->createEncoder($this->modelName, $this->schemaName);
        return $this->response($encoder, $result['data']);
    }
    //==== End: Main method ====//
}
