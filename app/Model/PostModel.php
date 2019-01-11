<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 07/12/2018
 * Time: 01:24
 */

namespace Blog\app\Model;
use Blog\app\Controller\CommentController;
use Blog\core\Config;
use Blog\core\Model;


class PostModel extends Model
{
    /**
     * @param $id
     * @param $status
     * @return mixed
     * Put a Post in the trash.
     */
    public function changeStatus($id, $status)
    {
        // Builds the query string
        $req = "UPDATE " . $this->tableName . " SET status=? WHERE id=?";

        // Updates the selected row
        $req = $this->db->pdo()->prepare($req);
        if($req->execute(array($status, $id))) {
            return true;
        }
        return false;
        // Returns the selected row id
        //return $id;
    }

    public function getAuthor($id)
    {
        $userModel = new UserModel($this->db());
        $users = $userModel->tableName;
        $req = "SELECT $users.username, $users.email, $users.biography FROM $users INNER JOIN $this->tableName WHERE $users.id = $this->tableName.creatorId AND $this->tableName.id = $id";

        return $this->db->query($req)->fetch();
    }

    public function getAuthorName($id)
    {
        $userModel = new UserModel($this->db());
        $users = $userModel->tableName;
        $req = "SELECT $users.username FROM $users INNER JOIN $this->tableName WHERE $users.id = $this->tableName.creatorId AND $this->tableName.id = $id";

        return $this->db->query($req)->fetchColumn();
    }


    public function getAllBy ($status = -1, $limit = -1) {
        $userModel = new UserModel($this->db());
        $usersTable = $userModel->tableName;
        $commentModel = new CommentModel($this->db());
        $commentTable = $commentModel->tableName;
        $req = "SELECT $this->tableName.id, $this->tableName.title, $this->tableName.subtitle, $this->tableName.content, $this->tableName.creationDate, $this->tableName.featuredImage, $usersTable.username as author, COUNT($commentTable.id) as commentsNb FROM $this->tableName LEFT JOIN $usersTable ON $this->tableName.creatorId = $usersTable.id LEFT JOIN $commentTable ON $this->tableName.id = $commentTable.postId";
        // , COUNT($commentTable.id) as nbComments
        // AND $commentTable.postId = $this->tableName.id
        if($status !== -1) {
            $req = $req . " WHERE $this->tableName.status=\"" . $status . "\"";
        }
        $req = $req . " GROUP BY $this->tableName.id";
        if($limit !== -1) {
            $req = $req . " LIMIT " .$this->limit;
        }
        //var_dump($req);
        $data = $this->db->pdo()->query($req);
        return $data->fetchAll();
    }
}