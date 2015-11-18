<?php
namespace Tarth\Policy;

use Tarth\Task\TaskInterface;
use Tarth\Policy\AbstractPolicy;
use Tarth\Params;
use Tarth\Tool\Redis;

class TimerPolicy extends AbstractPolicy {
    public $name = 'Timer';
    public $runAt = false;
    public $canClose = false;

    protected $_order = PolicyInterface::ORDER_FIRST;

    public function beforeRun(TaskInterface $task) {
        if ($this->canClose) {
            $closed = Redis::cacheRedis()->hDel('closed_tasks', $task->parentId > 0 ? $task->parentId : $task->id);
            if ($closed) {
                return TaskInterface::STATUS_CLOSED_BY_CLIENT;
            }
        }

        if ($this->runAt > $task->beginTime){
            return TaskInterface::STATUS_NOT_READY;
        }

        return true;
    }

    public function afterRun(TaskInterface $task) {
        if ($task->status == TaskInterface::STATUS_NOT_READY) {
            $task->push(true);
        }
    }
}
