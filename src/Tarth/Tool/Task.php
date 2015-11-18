<?php
namespace Tarth\Tool;

use Tarth\Task\TaskInterface;
use Tarth\Task\AbstractTask;
use Tarth\Task\MultiTask;
use Tarth\Tool\Redis;

class Task {
    const HEADER_ALLERIA_CRC = "x-tarth-crc";
    const OPENSSL_PRIVATE_KEY = "-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQD3thvo5Cc/pwTa
YWq2MIKjN4aPGmwDoSyca3bSHcv7Nf39QJMXuq9kF6WPd5iGP98qG/sATC3UjFZH
PyeBjwTtEqELQhThKjPVMCzKL75i8rqFCWpKCZ9/Nxs8zs5RHGrDt0qjlNFvJP/s
ayt9vZDTWfqW6z6hT6UR0D/30UD0XUNXdXTp2QXhA0UDLefMkj4b5hKod+d3wK2s
LE6Cj/wIW0yAb+kHC5widYKDQM0pQJabiUTVQKDY/iiDVRzJo8y6wl0OjoEVjTPP
UGefiVpgCE98reVRJZu7g2BZYoAnZQsB+0BAayau90TlTYimxPB5azcuzPjY/t/4
fM3vtYM7AgMBAAECggEAW45czHyfoSA7Y/gDuCk/79HTE0uBxumoknwJ4+mNfmFb
amKWu3uN3iH7WIaswloTQv4qjNabTec88IKAOJvDB1kOWxnmm768f7yZoXV6Ghp7
JDbxqUHbSOr2T0hk64fkUkiJ9uJHcpwrV5fY8FXDlMq6G1QBxx+n9GiTbCZUqryf
wrTbjO0fr8nk2pt2uuJTgfzELF1ZDKsDjKZFysPZISgMjyRMqScAWeC1hud1osS9
hUJC77pZdfekga23yr2DRmCbWO1YO84JTdqQUOI7/srzU4u3MYISfvH7dJXZS0W8
HFum3nRFf+8A0v6Ii9EiJ/yj3xwM7N27dgCsrojXWQKBgQD8xuyQ3Ui8GmPkxDL0
qGYNxzkukQRXORAGU8wBhuhM7hIgiEMAaOCMcRCmKYVSkNvCGWZN92LMc5q4WA4D
xEucYouuGLurXgqmxvh64HAeb/mrMMSt7m6vg252K0hx7+EEq2Kp/puWYigl4CGW
+Ps5FkQFFyR3w/lTjqqiOxMdlwKBgQD63qZ61OncK9SlEroLlR4DKGaxio7DJyyu
1Mz2Bnhpj+aB7mwWzBozA5hLDy646kY97cda0sb1H40H2CTPA//DJv9cRFRUHTfR
82M4fxx63LJC9KhD6t2Ey71BctCTAiUIBIYZrMMgBLoPjv3DxAcDhXMvOG6OlDTL
8XdA51uD/QKBgQCulnJ0R/JhwVR3gC+1nc4G/C/5gr3dxJLV2/DOqTAvWkt43sRw
Nv/I4JrgOVNVSKoQMNzhQtmhXsNhSag8X8rdc48IKxsL7IMs18ZtkDDARRTLcX6W
p1UZsoyL369Eyqq/P+SAh1NNFfSm3Fw22zchIcjPP0G32sqNNL+UF3tHdwKBgGko
ESKlws1udfkny2J1hBoQwlMjYEo/ToSOYMez2J9vGVFXbmlz7nt5w0mbOJt8YCsv
U4QnnAw9yHEEUhGQfJIB88JqReroQHC6E9ontfluLy7PvQSTG33BpTgc937XxEwD
EW8LstLmCFPjPU3lCoeYVbryba1IRIVEVxeWow7FAoGBAIrCLsubwAz/3XL2j1tW
viRZBkHNJ2/anSuKvFLYpBfRguLfjVam8EL3NpwOcxy8TZPxhsvMF6641WOKO6tc
5ZvJfmmSgzTU5XxeGWOOHPVqPsJcs9+Yic6yIwp7qawxWd6+KliSf7r0P4FxigTK
OYUvGbn0YucNYSaJ3voIp/IT
-----END PRIVATE KEY-----
";

