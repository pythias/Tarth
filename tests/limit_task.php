<?php
require_once __DIR__ . '/../vendor/autoload.php';

$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/normal?case=limit&date=' . date('Ymd'));
$task->setMaxPerMinute(3)->setMaxPerDay(10);

echo \Tarth\Tool\Task::exec();
