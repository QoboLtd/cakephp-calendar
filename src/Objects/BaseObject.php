<?php
namespace Qobo\Calendar\Objects;

use Cake\Utility\Inflector;
use Qobo\Calendar\Objects\CalendarObjectInterface;

class BaseObject implements CalendarObjectInterface
{
    public function setAttribute($name, $value = null)
    {
        $property = Inflector::variable($name);

        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

    public function getAttribute($name)
    {
        $property = Inflector::variable($name);

        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            throw new \Exception("Property {$property} doesn't exist in the object");
        }
    }

}

