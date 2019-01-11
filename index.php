<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/Autoloader.php';


// Calls the autoloader
use Blog\app\Controller\PostController;
use Blog\core\Autoloader;
Autoloader::register();

use Blog\app\Controller\UserController;
use Blog\app\Model\UserModel;
use Blog\core\Router;

/*if (isset($_GET['action']) && isset($_GET['module'])) {
    if ($_GET['module'] == 'posts') {
        $postController = new PostController();
        if ($_GET['action'] == 'show') {
            $postController->showAction($_GET['id']);
        } else if ($_GET['action'] == 'add') {
            $postController->addAction();
        } else if ($_GET['action'] == 'index') {
            $postController->indexAction();
        }
    }
}*/

session_start();

try {
    $router = new Router($_GET['url']);

    // POSTS
    // -- Frontend
    $router->get('/posts/', 'Post#indexAction');
    $router->post('/posts/', 'Post#indexAction');
    $router->get('/posts/:id', 'Post#showAction')->with('id', '[0-9]+');
    $router->post('/posts/:id', 'Post#showAction')->with('id', '[0-9]+');

    // -- Frontend    // USERS
    $router->get('/register', 'User#registerAction');
    $router->post('/register', 'User#registerAction');
    $router->get('/login', 'User#loginAction');
    $router->post('/login', 'User#loginAction');
    $router->get('/logout', 'User#logoutAction');
    $router->post('/logout', 'User#logoutAction');
    $router->get('/profile', 'User#profileAction');
    $router->post('/profile', 'User#profileAction');

    // COMMENTS
    // -- Frontend
    $router->get('/posts/:id/comment/', 'Comment#addAction')->with('id', '[0-9]+');
    $router->post('/posts/:id/comment', 'Comment#addAction')->with('id', '[0-9]+');

    // CONTACT
    $router->get('/contact', 'Contact#showContactPageAction');
    $router->post('/contact', 'Contact#showContactPageAction');

    // HOME
    $router->get('/', 'Home#showHomePageAction');
    $router->post('/', 'Home#showHomePageAction');

    // BACKEND
    $router->get('/backend', 'Backend#showHomeAction');
    $router->post('/backend', 'Backend#showHomeAction');

    $router->get('/backend/posts', 'Post#showListPostsAction');
    $router->get('/backend/posts/trash', 'Post#showPostsTrashedAction');
    $router->post('/backend/posts/trash', 'Post#showPostsTrashedAction');
    $router->get('/backend/posts/add', 'Post#createPostAction');
    $router->post('/backend/posts/add', 'Post#createPostAction');
    $router->get('/backend/posts/edit/:id', 'Post#editPostAction');
    $router->post('/backend/posts/edit/:id', 'Post#editPostAction');
    $router->get('/backend/posts/trash/:id', 'Post#trashPostAction');
    $router->get('/backend/posts/delete/:id', 'Post#deletePostAction');
    $router->post('/backend/posts/delete/:id', 'Post#deletePostAction');
    $router->get('/backend/posts/publish/:id', 'Post#publishPostAction');

    $router->get('/backend/users', 'User#showListUsersAction');
    $router->get('/backend/users/add', 'User#createUserAction');
    $router->post('/backend/users/add', 'User#createUserAction');
    $router->get('/backend/users/edit/:id', 'User#editUserAction')->with('id', '[0-9]+');
    $router->post('/backend/users/edit/:id', 'User#editUserAction')->with('id', '[0-9]+');
    $router->get('/backend/users/delete/:id', 'User#deleteUserAction')->with('id', '[0-9]+');
    $router->post('/backend/users/delete/:id', 'User#deleteUserAction')->with('id', '[0-9]+');

    $router->get('/backend/comments', 'Comment#showListCommentsAction');
    $router->get('/backend/comments/trash', 'Comment#showCommentsTrashedAction');
    $router->get('/backend/comments/moderate', 'Comment#showCommentsToModerateAction');
    $router->get('/backend/comments/publish/:id', 'Comment#publishCommentAction')->with('id', '[0-9]+');
    $router->get('/backend/comments/trash/:id', 'Comment#trashCommentAction')->with('id', '[0-9]+');
    $router->get('/backend/comments/delete/:id', 'Comment#deleteCommentAction')->with('id', '[0-9]+');

    $router->run();
} catch(Exception $e) {
    die("Erreur ! " . $e->getMessage());
}
