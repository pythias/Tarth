<?php
namespace Tarth\Task;

use Tarth\Params;
use Tarth\Task\AbstractTask;
use \Curl\Curl;

class EmailTask extends AbstractTask {
    public $to = '';
    public $subject = '';
    public $message = '';
    
    protected function _processTask() {
        return mail($this->to, $this->subject, $this->message);
    }

    public function key() {
        return md5("{$this->to}|{$this->subject}|{$this->message}");
    }
}
