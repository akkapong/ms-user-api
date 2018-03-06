<?php
namespace Core\Repositories;

use Phalcon\Exception;

class CollectionRepositories extends \Phalcon\Mvc\Micro {


    //==== Start: Define variable ====//
    //==== Start: Define variable ====//


    //==== Start: Support method ====//
    //Method for get data by filter
    protected function getDataByParams($params)
    {
        //Create conditions
        $conditions = $this->mongoLibrary->createConditionFilter($params, $this->allowFilter);
        //create model
        $model      = $this->model;
        
        $filterCon  = [$conditions];
        
        //Manage order
        $filterCon  = $this->mongoLibrary->manageOrderInParams($params, $filterCon, $this->allowFilter);

        //query data
        $total = 0;
        if (!isset($params['limit']) || empty($params['limit'])) {
            //no limit
            $datas = $model->find($filterCon);
            $total = count($datas);
        } else {
            //wirh limit
            $total     = $model->count($filterCon);
            $filterCon = $this->mongoLibrary->manageLimitOffsetInParams($params, $filterCon);
            $datas     = $model->find($filterCon);
            
        }

        return [$datas, $total];
    }

    //Method for get data by id
    protected function getDataById($id)
    {
        $model = $this->model;
        return $model->findById($id);
    }

    //Method for check duplicate (insert)
    protected function checkDuplicate($filter)
    {
        $model = $this->model;
        $datas = $model->find([$filter]);

        if (!empty($datas))
        {
            return [true, $datas[0]];
        }

        return [false];
    }

    //Method for insert data to db
    protected function insertData($params)
    {
        $model = $this->model;
        //add member data to model
        foreach ($params as $key => $value) {
            if ( property_exists($model, $key) ) {
                $model->{$key} = isset($value)?$value:'';
            }
        }

        if (!$model->save())
        {
            return null;
        }

        return $model;
    }

    //Method for update data to db
    protected function updateData($model, $params)
    {
        //add member data to model
        foreach ($params as $key => $value) {
            if ( property_exists($model, $key) ) {
                $model->{$key} = $value;
            }
        }
        
        if (!$model->save())
        {
            return null;
        }

        return $model;
    }

    //Method for delete data from db
    protected function deleteData($model)
    {
        if (!$model->delete())
        {
            return null;
        }

        return $model;
    }
    //==== End: Support method ====//
}