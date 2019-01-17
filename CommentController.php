<?php

namespace Blog\app\Controller;

use Blog\app\Model\CommentModel;
use Blog\core\Controller;
use Blog\app\Entity\Comment;
use Blog\core\Config;
use Blog\core\Database;
&use Twig\Error\Error;
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 09/12/2018
 * Time: 20:15
 */

class CommentController extends Controller
{
    private $postController;

    public function __construct()
    {
        parent::__construct();
        $this->model = new CommentModel(Database::getInstance(Config::getConfigFromYAML(__DIR__ . "/../../config/database/db.yml")));
        $this->postController = new PostController();
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

        echo $this->twig->render("comments/backend/index.html.twig", array("comments" => $comments));
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

        echo $this->twig->render("comments/backend/index.html.twig", array("comments" => $comments));
    }

    /**
     * @param $id
     * Gets a single comment in the database and renders it.
     */
    public function showAction($id) {
        $comment = new Comment($this->model()->getSingle($id));
        try {
            echo $this->twig->render("comments/detail.html.twig", array("comment" => $comment));
        } catch (Error $e) {
            $e->getMessage();
        }
    }


    public function addAction($postId) {
        if(!isset($_POST) || empty($_POST)) {
            //echo $this->twig->render('comments/add.html.twig', array('postId' => $postId));
            // SHOW ERROR
        }
        else {
            // Adds the data not set in the form (the creationDate, and the status (In Moderation))
            $comment = new Comment($_POST);
            $comment->setCreationDate(date('Y-m-d H:i'));
            $comment->setStatus(Comment::COMMENT_IN_MODERATION);
            $this->model->create($comment->toArray());

            // Redirects to the showAction($id)
            header('Location: /OpenClassrooms/projet-5/blog/blog/posts/' . $_POST['postId']);
        }
    }


    /**
     * Gets all comments that have "In Moderation" status.
     */
    public function listInModerationAction() {
        // TODO : check if the user is an admin
        foreach($data = $this->model()->getComments() as $comment) {
            $comments[] = new Comment($comment);
        }

        echo $this->twig->render("comments/backend/index.html.twig", array("comments" => $comments, "title" => "Commentaires en modération"));
    }


    /**
     * Sets the Comment's status to "Published", and redirects the user to the Comment's related Post
     * @param $id
     * @throws \Exception
     */
    public function publishCommentAction($id) {
        // TODO : check if the user is an admin
        if($comment = new Comment($this->model()->moderateComment($id, Comment::COMMENT_TRASH))) {
            header("Location: /OpenClassrooms/projet-5/blog/blog/posts/show/" . $comment->postId());
        }
        else {
            throw new \Exception("Commentaire : suppression impossible");
        }
    }

    /**
     * @param $id
     * Sets the Comment's status to "Trash", and redirects the user to the Comment's related Post
     * @throws \Exception
     */
    public function trashCommentAction($id) {
        // TODO : check if the user is an admin
        if($comment = new Comment($this->model()->moderateComment($id, Comment::COMMENT_TRASH))) {
            header("Location: /OpenClassrooms/projet-5/blog/blog/posts/show/" . $comment->postId());
        }
        else {
            throw new \Exception("Commentaire : suppression impossible");
        }
    }

    /**
     * Permanently deletes a comment from the database, and redirects the user to the Comment's related Post.
     * @param $id
     * @throws \Exception
     */
    public function deleteAction($id)
    {
        $comment = new Comment($this->model->getSingle($id));
        $postId = $comment->postId();
        // TODO : check if the user is an admin or author of the comment
        if ($this->model()->delete($comment->id())) {
            header("Location: /OpenClassrooms/projet-5/blog/blog/posts/" . $postId);
        } else {
            throw new \Exception("Commentaire : suppression impossible");
        }
    }
}