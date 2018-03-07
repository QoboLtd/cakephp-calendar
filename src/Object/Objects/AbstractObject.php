<?php
namespace Qobo\Calendar\ObjectType;

use Cake\Utility\Inflector;

abstract class AbstractObject implements ObjectInterface
{
    /**
     * Set Calendar Id
     *
     * @param mixed $id of the calendar
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Entity Provider
     *
     * Specifies which Cake\ORM\Entity is responsible for an object
     *
     * @return string $entity provider containing full object path to it
     */
    protected function getEntityProvider()
    {
        return $this->entityProvider;
    }

    /**
     * Convert Generic Object to corresponding Cake\ORM\Entity
     *
     * Entity is assembled based on Cake\ORM\Entity and
     * prepopulated with the data of a given object instance
     * via getters.
     *
     * @return \Cake\ORM\Entity $entity of the calendar
     */
    public function toEntity()
    {
        $data = [];

        $entityProvider = $this->getEntityProvider();
        foreach ($this as $property => $value) {
            $method = Inflector::variable('get ' . $property);

            if (method_exists($this, $method) && is_callable([$this, $method])) {
                $field = Inflector::underscore($property);
                $data[$field] = $this->$method();
            }
        }

        $entity = new $entityProvider($data);

        foreach ($data as $property => $value) {
            //$entity->setDirty($property, false);
        }

        return $entity;
    }
}
