<?php
namespace Qobo\Calendar\ObjectType\Parsers\Cake;

use Cake\ORM\Entity;
use Cake\Utility\Inflector;
use Qobo\Calendar\ObjectType\Event;
use Qobo\Calendar\ObjectType\Parsers\ParserInterface;
use \InvalidArgumentException;

class AbstractCakeParser implements ParserInterface
{
    /**
     * Get Entity Provider
     *
     * Used to map parser with appropriate ObjectType instance.
     *
     * @return string $entityProvder of ObjectType class
     */
    protected function getEntityProvider()
    {
        return $this->entityProvider;
    }

    /**
     * Parse Cake Entity into generic ObjectType object object
     *
     * Based on the CakeEntity that is being thrown in
     * specific parser will be invoked to return corresponding ObjectType
     * instance.
     *
     * @param \Cake\ORM\Entity $data of Entity instance
     * @return object $object with aggregated data
     */
    public function parse($data)
    {
        $entityProvider = $this->getEntityProvider();

        $object = new $entityProvider();

        if (!$data instanceof Entity) {
            throw new InvalidArgumentException("[Cake] Parser expects \Cake\ORM\Entity object");
        }

        $properties = $data->visibleProperties();

        foreach ($properties as $property) {
            $setter = 'set' . Inflector::variable($property);

            if (method_exists($object, $setter)) {
                $object->$setter($data->$property);
            }
        }

        return $object;
    }
}
