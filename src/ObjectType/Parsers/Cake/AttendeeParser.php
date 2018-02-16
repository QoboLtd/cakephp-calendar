<?php
namespace Qobo\Calendar\ObjectType\Parsers\Cake;

use Cake\ORM\Entity;
use Cake\Utility\Inflector;
use Qobo\Calendar\ObjectType\Attendee;
use Qobo\Calendar\ObjectType\Parsers\ParserInterface;
use \InvalidArgumentException;

class AttendeeParser implements ParserInterface
{
    /**
     * Parse Attendees Cake Entity into generic Attendee object
     *
     * @param \Cake\ORM\Entity $data of Attendee instance
     * @return \Qobo\Calendar\ObjectType\Attendee $object with aggregated data
     */
    public function parse($data)
    {
        $object = new Attendee();

        if (!$data instanceof Entity) {
            throw new InvalidArgumentException("[Cake] Parser expects \Cake\ORM\Entity object");
        }

        $properties = $data->visibleProperties();

        foreach ($properties as $property) {
            $setter = 'set' . Inflector::variable($property);

            if (method_exists($object, $setter)) {
                $object->$setter($data->$property);
            }
        }

        return $object;
    }
}
