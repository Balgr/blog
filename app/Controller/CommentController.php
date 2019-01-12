<?php

namespace Blog\app\Controller;

use Blog\app\Model\CommentModel;
use Blog\core\Controller;
use Blog\app\Entity\Comment;
use Blog\core\Config;
use Blog\core\Database;
use Twig\Error\Error;

class CommentController extends Controller
{
    private $currentUser;

    public function __construct()
    {
        parent::__construct();
        $this->model = new CommentModel(Database::getInstance(Config::getConfigFromYAML(__DIR__ . "/../../config/database/db.yml")));

        if(isset($_SESSION['user'])) {
            $this->currentUser = UserController::currentUser();
        }
    }

    /***********************************
     ************ BACKEND **************
     ***********************************/

    public function showListCommentsAction() {
        $comments = $this->getComments();
        echo $this->twig->render("backend/comments/index.html.twig", array("currentUser" => $this->currentUser, "comments" => $comments, "current" => array("comments", "list")));
    }

    public function showCommentsTrashedAction() {
        $comments = $this->getComments(Comment::COMMENT_TRASH);
        echo $this->twig->render("backend/comments/index.html.twig", array("currentUser" => $this->currentUser, "comments" => $comments, "current" => array("comments", "trash")));
    }

    public function showCommentsToModerateAction() {
        $comments = $this->getComments(Comment::COMMENT_IN_MODERATION);
        echo $this->twig->render("backend/comments/index.html.twig", array("currentUser" => $this->currentUser, "comments" => $comments, "current" => array("comments", "moderate")));
    }

    public function deleteCommentAction($id) {
        if($this->delete($id)) {
            header('Location: /backend/comments');
        }
    }

    private function delete($id)
    {
        $comment = new Comment($this->model->getSingle($id));
        if (!is_null($comment)) {
            if (($this->model->delete($id))) {
                return true;
            }
        }
        else {
            throw new \Exception("ERREUR ! Comment invalide.");
        }
        return false;
    }

    public function trashCommentAction($id) {
        if($this->trash($id)) {
            header('Location: /backend/comments');
        }
    }

    private function trash($id) {
        return $this->changeStatus($id, Comment::COMMENT_TRASH);
    }

    public function publishCommentAction($id) {
        if($this->publish($id)) {
            header('Location: /backend/comments');
        }
    }

    private function publish($id) {
        return $this->changeStatus($id, Comment::COMMENT_PUBLISHED);
    }

    private function changeStatus($id, $status) {
        if(UserController::isCurrentUserAdmin()) {
            $user = unserialize($_SESSION['user']);
            if($user != false) {
                $post = new Comment($this->model->getSingle($id));
                if($this->model->moderateComment($post->id(), $status)) {
                    return true;
                }
                else {
                    throw new \Exception("Impossible de corbeiller.");
                }
            }
        }
        return false;
    }


    private function getComments($status = null, $limit = CommentModel::NO_LIMIT) {
        if($limit === CommentModel::NO_LIMIT) {
            $limit = $this->limit;
        }
        $data = $this->model->getComments($status, $limit);
        $comments = [];
        foreach($data as $commentData) {
            $comment= new Comment($commentData);
            $comments[] = $comment;
        }
        return $comments;
    }
















    /**
     * @param int $limit
     * Shows $limit number of Comments. If no limit is defined, the default limit is used.
     */
    public function indexAction($limit = CommentModel::NO_LIMIT) {
        if($limit === CommentModel::NO_LIMIT) {
            $limit = $this->limit;
        }
        $data = $this->model->getAll($limit);
        $comments = [];
        foreach($data as $comment) {
            $comments[] = new Comment($comment);
        }

        echo $this->twig->render("comments/backend/index.html.twig.twig", array("comments" => $comments));
    }




    public function commentsByPostAction($postId, $limit = CommentModel::NO_LIMIT) {
        if($limit === CommentModel::NO_LIMIT) {
            $limit = $this->limit;
        }

        $data = $this->model->getAllByPost($postId, $limit);
        $comments = [];
        foreach($data as $comment) {
            $comments[] = new Comment($comment);
        }

        echo $this->twig->render("comments/backend/index.html.twig.twig", array("comments" => $comments));
    }

    /**
     * @param $id
     * Gets a single comment in the database and renders it.
     */
    public function showAction($id) {
        $comment = new Comment($this->model()->getSingle($id));
        try {
            echo $this->twig->render("comments/detail.html.twig", array("comment" => $comment));
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }


    public function addAction() {
        if(!isset($_POST)) {
            $this->errors['empty'] = 'Veuillez envoyer le formulaire correctement.';
        }
        else {

            // Adds the data not set in the form (the creationDate, the status (In Moderation), and the author info if it is connected)
            if (!is_null($this->currentUser)) {
                $_POST['authorName'] = $this->currentUser->username();
                $_POST['authorEmail'] = $this->currentUser->email();
            }
            $_POST['creationDate'] = date('Y-m-d H:i');
            $_POST['status'] = Comment::COMMENT_IN_MODERATION;

            $this->validateAndSanitizePostData();

            /* data is validated and sanitize */
            if(empty($this->errors)) {
                $this->model->create($_POST);
                header('Location: /posts/' . $_POST['postId']);
            }
        }
        $postController = new PostController();
        $postController->showAction($_POST['postId'], $this->errors);
    }


    public function getNumberOfComments($postId)
    {
        return $this->model->countComments($postId);
    }

    private function validateAndSanitizePostData()
    {
        if(empty($_POST['authorName']) OR empty($_POST['authorEmail']) OR empty($_POST['content']) OR empty($_POST['postId'])) {
            $this->errors['empty'] = 'Veuillez remplir tous les champs';
        }
        else {
            // Checks email
            $_POST['authorEmail'] = filter_var($_POST['authorEmail'], FILTER_SANITIZE_EMAIL);
            if (!filter_var($_POST['authorEmail'], FILTER_VALIDATE_INT) === false) {
                $this->errors['email'] = 'Veuillez entrer un email correct.';
            }

            $_POST['authorName'] = strip_tags($_POST['authorName']);
            if (!preg_match('/^[a-zA-Z0-9]{5,}/', $_POST['authorName'])) {
                $this->errors['authorName'] = 'Veuillez entrer un nom d\'utilisateur, au moins 6 caractères alphanumériques.';
            }

            $_POST['content'] = strip_tags($_POST['content']);
            if (!preg_match('/^.{15,}$', $_POST['content']) === FALSE) {
                $this->errors['content'] = 'Le commentaire doit contenir au moins 15 caractères.';
            }
        }
    }
}