<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/config.php';

$task = \Tarth\Tool\Task::createApiTask(TARTH_TEST_TASK_API . '?case=timer&time=' . time());
$task->runAfter(100);

echo \Tarth\Tool\Task::exec();
