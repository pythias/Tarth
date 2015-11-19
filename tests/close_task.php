<?php
require 'include.php';

$taskId = isset($argv[1]) ? intval($argv[1]) : 0;
if ($taskId == 0) {
    echo "Task id is invalid.";
}

echo \Tarth\Tool\Task::closeTask($taskId);

