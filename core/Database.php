<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 06/12/2018
 * Time: 16:28
 */

namespace Core;

use PDO;

/**
 * Class Database
 * @package Core
 *
 * The Database class follows the Singleton pattern.
 * As such, ONLY ONE instance of it can exist at the same time, to prevent expensive operations
 * (i.e. connection, etc.).
 * The Singleton pattern requires :
 *  - A private static attribute holding the current instance of the class
 *  - A private constructor
 *  - Beacause of the private constructor, the object is created within the class itself,
 *    in a public function that first check whether such an object already exists
 */
class Database
{
    private static $instance = null;
    private $db;

    private function __construct($data)
    {
        if(array_key_exists('driver', $data)
            && array_key_exists('host', $data)
            && array_key_exists('dbname', $data))
        {
            $data['dsn'] = $data['driver'] . ":host=" . $data['host']  . ";dbname=" . $data['dbname'] . ";";
            unset($data['driver']);
            unset($data['host']);
            unset($data['dbname']);
        }

        if(array_key_exists('dsn', $data)
            && array_key_exists('username', $data)
            && array_key_exists('password', $data))
        {
            $this->db = new \PDO($data['dsn'], $data['username'], $data['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        }
    }

    public static function getInstance($data) {
        if(self::$instance) {
            self::$instance = new Database($data);
        }
        return self::$instance;
    }
}