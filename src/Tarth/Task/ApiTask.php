<?php
namespace Tarth\Task;

use Tarth\Task\AbstractTask;
use \Curl\Curl;

class ApiTask extends AbstractTask {
    public $url = '';
    public $method = 'GET';
    public $data = array();
    public $timeout = 5;
    public $header = array();
    public $includeTarthHeader = false;

    protected function _processTask() {
        try {
            $curl = new Curl();
            $curl->setUserAgent('got/Tarth');

            if ($this->includeTarthHeader) {
                $curl->setHeader(\Tarth\Tool\Task::HEADER_ALLERIA_CRC, \Tarth\Tool\Task::getTarthHeader($this));
            }

            if (isset($this->header)) {
                foreach ($this->header as $key => $value) {
                    $curl->setHeader($key, $value);
                }
            }
            
            call_user_func_array(array($curl, strtolower($this->method)), array($this->url, $this->data));
            
            if ($curl->error) {
                return false;
            } else {
                //响应码大于300为请求失败
                return $curl->httpStatusCode < 300;
                
                //接口返回为json格式，返回值中有code为0
                //return $curl->response->code == 0;
            }
        } catch (Exception $e) {
            
        }
    }

    public function key() {
        return md5($this->url . '|' . json_encode($this->data));
    }
}
