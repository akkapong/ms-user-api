<?php

use Core\Validations\MyValidations;
use Phalcon\Exception;

class TestMyValidations extends UnitTestCase
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
    public function testManageValidateResponseNoError()
    {
        //create class
        $class = new MyValidations();

        $result = $this->callMethod(
            $class,
            'manageValidateResponse',
            [[]]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testManageValidateResponseHaveError()
    {
        //mock message
        $message =  new \Phalcon\Config( [
            'validateFail' => [
                'code'     => 400,
                'status'   => 'Error',
                'msgError' => 'Validate Fail',
            ]   
        ] );

        //register message
        $this->di->set('message', $message, true);

        //mock messageErr
        $messageErr = Mockery::mock('MessageErr');
        $messageErr->shouldReceive('getMessage')->andReturn("The test is required");
        $messageErr->shouldReceive('getField')->andReturn("test");

        $messageErrs = [$messageErr];
        //create class
        $class = new MyValidations();

        $result = $this->callMethod(
            $class,
            'manageValidateResponse',
            [$messageErrs]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('validate_error', $result);
        $this->assertInternalType('array', $result['validate_error']);
        $this->assertArrayHasKey('status', $result['validate_error']);
        $this->assertArrayHasKey('message', $result['validate_error']);
        $this->assertArrayHasKey('code', $result['validate_error']);
        $this->assertArrayHasKey('datas', $result['validate_error']);
        $this->assertEquals(400, $result['validate_error']['code']);
        $this->assertEquals('Error', $result['validate_error']['status']);
        $this->assertEquals('Validate Fail', $result['validate_error']['message']);
        $this->assertInternalType('array', $result['validate_error']['datas']);
        $this->assertCount(1, $result['validate_error']['datas']);
        $this->assertArrayHasKey('msgError', $result['validate_error']['datas'][0]);
        $this->assertArrayHasKey('fieldError', $result['validate_error']['datas'][0]);
        $this->assertEquals('The test is required', $result['validate_error']['datas'][0]['msgError']);
        $this->assertEquals('test', $result['validate_error']['datas'][0]['fieldError']);
    }

    public function testValidateApiError()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'required',
                'fields' => ['test1', 'test2'],
            ],
        ];

        $input = [
            'test1' => "11111",
            'test2' => "22222",
        ];

        //Mock error
        $error = [
            'status'  => 'Error',
            'code'    => 400,
            'message' => 'Validate Fail',
            'datas'   => [
                [
                    'msgError'   => 'The test is required',
                    'fieldError' => 'test',
                ]
            ]
        ];

        //create class
        $class = $this->getMockBuilder('Core\Validations\MyValidations')
                    ->setMethods(['validate'])
                    ->getMock();

        $class->method('validate')
            ->willReturn([
                'validate_error' => $error,
            ]);

        $result = $this->callMethod(
            $class,
            'validateApi',
            [$rules, [], $input]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('validate_error', $result);
        $this->assertInternalType('array', $result['validate_error']);
        $this->assertArrayHasKey('status', $result['validate_error']);
        $this->assertArrayHasKey('message', $result['validate_error']);
        $this->assertArrayHasKey('code', $result['validate_error']);
        $this->assertArrayHasKey('datas', $result['validate_error']);
        $this->assertEquals(400, $result['validate_error']['code']);
        $this->assertEquals('Error', $result['validate_error']['status']);
        $this->assertEquals('Validate Fail', $result['validate_error']['message']);
        $this->assertInternalType('array', $result['validate_error']['datas']);
        $this->assertCount(1, $result['validate_error']['datas']);
        $this->assertArrayHasKey('msgError', $result['validate_error']['datas'][0]);
        $this->assertArrayHasKey('fieldError', $result['validate_error']['datas'][0]);
        $this->assertEquals('The test is required', $result['validate_error']['datas'][0]['msgError']);
        $this->assertEquals('test', $result['validate_error']['datas'][0]['fieldError']);
    }

    public function testValidateApiSuccessNoDefault()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'required',
                'fields' => ['test1', 'test2'],
            ],
        ];

        $input = [
            'test1' => "11111",
            'test2' => "22222",
        ];

        //create class
        $class = $this->getMockBuilder('Core\Validations\MyValidations')
                    ->setMethods(['validate'])
                    ->getMock();

        $class->method('validate')
            ->willReturn([]);

        $result = $this->callMethod(
            $class,
            'validateApi',
            [$rules, [], $input]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, $input);
    }

    public function testValidateApiSuccessHaveDefault()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'required',
                'fields' => ['test1', 'test2'],
            ],
        ];

        $default = [
            'test1' => '44444',
            'test3' => '33333',
        ];

        $input = [
            'test1' => "11111",
            'test2' => "22222",
        ];

        //create class
        $class = $this->getMockBuilder('Core\Validations\MyValidations')
                    ->setMethods(['validate'])
                    ->getMock();

        $class->method('validate')
            ->willReturn([]);

        $result = $this->callMethod(
            $class,
            'validateApi',
            [$rules, $default, $input]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals(count($result), 3);
        $this->assertEquals($result['test1'], '11111');
        $this->assertEquals($result['test3'], '33333');
    }

    public function testValidateNoRequired()
    {
        //mock message
        $message =  new \Phalcon\Config( [
            'validateFail' => [
                'code'     => 400,
                'status'   => 'Error',
                'msgError' => 'Validate Fail',
            ]   
        ] );

        //register message
        $this->di->set('message', $message, true);

        //Define parameter
        $rules = [
            [
                'type'   => 'required',
                'fields' => ['test1', 'test2'],
            ],
        ];
        $input = [
            'test1' => "11111",
            'test3' => "33333",
        ];

        // //create class
        $class = new MyValidations();
        
        $result = $this->callMethod(
            $class,
            'validate',
            [$input, $rules]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('validate_error', $result);
        $this->assertInternalType('array', $result['validate_error']);
        $this->assertArrayHasKey('status', $result['validate_error']);
        $this->assertArrayHasKey('message', $result['validate_error']);
        $this->assertArrayHasKey('code', $result['validate_error']);
        $this->assertArrayHasKey('datas', $result['validate_error']);
        $this->assertEquals(400, $result['validate_error']['code']);
        $this->assertEquals('Error', $result['validate_error']['status']);
        $this->assertEquals('Validate Fail', $result['validate_error']['message']);
        $this->assertInternalType('array', $result['validate_error']['datas']);
        $this->assertCount(1, $result['validate_error']['datas']);
        $this->assertArrayHasKey('msgError', $result['validate_error']['datas'][0]);
        $this->assertArrayHasKey('fieldError', $result['validate_error']['datas'][0]);
        $this->assertEquals('The test2 is required', $result['validate_error']['datas'][0]['msgError']);
        $this->assertEquals('test2', $result['validate_error']['datas'][0]['fieldError']);
    }

    public function testValidateNoNumber()
    {
        //mock message
        $message =  new \Phalcon\Config( [
            'validateFail' => [
                'code'     => 400,
                'status'   => 'Error',
                'msgError' => 'Validate Fail',
            ]   
        ] );

        //register message
        $this->di->set('message', $message, true);


        //Define parameter
        $rules = [
            [
                'type'   => 'number',
                'fields' => ['test1', 'test2'],
            ],
        ];
        $input = [
            'test1' => "11111",
            'test3' => "xxxxx",
        ];

        $class = new MyValidations();

        $result = $this->callMethod(
            $class,
            'validate',
            [$input, $rules]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('validate_error', $result);
        $this->assertInternalType('array', $result['validate_error']);
        $this->assertArrayHasKey('status', $result['validate_error']);
        $this->assertArrayHasKey('message', $result['validate_error']);
        $this->assertArrayHasKey('code', $result['validate_error']);
        $this->assertArrayHasKey('datas', $result['validate_error']);
        $this->assertEquals(400, $result['validate_error']['code']);
        $this->assertEquals('Error', $result['validate_error']['status']);
        $this->assertEquals('Validate Fail', $result['validate_error']['message']);
        $this->assertInternalType('array', $result['validate_error']['datas']);
        $this->assertCount(1, $result['validate_error']['datas']);
        $this->assertArrayHasKey('msgError', $result['validate_error']['datas'][0]);
        $this->assertArrayHasKey('fieldError', $result['validate_error']['datas'][0]);
        $this->assertEquals('Test2 must be numberic', $result['validate_error']['datas'][0]['msgError']);
        $this->assertEquals('test2', $result['validate_error']['datas'][0]['fieldError']);
    }

    public function testValidateNoType()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'test',
                'fields' => ['test1'],
            ],
        ];
        $input = [
            'test1' => "11111",
        ];

        $class = new MyValidations();

        $result = $this->callMethod(
            $class,
            'validate',
            [$input, $rules]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, []);
    }

    public function testValidateNotInWithin()
    {
        //mock message
        $message =  new \Phalcon\Config( [
            'validateFail' => [
                'code'     => 400,
                'status'   => 'Error',
                'msgError' => 'Validate Fail',
            ]   
        ] );

        //register message
        $this->di->set('message', $message, true);

        //Define parameter
        $rules = [
            [
                'type'   => 'within',
                'fields' => ['test1' => ['111', '222']],
            ],
        ];
        $input = [
            'test1' => "11111",
        ];

        $class = new MyValidations();

        $result = $this->callMethod(
            $class,
            'validate',
            [$input, $rules]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('validate_error', $result);
        $this->assertInternalType('array', $result['validate_error']);
        $this->assertArrayHasKey('status', $result['validate_error']);
        $this->assertArrayHasKey('message', $result['validate_error']);
        $this->assertArrayHasKey('code', $result['validate_error']);
        $this->assertArrayHasKey('datas', $result['validate_error']);
        $this->assertEquals(400, $result['validate_error']['code']);
        $this->assertEquals('Error', $result['validate_error']['status']);
        $this->assertEquals('Validate Fail', $result['validate_error']['message']);
        $this->assertInternalType('array', $result['validate_error']['datas']);
        $this->assertCount(1, $result['validate_error']['datas']);
        $this->assertArrayHasKey('msgError', $result['validate_error']['datas'][0]);
        $this->assertArrayHasKey('fieldError', $result['validate_error']['datas'][0]);
        $this->assertEquals('The test1 must be in 111 , 222', $result['validate_error']['datas'][0]['msgError']);
        $this->assertEquals('test1', $result['validate_error']['datas'][0]['fieldError']);
    }

    public function testValidateInWithin()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'within',
                'fields' => ['test1' => ['111', '222']],
            ],
        ];
        $input = [
            'test1' => "111",
        ];

        $class = new MyValidations();

        $result = $this->callMethod(
            $class,
            'validate',
            [$input, $rules]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, []);
    }

    public function testValidateSuccess()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'required',
                'fields' => ['test1'],
            ], [
                'type'   => 'accesstype',
                'fields' => ['test1'],
            ],
        ];
        $input = [
            'test1' => "MOBILE",
        ];

        $class = new MyValidations();

        $result = $this->callMethod(
            $class,
            'validate',
            [$input, $rules]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, []);
    }
    //------- end: Test function ---------//
}