    const OPENSSL_PUBLIC_KEY = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA97Yb6OQnP6cE2mFqtjCC
ozeGjxpsA6EsnGt20h3L+zX9/UCTF7qvZBelj3eYhj/fKhv7AEwt1IxWRz8ngY8E
7RKhC0IU4Soz1TAsyi++YvK6hQlqSgmffzcbPM7OURxqw7dKo5TRbyT/7Gsrfb2Q
01n6lus+oU+lEdA/99FA9F1DV3V06dkF4QNFAy3nzJI+G+YSqHfnd8CtrCxOgo/8
CFtMgG/pBwucInWCg0DNKUCWm4lE1UCg2P4og1UcyaPMusJdDo6BFY0zz1Bnn4la
YAhPfK3lUSWbu4NgWWKAJ2ULAftAQGsmrvdE5U2IpsTweWs3Lsz42P7f+HzN77WD
OwIDAQAB
-----END PUBLIC KEY-----
";

    private static $_tasks = array();
    private static $_atomTask = null;

    private static function _createTask($taskInfo) {
        $task = AbstractTask::createTask($taskInfo);
        if ($task) {
            self::$_tasks[] = $task;
        }
        
        return $task;
    }

    private static function _reset() {
        self::$_tasks = array();
        self::$_atomTask = null;
    }

    /**
     * 获取请求校验码
     * @param  TaskInterface $task [description]
     * @return [type]        [description]
     */
    static public function getTarthHeader(TaskInterface $task) {
        $crypted = "";
        openssl_public_encrypt(microtime(true) . $task->key(), $crypted, self::OPENSSL_PUBLIC_KEY);

        return $crypted;
    }

    /**
     * 在接口逻辑中检查请求来源是否合法
     * @return [type] [description]
     */
    static public function isRequestFromTarth() {
        $headers = getallheaders();
        if (isset($crypted[self::HEADER_ALLERIA_CRC]) === false) {
            return false;
        }

        $crypted = $crypted[self::HEADER_ALLERIA_CRC];
        $decrypted = '';
        $result = openssl_private_decrypt($crypted, $decrypted, self::OPENSSL_PRIVATE_KEY);
        
        return $result;
    }

    /**
     * 创建API回调任务
     * @param  string $url    API地址
     * @param  string $method API调用方式，默认为GET
     * @param  array  $params API调用的参数（当方法为GET时，此数组会自动组装到URL中，数据会都在$_GET里，如果不是GET方法，那么数据都在$_POST里）
     * @return object 任务对象
     */
    static public function createApiTask($url, $method = 'GET', $params = array()) {
        if ($method == 'GET' && count($params) > 0) {
            $url = $url . (strpos($url, '?') ? '&' : '?'). http_build_query($params);
            $params = array();
        }

        $data = array(
            'url' => $url,
            'method' => $method,
            'data' => $params,
            'name' => 'Api',
        );        

        return self::_createTask($data);
    }

    /**
     * 创建邮件发送任务
     * @param  string $to        接收人邮箱
     * @param  string $subject   邮件标题
     * @param  string $message   邮件内容
     * @return object 任务对象
     */
    static public function createEmailTask($to, $subject, $message) {
        $data = array(
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
            'name' => 'Email',
        );
        
        return self::_createTask($data);
    }

    /**
     * 组合成原子任务
     * @return object 任务对象
     */
    static private function _toMultiTask($isAtom = false) {
        $tid = AbstractTask::newId();
        foreach (self::$_tasks as $task) {
            $task->parentId = $tid;
        }

        $multiInfo = array(
            'tasks' => self::$_tasks,
            'id' => $tid,
            'isAtom' => $isAtom,
            'name' => 'Multi',
        );

        self::$_atomTask = self::_createTask($multiInfo);

        return self::$_atomTask;
    }

    /**
     * 组合成原子任务
     * @return object 任务对象
     */
    static public function atomTask() {
        return self::_toMultiTask(true);
    }

    /**
     * 执行任务列表
     * @return integer 任务ID
     */
    static public function exec() {
        $taskId = 0;
        if (count(self::$_tasks) > 1) {
            if (self::$_atomTask == null) {
                self::_toMultiTask(false);
            }
            $taskId = self::$_atomTask->push();
        } else {
            $taskId = self::$_tasks[0]->push();
        }

        self::_reset();

        return $taskId;
    }

    /**
     * 关闭等待中的定时任务
     * @param  integer $taskId    任务ID
     * @return [type]            [description]
     */
    static public function closeTask($taskId) {
        return Redis::cacheRedis()->hSet('closed_tasks', $taskId, 1);
    }
}