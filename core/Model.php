<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 06/12/2018
 * Time: 23:44
 */

namespace Core;

abstract class Model
{
    private $db;

    /* Constantes d'exceptions */

    public function __construct($db)
    {
        $this->db = $db;
        try {
            $this->db->query('SELECT 1');
            echo "OK :) ";
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * @param $tableName
     * @param $data
     * @return mixed
     * Creates a new row in the $tableName table, with the provided $data.
     * $data must contain the table columns names as its keys, and the required values as values.
     */
    public function create($tableName, $data) {
        // Creates the list of rows and columns to add in the query (format : id, author_name, ...)
        $columnsInsert = implode(", ", array_keys($data));
        $rowInsert = "'". implode("', '", array_values($data)) ."'";

        // FOR TESTING PURPOSES
        var_dump($columnsInsert);
        var_dump($rowInsert);

        $req = "INSERT INTO " . $tableName . " (".$columnsInsert .") " . " VALUES (".$rowInsert.")";

        return $this->db->query($req);
    }

    /**
     * @param $id
     * @param $data
     * @return mixed
     * Updates an existing row in the $tableName table, with the provided $data.
     * $data must contain the table columns names as its keys, and the required values as values.
     * $id will allow the function to update the correct row and MUST contain an 'id' key.
     */
    public function update($tablename, $data) {
        // Creates the list of rows and columns to add in the query (format : id = value, author_name = value, ...)
        $values = '';
        foreach($data as $key => $val) {
            $values .= $key . '="' . $val . '", ';
        }

        // Delete the final ', ' that would cause an error in SQL.
        $values = substr($values, 0, strlen($values)-2);


        $req = "UPDATE ". $tablename . " SET " . $values . "WHERE id=" . $data['id'];

        return $this->db->query($req);

    }

    /**
     * @param $tablename
     * @param $id
     * @return mixed
     * Deletes the row with the provided $id in the $tablename table.
     *
     */
    public function delete($tablename, $id) {
        $req = "DELETE FROM " . $tablename . " WHERE id=" . $id;
        echo $req;

        return $this->db->query($req);
    }

    /**
     * @param $tablename
     * @return mixed
     * Returns the list of ALL rows in the $tablename table.
     */
    public function getAll($tablename) {
        $req = "SELECT * FROM " . $tablename;
        $data = $this->db->query($req);

        return $data->fetchAll(\PDO::FETCH_CLASS);;
    }

    /**
     * @param $tablename
     * @param $id
     * @return mixed
     * Gets a single element from the table $tablename, with the required $id.
     */
    public function getSingle($tablename, $id) {
        if(is_integer($id)) {
            $req = "SELECT * FROM " . $tablename . " WHERE id=" . (int) $id;
            echo $req;
            $data = $this->db->query($req);
            $data = $data->fetch(\PDO::FETCH_ASSOC);
            return $data;
        }
    }
}