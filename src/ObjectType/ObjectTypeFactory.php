<?php
namespace Qobo\Calendar\ObjectType;

use Cake\Filesystem\Folder;
use InvalidArgumentException;
use RuntimeException;

class ObjectTypeFactory
{
    /**
     * Get Calendar Object Instance
     *
     * Pass entry calendar and specify source type to parse
     * data into Calendar Object.
     *
     * @param mixed $data with calendar information
     * @param string $type of parser to be used
     *
     * @return \Qobo\Calendar\ObjectType\Calendars\Calendar $instance of the calendar
     */
    public static function getCalendarInstance($data = null, $type = null)
    {
        $instance = null;
        if (empty($type)) {
            throw new InvalidArgumentException('Specify instance type');
        }

        $parser = self::getCalendarParser($type);
        if (is_object($parser)) {
            $instance = $parser->parse($data);
        }

        return $instance;
    }

    /**
     * Get Calendar Parser object
     *
     * @param string $name of the parser to be used
     * @param array $options with extra configs
     *
     * @return \Qobo\Calendar\ObjectType\Calendars\Parsers\ParserInterface $object
     */
    public static function getCalendarParser($name, array $options = [])
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
