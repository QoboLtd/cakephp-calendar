<?php
namespace Qobo\Calendar\ObjectType;

use Cake\Filesystem\Folder;
use InvalidArgumentException;
use RuntimeException;

class ObjectTypeFactory
{
    public static function getCalendarInstance($data = null, $type = null)
    {
        $instance = null;
        if (empty($type)) {
            throw new InvalidArgumentException('Specify instance type');
        }

        $parser = self::getParser($type);
        if (is_object($parser)) {
            $instance = $parser->parse($data);
        }

        return $instance;
    }

    public static function getParser($name, array $options = [])
    {
        $target = null;
        $path = empty($options['path']) ? dirname(__FILE__) . DS . 'Calendars' . DS . 'Parsers' : $options['path'];

        $dir = new Folder($path);
        $fileName = $name . 'Parser.php';

        foreach ($dir->find('.*Parser\.php$') as $file) {
            if ($file == $fileName) {
                $target = $file;
            }
        }

        if (empty($target)) {
            throw new RuntimeException("No parser found for type [$name]");
        }

        // chomp extension
        $target = substr($target, 0, -4);
        $className = __NAMESPACE__ . '\\Calendars\\Parsers\\' . $target;

        if (!class_exists($className)) {
            throw new RuntimeException("No class [$className] found");
        }

        $object = new $className();

        return $object;
    }
}
