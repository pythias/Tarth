<?php
require_once __DIR__ . '/../vendor/autoload.php';

$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/normal?case=multi&index=1&time=' . date('YmdHis'));
$task->runAfter(300);

$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/normal?case=multi&index=2&time=' . date('YmdHis'));
$task->runAfter(600);

echo \Tarth\Tool\Task::exec();
