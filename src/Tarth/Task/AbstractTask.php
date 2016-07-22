<?php
namespace Tarth\Task;

use Tarth\Params;
use Tarth\Policy\AbstractPolicy;
use Tarth\Tool\Log;
use Tarth\Tool\Redis;
use Tarth\Policy\LimitPolicy;

abstract class AbstractTask extends Params implements TaskInterface {
    abstract protected function _processTask();
    abstract public function key();

    public $id = 0;
    public $parentId = 0;
    public $retry = 3;
    public $priority = TaskInterface::PRIORITY_THIRD;
    public $policies = null;
    public $beginTime = 0;
    public $status = TaskInterface::STATUS_NEW;
    public $duration = 0;

    const RETRY_MAX = 10;
    const RETRY_SLEEP_MS = 10000;

    private $_filterKeys = array('beginTime', 'parentId', 'status', 'duration');

    public function run() {
        $this->beginTime = microtime(true);
        $retryTimes = 0;

        if ($this->_canRun() == false) {
            $this->duration = microtime(true) - $this->beginTime;
            $this->_afterRun();
            return true;
        }

        while ($retryTimes < $this->retry) {
            if ($this->_processTask() == true) {
                $this->status = TaskInterface::STATUS_DONE;
                break;
            }

            $this->status = TaskInterface::STATUS_DONE_ERROR;
            $retryTimes++;

            usleep($retryTimes * self::RETRY_SLEEP_MS);
        }

        $this->duration = microtime(true) - $this->beginTime;
        $this->_afterRun();
        return true;
    }

    public function push($toQueue = false) {
        try {
            if ($toQueue == false) {
                $runTimeInFuture = $this->_runAtFuture();
            } else {
                $runTimeInFuture = false;
            }
            
            if ($runTimeInFuture) {
                if (Redis::queueRedis()->rPush('tarth-timer-' . $runTimeInFuture, (string)$this)) {
                    return $this->id;
                }
            } else {
                if (Redis::queueRedis()->rPush('tarth-queue-' . $this->priority, (string)$this)) {
                    return $this->id;
                }
            }
        } catch (Exception $e) {
            Log::logger()->addError($e->getMessage);
        }

        return false;
    }

    private function _canRun() {
        foreach ($this->policies as $policy) {
            $status = $policy->beforeRun($this);
            if ($status !== true) {
                $this->status = $status;
                return false;
            }
        }

        return true;
    }

    private function _afterRun() {
        foreach ($this->policies as $policy) {
            $policy->afterRun($this);
        }
    }

    protected function _initPolicies() {
        if (empty($this->policies)) {
            $this->policies = array();
            return;
        }
        
        $policies = array();
        foreach ($this->policies as $policyInfo) {
            $policy = AbstractPolicy::createPolicy($policyInfo);
            if ($policy) {
                $policies[] = $policy;
            }
        }

        //按照优先级排序
        AbstractPolicy::sortPolicies($policies);

        $this->policies = $policies;
    }

    static public function createTask($taskInfo) {
        $name = isset($taskInfo['name']) ? $taskInfo['name'] : 'Api';
        $className = "\\Tarth\\Task\\" . ucfirst(strtolower($name)) . "Task";
        if (class_exists($className) == false) {
            return false;
        }

        $task = new $className($taskInfo);
        if ($task->id == 0) {
            $task->id = self::newId();
        }

        $task->_initPolicies();

        return $task;
    }

    static public function newId() {
        return sprintf("%u%02u", microtime(true) * 1000000, rand(0, 99));
    }

    private function _addPolicy($policy) {
        if ($this->policies == null) {
            $this->policies = array();
        }

        $this->policies[] = $policy;

        return $this;
    }

    /**
     * 设定任务的优先级（0-2）
     * @param integer $level 优先级，从0到2
     */
    public function setPriority($level) {
        $level = intval($level);

        if ($level < TaskInterface::PRIORITY_FIRST || $level > TaskInterface::PRIORITY_THIRD) {
            $level = TaskInterface::PRIORITY_THIRD;
        }

        $this->priority = $level;
        return $this;
    }

    /**
     * 每秒钟最多运行多少次       
     * @param integer $max 次数
     */
    public function setMaxPerSecond($max) {
        $policy = new LimitPolicy();
        $policy->max = $max;
        $policy->unit = LimitPolicy::UNIT_SECOND;

        return $this->_addPolicy($policy);
    }

    /**
     * 每分钟最多运行多少次       
     * @param integer $max 次数
     */
    public function setMaxPerMinute($max) {
        $policy = new LimitPolicy();
        $policy->max = $max;
        $policy->unit = LimitPolicy::UNIT_MINUTE;

        return $this->_addPolicy($policy);
    }

    /**
     * 每小时最多运行多少次       
     * @param integer $max 次数
     */
    public function setMaxPerHour($max) {
        $policy = new LimitPolicy();
        $policy->max = $max;
        $policy->unit = LimitPolicy::UNIT_HOUR;

        return $this->_addPolicy($policy);
    }

    /**
     * 每天最多运行多少次       
     * @param integer $max 次数
     */
    public function setMaxPerDay($max) {
        $policy = new LimitPolicy();
        $policy->max = $max;
        $policy->unit = LimitPolicy::UNIT_DAY;

        return $this->_addPolicy($policy);
    }

    /**
     * 开启任务不能重复运行的检查
     */
    public function forbidDuplicate() {
        $policy = new \Tarth\Policy\DuplicatePolicy();

        return $this->_addPolicy($policy);
    }

    private function _runAtFuture() {
        if (is_array($this->policies) === false) {
            return false;
        }

        foreach ($this->policies as $policy) {
            if ($policy->name == 'Timer') {
                if ($policy->runAt > time()) {
                    return $policy->runAt;
                }
            }
        }

        return false;
    }

    private function _getTimerPolicy() {
        $timerPolicy = null;

        if (is_array($this->policies)) {
            foreach ($this->policies as $policy) {
                if ($policy->name == 'Timer') {
                    $timerPolicy = $policy;
                    break;
                }
            }
        }

        if ($timerPolicy == null) {
            $timerPolicy = new \Tarth\Policy\TimerPolicy();
            $this->_addPolicy($timerPolicy);
        }

        return $timerPolicy;
    }

    /**
     * 任务可以关闭
     */
    public function canClose() {
        $policy = $this->_getTimerPolicy();
        $policy->canClose = true;

        return $this;
    }

    /**
     * 任务启动时间戳
     * @param  integer $timestamp 时间戳
     */
    public function runAt($timestamp) {
        $time = time();
        if ($time > $timestamp) {
            throw new Exception("Timer task cannt in the past.", 1);
        }

        $policy = $this->_getTimerPolicy();
        $policy->runAt = intval($timestamp);

        return $this;
    }

    /**
     * 任务在多少秒以后启动
     * @param  integer $seconds 秒数
     */
    public function runAfter($seconds) {
        $policy = $this->_getTimerPolicy();
        $policy->runAt = time() + $seconds;

        return $this;
    }

    /**
     * 获取任务的队列描述内容
     */
    public function __toString() {
        $clone = clone $this;
        foreach ($this->_filterKeys as $key) {
            unset($clone->$key);
        }
        
        return json_encode($clone);
    }
}
