<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 17/01/2019
 * Time: 18:10
 */

namespace Blog\app\Controller;

use Blog\app\Entity\Post;
use Blog\app\Controller\PostController;
use Blog\core\Controller;
use Blog\core\Database;
use DateTime;

class SitemapController extends Controller
{
    const PATH = 'https://farem.tech/';
    private $postController;

    public function generateSitemapAction() {
        $this->postController = new PostController();

        $posts = $this->postController->generateSitemap();

        foreach($posts as $post) {
            $urls[] = array(
                "loc" => 'https://farem.tech/posts/' . $post['id'],
                "lastEdit" => is_null($post['lastEditDate'])
                    ? DateTime::createFromFormat('m-d-Y', $post['creationDate'])
                    : DateTime::createFromFormat('m-d-Y', $post['lastEditDate']),
                "changeFreq" => 'weekly'
            );
        }
        //var_dump($urls);
        header('Content-Type: application/xml; charset=utf-8');
        echo $this->twig->render('sitemap.xml.twig', array("urls" => $urls));
    }
}
