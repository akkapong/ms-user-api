<?php
namespace Users\Repositories;

use Core\Repositories\CollectionRepositories;
use Users\Collections\UserCollection;

class UserRepositories extends CollectionRepositories {

    //==== Start: Define variable ====//
    public $module         = 'users';
    public $collectionName = 'UserCollection';
    public $allowFilter    = ['username', 'password', 'ref_type', 'ref_id', 'status', 'created_at', 'updated_at'];
    public $model;
    //==== Start: Define variable ====//


    //==== Start: Support method ====//
    public function __construct()
    {
        $this->model = new UserCollection();
        parent::__construct();
    }
    //==== End: Support method ====//
}