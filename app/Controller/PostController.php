<?php


namespace Blog\app\Controller;

use Blog\app\Entity\Comment;
use Blog\app\Entity\Post;
use Blog\app\Entity\User;
use Blog\app\Model\CommentModel;
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
    private $currentUser;

    public function __construct()
    {
        parent::__construct();

        $this->model = new PostModel(Database::getInstance(Config::getConfigFromYAML(__DIR__ . "/../../config/database/db.yml")));
        $this->commentController = new CommentController();
        if(isset($_SESSION['user'])) {
            $this->currentUser = UserController::currentUser();
        }

        // Sets the uploaded files path
        $this->uploadPath = __DIR__ . '/../..' . Config::getConfigFromYAML(__DIR__ . '/../../config/config.yml')['posts']['upload_path'];

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateAndSanitizePostData();
        }
    }

    /**
     * BACKEND ROUTES
     */
    public function showListPostsAction() {
        UserController::whenCurrentUserAccessBackend();
        $posts = $this->getPosts();
        echo $this->twig->render("backend/posts/index.html.twig", array("currentUser" => $this->currentUser, "errors" => $this->errors, "posts" => $posts, "current" => array("posts", "list")));
    }

    public function showPostsTrashedAction() {
        UserController::whenCurrentUserAccessBackend();
        $posts = $this->getPosts(Post::POST_STATUS_TRASH);
        echo $this->twig->render("backend/posts/index.html.twig", array("currentUser" => $this->currentUser, "errors" => $this->errors, "posts" => $posts, "current" => array("posts", "trash")));
    }

    public function createPostAction() {
        UserController::whenCurrentUserAccessBackend();
        if($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo $this->twig->render("backend/posts/detail.html.twig", array("currentUser" => $this->currentUser, "imgPath" => $this->uploadPath, "current" => array("posts", "add")));
        }
        else {
            if(empty($this->errors)) {
                $this->addPost($_POST);
                header('Location: /backend/posts/');
            }
            echo $this->twig->render("backend/posts/detail.html.twig", array("currentUser" => $this->currentUser, "imgPath" => $this->uploadPath, "errors" => $this->errors, "current" => array("posts", "add")));
        }
    }
    private function addPost($data) {
        // Adds the Post data not set in the form
        $data['featuredImage'] = $this->uploadImage();
        $data['creationDate'] = date('Y-m-d H:i');
        $data['creatorId'] = $this->currentUser->id();
        if(!empty($this->errors)) {
            $this->createPostAction();
            return;
        }
        return $this->model->create($data);
    }

    public function editPostAction($id) {
        UserController::whenCurrentUserAccessBackend();
        $post = new Post($this->model()->getSingle($id));
        if(!$post->isValid()) {
            $this->errors['undefined'] = "Le post #$id n'existe pas";
            $this->showListPostsAction();
        }
        // Decodes the Post content in HTML for rendering
        $post->setContent(html_entity_decode($post->content()));

        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            if($post->isValid()) {
                echo $this->twig->render("backend/posts/detail.html.twig", array("currentUser" => $this->currentUser, "post" => $post, "imgPath" => $this->uploadPath, "current" => array("posts", "list")));
            }
        }
        else if($_SERVER['REQUEST_METHOD'] == 'POST') {
            if(empty($this->errors)) {
                $this->editPost($_POST, $id);
                header('Location: /backend/posts/');
            }
            echo $this->twig->render("backend/posts/detail.html.twig", array("currentUser" => $this->currentUser, "post" => $post, "imgPath" => $this->uploadPath, "errors" => $this->errors, "current" => array("posts", "list")));
        }
    }

    private function editPost($data) {
        // Adds the Post data not set in the form
        $data['lastEditDate'] = date('Y-m-d H:i');

        if($_FILES['featuredImage']['size'] != 0) {
            $data['featuredImage'] = $this->uploadImage();
        } else {
            unset($data['featuredImage']);
        }


        // Removes the unmodified data from the form
        unset($data['creatorId']);
        unset($data['creationDate']);
        return $this->model->update($data);
    }

    public function deletePostAction($id) {
        UserController::whenCurrentUserAccessBackend();
        if($this->deletePost($id)) {
            header('Location: /backend/posts');
        }
    }

    private function deletePost($id)
    {
        $post = new Post($this->model->getSingle($id));
        if ($post->isValid()) {
            if (($this->model->delete($id))) {
                return true;
            }
        }
        else {
            throw new \Exception("ERREUR ! Post invalide.");
        }
        return false;
    }

    public function trashPostAction($id) {
        UserController::whenCurrentUserAccessBackend();
        if($this->trash($id)) {
            header('Location: /backend/posts');
        };
    }

    private function trash($id) {
        return $this->changeStatus($id, Post::POST_STATUS_TRASH);
    }

    public function publishPostAction($id) {
        UserController::whenCurrentUserAccessBackend();
        if($this->publish($id)) {
            header('Location: /backend/posts');
        }
    }

    public function publish($id) {
        return $this->changeStatus($id, Post::POST_STATUS_PUBLISHED);
    }

    private function changeStatus($id, $status) {
         $post = new Post($this->model->getSingle($id));
         if($post->isValid()) {
             if($this->model->changeStatus($post->id(), $status)) {
                 return true;
             }
             else {
                 $this->errors['status_unchangeable'] = "Impossible de changer le statut du Post";
                 return false;
             }
         }

         $this->errors['undefined'] = "Le Post #" . $id . " n'existe pas";
         return false;
    }

    /**
     * FRONTEND
     * @param int $limit
     * Shows $limit number of Posts.
     */
    public function indexAction($limit = PostModel::NO_LIMIT) {
        $posts = $this->getPosts(Post::POST_STATUS_PUBLISHED, $limit);
        echo $this->twig->render("frontend/posts/index.html.twig", array("posts" => $posts, "currentUser" => $this->currentUser));
    }

    /**
     * Gets a single post in the database and renders it.
     * @param $id
     * @param null $errors
     * @throws \Exception
     */
    public function showAction($id, $errors = null) {
        $post = new Post($this->model()->getSingle($id));
        if($post->isValid()) {
            $this->commentController = new CommentController();
            $post->setComments($this->commentController->model()->getAllPublishedByPost($post->id(), $this->commentController->model::NO_LIMIT));
            $post->setAuthor($this->retrieveAuthor($id));

            // Decodes the Post content in HTML for rendering
            $post->setContent(html_entity_decode($post->content()));

            echo $this->twig->render("frontend/posts/blog-details-2.html.twig", array("post" => $post, "currentUser" => $this->currentUser, "errors" => $errors));
        }
        else {
            throw new \Exception('Le post spécifié n\'existe pas.');
        }
    }

    private function uploadImage()
    {
        $storage = new \Upload\Storage\FileSystem($this->uploadPath);
        $file = new \Upload\File('featuredImage', $storage);

        $validations = new \Upload\Validation\Mimetype(array('image/png', 'image/jpeg'));
        $size = new \Upload\Validation\Size('5M');

        $file->addValidations(array($validations, $size));

        try {
            $file->upload();
        } catch (\Exception $e) {
            $this->errors['featuredImage'] = 'Upload impossible';
        }
        return $file->getNameWithExtension();
    }


    private function getPosts($status = -1, $limit = PostModel::NO_LIMIT) {
        if($limit === PostModel::NO_LIMIT) {
            $limit = $this->limit;
        }
        $data = $this->model->getAll($status, $limit);
        $posts = [];
        foreach($data as $postData) {
            $post= new Post($postData);
            $content = str_replace('amp;', '', $post->content());
            $post->setContent(html_entity_decode($content));
            $post->setAuthor($this->retrieveAuthor($post->id()));
            $post->setCommentsNb($this->commentController->getNumberOfComments($post->id()));
            $posts[] = $post;
        }
        return $posts;
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

    private function validateAndSanitizePostData()
    {
        if(empty($_POST['title']) || empty($_POST['subtitle']) || empty($_POST['content'])) {
            $this->errors['empty'] = 'Veuillez remplir tous les champs';
        }
        else {
            // Checks title
            $_POST['title'] = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
            if (filter_var($_POST['title'], FILTER_SANITIZE_STRING) === false) {
                $this->errors['title'] = 'Veuillez entrer un titre correct';
            }
            else if(strlen($_POST['title']) > 50) {
                $this->errors['title'] = 'Le titre ne peut contenir plus de 50 caractères.';
            }

            // Checks subtitle
            $_POST['subtitle'] = htmlentities($_POST['subtitle']);
            $_POST['subtitle'] = filter_var($_POST['subtitle'], FILTER_SANITIZE_STRING);
            if (!preg_match('/^.{,80}$/', $_POST['subtitle'])) {
                $this->errors['subtitle'] = 'Le sous-titre doit contenir moins de 80 caractères';
            }
            // Checks content
            $_POST['content'] = htmlentities($_POST['content']);

            if(isset($_POST['lastEditReason'])) {
                if (!preg_match('/^.{,100}$/', $_POST['lastEditReason'])) {
                    $this->errors['lastEditReason'] = 'Le motif d\'édition doit contenir moins de 100 caractères.';
                }
            }
        }

        // The checks for the Image input are handled by the Upload library.
    }

    private function retrieveAuthor($id)
    {
        $user = new User($this->model->getAuthor($id));
        $user->setBiography(html_entity_decode($user->biography()));

        return $user;
    }
}
