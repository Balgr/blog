<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 10/12/2018
 * Time: 15:29
 */

namespace Blog\core;

class Autoloader {

    public function __construct() {

    }

    /**
     * Registers class to autoload
     */
    public static function register() {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Autoloads the class.
     * For each $class that must be loaded, this function gets the Class path (it loads the file located at
     * "Namespace/Path/To/Class" by deleting everything before the first '/'.
     */
    public static function autoload($class) {

        $class = str_replace('\\', '/', $class);
        $class = substr($class, strpos($class, '/'), strlen($class)-1);
        $dir = __DIR__;

        require "$dir/..$class.php";
    }
}