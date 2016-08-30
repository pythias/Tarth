<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/config.php';

\Tarth\Tool\Redis::setCacheServer('127.0.0.1:6379');
\Tarth\Tool\Redis::setQueueServer('127.0.0.1:6379');

$task = \Tarth\Tool\Task::createApiTask(TARTH_TEST_TASK_API);
echo \Tarth\Tool\Task::exec();

