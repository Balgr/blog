<?php


namespace Blog\app\Controller;

use Blog\app\Entity\User;
use Blog\app\Model\UserModel;
use Blog\core\Config;
use Blog\core\Controller;
use Blog\core\Database;
use mysql_xdevapi\Exception;
use Twig\Error\Error;

/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 09/12/2018
 * Time: 20:15
 */

class UserController extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new UserModel(Database::getInstance(Config::getConfigFromYAML(__DIR__ . "/../../config/database/db.yml")));
    }

    /**
     * @param int $limit
     * Shows $limit number of Users.
     */
    public function indexAction($limit = UserModel::NO_LIMIT) {
        if($limit < 0) {
            $limit = $this->limit;
        }
        $data = $this->model->getAll($limit);
        $users = [];
        foreach($data as $user) {
            $users[] = new User($user);
        }

        echo $this->twig->render("users/index.html.twig", array("users" => $users));
    }

    /**
     * @param $id
     * Gets a single user in the database and renders it.
     */
    public function showAction($id) {
        $user = new User($this->model()->getSingle($id));
        try {
            echo $this->twig->render("users/detail.html.twig", array("user" => $user));
        } catch (Error $e) {
            $e->getMessage();
        }
    }

    /**
     * Adds a new User in the database, and redirect the user towards the page that will show it to him.
     */
    public function addAction() {
        if(!isset($_POST) || empty($_POST)) {
            echo $this->twig->render('users/add.html.twig', array("user" =>
                array("category" =>
                    array(
                        'Member' => User::STATUS_MEMBER,
                        'Admin' => User::STATUS_ADMIN
                    )
                )
            ));
        }
        else {
            // Adds the User data not set in the form
            $_POST['dateInscription'] = date('Y-m-d H:i');
            $userId = $this->model->create($_POST);

            // Redirects to the showAction($id)
            header('Location: /OpenClassrooms/projet-5/blog/blog/users/show/' . $userId);
        }
    }

    /**
     * @param $id
     * Allows the edition of the User entity in the database.
     * If the form has not been sent yet (no or empty $_POST), the form is displayed with the User data in it.
     * If the form has been sent ($_POST), the data is updated in the database, and the user is redirected to the showUser.
     */
    public function editAction($id) {
        $user = new User($this->model->getSingle($id));
        if(!isset($_POST) || empty($_POST)) {
            echo $this->twig->render('users/edit.html.twig', array("user" => $user->toArray()));
        }
        else {
            $userId = $this->model->update($_POST);

            header('Location: /OpenClassrooms/projet-5/blog/blog/users/show/' . $userId);
        }
    }

    public function deleteAction($id)
    {
        $user = new User($this->model->getSingle($id));
        if ($user->isValid()) {
            if (($this->model->delete($id))) {
                header('Location: /OpenClassrooms/projet-5/blog/blog/users/');
            }
        }

        /** If the post is not valid (meaning that the $id was not correct), or the deletion fails for any reason,
         *  an exception is thrown.
         */
        throw new \Exception("ERREUR ! User invalide.");
    }
}