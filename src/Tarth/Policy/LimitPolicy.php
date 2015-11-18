<?php
namespace Tarth\Policy;

use Tarth\Task\TaskInterface;
use Tarth\Policy\AbstractPolicy;
use Tarth\Params;
use Tarth\Tool\Redis;

class LimitPolicy extends AbstractPolicy {
    public $name = 'Limit';
    public $max = 1;
    public $unit = self::UNIT_MINUTE;

    const CACHE_KEY = 'tarth-limit-%s-%s';
    const UNIT_DAY = 'd';
    const UNIT_HOUR = 'h';
    const UNIT_MINUTE = 'm';
    const UNIT_SECOND = 's';

    private $_units = array(
        self::UNIT_SECOND => array('s', 1),
        self::UNIT_MINUTE => array('i', 60),
        self::UNIT_HOUR => array('H', 3600),
        self::UNIT_DAY => array('d', 86400),
    );

    public function beforeRun(TaskInterface $task) {
        if (isset($this->_units[$this->unit]) == false) {
            $this->unit = self::UNIT_MINUTE;
        }

        $timeString = date($this->_units[$this->unit][0]) . $this->unit;
        $cacheKey = sprintf(self::CACHE_KEY, $task->key(), $timeString);
        
        $currentCount = Redis::cacheRedis()->incrBy($cacheKey, 1);
        if ($currentCount == 1) {
            $timeout = $this->_units[$this->unit][1];
            Redis::cacheRedis()->setTimeout($cacheKey, $timeout);
        }

        if ($currentCount > $this->max) {
            return TaskInterface::STATUS_SKIP_BY_LIMIT;
        }

        return true;
    }

    public function afterRun(TaskInterface $task) {

    }
}
