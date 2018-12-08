<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 06/12/2018
 * Time: 11:29
 */

namespace App\Entity;
use Core\Entity;

class User extends Entity
{
    private $nickname;
    private $password;
    private $email;
    private $dateInscription;
    private $category;

    public function isValid()
    {
        return ! empty($this->nickname) || empty($this->password) || empty($this->email) || empty($this->category);
    }

    /**
     * @return mixed
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * @param mixed $nickname
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;
    }

    /**
     * @return mixed
     */
    public function getPassword()
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
    public function getEmail()
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
    public function setDateInscription(\DateTime $dateInscription)
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

}