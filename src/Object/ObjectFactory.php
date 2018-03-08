<?php
namespace Qobo\Calendar\Object;

use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;
use Qobo\Calendar\Object\Parsers\Json\Event as EventParser;
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

        if ('Event' == $target) {
            $parser = new EventParser();
            $path .= 'calendar_events';
            $filename = 'default.json';
        }
        $filename = $path . DS . Inflector::underscore($filename);
        if (file_exists($filename)) {
            $object = $parser->parse($filename);
        }

        return $object;
    }
}
