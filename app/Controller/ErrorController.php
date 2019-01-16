<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 12/01/2019
 * Time: 20:17
 */

namespace Blog\app\Controller;

use Blog\core\Controller;

class ErrorController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show404Action() {
        echo $this->twig->render("404.html.twig");
    }

    public function show403Action() {
        echo $this->twig->render("403.html.twig");
    }
}
