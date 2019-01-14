<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 01/01/2019
 * Time: 15:46
 */

namespace Blog\app\Controller;


use Blog\core\Controller;

class HomeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function showHomePageAction() {
        echo $this->twig->render("frontend/index-7.html.twig", array("currentUser" => UserController::currentUser()));
    }

}
