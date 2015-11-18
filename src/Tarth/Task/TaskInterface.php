<?php
namespace Tarth\Task;

interface TaskInterface {
    const STATUS_DEFAULT = 0;
    const STATUS_NEW = 1;
    const STATUS_DONE = 2;
    const STATUS_PROCESSING = 3;
    const STATUS_DONE_ERROR = 4;
    const STATUS_NOT_READY = 5;
    const STATUS_SKIP_BY_DUPLICATE = 6;
    const STATUS_SKIP_BY_LIMIT = 7;
    const STATUS_CLOSED_BY_CLIENT = 8;

    const PRIORITY_FIRST = 0;
    const PRIORITY_SECOND = 1;
    const PRIORITY_THIRD = 2;

    public function run();
    public function key();
    public function push($toQueue = false);
}
