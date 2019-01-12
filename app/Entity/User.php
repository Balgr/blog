<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 06/12/2018
 * Time: 11:29
 */

namespace Blog\app\Entity;
use Blog\core\Entity;

class User extends Entity
{
    private $username;
    private $password;
    private $email;
    private $dateInscription;
    private $category;
    private $biography;

    const STATUS_ADMIN = 0;
    const STATUS_MEMBER = 1;

    public function isValid()
    {
        if(empty($this->username) || empty($this->password)) {
            return false;
        }
        return true;
    }

    public function isAdmin() {
        if(intval($this->category) == self::STATUS_ADMIN) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function username()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function password()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function email()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function dateInscription()
    {
        return $this->dateInscription;
    }

    /**
     * @param mixed $dateInscription
     */
    public function setDateInscription($dateInscription)
    {
        $this->dateInscription = $dateInscription;
    }

    /**
     * @return mixed
     */
    public function category()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }


    /**
     * @return mixed
     */
    public function biography()
    {
        return $this->biography;
    }

    /**
     * @param mixed $biography
     */
    public function setBiography($biography)
    {
        $this->biography = $biography;
    }
}