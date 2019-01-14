<?php


namespace Blog\app\Controller;

use Blog\app\Entity\Post;
use Blog\app\Entity\User;
use Blog\app\Model\PostModel;
use Blog\core\Config;
use Blog\core\Controller;
use Blog\core\Database;
use Blog\core\Router;
use mysql_xdevapi\Exception;
use Twig\Error\Error;

/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 09/12/2018
 * Time: 20:15
 */

class PostController extends Controller
{
    private $commentController;
    private $userController;

    public function __construct()
    {
        parent::__construct();
        $path = dirname(__DIR__) . "/../../blog/config/database/db.yml";
        $this->model = new PostModel(Database::getInstance(Config::getConfigFromYAML($path)));
        $this->userController = new UserController();
    }

    /**
     * FRONTEND METHODS
     */

    /**
     * @param int $limit
     * Shows $limit number of Posts.
     */
    public function indexAction($limit = PostModel::NO_LIMIT) {
        if($limit === PostModel::NO_LIMIT) {
            $limit = $this->limit;
        }
        $data = $this->model->getAll($limit);
        $posts = [];
        foreach($data as $post) {
            $posts[] = new Post($post);
        }
        echo $this->twig->render("posts/backend/index.html.twig", array("posts" => $posts));
    }

    /**
     * Gets a single post in the database and renders it.
     * @param $id
     * @throws \Exception
     */
    public function showAction($id) {
        $post = new Post($this->model()->getSingle($id));
        if($post->isValid()) {
            $this->commentController = new CommentController();
            $comments = $this->commentController->model()->getAllByPost($id, $this->commentController->model::NO_LIMIT);

            // Renders the post with the comments
            try {
                echo $this->twig->render("posts/backend/detail.html.twig", array("post" => $post, "comments" => $comments));
            } catch (Error $e) {
                $e->getMessage();
            }
        }
        else {
            throw new \Exception('Le post spécifié n\'existe pas.');
        }

    }

    /**
     * Adds a new Post in the database, and redirect the user towards the page that will show it to him.
     * @throws \Exception
     */
    public function addAction() {
        if($this->userController->isCurrentUserAdmin()) {
            $user = unserialize($_SESSION['id']);
            echo $this->twig->render('posts/backend/add.html.twig', array('creatorId' => $user->id()));
        }
        else {
            // Adds the Post data not set in the form
            $_POST['creationDate'] = date('Y-m-d H:i');
            $postId = $this->model->create($_POST);

            // Redirects to the showAction($id)
            header('Location: /OpenClassrooms/projet-5/blog/blog/posts/' . $postId);
        }
    }

    /**
     * @param $id
     * @throws \Exception
     * Allows the edition of the Post entity in the database.
     * If the form has not been sent yet (no or empty $_POST), the form is displayed with the Post data in it.
     * If the form has been sent ($_POST), the data is updated in the database, and the user is redirected to the showPost.
     */
    // TODO : Must check if the user is identified and is an admin OR the author of the post before authorizing him to edit.
    /**
     * @param $id
     */
    public function editAction($id) {
        if($this->userController->isCurrentUserAdmin() || $this->userController->isCurrentUserAuthorOfPost($id)) {
            $post = new Post($this->model->getSingle($id));
            if(!isset($_POST) || empty($_POST)) {
                echo $this->twig->render('posts/backend/edit.html.twig', array("post" => $post->toArray()));
            }
            else {
                // Adds the Post data not set in the form
                $_POST['lastEditDate'] = date('Y-m-d H:i');
                $postId = $this->model->update($_POST);

                header('Location: /OpenClassrooms/projet-5/blog/blog/posts/' . $postId);
            }
        }
    }

    /**
     * Changes the Post's status.
     * @param $id
     * @throws \Exception
     */
    private function changeStatus($id, $status) {
        if($this->userController->isCurrentUserAdmin()) {
            $user = unserialize($_SESSION['user']);
            if($user != false) {
                $post = new Post($this->model->getSingle($id));
                if($this->model->changeStatus($post->id(), $status)) {
                    header('Location: /OpenClassrooms/projet-5/blog/blog/posts/');
                }
                else {
                    throw new \Exception("Impossible de corbeiller.");
                }
            }
        }
    }

    public function trashAction($id) {
        try {
            $this->changeStatus($id, Post::POST_STATUS_TRASH);
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    public function publishAction($id) {
        try {
            $this->changeStatus($id, Post::POST_STATUS_PUBLISHED);
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }


    /**
     * Permanently deletes a Post from the database.
     * @param $id
     * @throws \Exception
     */
    public function deleteAction($id)
    {
        if($this->userController->isCurrentUserAdmin() || $this->userController->isCurrentUserAuthorOfPost($id)) {
            $post = new Post($this->model->getSingle($id));
            if ($post->isValid()) {
                if (($this->model->delete($id))) {
                    header('Location: /OpenClassrooms/projet-5/blog/blog/users/');
                }
            }

            /** If the post is not valid (meaning that the $id was not correct), or the deletion fails for any reason,
             *  an exception is thrown.
             */
            throw new \Exception("ERREUR ! Post invalide.");
        }

    }



    /**
     * @return mixed
     */
    public function commentController()
    {
        return $this->commentController;
    }

    /**
     * @param mixed $commentController
     */
    public function setCommentController($commentController)
    {
        $this->commentController = $commentController;
    }

    /**
     * @return mixed
     */
    public function userController()
    {
        return $this->userController;
    }

    /**
     * @param mixed $userController
     */
    public function setUserController($userController)
    {
        $this->userController = $userController;
    }
}