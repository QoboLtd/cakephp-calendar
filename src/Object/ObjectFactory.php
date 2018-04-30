<?php
namespace Qobo\Calendar\Object;

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use Qobo\Calendar\Object\Parsers\Json\Event;

class ObjectFactory
{
    /**
     * Get Parser Configuration
     *
     * The map object will simplify convertion of incoming EntityInterface
     * into appropriate event_type based on the found map.
     *
     * Map configuration can be located on the app's level,
     * or specified via `config/calendar.php` within `Calendar.Modules.<ModuleName>.path`.
     *
     * The base directory should contain one or more of the following subdirs:
     * - event_types/<default>.json - default module conversion map. Aka Contacts -> Event.
     * - calendars/<default>.json - convertion of Entity to Calendar.
     * - attendees/<default>.json - convertion of Entity to Attendee record.
     *
     * @param string $name of the table to check within subdir of config
     * @param string $target name of the object type (calendars,events,attendees,etc.)
     * @param array $options with defined parser type to use.
     *
     * @return object $result containing stdClass of configs.
     */
    public static function getParserConfig($name, $target, array $options = [])
    {
        $object = null;
        $type = !empty($options['type']) ? $options['type'] : 'Json';

        if ('Json' === $type) {
            $name = Inflector::camelize(Inflector::pluralize($name));
            $config = Configure::read('Calendar.Modules.' . $name);

            $path = !empty($config['path']) ? $config['path'] : CONFIG . 'Modules' . DS . $name . DS . 'config' . DS;
            $filename = !empty($options['event_type']) ? $options['event_type'] . '.json' : 'default.json';

            if ('Event' == $target) {
                $parser = new Event();
                $path .= 'calendar_events' . DS . $filename;
                $object = $parser->parse($path);
            }
        }

        return $object;
    }
}
