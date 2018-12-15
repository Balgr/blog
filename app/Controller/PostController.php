<?php


namespace Blog\app\Controller;

use Blog\app\Entity\Post;
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

    public function __construct()
    {
        parent::__construct();
        $this->model = new PostModel(Database::getInstance(Config::getConfigFromYAML(__DIR__ . "/../../config/database/db.yml")));
        //$this->twig->addPath($this->templatesPath . '/comments/backend/');
    }

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

        echo $this->twig->render("posts/index.html.twig", array("posts" => $posts));
    }

    /**
     * @param $id
     * Gets a single post in the database and renders it.
     */
    public function showAction($id) {
        $post = new Post($this->model()->getSingle($id));
        $this->commentController = new CommentController();
        $comments = $this->commentController->commentsByPostAction($id);

        // Renders the post with the comments
        try {
            echo $this->twig->render("posts/detail.html.twig", array("post" => $post, "comments" => $comments));
        } catch (Error $e) {
            $e->getMessage();
        }
    }

    /**
     * Adds a new Post in the database, and redirect the user towards the page that will show it to him.
     */
    public function addAction() {
        if(!isset($_POST) || empty($_POST)) {
            echo $this->twig->render('posts/add.html.twig', array('creatorId' => 1));
        }
        else {
            // Adds the Post data not set in the form
            $_POST['creationDate'] = date('Y-m-d H:i');
            $postId = $this->model->create($_POST);

            // Redirects to the showAction($id)
            header('Location: /OpenClassrooms/projet-5/blog/blog/posts/show/' . $postId);
        }
    }

    /**
     * @param $id
     * Allows the edition of the Post entity in the database.
     * If the form has not been sent yet (no or empty $_POST), the form is displayed with the Post data in it.
     * If the form has been sent ($_POST), the data is updated in the database, and the user is redirected to the showPost.
     */
    public function editAction($id) {
        $post = new Post($this->model->getSingle($id));
        if(!isset($_POST) || empty($_POST)) {
            echo $this->twig->render('posts/edit.html.twig', array("post" => $post->toArray()));
        }
        else {
            // Adds the Post data not set in the form
            $_POST['lastEditDate'] = date('Y-m-d H:i');
            $postId = $this->model->update($_POST);

            header('Location: /OpenClassrooms/projet-5/blog/blog/posts/show/' . $postId);
        }
    }

    public function deleteAction($id)
    {
        $post = new Post($this->model->getSingle($id));
        if ($post->isValid()) {
            if (($this->model->delete($id))) {
                header('Location: /OpenClassrooms/projet-5/blog/blog/users/');
            }
        }

        /** If the post is not valid (meaning that the $id was not correct), or the deletion fails for any reason,
         *  an exception is thrown.
         */
        throw new \Exception("ERREUR ! User invalide.");
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
}