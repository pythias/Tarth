<?php
require 'include.php';

$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/normal?case=timer&time=' . time());
$task->runAfter(100);

echo \Tarth\Tool\Task::exec();
