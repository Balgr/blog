<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 06/12/2018
 * Time: 10:30
 */

namespace Blog\core;
require('Hydrator.php');

/**
 * Class Entity
 * @package Core
 * TODO : The Entity must contain a method that transforms it to an array instead of doing it in each Entity child.
 */

abstract class Entity
{
    use Hydrator;

    protected $id;

    abstract protected function isValid();

    public function __construct($donnees)
    {
        if (!empty($donnees)) {
            $this->hydrate($donnees);
        }
    }

    public function isNew() {
        return empty($this->id);
    }

    public function id() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = (int) $id;
    }
}