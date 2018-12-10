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

    /* Constantes d'exceptions */

    public function __construct($db)
    {
        // Connect to the database
        $this->db = $db;
        try {
            $this->db->query('SELECT 1');
            echo "OK :) ";
        } catch (\PDOException $e) {
            die($e->getMessage());
        }

        /**
         * Sets the name of the Entity to the corresponding attribute,
         * then saves the name of the table we are currently working on.
         */
        $this->setEntityClass();
        $this->tableName = str_replace("model", "", strtolower($this->entityClass).'s');
        echo $this->entityClass;
        $this->limit = Config::getConfigFromYAML(__DIR__ . "/../config/database/entities.yml")[$this->entityClass]['indexLimit'];
    }

    /**
     * @param $data
     * @return mixed
     * Creates a new row in the $this->tableName table, with the provided $data.
     * $data must contain the table columns names as its keys, and the required values as values.
     */
    public function create($data) {
        // Creates the list of rows and columns to add in the query (format : id, author_name, ...)
        $columnsInsert = implode(", ", array_keys($data));
        $rowInsert = "'". implode("', '", array_values($data)) ."'";

        $req = "INSERT INTO " . $this->tableName . " (".$columnsInsert .") " . " VALUES (".$rowInsert.")";

        //echo "<br>" . $req;
        return $this->db->query($req);
    }

    /**
     * @param $data
     * @return mixed
     * Updates an existing row in the $this->tableName table, with the provided $data.
     * $data must contain the table columns names as its keys, and the required values as values.
     * $id will allow the function to update the correct row and MUST contain an 'id' key.
     */
    public function update($data) {
        // Creates the list of rows and columns to add in the query (format : id = value, author_name = value, ...)
        $values = '';
        foreach($data as $key => $val) {
            $values .= $key . '="' . $val . '", ';
        }

        // Delete the final ', ' that would cause an error in SQL.
        $values = substr($values, 0, strlen($values)-2);


        $req = "UPDATE ". $this->tableName . " SET " . $values . "WHERE id=" . $data['id'];

        return $this->db->query($req);
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
     * @param int $limit
     * @return mixed
     * Returns the list of ALL rows in the $this->tableName table.
     */
    public function getAll($limit = -1) {
        if($limit < 0) {
            $limit = $this->limit;
        }
        $req = "SELECT * FROM " . $this->tableName . ' LIMIT ' . $limit;
        echo "<br><br><br>" . $req . "<br><br><br>";
        $data = $this->db->query($req);

        return $data->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $id
     * @return mixed
     * Gets a single element from the table $this->tableName, with the required $id.
     */
    public function getSingle($id) {
        if(is_integer($id)) {
            $req = "SELECT * FROM " . $this->tableName . " WHERE id=" . (int) $id;

            $data = $this->db->query($req);
            $data->setFetchMode(\PDO::FETCH_ASSOC);

            return $data->fetch();
        }
    }

    /**
     * @return mixed
     */
    public function tableName()
    {
        return $this->tableName;
    }

    /**
     * @param $tableName
     * @return mixed
     *
     * Sets the table name to the short
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @return mixed
     */
    public function entityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param mixed $class
     */
    public function setEntityClass()
    {
        $this->entityClass = new \ReflectionClass($this);
        $this->entityClass = $this->entityClass->getShortName();
        //$this->entityClass = "Blog\app\Model\\" . $this->entityClass;
        //str_replace('Model', '', $this->entityClass);
        return $this->entityClass;
    }
}