<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/config.php';

$task = \Tarth\Tool\Task::createApiTask(TARTH_TEST_TASK_API . '?case=limit&date=' . date('Ymd'));
$task->setMaxPerMinute(3)->setMaxPerDay(10);

echo \Tarth\Tool\Task::exec();
