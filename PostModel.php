<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 07/12/2018
 * Time: 01:24
 */

namespace Blog\app\Model;
use Blog\core\Config;
use Blog\core\Model;


class PostModel extends Model
{
    /**
     * @param $id
     * @return mixed
     * Put a Post in the trash.
     *
    */
    public function changeStatus($id, $status)
    {
        // Builds the query string
        $req = "UPDATE " . $this->tableName . " SET status=? WHERE id=?";


        // Updates the selected row
        $req = $this->db->pdo()->prepare($req);
        $req->execute(array($status, $id));

        // Returns the selected row id
        return $id;
    }
}