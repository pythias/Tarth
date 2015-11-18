<?php
require_once __DIR__ . '/../vendor/autoload.php';

$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/normal?case=timer&time=' . time());
$task->runAfter(100);

echo \Tarth\Tool\Task::exec();
