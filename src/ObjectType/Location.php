<?php
namespace Qobo\Calendar\ObjectType;

class Location extends AbstractObjectType
{
    protected $entityProvider = '\Qobo\Calendar\Model\Entity\EventLocation';

    protected $id;

    protected $source;

    protected $sourceId;
}
