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
    $router->get('/users/edit/:id', 'BackendUser#editAction')->with('id', '[0-9]+');
    $router->post('/users/edit/:id', 'BackendUser#editAction')->with('id', '[0-9]+');

    // COMMENTS
    // -- Frontend
    $router->get('/posts/:id/comment/', 'Comment#addAction')->with('id', '[0-9]+');
    $router->post('/posts/:id/comment', 'Comment#addAction')->with('id', '[0-9]+');
    $router->get('/comments/edit/:id', 'Comment#editAction')->with('id', '[0-9]+');
    $router->post('/comments/edit/:id', 'Comment#editAction')->with('id', '[0-9]+');
    $router->get('/comments/show/:id', 'Comment#showAction')->with('id', '[0-9]+');
    $router->post('/comments/show/:id', 'Comment#showAction')->with('id', '[0-9]+');

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
    $router->get('/backend/comments/moderate', 'Comment#showCommentsToModerateAction');
    $router->get('/backend/comments/publish/:id', 'Comment#publishCommentAction')->with('id', '[0-9]+');
    $router->get('/backend/comments/trash/:id', 'Comment#trashCommentAction')->with('id', '[0-9]+');
    $router->get('/backend/comments/delete/:id', 'Comment#deleteCommentAction')->with('id', '[0-9]+');

    $router->run();
} catch(Exception $e) {
    die("Erreur ! " . $e->getMessage());
}


//$postController = new PostController();
//$postController->indexAction();
//$postController->showAction(21);


/*use Blog\core\Config;
use Blog\core\Database;
//use Blog\app\Entity\Post;
use Blog\app\Helper\UserEntityHelper;
//use Blog\app\Helper\CommentEntityHelper;


echo "Index !<br><br>";

//$postController = new PostController();
//$postController->indexAction();



/*$userModel = new UserModel($db);
$data = $userModel->getSingle(1);
$user = new User($data);
$userController = new UserController();
$us
û
/*
//var_dump($data);


$post = new Post(array(
                    'title' => 'Titre',
                    'subtitle' => 'Sous-titre',
                    'content' => 'Top, ça marche !',
                    'creationDate' => date('Y-m-d H:i:s'),
                    'lastEditDate' => null,
                    'lastEditReason' => null,
                    'featuredImage' => 'img.png',
                    'status' => 'Published',
                    'creatorId' => 1
));
//var_dump(PostEntityHelper::getAttributesOf($post));

*/

//$postModel = new PostModel($db);
//$postController = new PostController();
//$postController->indexAction();
//$postController->showAction(21);

//$commentController = new CommentController();
//$commentController->indexAction();
//$commentController->showAction(4);

//$userController = new UserController();
//$userController->indexAction();
//$userController->showAction(1);

/*



echo '<br><br><br><br>Attributs du post créé : ';
var_dump(PostEntityHelper::getAttributesOf($post));
$postModel = new PostModel($db);
$postModel->create(PostEntityHelper::getAttributesOf($post));


$comment = new Comment(array(
                        'content' => 'Super post ! Je kiffe !' . rand(0,1000),
                        //'creationDate' => date('Y-m-d H:i:s'),
                        'creationDate' => null,
                        'authorName' => 'Batman',
                        'status' => 'Published',
                        'postId' => 21
));
$commentModel = new CommentModel($db);

echo '<br><br><br><br>Attributs du commentaire créé : ';
var_dump(CommentEntityHelper::getAttributesOf($comment));
$commentModel->create(CommentEntityHelper::getAttributesOf($comment));


$updatedData = array(
    'content' => 'Edited comment : ' . rand(0,1000),
    'creationDate' => date('Y-m-d H:i:s'),
    'authorName' => 'Batman',
    'status' => 'Published',
    'postId' => 21
);

$comment->setId(3);
$comment->setContent("Edited comment : " . rand(0,500));
$comment->setCreationDate(date('Y-m-d H:i:s'));
//$commentModel->delete("comments", $comment->id());

var_dump($postModel->getAll());

*/
