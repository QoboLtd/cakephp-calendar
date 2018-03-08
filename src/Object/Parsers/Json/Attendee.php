<?php
namespace Qobo\Calendar\Object\Parsers\Json;

use Qobo\Utils\ModuleConfig\Parser\V2\Json\AbstractJsonParser;

class Attendee extends AbstractJsonParser
{
    protected $schema;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->schema = 'file://' . dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'config' . DS . 'Schema' . DS . 'attendee.json';
    }
}
