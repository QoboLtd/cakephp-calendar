<?php
namespace Qobo\Calendar\Object\Parsers\Json;

use Qobo\Utils\ModuleConfig\Parser\V2\Json\AbstractJsonParser;

class Event extends AbstractJsonParser
{
    protected $schema = 'file://' . ROOT . DS . 'config' . DS . 'Schema' . DS . 'event.json';
}

