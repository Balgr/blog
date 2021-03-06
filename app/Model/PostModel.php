<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 07/12/2018
 * Time: 01:24
 */

namespace Blog\app\Model;
use Blog\app\Controller\CommentController;
use Blog\app\Entity\Post;
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
        $req = "UPDATE $this->tableName  SET status=? WHERE id=?";

        // Updates the selected row
        $req = $this->db->pdo()->prepare($req);
        if($req->execute(array($status, $id))) {
            return true;
        }
        return false;
    }

    public function getAuthor($id)
    {
        $userModel = new UserModel($this->db());
        $users = $userModel->tableName;
        $req = "SELECT $users.username, $users.email, $users.biography FROM $users INNER JOIN $this->tableName WHERE $users.id = $this->tableName.creatorId AND $this->tableName.id = ?";
        $params = array($id);

        $req = $this->db->pdo()->prepare($req);
        if($req->execute($params)) {
            return $req->fetch(\PDO::FETCH_ASSOC);
        }
        return false;
    }


    public function getAllBy ($status = -1, $limit = -1) {
        $params = array();
        $userModel = new UserModel($this->db());
        $usersTable = $userModel->tableName;
        $commentModel = new CommentModel($this->db());
        $commentTable = $commentModel->tableName;
        $req = "SELECT $this->tableName.id as idx, $this->tableName.title, $this->tableName.subtitle, $this->tableName.content, $this->tableName.creationDate as creationDate, $this->tableName.featuredImage, $usersTable.username as author, COUNT($commentTable.id) as commentsNb FROM $this->tableName LEFT JOIN $usersTable ON $this->tableName.creatorId = $usersTable.id LEFT JOIN $commentTable ON $this->tableName.id = $commentTable.postId";
        // , COUNT($commentTable.id) as nbComments
        // AND $commentTable.postId = $this->tableName.id
        if($status !== -1) {
            $req = $req . ' WHERE $this->tableName.status = ?';
            $params[] = $status;
        }

        $req = $req . " GROUP BY idx";

        if($limit !== -1) {
            $req = $req . " LIMIT ?";
            $params[] = $limit;
        }

        $req = $req . " ORDER BY creationDate DESC";

        $req = $this->db->pdo()->prepare($req);
        if($req->execute($params)) {
            return $req->fetchAll();
        }
        return false;
    }

    public function sitemapData() {
        return $this->db->pdo()->query("SELECT id, creationDate, lastEditDate FROM $this->tableName WHERE status = '" . Post::POST_STATUS_PUBLISHED . "'");
    }
}
