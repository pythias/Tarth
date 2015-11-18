<?php
namespace Tarth\Task;

use Tarth\Params;
use Tarth\Task\AbstractTask;

class MultiTask extends AbstractTask {
    public $tasks = null;
    public $isAtom = false;

    protected function _processTask() {
        try {
            if ($this->isAtom) {
                foreach ($this->tasks as $task) {
                    if (is_array($task)) {
                        $task = AbstractTask::createTask($task);
                    }
                    
                    $task->run();
                }
            } else {
                foreach ($this->tasks as $task) {
                    if (is_array($task)) {
                        $task = AbstractTask::createTask($task);
                    }

                    $task->push();
                }
            }
        } catch (Exception $e) {
            Log::logger()->addError($e->getMessage);
        }        
    }

    public function key() {
        return md5(json_encode($this->tasks));
    }
}
