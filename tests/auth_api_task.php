<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/config.php';

$task = \Tarth\Tool\Task::createApiTask(TARTH_TEST_AUTH_TASK_API . '?w=1');
$task->includeTarthHeader = true; 

$task = \Tarth\Tool\Task::createApiTask(TARTH_TEST_AUTH_TASK_API);

echo \Tarth\Tool\Task::exec();

