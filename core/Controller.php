<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 08/12/2018
 * Time: 18:47
 */

namespace Blog\core;

/**
 * Class Controller
 * @package Core
 */

abstract class Controller
{
    protected $twig;
    protected $loader;
    protected $model;
    protected $entity;
    protected $listObj;
    protected $currentObj;
    protected $templatesPath;
    protected $limit;
    protected $errors;
    protected $uploadPath;
    protected $csrf;


    public function __construct()
    {
        $this->errors = array();

        $this->entity = str_replace("Controller", "", get_class($this));
        $this->entity = str_replace("Blog\app\\\\", "", $this->entity);

        $this->instantiateTwig();
        $this->limit = Config::getConfigFromYAML(__DIR__ . "/../config/database/entities.yml")[$this->entity]['indexLimit'];

        if (!isset($_SESSION)) {
            session_start();
        }
    }

    /**
     * @return mixed
     */
    public function listObj()
    {
        return $this->listObj;
    }

    /**
     * @param mixed $listObj
     */
    public function setListObj($listObj)
    {
        $this->listObj = $listObj;
    }

    /**
     * @return mixed
     */
    public function currentObj()
    {
        return $this->currentObj;
    }

    /**
     * @param mixed $currentObj
     */
    public function setCurrentObj($currentObj)
    {
        $this->currentObj = $currentObj;
    }

    /**
     * @return mixed
     */
    public function templatesPath()
    {
        return $this->templatesPath;
    }

    /**
     * @param mixed $templatesPath
     */
    public function setTemplatesPath($templatesPath)
    {
        $this->templatesPath = $templatesPath;
    }

    /**
     * @return mixed
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * @param mixed $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return mixed
     */
    public function uploadPath()
    {
        return $this->uploadPath;
    }

    /**
     * @param mixed $uploadPath
     */
    public function setUploadPath($uploadPath)
    {
        $this->uploadPath = $uploadPath;
    }

    /**
     * @return mixed
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * @return mixed
     */
    public function getCsrf()
    {
        return $this->csrf;
    }

    /**
     * @param mixed $csrf
     */
    public function setCsrf($csrf)
    {
        $this->csrf = $csrf;
    }

    /**
     * @param mixed $model
     */
    protected function setModel($model)
    {
        $this->model = $model;
    }

    private function instantiateTwig()
    {
        $this->setTemplatesPath(Config::getConfigFromYAML(dirname(__DIR__). '/config/twig.yml')['views_path']);
        $this->setTemplatesPath(dirname(__DIR__) . $this->templatesPath);
        
        $this->loader = new \Twig_Loader_Filesystem($this->templatesPath());
        $this->twig = new \Twig_Environment($this->loader, array('debug' => true));
    }

    protected function checkCSRF()
    {
        if (isset($_SESSION['csrf']) && isset($_POST['csrf']) && !empty($_SESSION['csrf']) && !empty($_POST['csrf'])) {
            if ($_SESSION['csrf'] == $_POST['csrf']) {
                unset($_POST['csrf']);
                return true;
            }
        }
        $this->errors[] = "Le jeton d'authentification ne correspond pas";
        return false;
    }

    protected function generateToken()
    {
        $this->csrf = bin2hex(random_bytes(32));
        $_SESSION['csrf'] = $this->csrf;
    }
}
