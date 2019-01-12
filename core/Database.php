<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 06/12/2018
 * Time: 16:28
 */

namespace Blog\core;

use mysql_xdevapi\Exception;
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
    private $pdo;

    private function __construct($data)
    {
        $this->pdo = $this->pdoConnect($data);
    }

    public static function getInstance($data) {
        if(is_null(self::$instance)) {
            self::$instance = new Database($data);
        }
        return self::$instance;
    }

    /**
     * @return PDO
     */
    public function pdo()
    {
        return $this->pdo;
    }


    public function query($statement) {
        return $this->pdo->query($statement);
    }

    public function pdoConnect($data) {
        // Checks of if the DSN is defined in $data.
        // If its not, builds it from the driver, host and dbname informations.
        if(array_key_exists('driver', $data)
            && array_key_exists('host', $data)
            && array_key_exists('dbname', $data))
        {
            $data['dsn'] = $data['driver'] . ":host=" . $data['host']  . ";dbname=" . $data['dbname'] . ";charset=" . $data['charset'].";";
            unset($data['driver']);
            unset($data['host']);
            unset($data['dbname']);
        }


        // Checks if the DSN, the username and the password are defined.
        // The, establishes a connection to the Database
        if(array_key_exists('dsn', $data)
            && array_key_exists('username', $data)
            && array_key_exists('password', $data))
        {
            try {
                $pdo = new PDO($data['dsn'], $data['username'], $data['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            } catch (Exception $e) {
                echo "Erreur de connexion à la base de données : veuillez vérifier les informations de connexion." . $e->getMessage();
            }
        }

        return $pdo;
    }
}