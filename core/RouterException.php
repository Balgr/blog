<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 11/12/2018
 * Time: 14:16
 */

namespace Blog\core;


class RouterException extends \Exception
{
    public function __construct($message = null, $code = 0)
    {
        parent::__construct($message, $code);
    }
}