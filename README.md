Tarth
==========

`Tarth` is an asynchronous, concurrent, distributed task process framework. 

Requires
--------

* PHP 5.3 or Higher
* A POSIX compatible operating system (Linux, OSX, BSD)
* POSIX and PCNTL extensions for PHP
* Redis extensions 

Features
--------

* Asynchronous API callback, multi API callback
* Support timing callback, similar to crontab, but include retry, security, controllable options
* Support task number limit, speed controller
* Support callback priority level
* Stark Features

Usage
--------

You can use `Stark` to start the timer and processor daemon:
```bash
php vendor/got/stark/src/Stark/run.php -f scripts/timer.ini
php vendor/got/stark/src/Stark/run.php -f scripts/processor.ini
```

Daemon
--------
Use [Stark](https://github.com/pythias/Stark)

Task Tools
--------

### API
Class: \Tarth\Tool\Task
#### Create
```php
static public function createApiTask($url, $method = 'GET', $params = array())
static public function createEmailTask($to, $subject, $message)
```
#### Control
```php
static public function atomTask()
static public function exec()
static public function closeTask($taskId)
```
#### Security
```php
static public function getTarthHeader(TaskInterface $task)
static public function isRequestFromTarth()
```
### Samples
#### Normal task
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

\Tarth\Tool\Redis::setCacheServer('127.0.0.1:6379');
\Tarth\Tool\Redis::setQueueServer('127.0.0.1:6379');

$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/normal');
echo \Tarth\Tool\Task::exec();
```
#### Timer task
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/normal?case=timer&time=' . time());
$task->runAfter(100);

echo \Tarth\Tool\Task::exec();

```
#### Multi task
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/normal?case=atom&index=1&date=' . date('YmdHis'));
$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/normal?case=atom&index=2&date=' . date('YmdHis'));
echo \Tarth\Tool\Task::exec();

```
#### Atom task
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/normal?case=atom&index=1&date=' . date('YmdHis'));
$task = \Tarth\Tool\Task::createApiTask('http://alleria.mcp.wap.grid.sina.com.cn/test/normal?case=atom&index=2&date=' . date('YmdHis'));
$task = \Tarth\Tool\Task::atomTask();
$task->runAfter(600)->canClose();

echo \Tarth\Tool\Task::exec();

```

Configs
--------

### Daemon configs
See [Stark](https://github.com/pythias/Stark)
### Redis configs
#### Config in daemon ini
```ini
[queue]
type = "redis"
host = "127.0.0.1"
port = "6379"
key = "tarth-queue-0 tarth-queue-1 tarth-queue-2"
```
#### Change in tools
```php
<?php
\Tarth\Tool\Redis::setCacheServer('127.0.0.1:6379');
\Tarth\Tool\Redis::setQueueServer('127.0.0.1:6379');
```

Task Options
--------

```php
public function setPriority($level)
public function setMaxPerSecond($max)
public function setMaxPerMinute($max)
public function setMaxPerHour($max)
public function setMaxPerDay($max)
public function forbidDuplicate()
public function canClose()
public function runAt($timestamp)
public function runAfter($seconds)
```


