<?php
require 'include.php';

\Tarth\Tool\Redis::setCacheServer('127.0.0.1:6379');
\Tarth\Tool\Redis::setQueueServer('127.0.0.1:6379');

$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/normal');
echo \Tarth\Tool\Task::exec();

