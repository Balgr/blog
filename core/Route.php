<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 11/12/2018
 * Time: 14:15
 */

namespace Blog\core;


class Route {
    private $path;
    private $callable;
    private $matches = [];
    private $params = [];
    public $url;
    public function __construct($path, $callable) {
        $this->path = trim($path, '/');
        $this->callable = $callable;
    }
    /*
     * Gets whether requested URL matches
     */
    public function match($url) {
        //Remove initial and final "/"
        $url = trim($url, '/');
        $this->url = $url;
        //Replace param (eg. "id") by everything but "/" to extract param
        $path = preg_replace_callback('#:([\w]+)#', [$this, 'paramMatch'], $this->path);
        $regex = "#^".$path."$#i";
        if (!preg_match($regex, $url, $matches)) {
            return false;
        }
        //Remove first array item (path/param) to only keep second item (param).
        array_shift($matches);
        $this->matches = $matches;
        return true;
    }
    /*
     * Gets parameter matching
     */
    private function paramMatch($match) {
        if (isset($this->param[$match[1]])) {
            return '('.$this->params[$match[1]].')';
        }
        return '([^/]+)';
    }
    /*
     * Calls dynamically controller/callable
     */
    public function call() {
        if (is_string($this->callable)) {
            //It's a controller to init
            $params = explode('#', $this->callable);
            $controller = "Blog\\app\\Controller\\".$params[0]."Controller";
            $controller = new $controller();
            $action = $params[1];

            return call_user_func_array([$controller, $action], $this->matches);
        }
        return call_user_func_array($this->callable, $this->matches);
    }
    /*
     * Adds "with" param condition
     */
    public function with($param, $regex) {
        $this->params[$param] = $regex;
        return $this;
    }
    /*
     * Gets URL path
     */
    public function getUrl($params) {
        $path = $this->path;
        //e.g.: /path/:id
        // $k = id
        foreach ($params as $k => $v) {
            $path = str_replace(':'.$k, $v, $path);
        }
        return $path;
    }
}