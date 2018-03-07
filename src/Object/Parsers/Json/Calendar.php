<?php
namespace Qobo\Calendar\Object\Parsers\Json;

use Qobo\Utils\ModuleConfig\Parser\V2\Json\AbstractJsonParser;

class Calendar extends AbstractJsonParser
{
    protected $schema = 'file://' . ROOT . DS . 'config' . DS . 'Schema' . DS . 'calendar.json';
}

