<?php
$auto_load_file = __DIR__ . '/../vendor/autoload.php';
if (file_exists($auto_load_file)) {
    require_once $auto_load_file;
} else {
    require_once __DIR__ . '/../../../autoload.php';
}
