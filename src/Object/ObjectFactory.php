<?php
namespace Qobo\Calendar\Object;

use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;
use InvalidArgumentException;
use Qobo\Calendar\Object\Parsers\Json\Event;

class ObjectFactory
{
    const TYPE_DELIMITER = '::';

    /**
     * Get JSON/Config for Entity conversion
     *
     * Fetch JSON/Config map of the entities to be transpiled
     * to ORM Entity
     *
     * @param string $entityName to be converted to, aka 'Calls'
     * @param string $objectName target conversion object, aka 'Event'
     * @param string $configName of the map, aka 'Json::Calls::Default'
     *
     * @return object $data containing the map for transpiling
     */
    public static function getConfig($entityName, $objectName, $configName = null)
    {
        $data = [];

        list($format, $remaining) = explode(self::TYPE_DELIMITER, $configName, 2);

        $format = Inflector::camelize($format);

        if ('Json' == $format) {
            $subdir = self::getConfigTypeDir($objectName);
            $path = CONFIG . 'Modules' . DS . $entityName . DS . 'config' . DS . $subdir;

            $files = self::getModuleFiles($path);
            $list = self::getModuleConfigNames($entityName, $files, $path);

            $parserName = Inflector::classify(Inflector::singularize($objectName));
            $namespace = 'Qobo\\Calendar\\Object\\Parsers\\Json\\';

            $className = $namespace . $parserName;

            if (class_exists($className)) {
                $parser = new $className();
            }

            $configFiles = array_flip($list);

            if (!$configFiles[$configName]) {
                throw new InvalidArgumentException("Couldn't find [$configName] config file for $entityName parser");
            }

            $data = $parser->parse($configFiles[$configName]);
        } elseif ('Config' == $format) {
            $data = self::getDataFromConfig($objectName, $configName);
        } else {
            throw new InvalidArgumentException("Given [$format] is not support");
        }

        return $data;
    }

    /**
     * Get Config map from config/calendar.php
     *
     * @param string $objectName for the target, aka 'Event'
     * @param string $configName of the map aka 'Json::Calls::Default'
     *
     * @return object $result containing the map for conversion
     */
    public static function getDataFromConfig($objectName, $configName)
    {
        $result = [];
        list($format, $calendar, $type) = explode(self::TYPE_DELIMITER, $configName, 3);

        $configs = Configure::read('Calendar.Types');

        foreach ($configs as $item) {
            if ($item['value'] !== Inflector::underscore($calendar)) {
                continue;
            }

            foreach ($item['calendar_events'] as $eventName => $value) {
                if ($eventName == Inflector::underscore($type)) {
                    $result = $value;
                }
            }
        }

        if (!empty($result)) {
            $result = json_decode(json_encode($result['properties']));
        }

        return $result;
    }

    /**
     * Fetch the list of configs based on the config/Module
     *
     * @param string $entityName the name of the module, aka 'Calls'
     * @param string $objectName the name of the config, aka 'Json::Calls::Default'
     *
     * @return array $configs containing the list of configs
     */
    public static function getConfigList($entityName, $objectName)
    {
        $configs = [];

        $subdir = self::getConfigTypeDir($objectName);
        $path = CONFIG . 'Modules' . DS . $entityName . DS . 'config' . DS . $subdir;

        $files = self::getModuleFiles($path);
        $list = self::getModuleConfigNames($entityName, $files, $path);

        foreach ($list as $file => $caption) {
            $configs[$caption] = $caption;
        }

        return $configs;
    }

    /***
     * Get JSON config files to map entities
     *
     * @param string $path to map directory config/Modules/Integrations/
     *
     * @return array $configs containing json files
     */
    public static function getModuleFiles($path = null)
    {
        $configs = [];

        if (empty($path)) {
            throw new InvalidArgumentException(__('Specify [path] for the JSON configs'));
        }

        $folder = new Folder($path);
        $files = $folder->findRecursive('.*\.json');

        if (!empty($files)) {
            $configs = $files;
        }

        return $configs;
    }

    /**
     * Trim down the list of configs based on the file paths
     *
     * @param string $entityName of the object, aka 'Calls'
     * @param array $files from the given config directory
     * @param string $path of the basename
     *
     * @return array $configs with files converted to human-readable format
     */
    public static function getModuleConfigNames($entityName, array $files = [], $path = null)
    {
        $configs = [];
        foreach ($files as $k => $file) {
            $label = str_replace($path, null, $file);
            $label = str_replace('.json', null, $label);

            $parts = array_filter(explode('/', $label));
            $parts = array_values($parts);

            foreach ($parts as $key => $part) {
                $parts[$key] = Inflector::camelize($part);
            }

            array_unshift($parts, 'Json', $entityName);

            $name = implode(self::TYPE_DELIMITER, $parts);
            $configs[$file] = $name;
        }

        return $configs;
    }

    /**
     * Get Subdirectory of the files based on the $objectName
     *
     * @param string $type of the object maps, aka Event|Calendar|Attendee
     *
     * @return string $subdir for the path, aka 'calendar_events'
     */
    public static function getConfigTypeDir($type = null)
    {
        $subdir = null;
        $type = Inflector::underscore(Inflector::pluralize($type));

        if (!in_array($type, ['events', 'attendees', 'calendars'])) {
            throw new InvalidArgumentException(__('Wrong Config Calendar Type'));
        }

        if ('calendars' == $type) {
            $subdir = 'calendar';
        }

        if ('events' == $type) {
            $subdir = 'calendar_' . $type;
        }

        if ('attendees' == $type) {
            $subdir = 'calendar_' . $type;
        }

        return $subdir;
    }
}
