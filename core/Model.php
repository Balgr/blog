<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 06/12/2018
 * Time: 23:44
 */

namespace Blog\core;

abstract class Model
{
    protected $db;
    protected $tableName;
    protected $entityClass;
    protected $limit;

    /* Class constants*/
    const NO_LIMIT = -1;

    public function __construct(Database $db)
    {
        // Connect to the database
        $this->db = $db;
        try {
            $this->db->query('SELECT 1');
        } catch (\PDOException $e) {
            echo "Erreur de connexion à la base de données : " . $e->getMessage();
        }

        /**
         * Sets the name of the Entity to the corresponding attribute,
         * then sets the name of the table we are currently working on.
         */
        $this->setEntityClass();
        $this->tableName = strtolower($this->entityClass) .'s';
        $this->limit = Config::getConfigFromYAML(__DIR__ . "/../config/database/entities.yml")[$this->entityClass]['indexLimit'];
    }

    public function getSingle ($id) {
        $req = "SELECT * FROM " . $this->tableName . " WHERE id=?";
        $req = $this->db->pdo()->prepare($req);
        $req->execute(array($id));
        return $req->fetch();
    }


    public function getAll ($status = -1, $limit = -1) {
        $req = "SELECT * FROM " . $this->tableName;
        if($status != -1) {
            $req .=  " WHERE $this->tableName.status = '$status'";
        }
        if($limit != -1) {
            $req = $req . " LIMIT " .$this->limit;
        }
        $data = $this->db->pdo()->query($req);
        return $data->fetchAll();
    }

    /**
     * @param $data
     * @return mixed
     * Creates a new row in the $this->tableName table, with the provided $data.
     * $data must contain the table columns names as its keys, and the required values as values.
     * $data is passed through PDO::prepare(), so that it escapes chars.
     */
    public function create($data) {
        // Creates the list of rows and columns to build the query (format : id, author_name, ...)
        $columnsInsert = implode(", ", array_keys($data));
        $preparedString = rtrim(str_repeat('?,', count($data)), ',');

        $req = "INSERT INTO " . $this->tableName . " (".$columnsInsert .") " . " VALUES (".$preparedString.")";
        // Insert the row
        $req = $this->db->pdo()->prepare($req);
        $req->execute(array_values($data));

        // Returns the last inserted row id
        return $this->db->pdo()->lastInsertId();
    }

    /**
     * @param $data
     * @return mixed
     * Updates an existing row in the $this->tableName table, with the provided $data.
     * $data must contain the table columns names as its keys, and the required values as values.
     * $id will allow the function to update the correct row and MUST contain an 'id' key.
     */
    public function update($data) {
        // Retrieves the object id
        $id = $data['id'];
        unset($data['id']);
        if(isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }

        // Builds the query string
        $preparedData = '';
        foreach($data as $key => $val) {
            $preparedData .= $key . '= ?, ';
            $values[] = $val;
        }
        $preparedData = rtrim($preparedData, ', ');
        $req = "UPDATE ". $this->tableName . " SET " . $preparedData . " WHERE id=" . $id . ' ';


        // Updates the selected row
        $req = $this->db->pdo()->prepare($req);
        $req->execute(array_values($data));

        // Returns the selected row id
        return true;
    }

    /**
     * @param $id
     * @return mixed
     * Deletes the row with the provided $id in the $this->tableName table.
     *
     */
    public function delete($id) {
        $req = "DELETE FROM " . $this->tableName . " WHERE id=" . $id;
        echo $req;

        return $this->db->query($req);
    }

    /**
     * @return mixed
     */
    public function entityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param mixed $entityClass
     * @return string
     * @throws \ReflectionException
     */
    public function setEntityClass()
    {
        $entity = new \ReflectionClass($this);
        $this->entityClass = str_replace("Model", "", $entity->getShortName());
        return $this->entityClass;
    }

    /**
     * @return Database
     */
    public function db()
    {
        return $this->db;
    }
}