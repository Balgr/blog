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
    private $userController;
    private $currentUser;

    public function __construct()
    {
        parent::__construct();
        $this->userController = new UserController();
        $this->commentController = new CommentController();
        if(isset($_SESSION['user'])) {
            $this->currentUser = UserController::currentUser();
        }

    }

    public function showHomePageAction() {
        echo $this->twig->render("frontend/index-7.html.twig", array("currentUser" => $this->currentUser));
    }

}