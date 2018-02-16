<?php
namespace Qobo\Calendar\ObjectType\Parsers\Cake;

use Cake\ORM\Entity;
use Cake\Utility\Inflector;
use Qobo\Calendar\ObjectType\Calendar;
use Qobo\Calendar\ObjectType\Parsers\ParserInterface;
use \InvalidArgumentException;

class CalendarParser implements ParserInterface
{
    /**
     * Parse Data received as Cake\ORM\Entity object
     *
     * Converts to Calendar generic object type
     *
     * @param object $data received for parser
     * @return \Qobo\Calendar\ObjectType\Calendars\Calendar $calendar being set.
     */
    public function parse($data)
    {
        $calendar = new Calendar();

        if (!$data instanceof Entity) {
            throw new InvalidArgumentException("AppEntity Parser expects \Cake\ORM\Entity object");
        }

        $properties = $data->visibleProperties();

        foreach ($properties as $property) {
            $setter = 'set' . Inflector::variable($property);

            if (method_exists($calendar, $setter)) {
                $calendar->$setter($data->$property);
            }
        }

        return $calendar;
    }
}
