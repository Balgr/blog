<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 07/12/2018
 * Time: 11:54
 */

namespace Core;


use ReflectionClass;
use ReflectionProperty;

/**
 * Class EntityHelper
 * @package Core
 * The EntityHelper contains some useful functions that may be required but are not part from the Entity structure.
 */
abstract class EntityHelper
{
    /**
     * @param $string
     * @return string
     * This static function transforms the passed string from the 'camelCaseFormat' to the 'lowercase_delimited_format".
     * It allows the Model to insert the data in the database in the correct format.
     */
    protected static function camelCaseToSnakeCase($string) {
        $string = preg_replace('/([A-Z])/', '_$0', $string);
        return strtolower($string);
    }

    /**
     * @param $string
     * @return bool|string
     *
     * This function removes the namespace from a class name.
     */
    protected static function removeNamespaceFromString($string) {
        $lastBackslashPos = strrpos($string, '\\');
        return substr($string, $lastBackslashPos);
    }

    // This method retrieves all the private attribute of an object through a ReflectionClass.
    // 1. We convert their names to the SQL table format (ex: authorName to author_name).
    // 2. We add them to an array['name_of_the_attribute] = value;
    /**
     * @param Entity $entity
     * @return array
     * @throws \ReflectionException
     *
     * This function uses a ReflectionClass to retrieve the data from the entity in a format
     * compliant with the database.
     * It :
     * 1. Converts the attributes names to the SQL table format (ex : authorName to author_name)
     * 2. Add them to an array : 'name_of_the_attribute' => 'value'.
     */
    public static function getAttributesOf(Entity $entity) {
        $attr = [];
        try {
            $reflection = new ReflectionClass($entity);

            /* Retrieves all the properties from the entity object, sets them accessible, gets the required
               data and sets them back to inaccessible state.
            */
            foreach($reflection->getProperties() as $prop) {
                $prop->setAccessible(true);
                $name = self::removeNamespaceFromString($prop->getName());
                $name = self::camelCaseToSnakeCase($name);

                //Si la variable est nulle, on écrit NULL
                if($prop->getValue($entity) === null) {
                    $attr[$name] = "NULL";
                } else {
                    $attr[$name] = $prop->getValue($entity);
                }

                $prop->setAccessible(false);
            }
        } catch (ReflectionException $e) {
            $e->getMessage();
        }

        return $attr;
    }

}