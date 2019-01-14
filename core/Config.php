<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 06/12/2018
 * Time: 12:34
 */

namespace Blog\core;

use Symfony\Component\Yaml\Yaml;


abstract class Config
{
    /**
     * @param $filename : string
     * @return mixed
     */
    public static function getConfigFromYAML($filename) {
        return Yaml::parseFile((string) $filename);
    }
}
