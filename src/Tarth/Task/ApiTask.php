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
    public $host = '';
    public $successCode = '';
    public $statusKey = 'code';

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

            //设定自定义host
            if ($this->host) {
                $curl->setOpt(CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                $curl->setOpt(CURLOPT_PROXY, $this->host);
                $port = $this->_getUrlPort($this->url);

                if ($port) {
                    $curl->setOpt(CURLOPT_PROXYPORT, $port);
                }
            }
            
            call_user_func_array(array($curl, strtolower($this->method)), array($this->url, $this->data));
            
            if ($curl->error) {
                return false;
            } else {
                //响应码大于300为请求失败
                if ($curl->httpStatusCode >= 300) {
                    return false;
                }

                //自定义
                if ($this->successCode) {
                    $ret = (is_array($curl->response) ? $curl->response : json_decode($curl->response, true));

                    return (isset($ret[$this->statusKey]) && $ret[$this->statusKey] == $this->successCode);
                }

                return true;
                
                //接口返回为json格式，返回值中有code为0
                //return $curl->response->code == 0;
            }
        } catch (Exception $e) {
            
        }
    }

    protected function _getUrlPort($url) {
        $ret = parse_url($url);

        if (isset($ret['scheme'])) {
            if (isset($ret['port'])) {
                return $ret['port'];
            }

            if ($ret['scheme'] == 'http') {
                return 80;
            }

            if ($ret['scheme'] == 'https') {
                return 443;
            }
        }

        return false;
    }

    public function key() {
        return md5($this->url . '|' . json_encode($this->data));
    }
}
