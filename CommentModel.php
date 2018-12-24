<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 08/12/2018
 * Time: 16:50
 */

namespace Blog\app\Model;
use Blog\app\Entity\Comment;
use Blog\app\Entity\Post;
use Blog\core\Config;
use Blog\core\Model;
use mysql_xdevapi\Exception;


class CommentModel extends Model
{
    public function getComments($status = null) {
        $req = "SELECT * FROM " . $this->tableName;

        if(!is_null($status)) {
            if($status == Comment::COMMENT_PUBLISHED || $status == Comment::COMMENT_TRASH || $status == Comment::COMMENT_IN_MODERATION ) {
                $req .= " WHERE status = " . $status;
            }
            else {
                throw new \Exception("Commentaires : statut invalide.");
            }
        }

        $data = $this->db->pdo()->query($req);

        if(!is_bool($data)) {
            return $data->fetchAll();
        }

        else {
            throw new \Exception("Erreur : requête");
        }
    }

    public function moderateComment($id, $newStatus) {
        // Updates the Comment row
        $req = "UPDATE " . $this->tableName . " SET status=? WHERE id=?";
        $req = $this->db->pdo()->prepare($req);
        $data = $req->execute(array($newStatus, $id));

        // Checks if the request executed properly : if so, returns the updated Comment to allow redirection by the Controller
        if($data === true) {
            return $this->getSingle($id);
        }

        else {
            throw new \Exception("Post : id inexistant.");
        }
    }

    public function getAllByPost($postId, $limit)
    {
        $req = "SELECT * FROM " . $this->tableName . " WHERE postId = ?";
        $req = $this->db->pdo()->prepare($req);
        $req->execute(array($postId));
        $data = $req->fetchAll();

        if($data !== false) {
            return $data;
        }
        else {
            throw new \Exception("Erreur : requête");
        }
    }

}