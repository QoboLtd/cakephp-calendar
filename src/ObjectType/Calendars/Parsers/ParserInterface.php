<?php
namespace Qobo\Calendar\ObjectType\Calendars\Parsers;

interface ParserInterface
{
    /**
     * Parse Data into Generic Calendar Object
     *
     * @param mixed $data passed
     * @return \Qobo\Calendar\ObjectType\Calendars\Calendar $calendar instance
     */
    public function parse($data);
}
