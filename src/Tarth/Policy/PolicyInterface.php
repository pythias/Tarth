<?php
namespace Tarth\Policy;

use Tarth\Task\TaskInterface;

interface PolicyInterface {
    const ORDER_FIRST = 0;
    const ORDER_SECOND = 100;
    const ORDER_THIRD = 200;
    const ORDER_FOUTH = 300;

    public function beforeRun(TaskInterface $task);
    public function afterRun(TaskInterface $task);
}
