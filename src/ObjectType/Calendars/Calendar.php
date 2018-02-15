<?php
namespace Qobo\Calendar\ObjectType\Calendars;

use Qobo\Calendar\Model\Entity\Calendar as CalendarEntity;

class Calendar
{
    protected $id;

    protected $name;

    protected $icon;

    protected $color;

    protected $calendarType;

    protected $source;

    protected $sourceId;

    protected $isActive;

    protected $isEditable;

    protected $isPublic;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setColor($color)
    {
        $this->color = $color;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setCalendarType($type)
    {
        $this->calendarType = $type;
    }

    public function getCalendarType()
    {
        return $this->calendarType;
    }

    public function setSource($source)
    {
        $this->source = $source;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;
    }

    public function setActive($isActive)
    {
        $this->isActive = $isActive;
    }

    public function getActive()
    {
        return $this->isActive;
    }

    public function setEditable($isEditable = false)
    {
        $this->isEditable = $isEditable;
    }

    public function getEditable()
    {
        return $this->isEditable;
    }

    public function setIsPublic($isPublic = false)
    {
        $this->isPublic = $isPublic;
    }

    public function getIsPublic()
    {
        return $this->isPublic;
    }

    public function toEntity()
    {

    }
}
