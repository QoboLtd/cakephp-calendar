<?php
namespace Qobo\Calendar\ObjectType;

use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;
use \ArrayObject;
use \InvalidArgumentException;
use \RuntimeException;

class ObjectTypeFactory
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

        $prefix = 'calendar_';
        $ext = '.json';

        $filename = $prefix . Inflector::underscore(Inflector::pluralize($target)) . $ext;

        $content = file_get_contents($path . $filename);

        if (empty($content)) {
            return $object;
        }

        $object = json_decode($content);
        $object = (object) $object;

        return $object;
    }
}
