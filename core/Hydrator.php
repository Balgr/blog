<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 04/12/2018
 * Time: 16:25
 */

namespace Core;

trait Hydrator {

    public function hydrate($data) {
        foreach ($data as $key => $value) {
            $method = 'set'.ucfirst($key);

            if (is_callable([$this, $method]) && method_exists($this, $method)) {
                $this->$method($value);
            }
        }

    }
}