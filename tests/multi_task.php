<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/config.php';

$task = \Tarth\Tool\Task::createApiTask(TARTH_TEST_TASK_API . '?case=multi&index=1&time=' . date('YmdHis'));
$task->runAfter(300);

$task = \Tarth\Tool\Task::createApiTask(TARTH_TEST_TASK_API . '?case=multi&index=2&time=' . date('YmdHis'));
$task->runAfter(600);

echo \Tarth\Tool\Task::exec();
