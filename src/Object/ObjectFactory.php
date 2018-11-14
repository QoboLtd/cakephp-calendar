<?php
namespace Qobo\Calendar\Object;

use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;
use InvalidArgumentException;
use Qobo\Calendar\Object\Parsers\Json\Event;
use \RuntimeException;

class ObjectFactory
{
    /** @var string */
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
     * @return mixed[] $data containing the map for transpiling
     */
    public static function getConfig(?string $entityName = null, ?string $objectName = null, ?string $configName = null): array
    {
        $data = [];

        if (empty($objectName) || empty($entityName) || empty($configName)) {
            return $data;
        }

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

            if (!class_exists($className)) {
                throw new RuntimeException(
                    sprintf('Class %s does not exist', $className)
                );
            }

            $parser = new $className();
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
     * @return mixed[] $result containing the map for conversion
     */
    public static function getDataFromConfig(string $objectName, string $configName): array
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
     * @return mixed[] $configs containing the list of configs
     */
    public static function getConfigList(string $entityName, string $objectName): array
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
     * @param string|null $path to map directory config/Modules/Integrations/
     * @return string[] $configs containing json files
     */
    public static function getModuleFiles(?string $path = null): array
    {
        $configs = [];

        if (empty($path)) {
            throw new InvalidArgumentException((string)__('Specify [path] for the JSON configs'));
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
     * @param string[] $files from the given config directory
     * @param string $path of the basename
     *
     * @return mixed[] $configs with files converted to human-readable format
     */
    public static function getModuleConfigNames(string $entityName, array $files = [], string $path = ''): array
    {
        $configs = [];
        foreach ($files as $k => $file) {
            $label = str_replace($path, '', $file);
            $label = str_replace('.json', '', $label);

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
     * @return string|null $subdir for the path, aka 'calendar_events'
     */
    public static function getConfigTypeDir(?string $type = ''): ?string
    {
        $subdir = null;
        $type = Inflector::underscore(Inflector::pluralize($type));

        if (!in_array($type, ['events', 'attendees', 'calendars'])) {
            throw new InvalidArgumentException((string)__('Wrong Config Calendar Type'));
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
