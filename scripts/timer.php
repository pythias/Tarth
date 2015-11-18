<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Tarth\Task\AbstractTask;
use Tarth\Tool\Redis;

/**
 * 对于定时任务，超过一分钟的任务将会被丢弃
 */
define('MAX_BACKTRACK_SECONDS', 60);

function init($worker) {
    Redis::setCacheServer(get_option_value($worker->config, 'resources.cache', '127.0.0.1:6379'));
    Redis::setQueueServer(get_option_value($worker->config, 'resources.queue', '127.0.0.1:6379'));

    $worker->tarthTimerLastTime = 0;
    $currentTime = time();
    $minBacktrackTime = $currentTime - MAX_BACKTRACK_SECONDS;

    //读取上次执行到的时间
    $dir = get_option_value($worker->config, 'main.working_dir', '/tmp');
    $worker->tarthTimerLastTimeFile = "{$dir}/timer-last-{$worker->index}";
    if (file_exists($worker->tarthTimerLastTimeFile)) {
        $worker->tarthTimerLastTime = intval(file_get_contents($worker->tarthTimerLastTimeFile));
    }

    if ($minBacktrackTime > $worker->tarthTimerLastTime) {
        $worker->tarthTimerLastTime = $minBacktrackTime;
    } elseif ($worker->tarthTimerLastTime > $currentTime) {
        $worker->tarthTimerLastTime = $currentTime;
    }
}

function run($worker, $data) {
    //读取定时任务
    $currentTime = time();
    $data = Redis::queueRedis()->lPop('tarth-timer-' . $worker->tarthTimerLastTime);

    if ($data == false) {
        //在不超过当前时间情况下，获取下一秒的数据
        if ($worker->tarthTimerLastTime < $currentTime) {
            $worker->tarthTimerLastTime++;
        }

        return false;
    }
    
    $task = AbstractTask::createTask(json_decode($data, true));
    return $task->push(true);
}

function complete($worker) {
    file_put_contents($worker->tarthTimerLastTimeFile, $worker->tarthTimerLastTime);
}