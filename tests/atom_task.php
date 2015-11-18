<?php
require_once __DIR__ . '/../vendor/autoload.php';

$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/normal?case=atom&index=1&date=' . date('YmdHis'));
$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/normal?case=atom&index=2&date=' . date('YmdHis'));
$task = \Tarth\Tool\Task::atomTask();
$task->runAfter(600)->canClose();

echo \Tarth\Tool\Task::exec();
