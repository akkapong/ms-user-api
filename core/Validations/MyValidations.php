<?php
namespace Core\Validations;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\InclusionIn;

class MyValidations extends \Phalcon\Mvc\Micro
{
    //Method for manage validate response
    protected function manageValidateResponse($messages)
    {
        $responses = [];
        if (count($messages) > 0) {
            $errors             = [];
            $responses['validate_error'] = [];

            foreach ($messages as $message){
                $errors[] = [
                    'msgError'   => $message->getMessage(),
                    'fieldError' => $message->getField(),
                ];
            }
            $responses['validate_error']['status']  = $this->message->validateFail->status; 
            $responses['validate_error']['code']    = $this->message->validateFail->code; 
            $responses['validate_error']['message'] = $this->message->validateFail->msgError; 
            $responses['validate_error']['datas']   = $errors; 
        }

        
        return $responses;
    }

    public function validateApi($rules, $default = [], $input = [])
    {
        $return   = [];
        //=== Start: Validate process ====//
        $validate = $this->validate($input, $rules);

        if (!empty($validate['validate_error'])) {
            return $validate;
        }
        //=== End: Validate process ====//
        
        //Manage default
        foreach (array_keys($default) as $key) {

            if (isset($input[$key]) && !empty($input[$key])) {
                $return[$key] = $input[$key];
            } else {
                $return[$key] = $default[$key];
            }

        }

        return array_merge($input, $return);
    }

    public function validate($input, $rules)
    {
        $validation = new Validation();

        foreach ($rules as $value)
        {

            switch (strtolower($value['type']))
            {

                case 'required':

                    foreach ($value['fields'] as $field)
                    {
                        $validation->add($field, new PresenceOf([
                            'message' => 'The ' . $field . ' is required',
                        ]));
                    }

                    break;

                case 'number':

                    foreach ($value['fields'] as $field)
                    {
                        $validation->add($field, new Numericality([
                            'message' => ucfirst($field) . ' must be numberic',
                        ]));
                    }

                    break;

                case 'within':
                    foreach ( $value['fields'] as $key => $list ) {
                        $validation->add( $key, new InclusionIn( [
                            'message' => 'The '.$key.' must be in '.implode(" , ", $list),
                            'domain'  => $list
                        ]));
                    }
                    break;

                default:
                    //default
            }

        }

        $messages = $validation->validate($input);

        return $this->manageValidateResponse($messages);
    }
}