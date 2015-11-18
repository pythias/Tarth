<?php
namespace Tarth\Tool;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log {
    private static $_logger = null;

    static public function logger() {
        if (self::$_logger) {
            return self::$_logger;
        }

        self::$_logger = new Logger('tarth');
        self::$_logger->pushHandler(new StreamHandler('tarth.log', 100));

        return self::$_logger;
    }

    static public function setLocation($location, $level = Logger::INFO) {
        $logger = self::logger();
        $logger->popHandler();
        $logger->pushHandler(new StreamHandler($location, $level));

        return $logger;
    }
}