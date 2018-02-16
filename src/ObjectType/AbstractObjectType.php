<?php
namespace Qobo\Calendar\ObjectType;

abstract class AbstractObjectType
{
    /**
     * Convert Generic Object to corresponding Cake\ORM\Entity
     *
     * Entity is assembled based on Cake\ORM\Entity and
     * prepopulated with the data of a given object instance
     * via getters.
     *
     * @return \Cake\ORM\Entity $entity of the calendar
     */
    abstract public function toEntity();
}
