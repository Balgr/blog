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

class SitemapController extends Controller
{
    const PATH = 'https://farem.tech/';
    private $urls = array();
    private $postController;

    public function generateSitemapAction() {
        $this->postController = new PostController();

        $posts = $this->postController->generateSitemap();

        foreach($posts as $post) {
            $urls[] = array(
                "loc" => 'posts/' . $post['id'],
                "lastMod" => is_null($post['lastEditDate']) ? $post['lastEditDate'] : $post['creationDate'],
                "changeFreq" => 'weekly'
            );
        }

        header('Content-Type: application/xml; charset=utf-8');
        echo $this->twig->render('sitemap.xml.twig', array("urls" => $urls));
    }
}
