<?php

require_once __DIR__ . '/vendor/autoload.php';
require "core/Config.php";
require "core/Database.php";

use Core\Config;
use Core\Database;

echo "Index !<br>";

/* Database Connection Test */
$data = Config::getConfigFromYAML(__DIR__ . "\config\database\db.yml");

try {
    $db = Database::getInstance($data);
    die('ok');
} catch (Exception $e) {
    die($e->getMessage());
}
