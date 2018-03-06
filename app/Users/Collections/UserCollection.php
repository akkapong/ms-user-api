<?php

namespace Users\Collections;

use Phalcon\Mvc\MongoCollection;

class UserCollection extends MongoCollection
{
    public $username;
    public $password;
    public $ref_type;
    public $ref_id;
    public $status;
    public $created_at;
    public $updated_at;

    public function getSource()
    {
        return 'users';
    }
}