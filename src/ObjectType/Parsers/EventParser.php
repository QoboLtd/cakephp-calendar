<?php
namespace Qobo\Calendar\ObjectType\Parsers;

use Qobo\Utils\ModuleConfig\Parser\V2\Json\AbstractJsonParser;

class EventParser extends AbstractJsonParser
{
    protected $schema = 'file://' . ROOT . DS . 'config' . DS . 'Schema' . DS . 'event.json';
}

