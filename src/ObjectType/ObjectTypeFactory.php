<?php
namespace Qobo\Calendar\ObjectType;

use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;
use InvalidArgumentException;
use RuntimeException;

class ObjectTypeFactory
{
    /**
     * Get Object Instance
     *
     * Pass entry calendar and specify source type to parse
     * data into Calendar Object.
     *
     * @param mixed $data with calendar information
     * @param string $name of the object instance you pass
     * @param string $type of parser to be used
     *
     * @return object $instance base on the name and data passed.
     */
    public static function getInstance($data = null, $name = null, $type = null)
    {
        $instance = null;
        if (empty($type) || empty($name)) {
            throw new InvalidArgumentException('Specify instance type');
        }

        $parser = self::getParser($name, $type);
        if (is_object($parser)) {
            $instance = $parser->parse($data);
        }

        return $instance;
    }

    /**
     * Get Calendar Parser object
     *
     * @param string $name of the parser to be used
     * @param string $type of the Parser needed
     * @param array $options with extra configs
     *
     * @return \Qobo\Calendar\ObjectType\Calendars\Parsers\ParserInterface $object
     */
    public static function getParser($name, $type = null, array $options = [])
    {
        $target = null;
        $name = Inflector::classify($name);
        $type = Inflector::classify($type);
        $path = empty($options['path']) ? dirname(__FILE__) . DS . 'Parsers' . DS . $type : $options['path'];

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
        $className = __NAMESPACE__ . '\\Parsers\\' . $type . '\\' . $target;

        if (!class_exists($className)) {
            throw new RuntimeException("No class [$className] found");
        }

        $object = new $className();

        return $object;

    }
}
