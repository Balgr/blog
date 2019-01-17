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
 * Date: 01/01/2019
 * Time: 05:26
 */

class ContactController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function showContactPageAction() {
        echo $this->twig->render("frontend/contact.html.twig", array("currentUser" => UserController::currentUser()));
    }
}
