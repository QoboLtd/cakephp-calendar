<?php
namespace Qobo\Calendar\ObjectType\Calendars\Parsers;

use Qobo\Calendar\ObjectType\Calendars\Calendar;
use Cake\ORM\Entity;
use Cake\Utility\Inflector;
use \InvalidArgumentException;

class AppEntityParser implements ParserInterface
{
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
