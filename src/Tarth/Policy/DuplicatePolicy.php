<?php
namespace Tarth\Policy;

use Tarth\Task\TaskInterface;
use Tarth\Policy\AbstractPolicy;
use Tarth\Params;
use Tarth\Tool\Redis;

/**
 * 不能同时运行同一个任务的策略
 */
class DuplicatePolicy extends AbstractPolicy {
    public $name = 'Duplicate';

    const CACHE_KEY = 'tarth-dup-%s';
    const CHECK_MAX_TIME = 3600; //超过一小时的任务不算重复

    private $_key;

    public function beforeRun(TaskInterface $task) {
        $this->_key = sprintf(self::CACHE_KEY, $task->key());

        $notExist = Redis::cacheRedis()->setNx($this->_key, $task->beginTime);
        Redis::cacheRedis()->setTimeout($this->_key, self::CHECK_MAX_TIME);
        
        if ($notExist == false) {
            $lastTime = Redis::cacheRedis()->get($this->_key);
            if ($currentTime - $lastTime < self::CHECK_MAX_TIME) {
                return TaskInterface::STATUS_SKIP_BY_DUPLICATE;
            }
        }

        return true;
    }

    public function afterRun(TaskInterface $task) {
        Redis::cacheRedis()->delete($this->_key);
    }
}
