<?php

namespace Hn\ShareASecret\Utility;

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class ArrayModifier
{
    const ASC = 0;
    const DESC = 1;

    private static $currentProperty;
    private static $currentOrder;

    public static function compare($a, $b)
    {
        $aValue = ObjectAccess::getProperty($a, self::$currentProperty);
        $bValue = ObjectAccess::getProperty($b, self::$currentProperty);
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

    public static function getByNonNullProperty(array $array, string $property) : array
    {
        $return = [];
        foreach ($array as $value){
            if(ObjectAccess::getProperty($value, $property) !== null){
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
            if(!isset($return[ObjectAccess::getProperty($element, $property)])){
                $return[ObjectAccess::getProperty($element, $property)] = [];
            }
            $return[ObjectAccess::getProperty($element, $property)][] = $element;
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