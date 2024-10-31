<?php
namespace Slackr\Application\Helper;

if (!defined('ABSPATH'))
{
    exit;
}

class TypeHelper implements IHelper
{
    public static function getImplementingClasses($interfaceName) {
        return array_filter(
            get_declared_classes(),
            function($className) use ($interfaceName) {
                return in_array($interfaceName, class_implements($className));
            }
        );
    }

    public static function getCleanClassNameString($className)
    {
        $cleanedClassName = strtr($className, [
            '\\\\' => '\\'
        ]);
        
        return $cleanedClassName;
    }

    public static function getPropertyValue($obj, $property, $defaultReturn = null)
    {
        if(!is_object($obj))
        {
            if(is_array($obj))
            {
                $obj = (object)$obj;
            }
        }

        if(!is_object($obj) || !isset($obj->{$property}))
        {
            return $defaultReturn;
        }

        return $obj->{$property};
    }
}