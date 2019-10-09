<?php

namespace Hn\ShareASecret\Utility;

class ArrayModifier
{
    const ASC = 0;
    const DESC = 1;

    private static $currentProperty;
    private static $currentOrder;

    public static function compare($a, $b)
    {
        $aValue = self::getPropertyValue($a, self::$currentProperty);
        $bValue = self::getPropertyValue($b, self::$currentProperty);
        $compare = ($aValue == $bValue);
        if($compare) { return 0; }

        $compare = ($aValue < $bValue);
        if(self::$currentOrder == self::DESC){
            $compare = (!$compare);
        }
        if($compare){
            return -1;
        } else{
            return 1;
        }
    }

    public static function getMethodName(string $property)
    {
        // Replace first letter by its upper case
        $property = substr_replace($property, strtoupper(substr($property, 0, 1)),0, 1);
        return $getMethod = 'get' . $property;
    }

    public static function getPropertyValue($object, string $property)
    {
        $return = '';
        if(gettype($object) == 'object'){
            $return = $object->{self::getMethodName($property)}();
        }elseif (gettype($object) == 'array'){
            $return = $object[self::$currentProperty];
        }
        return $return;
    }

    public static function getByNonNullProperty(array $array, string $property) : array
    {
        $return = [];
        foreach ($array as $value){
            if(self::getPropertyValue($value, $property) !== null){
                $return[] = $value;
            }
        }
        return $return;
    }

    public static function sortByProperty(array $array, string $property, int $order = self::ASC)
    {
        self::$currentProperty = $property;
        self::$currentOrder = $order;
        usort($array, 'self::compare');
        return $array;
    }

    public static function groupByPropertyValue(array $array, string $property)
    {
        $return = [];
        foreach ($array as $element){
            if(!isset($return[self::getPropertyValue($element, $property)])){
                $return[self::getPropertyValue($element, $property)] = [];
            }
            $return[self::getPropertyValue($element, $property)][] = $element;
        }
        return $return;
    }

    public static function mergeArrayOfArrays($array)
    {
        $return = [];
        foreach($array as $arr){
            $return = array_merge($return, $arr);
        }
        return $return;
    }

    public static function sortByProperties(array $array, array $properties)
    {
        if(count($properties) == 0){
            return $array;
        }
        $property = key($properties);
        $order = array_shift($properties);
        $array = self::sortByProperty($array, $property, $order);
        $array = self::groupByPropertyValue($array, $property);
        foreach ($array as $key => $value){
            $array[$key] = self::sortByProperties($value, $properties);
        }
        $array = self::mergeArrayOfArrays($array);
        return $array;
    }
}