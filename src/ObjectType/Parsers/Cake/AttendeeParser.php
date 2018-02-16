<?php
namespace Qobo\Calendar\ObjectType\Parsers;

use Qobo\Calendar\ObjectType\Attendee;
use Qobo\Calendar\ObjectType\Parsers\ParserInterface;

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
    }
}
