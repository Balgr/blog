<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 12/01/2019
 * Time: 20:17
 */

namespace Blog\app\Controller;

use Blog\core\Controller;

class ErrorController extends Controller
{
    private $degresChiffresTwig;

    public function __construct()
    {
        parent::__construct();
        $this->degresChiffresTwig = [];
        for($i = 0; $i < 3; $i++) {
            $this->degresChiffresTwig[] = rand(-80, 80);
        }
    }

    public function show404Action() {
        echo $this->twig->render("404.html.twig", array('degres' => $this->degresChiffresTwig));
    }

    public function show403Action() {
        echo $this->twig->render("403.html.twig", array('degres' => $this->degresChiffresTwig));
    }

    /**
     * @return mixed
     */
    public function getDegresChiffresTwig()
    {
        return $this->degresChiffresTwig;
    }

    /**
     * @param mixed $degresChiffresTwig
     */
    public function setDegresChiffresTwig($degresChiffresTwig)
    {
        $this->degresChiffresTwig = $degresChiffresTwig;
    }
}
