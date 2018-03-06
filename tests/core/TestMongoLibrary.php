<?php

use Core\Libraries\MongoLibrary;
use Phalcon\Exception;

class TestMongoLibrary extends UnitTestCase
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
    public function testReplaceSpecialKeyOfRegex()
    {
        //create class
        $class = new MongoLibrary();

        //create parameter
        $params = ['ss$dd)gg^'];

        //call method
        $result = $this->callMethod(
            $class,
            'replaceSpecialKeyOfRegex',
            $params
        );
        
        //check result
        $this->assertEquals('ss\\$dd\\)gg\\^', $result);
    }

    public function testConvertValueForSearchLikeNoPercent()
    {
        //creste class
        $class = $this->getMockBuilder('Core\Libraries\MongoLibrary')
                    ->setMethods(['replaceSpecialKeyOfRegex'])
                    ->getMock();

        $class->method('replaceSpecialKeyOfRegex')
            ->willReturn("Test");

        //create parameter
        $params = ["Test"];

        //call method
        $result = $this->callMethod(
            $class,
            'convertValueForSearchLike',
            $params
        );

        //check result
        $this->assertFalse($result[0]);
        $this->assertEquals('Test', $result[1]);
    }

    public function testConvertValueForSearchLikePercentAtLast()
    {
        //creste class
        $class = $this->getMockBuilder('Core\Libraries\MongoLibrary')
                    ->setMethods(['replaceSpecialKeyOfRegex'])
                    ->getMock();

        $class->method('replaceSpecialKeyOfRegex')
            ->willReturn("Te%");

        //create parameter
        $params = ["Te%"];

        //call method
        $result = $this->callMethod(
            $class,
            'convertValueForSearchLike',
            $params
        );

        //check result
        $this->assertTrue($result[0]);
        $this->assertEquals('^Te', $result[1]);
    }

    public function testConvertValueForSearchLikePercentAtFirst()
    {
        //creste class
        $class = $this->getMockBuilder('Core\Libraries\MongoLibrary')
                    ->setMethods(['replaceSpecialKeyOfRegex'])
                    ->getMock();

        $class->method('replaceSpecialKeyOfRegex')
            ->willReturn("%est");


        //create parameter
        $params = ["%est"];

        //call method
        $result = $this->callMethod(
            $class,
            'convertValueForSearchLike',
            $params
        );

        //check result
        $this->assertTrue($result[0]);
        $this->assertEquals('est$', $result[1]);
    }

    public function testConvertValueForSearchLikePercentAtFirstAndLast()
    {
        //creste class
        $class = $this->getMockBuilder('Core\Libraries\MongoLibrary')
                    ->setMethods(['replaceSpecialKeyOfRegex'])
                    ->getMock();

        $class->method('replaceSpecialKeyOfRegex')
            ->willReturn("%es%");

        //create parameter
        $params = ["%es%"];

        //call method
        $result = $this->callMethod(
            $class,
            'convertValueForSearchLike',
            $params
        );

        //check result
        $this->assertTrue($result[0]);
        $this->assertEquals('es', $result[1]);
    }

    public function testManangeBetweenCondition()
    {
        //create class
        $class = new MongoLibrary();

        //call method
        $result = $this->callMethod(
            $class,
            'manangeBetweenCondition',
            [[], ['test' => [1,5]], 'test', ['$gte', '$lte']]
        );
        
        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('test', $result);
        $this->assertInternalType('array', $result['test']);
        $this->assertArrayHasKey('$gte', $result['test']);
        $this->assertArrayHasKey('$lte', $result['test']);
        $this->assertEquals(1,  $result['test']['$gte']);
        $this->assertEquals(5,  $result['test']['$lte']);
    }

    public function testManageFilterValueHaveLike()
    {
        //creste class
        $class = $this->getMockBuilder('Core\Libraries\MongoLibrary')
                    ->setMethods(['convertValueForSearchLike'])
                    ->getMock();

        $class->method('convertValueForSearchLike')
            ->willReturn([true, '^Tes']);

        //call method
        $result = $class->manageFilterValue('key', 'Tes%', []);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('key', $result);
        $this->assertArrayHasKey('$regex', $result['key']);
        $this->assertEquals('^Tes', $result['key']['$regex']);
    }

    public function testManageFilterValueNotLikeNullValue()
    {
        //creste class
        $class = $this->getMockBuilder('Core\Libraries\MongoLibrary')
                    ->setMethods(['convertValueForSearchLike'])
                    ->getMock();

        $class->method('convertValueForSearchLike')
            ->willReturn([false, 'null']);

        //call method
        $result = $class->manageFilterValue('key', 'null', []);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('key', $result);
        $this->assertNull($result['key']);
    }

    public function testManageFilterValueNotLikeNotNullValue()
    {
        //creste class
        $class = $this->getMockBuilder('Core\Libraries\MongoLibrary')
                    ->setMethods(['convertValueForSearchLike'])
                    ->getMock();

        $class->method('convertValueForSearchLike')
            ->willReturn([false, 'Test']);

        //call method
        $result = $class->manageFilterValue('key', 'Test', []);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('key', $result);
        $this->assertEquals('Test', $result['key']);
    }

    public function testCreateConditionFilter()
    {
        //creste class
        $class = $this->getMockBuilder('Core\Libraries\MongoLibrary')
                    ->setMethods(['manageFilterValue', 'manangeBetweenCondition'])
                    ->getMock();

        $class->method('manageFilterValue')
            ->willReturn(['key1' => 'Test1']);

        $class->method('manangeBetweenCondition')
            ->willReturn([
                'key1' => 'Test1',
                'key3' => [
                    '$in' => ['Test3']
                ],
                'key4' => [
                    '$gte' => 1,
                    '$lte' => 5,
                ]
            ]);

        //call method
        $result = $class->createConditionFilter(['key1' => 'Test1', 'key2' => 'Test2', 'key3' => ['Test3'], 'key4' => [1,5]], ['key1', 'key3', 'key4'], ['key3' => '$in', 'key4' => ['$gte', '$lte']]);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('key1', $result);
        $this->assertEquals('Test1', $result['key1']);
        $this->assertFalse(isset($result['ke2']));
        $this->assertArrayHasKey('key3', $result);
        $this->assertArrayHasKey('$in', $result['key3']);
        $this->assertInternalType('array', $result['key3']['$in']);
        $this->assertEquals(['Test3'], $result['key3']['$in']);
    }

    public function testManageLimitOffsetInParams()
    {
        //create class
        $class = new MongoLibrary();

        //call method
        $result = $class->manageLimitOffsetInParams(['limit' => 5, 'offset' => 0], []);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('limit', $result);
        $this->assertEquals(5, $result['limit']);
        $this->assertArrayHasKey('skip', $result);
        $this->assertEquals(0, $result['skip']);
    }

    public function testConvertOrderTypeOther()
    {
        //create class
        $class = new MongoLibrary();

        //create parameter
        $params = ['asc'];

        //call method
        $result = $this->callMethod(
            $class,
            'convertOrderType',
            $params
        );

        //check result
        $this->assertEquals(1, $result);
    }

    public function testConvertOrderTypeDesc()
    {
        //create class
        $class = new MongoLibrary();

        //create parameter
        $params = ['desc'];

        //call method
        $result = $this->callMethod(
            $class,
            'convertOrderType',
            $params
        );

        //check result
        $this->assertEquals(-1, $result);
    }

    public function testManageOrderInParamsNoOrder()
    {
        //create class
        $class = new MongoLibrary();

        //call method
        $result = $class->manageOrderInParams([], [], []);

        //check result
        $this->assertEmpty($result);
    }

    public function testManageOrderInParamsHaveOrderNotAllowAll()
    {
        //creste class
        $class = $this->getMockBuilder('Core\Libraries\MongoLibrary')
                    ->setMethods(['convertOrderType'])
                    ->getMock();

        $class->method('convertOrderType')
            ->willReturn(1);

        //call method
        $result = $class->manageOrderInParams([
            'order_by' => 'name:asc,description,name1:desc'
        ], [], ['xxx']);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertFalse(isset($result['sort']));
    }

    public function testManageOrderInParamsHaveOrder()
    {
        //creste class
        $class = $this->getMockBuilder('Core\Libraries\MongoLibrary')
                    ->setMethods(['convertOrderType'])
                    ->getMock();

        $class->method('convertOrderType')
            ->willReturn(1);

        //call method
        $result = $class->manageOrderInParams([
            'order_by' => 'name:asc,description,name1:desc'
        ], [], ['name', 'description']);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sort', $result);
        $this->assertEquals(1, count($result['sort']));
        $this->assertArrayHasKey('name', $result['sort']);
        $this->assertEquals(1, $result['sort']['name']);
    }

    public function testManageSortDataByIdList()
    {
        //create class
        $class = new MongoLibrary();

        //call method
        $result = $class->manageSortDataByIdList([
            [
                'id'   => '111',
                'name' => 'Test 1'
            ],[
                'id'   => '222',
                'name' => 'Test 2'
            ],[
                'id'   => '333',
                'name' => 'Test 3'
            ]
        ], '222,111,333');

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertEquals('222', $result[0]['id']);
        $this->assertArrayHasKey('id', $result[1]);
        $this->assertEquals('111', $result[1]['id']);
        $this->assertArrayHasKey('id', $result[2]);
        $this->assertEquals('333', $result[2]['id']);
    }

    public function testGetDetailDataById()
    {
        //Mock model 
        $model = Mockery::mock('Model');
        $model->shouldReceive('find')->andReturn([[
                'id'   => '58abd2f22f8331000a3acb91',
                'name' => 'Test'
            ]]);

        //creste class
        $class = $this->getMockBuilder('Core\Libraries\MongoLibrary')
                    ->setMethods(['createConditionFilter'])
                    ->getMock();

        $class->method('createConditionFilter')
            ->willReturn([
                'id' => [
                    '$in' => ['58abd2f22f8331000a3acb91']
                ]
            ]);

        //call method
        $result = $class->getDetailDataById($model, '58abd2f22f8331000a3acb91', ['id']);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('name', $result[0]);
    }

    public function testManageBetweenFilterNoKey()
    {
        //create class
        $class = new MongoLibrary();

        $params  = [
            'date_start' => '2017-01-01', 
            'date_end'   => '2017-05-01',
        ];
        $options = [];
        //call method
        $result = $class->manageBetweenFilter($params, $options);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($params, $result);
    }

    public function testManageBetweenFilterHaveKeyDateWrong()
    {
        //create class
        $class = new MongoLibrary();

        $params  = [
            'date_start'  => '2017-01-01', 
            'between_key' => 'date',
        ];
        $options = [];

        //call method
        $result = $class->manageBetweenFilter($params, $options);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($params, $result);
    }

    public function testManageBetweenFilterHaveKeySuccess()
    {
        //create class
        $class   = new MongoLibrary();
        
        $params  = [
            'date_start'  => '2017-01-01', 
            'date_end'    => '2017-05-01',
            'between_key' => 'date',
        ];
        $options = [];

        //call method
        $result = $class->manageBetweenFilter($params, $options);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('date', $result);
        $this->assertArrayNotHasKey('date_start', $result);
        $this->assertArrayNotHasKey('date_end', $result);
        $this->assertInternalType('array', $result['date']);
        $this->assertInternalType('array', $options);
        $this->assertArrayHasKey('date', $options);
    }
    //------- end: Test function ---------//
}