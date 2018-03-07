<?php
namespace Qobo\Calendar\Object;

use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;
use Qobo\Calendar\Object\Parsers\Json\EventParser;
use \ArrayObject;
use \InvalidArgumentException;
use \RuntimeException;

class ObjectFactory
{
    /**
     * Get ObjectType
     * The map is based on saved in config/Modules/<TableName>/config/calendar_events.json
     *
     * @param string $name of the table to check within subdir of config
     * @param string $target name of the object type (calendars,events,attendees,etc.)
     *
     * @return object $result containing stdClass of configs.
     */
    public static function getParserConfig($name, $target)
    {
        $object = null;
        $name = Inflector::camelize(Inflector::pluralize($name));
        $path = CONFIG . 'Modules' . DS . $name . DS . 'config' . DS;

        $config = Configure::read('Calendar.Modules.' . $name);

        if (!empty($config['path'])) {
            $path = $config['path'];
        }

        $ext = '.json';

        $filename = Inflector::underscore(Inflector::pluralize($target)) . $ext;
        /*
        $content = file_get_contents($path . $filename);

        if (empty($content)) {
            return $object;
        }

        $object = json_decode($content);
        $object = (object)$object;
        */
        return $object;
    }

    public static function foo()
    {
        $foo = new EventParser();
        $path = TESTS . 'config' . DS . 'Modules' . DS . 'Leads' . DS . 'config' . DS . 'calendar_events.json';
        pr($path);
        pr($foo);

        $parsed = $foo->parse($path);

        dd($parsed);
    }
}
