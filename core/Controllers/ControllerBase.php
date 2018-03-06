<?php
namespace Core\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;

use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Document\Link;

/**
 * ControllerBase
 * This is the base controller for all controllers in the application
 */
class ControllerBase extends Controller
{

    /**
     * Execute before the router so we can determine if this is a provate controller, and must be authenticated, or a
     * public controller that is open to all.
     *
     * @param Dispatcher $dispatcher
     * @return boolean
     */
    

    //Method for get input fron url
    protected function getUrlParams()
    {
        $params = $this->request->get();
        //remobe url
        unset($params["_url"]);

        return $params;
    }

    //Method for get post paramerter
    public function getPostInput()
    {
        $rawInput = $this->request->getRawBody();
        //convert to array
        $inputs = json_decode($rawInput, true);

        //convert empty to array
        if (empty($inputs))
        {
            $inputs = [];
        }

        return $inputs;
    }

    // //Method for get put paramerter
    // public function getPutInput()
    // {
    //     $rawInput = $this->request->getRawBody();
    //     //convert to array
    //     $inputs = json_decode($rawInput, true);

    //     //convert empty to array
    //     if (empty($inputs))
    //     {
    //         $inputs = $this->request->getPut();
    //     }

    //     return $inputs;
    // }


    protected function responseData(array $data, int $status_code = 200, string $message = 'OK')
    {
        $this->response->setContentType("application/json", "UTF-8");
        $this->response->setStatusCode($status_code, $message);
        $this->response->setJsonContent($data);

        return $this->response;
    }

    public function output(string $output="{}", int $code=200)
    {
        return $this->responseData(json_decode($output, true), $code);
    }

    //Method for create encoder
    protected function createEncoder($className, $schemaName)
    {
        $data[$className] = $schemaName;
        $encoder = Encoder::instance($data, new EncoderOptions(JSON_PRETTY_PRINT, $this->config->application->baseUri)); 

        return $encoder;
    }

    //Method for create paginate object
    protected function createPaginateObj($limit, $offset, $total)
    {
        $paginateObj = [];
        //add limit offset to total 
        if (isset($limit) && isset($offset)) {
            $paginateObj['limit']  = (int)$limit;
            $paginateObj['offset'] = (int)$offset;
        }
        if (isset($total)) {
            $paginateObj['total'] = (int)$total;
        }

        return $paginateObj;
    }

    //Method for response success data
    protected function response($encoder, $data, $limit=null, $offset=null, $total=null, $encodeMethod='encodeData')
    {
        //create pagination object
        $paginateObj = $this->createPaginateObj($limit, $offset, $total);
        
        //Define output data
        $datas       = [];
        if (empty($paginateObj)) {
            //no paginate
            $datas = $encoder->{$encodeMethod}($data);
            //encodeIdentifiers
        } else {
            //with paginate
            $datas = $encoder->withMeta($paginateObj)->{$encodeMethod}($data);
        }
        

        return $this->output($datas);
    } 

    protected function getErrorObjFromKey($key)
    {
        //get error
        $errorMsg      = $this->message;
        $statusDefault = 'Error';
        //Define outpit
        $outputs = [
            'code'    => 400,
            'message' => $key,
            'status'  => $statusDefault,
            'datas'   => [],
        ];

        if (isset($errorMsg[$key])) {
            $outputs['code']    = $errorMsg[$key]['code'];
            $outputs['message'] = $errorMsg[$key]['msgError'];
            $outputs['status'] = isset($errorMsg[$key]['msgStatus'])?$errorMsg[$key]['msgStatus']:$statusDefault;
        }

        return $outputs;
    }

    //Method for response error data
    protected function responseError($errorKey, $uri)
    {
        //get error obj from key
        if (is_array($errorKey)) {
            $errors = $errorKey;
        } else {
            $errors = $this->getErrorObjFromKey($errorKey);
        }
        
        //Create error oject
        $error = new Error(
            uniqid(),
            new Link($this->config->application->baseUri . $uri),
            $errors['status'],
            $errors['code'],
            $errors['message'],
            $errors['message'],
            $errors['datas']
        );

        $datas = Encoder::instance()->encodeError($error);

        return $this->output($datas, $errors['code']);
    }  

    // Method for get language from header
    protected function getLanguageFromHeader()
    {
        $headers = $this->request->getHeaders();

        if (isset($headers['Language'])) {
            return $headers['Language'];
        }
        return "en";
    }
}
