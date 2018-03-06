<?php
return new \Phalcon\Config( [

    /* Mission Fail */
    'missionFail' => [
        'code'     => 400,
        'msgError' => 'Mission Fail',
    ],

    /* Validate Fail */
    'validateFail' => [
        'code'     => 401,
        'status'   => 'Error',
        'msgError' => 'Validate Fail',
    ],

    /* Data Not Found */
    'dataNotFound' => [
        'code'     => 400,
        'msgError' => 'Data Not Found',
    ],

    /* Cannot Connect to Database */
    'connectDBError' => [
        'code'     => 400,
        'msgError' => 'Cannot Connect to Database',
    ],

    /* Insert Error */
    'insertError' => [
        'code'     => 400,
        'msgError' => 'Insert Error',
    ],

    /* Update Error */
    'updateError' => [
        'code'     => 400,
        'msgError' => 'Update Error',
    ],

    /* Delete Error */
    'deleteError' => [
        'code'     => 400,
        'msgError' => 'Delete Error',
    ],


    /* Data is duplicate */
    'dataDuplicate' => [
        'code'     => 400,
        'msgError' => 'Data is duplicate',
    ],

    /* login Fail */
    'loginFail' => [
        'code'     => 400,
        'msgError' => 'Login Fail',
    ],

    /* old passwor not match */
    'oldPasswordWrong' => [
        'code'     => 400,
        'msgError' => 'Old password not match',
    ],

    

] );