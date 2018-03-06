<?php
namespace Users\Services;

use Users\Repositories\UserRepositories;
use Users\Collections\UserCollection;

class UserService extends UserRepositories
{
    //==== Start: Define variable ====//
    
    //==== End: Define variable ====//


    //==== Start: Support method ====//

    //Method for create filter for check duplicate
    protected function createFilterForCheckDup(string $username, string $refType): array
    {
        return [
            'username' => $username,
            'ref_type' => $refType,
        ];
    }

    //Method for encrypt password
    protected function encryptPassword($password)
    {
        if (!empty($password)) {
            return $this->security->hash($password);
        }
        return "";
    }

    //Method for check password
    protected function checkPassword($passInDb, $passCheck)
    {
        return $this->security->checkHash($passCheck, $passInDb);
    }


    //==== End: Support method ====//


    //==== Stat: Main method ====//
    //Method for get data by filter
    public function getUser(array $params, ?int $limit, ?int $offset): array
    {
        //Define output
        $outputs = [
            'success' => true,
            'message' => '',
        ];

        try {
            //create filter
            $users         = $this->getDataByParams($params);

            if (!empty($limit)) {
                //get total record
                $outputs['total'] = $users[1];
            }

            $outputs['data'] = $users[0];

        } catch (\Exception $e) {
            $outputs['success'] = false;
            $outputs['message'] = 'missionFail';
        }
        

        return $outputs;
    }

    //Method for get data by id
    public function getUserDetail(string $id): array
    {
        //Define output
        $outputs = [
            'success' => true,
            'message' => '',
        ];

        try {
            //create filter
            $user  = $this->getDataById($id);

            if (empty($user)){
                $outputs['success'] = false;
                $outputs['message'] = 'dataNotFound';
                return $outputs;
            }

            $outputs['data'] = $user;

        } catch (\Exception $e) {
            $outputs['success'] = false;
            $outputs['message'] = 'missionFail';
        }
        

        return $outputs;
    }


    //Method for insert data
    public function createUser(array $params): array
    {
        //Define output
        $output = [
            'success' => true,
            'message' => '',
            'data'    => '',
        ];

        //Check Duplicate
        $filters = $this->createFilterForCheckDup($params['username'], $params['ref_type']);
        $isDups  = $this->checkDuplicate($filters);

        if ($isDups[0]) {
            //Cannot insert
            $output['success'] = false;
            $output['message'] = 'dataDuplicate';
            return $output;
        } 

        //get current date
        $current = date('Y-m-d H:i:s');
        
        //default date
        $params['created_at'] = $current;
        $params['updated_at'] = $current;

        //encrypt password
        $params['password']   = $this->encryptPassword($params['password']);

        //insert
        $res = $this->insertData($params);

        if (!$res)
        {
            //Cannot insert
            $output['success'] = false;
            $output['message'] = 'insertError';
            return $output;
        } 

        
        //add config data
        $output['data'] = $res;

        return $output;
    }

    //Method for update data
    public function updateUser(array $params): array
    {
        //Define output
        $output = [
            'success' => true,
            'message' => '',
            'data'    => '',
        ];
        
        //get data by id
        $user  = $this->getDataById($params['id']);

        //get value for check duplicate
        $username = isset($params['username'])? $params['username'] : $user->username;
        $refType  = isset($params['ref_type'])? $params['ref_type'] : $user->ref_type;

        //Check Duplicate
        $filters = $this->createFilterForCheckDup($username, $refType);
        $isDups  = $this->checkDuplicate($filters);

        if ( $isDups[0] && ((string)$isDups[1]->_id != $params['id']) ) {
            //Cannot insert
            $output['success'] = false;
            $output['message'] = 'dataDuplicate';
            return $output;
        } 

        //default date
        $params['updated_at'] = date('Y-m-d H:i:s');
        //remove password
        unset($params['password']);
        
        //update
        $res = $this->updateData($user, $params);

        if (!$res)
        {
            //Cannot insert
            $output['success'] = false;
            $output['message'] = 'updateError';
            return $output;
        }

        
        //add config data
        $output['data'] = $res;

        return $output;
    }


    //Method for delete data
    public function deleteUser(string $id): array
    {
        //Define output
        $output = [
            'success' => true,
            'message' => '',
            'data'    => '',
        ];

        //get data by id
        $user  = $this->getDataById($id);

        if (empty($user))
        {
            //No Data
            $output['success'] = false;
            $output['message'] = 'dataNotFound';
            return $output;
        }

        //delete
        $res = $this->deleteData($user);

        if (!$res)
        {
            //Cannot insert
            $output['success'] = false;
            $output['message'] = 'deleteError';
            return $output;
        }

        //get insert id
        $output['data'] = $res;

        return $output;
    }

    //Method for check login
    public function checkLogin(array $params) :array
    {
        //Define output
        $output = [
            'success' => true,
            'message' => '',
        ];

        //keep password
        $password = $params['password'];

        //remove password before get user
        unset($params['password']);

        //get user 
        $users = $this->getDataByParams($params)[0];

        if (empty($users[0])) {
            //No Data
            $output['success'] = false;
            $output['message'] = 'dataNotFound';
            return $output;
        }

        //validate login
        $res = $this->checkPassword($users[0]->password, $password);

        if (!$res)
        {
            //Cannot insert
            $output['success'] = false;
            $output['message'] = 'loginFail';
            return $output;
        }

        $output['data'] = $users[0];

        return $output;
    }

    //Method for change password
    public function changePassword(string $id, string $oldPass, string $newPass) :array
    {
        //Define output
        $output = [
            'success' => true,
            'message' => '',
            'data'    => '',
        ];

        //get user data
        $user = $this->getDataById($id);
        
        if (empty($user)) {
            //No Data
            $output['success'] = false;
            $output['message'] = 'dataNotFound';
            return $output;
        }

        //check old password
        if (!$this->checkPassword($user->password, $oldPass)) {
            //old password not match
            $output['success'] = false;
            $output['message'] = 'oldPasswordWrong';
            return $output;
        }

        
        //Define parameter for update
        $params = [
            'password'   => $this->encryptPassword($newPass), //encrypt password
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        //update password
        $res = $this->updateData($user, $params);

        if (!$res)
        {
            //Cannot insert
            $output['success'] = false;
            $output['message'] = 'updateError';
            return $output;
        }

        $output['data'] = $res;

        return $output;
    }
    //==== End: Main method ====//
}