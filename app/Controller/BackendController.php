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
        if(!isset($_SESSION['user'])) {
            throw new \Exception("Non connecté.");
        }
        else {
            $this->currentUser = UserController::currentUser();
            if(!$this->currentUser->isAdmin()) {
                // REDIRECTION DROITS INSUFFISANTS
                throw new \Exception("Droits insuffisants.");
            }
        }
    }

    public function showHomeAction() {
        echo $this->twig->render("backend/home.html.twig", array("currentUser" => $this->currentUser, "current" => array("home")));
    }
}