<?php

use Core\Repositories\CollectionRepositories;
use Phalcon\Exception;

class TestCollectionRepositories extends UnitTestCase
{
    //------ start: MOCK DATA ---------//
    //------ end: MOCK DATA ---------//

    //------- start: Method for support test --------//
    protected static function callMethod($obj, $name, array $args)
    {
        $class  = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
    //------- end: Method for support test --------//

    //------- start: Test function ---------//

    public function testGetDataByParamsNoLimit()
    {
        //mock MongoLibrary
        $mongoLibrary = Mockery::mock('MongoLibrary');
        $mongoLibrary->shouldReceive('createConditionFilter')->andReturn([
            'test' => '11111'
        ]);
        $mongoLibrary->shouldReceive('manageOrderInParams')->andReturn([[
            'test' => '11111'
        ]]);
        
        //register mongoLibrary
        $this->di->set('mongoLibrary', $mongoLibrary, true);

        //Mock model
        $model = Mockery::mock('Model');
        $model->shouldReceive('find')->andReturn([
            [
                "id"   => '58e27db58d6a71405dbbdb32',
                "test" => "11111" 
            ]
        ]);

        // //mock collection
        // $collection = Mockery::mock('Collection');
        // $collection->shouldReceive('get')->andReturn($model);

        // //register collections
        // $this->di->set('collections', $collection, true);


        //create repo
        $repo                 = new CollectionRepositories;
        $repo->allowFilter    = ['test'];
        $repo->module         = 'test';
        $repo->collectionName = 'tests';
        $repo->model          = $model;

        //create params
        $params = [['test' => '11111']];

        //call method
        $result = $this->callMethod(
            $repo,
            'getDataByParams',
            $params
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertInternalType('array', $result[0]);
        $this->assertInternalType('integer', $result[1]);
        $this->assertEquals(1, $result[1]);
        $this->assertEquals('58e27db58d6a71405dbbdb32', $result[0][0]['id']);
        $this->assertArrayHasKey('test', $result[0][0]);
    }

    public function testGetDataByParamsHaveLimit()
    {
        //mock mongoLibrary
        $mongoLibrary = Mockery::mock('MongoLibrary');
        $mongoLibrary->shouldReceive('createConditionFilter')->andReturn([
            'test' => '11111'
        ]);
        $mongoLibrary->shouldReceive('manageLimitOffsetInParams')->andReturn([
            'test' => '11111', 'limit' => 3, 'skip' => 0
        ]);
        $mongoLibrary->shouldReceive('manageOrderInParams')->andReturn([[
            'test' => '11111', 'limit' => 3, 'skip' => 0
        ]]);

        //register mongoLibrary
        $this->di->set('mongoLibrary', $mongoLibrary, true);

        //Mock model
        $model = Mockery::mock('Model');
        $model->shouldReceive('find')->andReturn([
            [
                "id"   => '58e27db58d6a71405dbbdb32',
                "test" => "11111" 
            ]
        ]);

        $model->shouldReceive('count')->andReturn(1);

        // //mock collection
        // $collection = Mockery::mock('Collection');
        // $collection->shouldReceive('get')->andReturn($model);

        // //register collections
        // $this->di->set('collections', $collection, true);



        //create repo
        $repo                 = new CollectionRepositories;
        $repo->allowFilter    = ['test'];
        $repo->module         = 'test';
        $repo->collectionName = 'tests';
        $repo->model          = $model;

        //create params
        $params = [['test' => '11111', 'limit' => 3]];

        //call method
        $result = $this->callMethod(
            $repo,
            'getDataByParams',
            $params
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertInternalType('array', $result[0]);
        $this->assertInternalType('integer', $result[1]);
        $this->assertEquals(1, $result[1]);
        $this->assertEquals('58e27db58d6a71405dbbdb32', $result[0][0]['id']);
        $this->assertArrayHasKey('test', $result[0][0]);
    }

    public function testGetDataById()
    {
        $datas = Mockery::mock('Data');
        //Mock model
        $model = Mockery::mock('Model');
        $model->shouldReceive('findById')->andReturn($datas);

        // //mock collection
        // $collection = Mockery::mock('Collection');
        // $collection->shouldReceive('get')->andReturn($model);

        // //register collections
        // $this->di->set('collections', $collection, true);

        //create repo
        $repo                 = new CollectionRepositories;
        $repo->module         = 'test';
        $repo->collectionName = 'tests';
        $repo->model          = $model;

        //call method
        $result = $this->callMethod(
            $repo,
            'getDataById',
            ['123']
        );

        //check result
        $this->assertInternalType('object', $result);
    }
    
    public function testCheckDuplicateCaseDup()
    {
        $datas = [Mockery::mock('Data')];
        //Mock model
        $model = Mockery::mock('Model');
        $model->shouldReceive('find')->andReturn($datas);

        // //mock collection
        // $collection = Mockery::mock('Collection');
        // $collection->shouldReceive('get')->andReturn($model);

        // //register collections
        // $this->di->set('collections', $collection, true);

        //create repo
        $repo                 = new CollectionRepositories;
        $repo->module         = 'test';
        $repo->collectionName = 'tests';
        $repo->model          = $model;

        //call method
        $result = $this->callMethod(
            $repo,
            'checkDuplicate',
            [['test' => '111']]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertTrue($result[0]);
        $this->assertInternalType('object', $result[1]);
    }

    public function testCheckDuplicateCaseNotDup()
    {
        $datas = [];
        //Mock model
        $model = Mockery::mock('Model');
        $model->shouldReceive('find')->andReturn($datas);

        // //mock collection
        // $collection = Mockery::mock('Collection');
        // $collection->shouldReceive('get')->andReturn($model);

        // //register collections
        // $this->di->set('collections', $collection, true);

        //create repo
        $repo                 = new CollectionRepositories;
        $repo->module         = 'test';
        $repo->collectionName = 'tests';
        $repo->model          = $model;

        //call method
        $result = $this->callMethod(
            $repo,
            'checkDuplicate',
            [['test' => '111']]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertFalse($result[0]);
    }

    public function testInsertDataCaseFail()
    {
        //Mock model
        $model = Mockery::mock('Model');
        $model->shouldReceive('save')->andReturn(false);
        $model->test = '';

        // //mock collection
        // $collection = Mockery::mock('Collection');
        // $collection->shouldReceive('get')->andReturn($model);

        // //register collections
        // $this->di->set('collections', $collection, true);

        //create repo
        $repo                 = new CollectionRepositories;
        $repo->module         = 'test';
        $repo->collectionName = 'tests';
        $repo->model          = $model;

        //call method
        $result = $this->callMethod(
            $repo,
            'insertData',
            [['test' => '111']]
        );

        //check result
        $this->assertNull($result);
    }

    public function testInsertDataCaseSuccess()
    {
        //Mock model
        $model = Mockery::mock('Model');
        $model->shouldReceive('save')->andReturn(true);
        $model->test = '';

        // //mock collection
        // $collection = Mockery::mock('Collection');
        // $collection->shouldReceive('get')->andReturn($model);

        // //register collections
        // $this->di->set('collections', $collection, true);

        //create repo
        $repo                 = new CollectionRepositories;
        $repo->module         = 'test';
        $repo->collectionName = 'tests';
        $repo->model          = $model;

        //call method
        $result = $this->callMethod(
            $repo,
            'insertData',
            [['test' => '111']]
        );

        //check result
        $this->assertInternalType('object', $result);
        $this->assertEquals($model, $result);
    }

    public function testUpdateDataCaseFail()
    {
        //Mock model
        $model = Mockery::mock('Model');
        $model->shouldReceive('save')->andReturn(false);
        $model->test = '';

        //create repo
        $repo                 = new CollectionRepositories;

        //call method
        $result = $this->callMethod(
            $repo,
            'updateData',
            [$model, ['test' => '111']]
        );

        //check result
        $this->assertNull($result);
    }

    public function testUpdateDataCaseSuccess()
    {
        //Mock model
        $model = Mockery::mock('Model');
        $model->shouldReceive('save')->andReturn(true);
        $model->test = '';

        //create repo
        $repo                 = new CollectionRepositories;

        //call method
        $result = $this->callMethod(
            $repo,
            'updateData',
            [$model, ['test' => '111']]
        );

        //check result
        $this->assertInternalType('object', $result);
        $this->assertEquals($model, $result);
    }

    public function testDeleteDataCaseFail()
    {
        //Mock model
        $model = Mockery::mock('Model');
        $model->shouldReceive('delete')->andReturn(false);

        //create repo
        $repo = new CollectionRepositories;

        //call method
        $result = $this->callMethod(
            $repo,
            'deleteData',
            [$model]
        );

        //check result
        $this->assertNull($result);
    }

    public function testDeleteDataCaseSuccess()
    {
        //Mock model
        $model = Mockery::mock('Model');
        $model->shouldReceive('delete')->andReturn(true);

        //create repo
        $repo = new CollectionRepositories;

        //call method
        $result = $this->callMethod(
            $repo,
            'deleteData',
            [$model]
        );

        //check result
        $this->assertInternalType('object', $result);
        $this->assertEquals($model, $result);
    }
    //------- end: Test function ---------//
}