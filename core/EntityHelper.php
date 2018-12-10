<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 07/12/2018
 * Time: 11:54
 */

namespace Blog\core;


use ReflectionClass;
use ReflectionException;

/**
 * Class EntityHelper
 * @package Core
 * The EntityHelper contains some useful functions that may be required but are not part from the Entity structure.
 */
abstract class EntityHelper
{
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

     /**
     * @param Entity $entity
     * @return array
     * @throws \ReflectionException
     *
     * This function uses a ReflectionClass to retrieve the data from the entity in a format
     * compliant with the database.
     * It adds the attributes ton an array in the format : 'nameAttribute' => 'value'
     */
    public static function getAttributesOf(Entity $entity) {
        $attr = [];
        try {
            $reflection = new ReflectionClass($entity);

            /**
             * Retrieves all the properties from the entity object, sets them accessible, gets the required
             * data and sets them back to inaccessible state.
            */
            foreach($reflection->getProperties() as $prop) {
                $prop->setAccessible(true);
                $name = self::removeNamespaceFromString($prop->getName());

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