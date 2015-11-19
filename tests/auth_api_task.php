<?php
require 'include.php';

$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/auth?w=1');
$task->includeTarthHeader = true; 

$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/auth');

echo \Tarth\Tool\Task::exec();

