<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 04/01/2019
 * Time: 15:17
 */

namespace Blog\app\Controller;

use Blog\core\Controller;

class BackendController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        UserController::whenCurrentUserAccessBackend();
    }

    public function showHomeAction() {
        echo $this->twig->render("backend/home.html.twig", array("currentUser" => UserController::currentUser(), "current" => array("home")));
    }
}
