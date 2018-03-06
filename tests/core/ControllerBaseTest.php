<?php

use Core\Controllers\ControllerBase;

use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Document\Link;

class ControllerBaseTest extends UnitTestCase
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
    public function testGetUrlParams()
    {
        //Mock request
        $request = Mockery::mock('Request');
        $request->shouldReceive("get")->andReturn([
            'test1' => '11111',
            'test2' => '22222',
            '_url'  => 'http://www.test.com/',
        ]);

        //register
        $this->di->set('request', $request, true);

        //create class
        $class = new ControllerBase();

        $result = $this->callMethod(
            $class,
            'getUrlParams',
            []
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result['test1'], '11111');
        $this->assertEquals($result['test2'], '22222');
    }

    public function testGetPostInput()
    {
        //Mock request
        $request = Mockery::mock('Request');
        $request->shouldReceive("getRawBody")->andReturn('{"test" : "11111"}');

        //register
        $this->di->set('request', $request, true);

        //create class
        $class = new ControllerBase();

        $result = $this->callMethod(
            $class,
            'getPostInput',
            []
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('test', $result);
        $this->assertEquals($result['test'], '11111');
    }

    public function testGetPostInputNoData()
    {
        //Mock request
        $request = Mockery::mock('Request');
        $request->shouldReceive("getRawBody")->andReturn('');

        //register
        $this->di->set('request', $request, true);

        //create class
        $class = new ControllerBase();

        $result = $this->callMethod(
            $class,
            'getPostInput',
            []
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, []);
    }

    public function testResponseData()
    {
        //Mock response
        $response = Mockery::mock('Response');
        $response->shouldReceive("setContentType")->andReturn(true);
        $response->shouldReceive("setStatusCode")->andReturn(true);
        $response->shouldReceive("setJsonContent")->andReturn(true);

        //register
        $this->di->set('response', $response, true);

        //create class
        $class = new ControllerBase();

        $result = $this->callMethod(
            $class,
            'responseData',
            [[], 200, 'success']
        );

        //check result
        $this->assertEquals($result, $response);
    }

    public function testOutput()
    {
        //create class
        $class = $this->getMockBuilder('Core\Controllers\ControllerBase')
                    ->setMethods([
                        'responseData',
                    ])
                    ->getMock();

        $class->method('responseData')
            ->willReturn("TEST");

        $result = $this->callMethod(
            $class,
            'output',
            ["{test:xxx}"]
        );

        $this->assertEquals("TEST", $result);
    }

    public function testCreateEncode()
    {
        //mock config
        $config = new \Phalcon\Config( [
            'application' => [
                'baseUri' => 'http://localhost.dev'
            ]   
        ] );

        //register config
        $this->di->set('config', $config, true);

        //create class
        $class = new ControllerBase;

        $result = $this->callMethod(
            $class,
            'createEncoder',
            ["Core\\Collections\\TestCollection", "Core\\Collections\\TestSchema"]
        );

        $this->assertNotEmpty($result);
        $this->assertInternalType('object', $result);
    }

    public function testCreatePaginateObjNoLimit()
    {
        //create class
        $class = new ControllerBase;

        $result = $this->callMethod(
            $class,
            'createPaginateObj',
            [null, null, null]
        );

        $this->assertEmpty($result);
    }

    public function testCreatePaginateObjWithLimit()
    {
        //create class
        $class = new ControllerBase;

        $result = $this->callMethod(
            $class,
            'createPaginateObj',
            [10, 0, 20]
        );

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('limit', $result);
        $this->assertArrayHasKey('offset', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertEquals(10, $result['limit']);
        $this->assertEquals(0, $result['offset']);
        $this->assertEquals(20, $result['total']);
    }

    public function testResponseNoPaginate()
    {
        //mock datas
        $datas  = Mockery::mock('Datas');
        //mock encoder
        $encoder = Mockery::mock('Encoder');
        $encoder->shouldReceive('encodeData')->andReturn($datas);
        //create class
        $class = $this->getMockBuilder('Core\Controllers\ControllerBase')
                    ->setMethods([
                        'createPaginateObj',
                        'output',
                    ])
                    ->getMock();

        $class->method('createPaginateObj')
            ->willReturn([]);

        $class->method('output')
            ->willReturn("TEST");

        $result = $this->callMethod(
            $class,
            'response',
            [$encoder, []]
        );

        $this->assertEquals("TEST", $result);
    }

    public function testResponseWithPaginate()
    {
        //mock datas
        $datas  = Mockery::mock('Datas');
        //mock encoder
        $encoderMeta = Mockery::mock('EncoderMeta');
        $encoderMeta->shouldReceive('encodeData')->andReturn($datas);

        $encoder = Mockery::mock('Encoder');
        $encoder->shouldReceive('withMeta')->andReturn($encoderMeta);
        //create class
        $class = $this->getMockBuilder('Core\Controllers\ControllerBase')
                    ->setMethods([
                        'createPaginateObj',
                        'output',
                    ])
                    ->getMock();

        $class->method('createPaginateObj')
            ->willReturn([
                'limit'  => 10,
                'offset' => 0,
                'total'  => 20,
            ]);

        $class->method('output')
            ->willReturn("TEST");

        $result = $this->callMethod(
            $class,
            'response',
            [$encoder, [], 10, 0, 20]
        );

        $this->assertEquals("TEST", $result);
    }

    public function testGetErrorObjFromKeyNoKeyInMessage()
    {
        //mock message
        $message = new \Phalcon\Config( [] );

        //register message
        $this->di->set('message', $message, true);

        //create class
        $class = new ControllerBase;

        $result = $this->callMethod(
            $class,
            'getErrorObjFromKey',
            ['test']
        );

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals('test', $result['message']);
        $this->assertEquals('Error', $result['status']);

    }

    public function testGetErrorObjFromKeyHaveKeyInMessage()
    {
        //mock message
        $message = new \Phalcon\Config( [
            'test' => [
                'code'      => 404,
                'msgError'  => 'Mission Fail',
                'msgStatus' => 'ERROR',
            ]
        ] );

        //register message
        $this->di->set('message', $message, true);

        //create class
        $class = new ControllerBase;

        $result = $this->callMethod(
            $class,
            'getErrorObjFromKey',
            ['test']
        );

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals(404, $result['code']);
        $this->assertEquals('Mission Fail', $result['message']);
        $this->assertEquals('ERROR', $result['status']);
    }

    public function testResponseErrorKeyIsArray()
    {
        //mock config
        $config = new \Phalcon\Config( [
            'application' => [
                'baseUri' => 'http://localhost.dev'
            ]
        ] );

        //register message
        $this->di->set('config', $config, true);

        //create class
        $class = $this->getMockBuilder('Core\Controllers\ControllerBase')
                    ->setMethods([
                        'getErrorObjFromKey',
                        'output',
                    ])
                    ->getMock();

        $class->method('getErrorObjFromKey')
            ->willReturn([
                'status'  => 'ERROR',
                'code'    => 404,
                'message' => 'Mission Fail',
            ]);

        $class->method('output')
            ->willReturn("TEST");

        $result = $this->callMethod(
            $class,
            'responseError',
            [[
                'code'    => 400, 
                'status'  => 'Error', 
                'message' => 'Test Error', 
                'datas'   => [], 
            ], 'users']
        );

        $this->assertEquals('TEST', $result);
    }

    public function testResponseError()
    {
        //mock config
        $config = new \Phalcon\Config( [
            'application' => [
                'baseUri' => 'http://localhost.dev'
            ]
        ] );

        //register message
        $this->di->set('config', $config, true);

        //create class
        $class = $this->getMockBuilder('Core\Controllers\ControllerBase')
                    ->setMethods([
                        'getErrorObjFromKey',
                        'output',
                    ])
                    ->getMock();

        $class->method('getErrorObjFromKey')
            ->willReturn([
                'status'  => 'ERROR',
                'code'    => 404,
                'message' => 'Mission Fail',
                'datas'   => [],
            ]);

        $class->method('output')
            ->willReturn("TEST");

        $result = $this->callMethod(
            $class,
            'responseError',
            ['test', 'users']
        );

        $this->assertEquals('TEST', $result);
    }

    public function testGetLanguageFromHeaderNoLanguage()
    {
        //mock request
        $request = Mockery::mock('Request');
        $request->shouldReceive('getHeaders')->andReturn([]);

        //register request
        $this->di->set('request', $request, true);

        //create class
        $class = new ControllerBase();

        $result = $this->callMethod(
            $class,
            'getLanguageFromHeader',
            []
        );

        $this->assertEquals('en', $result);
    }

    public function testGetLanguageFromHeaderHaveLanguage()
    {
        //mock request
        $request = Mockery::mock('Request');
        $request->shouldReceive('getHeaders')->andReturn([
            'Language' => 'th'
        ]);

        //register request
        $this->di->set('request', $request, true);

        //create class
        $class = new ControllerBase();

        $result = $this->callMethod(
            $class,
            'getLanguageFromHeader',
            []
        );

        $this->assertEquals('th', $result);
    }

    //------- end: Test function ---------//
}
