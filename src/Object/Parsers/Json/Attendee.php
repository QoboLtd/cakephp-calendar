<?php
namespace Qobo\Calendar\Object\Parsers\Json;

use Qobo\Utils\ModuleConfig\Parser\V2\Json\AbstractJsonParser;

class Attendee extends AbstractJsonParser
{
    protected $schema = 'file://' . ROOT . DS . 'config' . DS . 'Schema' . DS . 'attendee.json';
}

