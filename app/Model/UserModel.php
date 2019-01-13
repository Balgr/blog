<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 07/12/2018
 * Time: 01:24
 */

namespace Blog\app\Model;
use Blog\core\Model;

class UserModel extends Model
{

    public function getByUsername($username)
    {
        $req = "SELECT * FROM $this->tableName WHERE username=?";
        $req = $this->db->pdo()->prepare($req);
        $req->execute(array($username));
        return $req->fetch();
    }

    /**
     * Returns TRUE if the email is already registered in the database
     * @param $email
     * @return bool
     */
    public function isEmailAlreadyRegistered($email)
    {
        $req = "SELECT COUNT(*) FROM $this->tableName WHERE email=?";
        $req = $this->db->pdo()->prepare($req);
        $req->execute(array($email));
        if($req->fetchColumn() != 0) {
            return true;
        }
        return false;
    }

    /**
     * Returns TRUE if the username is already registered in the database
     * @param $username
     * @return bool
     */
    public function isUsernameAlreadyRegistered($username)
    {
        $req = "SELECT COUNT(*) FROM $this->tableName WHERE username=?";
        $req = $this->db->pdo()->prepare($req);
        $req->execute(array($username));
        if($req->fetchColumn() != 0) {
            return true;
        }
        return false;
    }
}
