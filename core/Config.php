<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 06/12/2018
 * Time: 12:34
 */

namespace Core;

use Symfony\Component\Yaml\Yaml;


abstract class Config
{
    // For testing purposes. The URL must be change to a __DIR__ path.
    const PATH_CONFIG = "http://localhost/OpenClassrooms/projet-5/blog";

    /**
     * @param $filename : string
     * @return mixed
     */
    public static function getConfigFromYAML($filename) {
        return Yaml::parseFile((string) $filename);
    }

    // TODO : getConfigFromXML($filename);
}