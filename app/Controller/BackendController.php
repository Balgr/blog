<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 04/01/2019
 * Time: 15:17
 */

namespace Blog\app\Controller;


use Blog\app\Entity\Post;
use Blog\app\Entity\User;
use Blog\core\Controller;

class BackendController extends Controller
{
    private $userController;
    private $commentController;
    private $postController;
    private $currentUser;

    public function __construct()
    {
        parent::__construct();

        $this->userController = new UserController();
        $this->commentController = new CommentController();
        $this->postController = new PostController();

        $this->checkIfUserConnectedAndAdmin();
    }

    public function showHomeAction() {
        echo $this->twig->render("backend/home.html.twig", array("currentUser" => $this->currentUser, "current" => array("home")));
    }

    public static function checkIfUserConnectedAndAdmin() {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
        }
        if(!UserController::currentUser()->isAdmin()) {
            //$errorController = new ErrorController();
            //$errorController->show403Action();
            header('Location: /forbidden');
        }
    }
}