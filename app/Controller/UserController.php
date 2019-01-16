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
    private $currentUser;

    public function __construct()
    {
        parent::__construct();
        $this->errors = [];
        $this->model = new UserModel(Database::getInstance(Config::getConfigFromYAML(__DIR__ . "/../../config/database/db.yml")));
        $this->categories = array(
            "Member" => User::STATUS_MEMBER,
            "Admin" => User::STATUS_ADMIN
        );
        $this->postModel = new PostModel($this->model()->db());

        // Retrieves the current logged-in User data and stores them
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            $this->currentUser = unserialize($_SESSION['user']);
        } else {
            $this->currentUser = false;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateAndSanitizePostData();
        }

        if (!isset($_SESSION)) {
            session_start();
        }
    }

    /**
     * BACKEND
     */

    /**
     * Prints the list of users
     */
    public function showListUsersAction()
    {
        $users = $this->model->getAll();

        echo $this->twig->render("backend/users/index.html.twig", array("currentUser" => $this->currentUser, "errors" => $this->errors, "users" => $users, "current" => array("users", "list")));
    }

    /**
     * Creates a new User in the databases if the current user posts data.
     * Else, shows the form to add a new user
     */
    public function createUserAction()
    {
        self::whenCurrentUserAccessBackend();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($this->errors())) {
            $this->addUser($_POST);
            header('Location: /backend/users/');
        }

        echo $this->twig->render("backend/users/detail.html.twig", array("currentUser" => $this->currentUser, "errors" => $this->errors, "current" => array("users", "add")));
    }

    private function addUser($data)
    {
        $data['dateInscription'] = date('Y-m-d H:i');
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        return $this->model->create($data);
    }

    public function editUserAction($id)
    {
        self::whenCurrentUserAccessBackend();
        $user = new User($this->model()->getSingle($id));
        if ($user->isValid()) {
            $user->setPassword('');
            $user->setBiography(html_entity_decode($user->biography()));
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->editUser($_POST, $id);
                header('Location: /backend/users/');
            }
        } else {
            $this->errors['undefined'] = "L'utilisateur #$id n'existe pas";
            $this->showListUsersAction();
        }
        echo $this->twig->render("backend/users/detail.html.twig", array(
            "currentUser" => $this->currentUser,
            "user" => $user,
            "errors" => $this->errors,
            "current" => array("users", "list")));
    }

    private function editUser($data, $id)
    {
        if (is_null($data['password']) || empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        return $this->model->update($data);
    }

    public function deleteUserAction($id)
    {
        self::whenCurrentUserAccessBackend();
        if ($this->deleteUser($id)) {
            header('Location: /backend/users');
        }
    }

    private function deleteUser($id)
    {
        $user = new User($this->model->getSingle($id));
        if (!is_null($user)) {
            if (($this->model->delete($id))) {
                return true;
            }
        }
        return false;
    }

    public function profileAction()
    {
        if ($this->currentUser !== false) {
            $this->editProfileAction($this->currentUser->id());
        } else {
            throw new Exception("Erreur : vous n'êtes pas connecté.");
        }
    }

    /**
     * This function checks if the User is not already logged in, then if the User passes
     */
    public function registerAction()
    {
        if ($this->currentUser !== false) {
            header('Location: /');
        }

        // If the User is NOT passing data from the registration form, shows him the form.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo $this->twig->render('frontend/register.html.twig',
                array(
                    "user" => array("category" => $this->categories),
                    "errors" => $this->errors
                ));
        } else {
            if (empty($this->errors)) {
                $this->checkEmailAndUsernameInDatabase($_POST);
            } else {
                echo $this->twig->render('frontend/register.html.twig',
                    array(
                        "user" => array("category" => $this->categories),
                        "errors" => $this->errors
                    ));
                return;
            }

            // Adds the User data not set in the form
            $_POST['dateInscription'] = date('Y-m-d H:i');
            $_POST['category'] = User::STATUS_MEMBER; // By default, a User is only a Member.
            $_POST['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $user = new User($this->model->getSingle($this->model->create($_POST)));
            $user->setPassword('');
            $_SESSION['user'] = serialize($user);
            $_SESSION['logged_in'] = true;

            header('Location: /');
        }
        // If the data is not correct (does not pass the validation), shows the form with errors
    }

    /**
     * @param $id
     * Allows the edition of the User entity in the database.
     * If the form has not been sent yet (no or empty $_POST), the form is displayed with the User data in it.
     * If the form has been sent ($_POST), the data is updated in the database, and the user is redirected to the showUser.
     */
    public function editProfileAction($id = null)
    {
        $user = new User($this->model->getSingle($id));
        if (!isset($_POST) || empty($_POST)) {
            $user->setPassword('');
            echo $this->twig->render('frontend/profile.html.twig', array("currentUser" => $user));
        } else {
            if (isset($_POST["password"]) && !empty($_POST["password"])) {
                $_POST["password"] = password_hash($_POST['password'], PASSWORD_BCRYPT);
            }
            $userId = $this->model->update($_POST);

            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                $user = unserialize($_SESSION["user"]);
                if ($user->id() === $userId) {
                    $user = new User($this->model->getSingle($userId));
                    $_SESSION['user'] = serialize($user);
                }
            }
            // Redirects to the edited User's detail page.
            header('Location: /profile');
        }
    }


    public function loginAction()
    {
        // If the User is already logged in...
        if ($this->currentUser !== false) {
            header("Location: /");
        } else {
            // Else, if the User sends a POST request, it means that he already completed the login form.
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {// Checks if the username exists in database
                $user = new User($this->model->getByUsername($_POST['username']));

                // If the User exists in the database, checks if the provided username and password are correct
                if ($user->isValid()) {
                    if(!empty($_POST['username']) && !empty($_POST['password'])) {
                        if (password_verify($_POST['password'], $user->password())) {
                            $_SESSION['user'] = serialize($user);
                            $_SESSION['logged_in'] = true;
                            header("Location: /");
                        } else {
                            $this->errors['password'] = 'Mot de passe incorrect';
                        }
                    }
                } /**
                 * Else, redirects to the login page stating that the user does not exist.
                 */
                else {
                    $this->errors['unregistered'] = "Utilisateur inconnu.";
                }
            }
        }
        echo $this->twig->render('frontend/login.html.twig', array("errors" => $this->errors));
    }


    public function logoutAction()
    {
        // If the current User is logged in, it is logged out and redirected to the home page
        if (isset($_SESSION['user'])) {
            session_destroy();
        }
        // If the current User is not logged in, it is redirected to the home page
        session_start();
        header("Location: /");
    }

    public static function whenCurrentUserAccessBackend()
    {
        if (!self::isCurrentUserAdmin()) {
            header('Location: /forbidden');
        }

        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public static function isCurrentUserAdmin()
    {
        $user = self::currentUser();

        if ($user !== false && $user->isValid()) {
            return $user->isAdmin();
        }
        return false;
    }

    public static function currentUser()
    {
        if (!isset($_SESSION['user'])) {
            return false;
        }
        $user = unserialize($_SESSION['user']);
        return $user;
    }

    private function checkEmailAndUsernameInDatabase($array)
    {
        $emailUsed = $this->model->isEmailAlreadyRegistered(htmlspecialchars($array['email']));
        $usernameUsed = $this->model->isUsernameAlreadyRegistered(htmlspecialchars($array['username']));
        if ($emailUsed || $usernameUsed) {
            if (!$emailUsed) {
                $this->errors['email'] = 'E-mail déjà existant.';
            }
            if (!$usernameUsed) {
                $this->errors['username'] = 'Nom d\'utilisateur déjà existant.';
            }
            return true;
        }
        return false;
    }


    private function validateAndSanitizePostData()
    {
        if (empty($_POST['username']) || empty($_POST['password']) || (isset($_POST['email']) && empty($_POST['email']))) {
            $this->errors['empty'] = 'Veuillez remplir tous les champs';
        } else {
            // Username
            $_POST['username'] = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
            if (!preg_match('/^[a-zA-Z0-9]{3,15}/', $_POST['username'])) {
                $this->errors['username'] = 'Le nom d\'utilisateur doit contenir entre 3 et 15 caractères alphanumériques.';
            }
            // Email
            if(isset($_POST['email'])) {
                $_POST['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                if (isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
                    $this->errors['email'] = 'Veuillez entrer un email correct.';
                }  else if (!preg_match('/^.{,30}$/', $_POST['email'])) {
                    $this->errors['email'] = 'L\'email ne peut contenir plus de 30 caractères.';
                }
            }

            // Password
            $_POST['password'] = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
            if (!preg_match('/^[a-zA-Z0-9]{5,}/', $_POST['password'])) {
                $this->errors['password'] = 'Le mot de passe doit contenir au moins 5 caractère alphanumériques.';
            }

            // Bio
            if(isset($_POST['biography']) && !empty($_POST['biography'])) {
                $_POST['biography'] = filter_var($_POST['biography'], FILTER_SANITIZE_STRING);
                if (!preg_match('/^.{0,300}$/', $_POST['biography'])) {
                    $this->errors['biography'] = 'La biographie ne peut contenir plus de 300 caractères.';
                }
                $_POST['biography'] = htmlentities($_POST['biography']);
            }

            // Catégory
            $_POST['category'] = filter_var((int)$_POST['category'], FILTER_SANITIZE_NUMBER_INT);
            $_POST['category'] = intval($_POST['category']);
            if ($_POST['category'] !== User::STATUS_ADMIN && $_POST['category'] !== User::STATUS_MEMBER) {
                $this->errors['category'] = 'Catégorie invalide : ' . $_POST['category'];
            }
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
     * @param $postModel
     */
    public function setPostModel($postModel)
    {
        $this->postModel = $postModel;
    }
}
