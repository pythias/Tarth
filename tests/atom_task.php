<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/config.php';

$task = \Tarth\Tool\Task::createApiTask(TARTH_TEST_TASK_API . '?case=atom&index=1&date=' . date('YmdHis'));
$task = \Tarth\Tool\Task::createApiTask(TARTH_TEST_TASK_API . '?case=atom&index=2&date=' . date('YmdHis'));
$task = \Tarth\Tool\Task::atomTask();
$task->runAfter(600)->canClose();

echo \Tarth\Tool\Task::exec();
