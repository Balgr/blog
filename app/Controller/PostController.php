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
        //$this->uploadPath = __DIR__ . '/../..' . Config::getConfigFromYAML(__DIR__ . '/../../config/config.yml')['posts']['upload_path'];
        $this->uploadPath = __DIR__ . '/../../public/uploads/posts/';
        //var_dump(is_dir(__DIR__ . '/../../public/uploads/posts/'));

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateAndSanitizePostData();
        }
    }

    /**
     * BACKEND ROUTES
     */
    public function showListPostsAction() {
        $posts = $this->getPosts();
        echo $this->twig->render("backend/posts/index.html.twig", array("currentUser" => $this->currentUser, "posts" => $posts));
    }

    public function createPostAction() {
        if($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo $this->twig->render("backend/posts/detail.html.twig", array("currentUser" => $this->currentUser, "imgPath" => $this->uploadPath));
        }
        else {
            if(empty($this->errors)) {
                $this->addPost($_POST);
                header('Location: /backend/posts/');
            }
            echo $this->twig->render("backend/posts/detail.html.twig", array("currentUser" => $this->currentUser, "imgPath" => $this->uploadPath, "errors" => $this->errors));
        }
    }
    private function addPost($data) {
        // Adds the Post data not set in the form
        $data['featuredImage'] = $this->uploadImage();
        $data['creationDate'] = date('Y-m-d H:i');
        $data['creatorId'] = $this->currentUser->id();
        return $this->model->create($data);
    }

    public function editPostAction($id) {
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $post = new Post($this->model()->getSingle($id));
            if($post->isValid()) {
                echo $this->twig->render("backend/posts/detail.html.twig", array("currentUser" => $this->currentUser, "post" => $post, "imgPath" => $this->uploadPath));
            }
        }
        else if($_SERVER['REQUEST_METHOD'] == 'POST') {
            if(empty($this->errors)) {
                $this->editPost($_POST, $id);
                header('Location: /backend/posts/');
            }
            echo $this->twig->render("backend/posts/detail.html.twig", array("currentUser" => $this->currentUser, "post" => $post, "imgPath" => $this->uploadPath, "errors" => $this->errors));
        }
    }

    private function editPost($data) {
        // Adds the Post data not set in the form
        $data['lastEditDate'] = date('Y-m-d H:i');
        if(!empty($_FILES)) {
            $data['featuredImage'] = $this->uploadImage();
        }

        // Removes the unmodified data from the form
        unset($data['creatorId']);
        unset($data['creationDate']);
        return $this->model->update($data);
    }

    public function deletePostAction($id) {
        if($this->deletePost($id)) {
            header('Location: /backend/posts');
        }
    }

    private function deletePost($id)
    {
        $post = new Post($this->model->getSingle($id));
        if ($post->isValid()) {
            if (($this->model->delete($id))) {
                //header('Location: /posts');
                return true;
            }
        }
        else {
            throw new \Exception("ERREUR ! Post invalide.");
        }
        return false;
    }

    public function trashPostAction($id) {
        if($this->trash($id)) {
            header('Location: /backend/posts');
        };
    }

    private function trash($id) {
        return $this->changeStatus($id, Post::POST_STATUS_TRASH);
    }

    public function publishPostAction($id) {
        if($this->publish($id)) {
            header('Location: /backend/posts');
        }
    }

    public function publish($id) {
        return $this->changeStatus($id, Post::POST_STATUS_PUBLISHED);
    }

    private function changeStatus($id, $status) {
        if(UserController::isCurrentUserAdmin()) {
            $user = new User(unserialize($_SESSION['user']));
            // if ($user->isValid()) {
            if($user != false) {
                $post = new Post($this->model->getSingle($id));
                if($this->model->changeStatus($post->id(), $status)) {
                    return true;
                }
                else {
                    throw new \Exception("Impossible de changer le statut.");
                }
            }
            return false;
        }
        return false;
    }

    /**
     * FRONTEND
     * @param int $limit
     * Shows $limit number of Posts.
     */
    public function indexAction($limit = PostModel::NO_LIMIT) {
        $posts = $this->model->getAllBy(Post::POST_STATUS_PUBLISHED, $limit);
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
            $post->setComments($this->commentController->model()->getAllByPost($id, $this->commentController->model::NO_LIMIT));
            $post->setAuthor(new User($this->model->getAuthor($id)));

            echo $this->twig->render("frontend/posts/blog-details-2.html.twig", array("post" => $post, "currentUser" => $this->currentUser, "errors" => $errors));
        }
        else {
            throw new \Exception('Le post spécifié n\'existe pas.');
        }
    }

    private function uploadImage()
    {
        var_dump($_FILES);
        $storage = new \Upload\Storage\FileSystem($this->uploadPath);
        $file = new \Upload\File('featuredImage', $storage);
        var_dump($_POST);

        $validations = new \Upload\Validation\Mimetype(array('image/png', 'image/jpeg'));
        $size = new \Upload\Validation\Size('5M');

        $file->addValidations(array($validations, $size));

        try {
            $file->upload();
            return $file->getNameWithExtension();
        } catch (\Exception $e) {
            print_r($file->getErrors());
        }
    }


    private function getPosts($status = -1, $limit = PostModel::NO_LIMIT) {
        if($limit === PostModel::NO_LIMIT) {
            $limit = $this->limit;
        }
        $data = $this->model->getAll($status, $limit);

        $posts = [];
        foreach($data as $postData) {
            $post= new Post($postData);
            $post->setAuthor($this->model->getAuthor($post->id()));
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
        if(empty($_POST['title']) OR empty($_POST['subtitle']) OR empty($_POST['content'])) {
            $this->errors['empty'] = 'Veuillez remplir tous les champs';
        }
        else {
            // Checks title
            $_POST['title'] = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
            if (!filter_var($_POST['title']) === false) {
                $this->errors['email'] = 'Veuillez entrer un titre correct';
            }

            // Checks subtitle
            $_POST['subtitle'] = htmlspecialchars($_POST['subtitle']);
            $_POST['subtitle'] = filter_var($_POST['subtitle'], FILTER_SANITIZE_STRING);
            if (!preg_match('/^.{5,}$', $_POST['subtitle'])) {
                $this->errors['subtitle'] = 'Le sous-titre doit contenir plus de 5 caractères.';
            }

            // Checks content
            $_POST['content'] = htmlspecialchars($_POST['content']);
            $_POST['content'] = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
            if (!preg_match('/^.{100,}$', $_POST['content']) && (strlen(trim($_POST['content'])) !== 0)) {
                $this->errors['content'] = 'Le post doit contenir au moins 100 caractères.';
            }
        }

        // The checks for the Image input are handled by the Upload library.
    }
}