<?php


namespace Blog\app\Controller;

use Blog\app\Entity\Post;
use Blog\app\Entity\User;
use Blog\app\Model\PostModel;
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
    protected $categories;
    private $postModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = new UserModel(Database::getInstance(Config::getConfigFromYAML(__DIR__ . "/../../config/database/db.yml")));
        $this->categories = array(
            "Member" => User::STATUS_MEMBER,
            "Admin" => User::STATUS_ADMIN
        );
        $this->postModel = new PostModel($this->model()->db());
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
        $user = new User($this->model->getSingle($id));
        if(isset($_SESSION['user'])) {
            $userSession = unserialize($_SESSION['user']);
            $user = $user->toArray();
            $user['category'] = $this->categories;

            if($userSession->id() === $user['id']) {
                echo $this->twig->render("users/edit.html.twig", array("user" => $user));
            }
            else {
                echo $this->twig->render("users/detail.html.twig", array("user" => $user));
            }
        }
        else {
            echo $this->twig->render("users/detail.html.twig", array("user" => $user));
        }
    }

    public function profileAction() {
        $user = unserialize($_SESSION['user']);
        $this->showAction($user->id());
    }

    /**
     * Adds a new User in the database, logs the said User in and redirect it to its profile page.
     */
    public function registerAction()
    {
        if(!isset($_SESSION)) {
            session_start();
        }
        // Empties the previous list of errors
        $errors = [];
        // If the User is already logged in, redirects him to its profile page.
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            header('Location: /OpenClassrooms/projet-5/blog/blog/profile/');
        }

        // If the User is NOT passing data from the registration form, shows him the form.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo $this->twig->render('users/add.html.twig', array("user" =>
                array("category" => $this->categories)
            ));
        }

        // Else, checks if all the data is correctly sent : if not, prints the form again with errors.
        else {
            /**
             * Check the User's username and/or e-mail already exists in the database.
             * If so, prints the registration form with errors.
             * TODO : refactor this.
             */
            $emailOK = $this->model->isEmailAlreadyRegistered(htmlspecialchars($_POST['email']));
            $usernameOK = $this->model->isUsernameAlreadyRegistered(htmlspecialchars($_POST['username']));
            if ($emailOK || $usernameOK) {
                if(!$emailOK) {
                    $this->errors['email'] = 'E-mail déjà existant.';
                }
                if (!$usernameOK) {
                    $this->errors['username'] = 'Nom d\'utilisateur déjà existant.';
                }
                echo $this->twig->render('users/add.html.twig',
                    array(
                        "user" => array("category" => $this->categories),
                        "errors" => $this->errors
                    ));
            }
            // Else, if everything is OK, saves the new User to the database and logs him in.
            else {
                // Adds the User data not set in the form
                $_POST['dateInscription'] = date('Y-m-d H:i');
                $_POST['password'] = password_hash($_POST['password'], PASSWORD_ARGON2I);
                var_dump($_POST);
                $user = new User($this->model->getSingle($this->model->create($_POST)));
                $user->setPassword('');
                $_SESSION['user'] = serialize($user);

                $_SESSION['logged_in'] = true;

                // Deletes the User's hashed password from the session

                // Redirects to the User's profile
                header('Location: /OpenClassrooms/projet-5/blog/blog/profile');
            }
        }
    }

    /**
     * @param $id
     * Allows the edition of the User entity in the database.
     * If the form has not been sent yet (no or empty $_POST), the form is displayed with the User data in it.
     * If the form has been sent ($_POST), the data is updated in the database, and the user is redirected to the showUser.
     */
    public function editAction($id = null) {
        $user = new User($this->model->getSingle($id));
        if (!isset($_POST) || empty($_POST)) {
            $userArray = $user->toArray();
            $userArray['category'] = $this->categories;
            echo $this->twig->render('users/edit.html.twig', array("user" => $userArray));
        }
        else {
            if(isset($_POST["password"])) {
                $_POST["password"] = password_hash($_POST['password'], PASSWORD_ARGON2I);
            }
            $userId = $this->model->update($_POST);

            if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                $user = unserialize($_SESSION["user"]);
                if($user->id() === $userId) {
                    $user = new User($this->model->getSingle($userId));
                    $_SESSION['user'] = serialize($user);
                }
            }
            // Redirects to the edited User's detail page.
            header('Location: /OpenClassrooms/projet-5/blog/blog/users/show/' . $userId);
        }
    }


    public function loginAction() {
        if(!isset($_SESSION)) {
            session_start();
        }

        if(isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true){
            header("Location: /OpenClassrooms/projet-5/blog/blog/profile");
        }
        else {
            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Checks if the User's username and password are not empty
                $username = trim($_POST['username']);
                $password = trim($_POST['password']);

                if (empty($username)) {
                    $this->errors['username'] = "Empty username";
                }
                if (empty($password)) {
                    $this->errors['password'] = "Empty password";
                }

                // If the username and password are provided...
                else {
                    // Checks if the username exists in database
                    $user = new User($this->model->getByUsername($username));
                    //var_dump($user);

                    /**
                     * If the User exists in the database, checks if the provided username and password are correct
                     */
                    if($user->isValid()) {
                        if($user->isAdmin() === false) {
                            $this->errors['status'] = "You have no rights to access this area.";
                        }
                        else {
                            /*echo '<br>Password:' . password_hash($password, PASSWORD_ARGON2I);
                            echo '<br>User:' . $user->password();*/
                            if (password_verify($password, $user->password())) {
                                $_SESSION['user'] = $user;
                                $_SESSION['logged_in'] = true;
                                //var_dump("CONNECTE AU BACKEND");
                                //header("Location: /OpenClassrooms/projet-5/blog/blog/backend/");
                            } else {
                                $this->errors['password'] = 'Incorrect password';
                            }
                        }
                    }
                    /**
                     * Else, redirects to the login page stating that the user does not exist.
                     */
                    else
                    {
                        $this->errors['unregistered'] = "This user does not exist.";
                    }
                }
                echo $this->twig->render('users/login.html.twig', array("errors" => $this->errors));
            }
            else {
                echo $this->twig->render('users/login.html.twig');
            }
        }
    }

    /**
     * Logs the current user out (destroy its session).
     */
    public function logoutAction() {
        // If the current User is logged in, it is logged out and redirected to the home page
        if(isset($_SESSION['user'])) {
            session_destroy();
        }
        // If the current User is not logged in, it is redirected to the home page
        session_start();
        header("Location: /OpenClassrooms/projet-5/blog/blog/");
    }

    /**
     * Permanently deletes a User from the database.
     * @param $id
     * @throws \Exception
     */
    public function deleteAction($id)
    {
        if(isset($_SESSION)) {
            $currentUser = unserialize($_SESSION['user']);
            if($currentUser->isAdmin()) {
                $user = new User($this->model->getSingle($id));

                if($user->isValid()) {
                    if (!$this->model->delete($id)) {
                        throw new \Exception("Erreur : suppression impossible.");
                    }
                }
            }
        }
    }



    public function isCurrentUserAdmin() {
        if(!isset($_SESSION['user'])) {
            throw new \Exception("No current user defined.");
        }
        else {
            $user = new User(unserialize($_SESSION['user']));
            return $user->isAdmin();
        }
    }

    public function isCurrentUserAuthorOfPost($postId) {
        $user = $this->getCurrentUser();
        $post = new Post($this->postModel->getSingle($postId));
        if($post->creatorId() === $user->id()) {
            return true;
        }
        return false;
    }

    public function getCurrentUser() {
        if(!isset($_SESSION['user'])) {
            throw new \Exception("No current user defined.");
        }
        else {
            return unserialize($_SESSION['user']);
        }
    }


    /**
     * @return mixed
     */
    public function categories()
    {
        return $this->categories;
    }

    /**
     * @param mixed $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return mixed
     */
    public function postModel()
    {
        return $this->postModel;
    }

    /**
     * @param mixed $postController
     */
    public function setPostModel($postModel)
    {
        $this->postModel = $postModel;
    }
}