<?php
namespace Qobo\Calendar\ObjectType;

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
     * Set Calendar name
     *
     * @param string $name for the calendar
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string $name of the calendar
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set Calendar Icon
     *
     * @param string $icon class for the calendar
     * @return void
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @return string $icon of the calendar
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set color in hex or in string
     *
     * @param string $color of the calendar
     * @return void
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string $color of calendar
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set Calendar Type
     *
     * @param string $calendarType for the calendar
     * @return void
     */
    public function setCalendarType($calendarType = 'default')
    {
        $this->calendarType = $calendarType;
    }

    /**
     * @return string $calendarType for calendar
     */
    public function getCalendarType()
    {
        return $this->calendarType;
    }

    /**
     * Set Calendar Source
     *
     * @param string $source from where calendar derives
     * @return void
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string $source of the calendar
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set Calendar Source ID of the origin source
     *
     * @param string $sourceId of calendar source
     * @return void
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;
    }

    /**
     * Set Calendar Active flag
     *
     * @param bool $isActive for the calendar
     * @return void
     */
    public function setActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return bool $isActive calendar flag
     */
    public function getActive()
    {
        return $this->isActive;
    }

    /**
     * Set editable calendar flag
     *
     * @param bool $isEditable for the calendar
     * @return void
     */
    public function setEditable($isEditable = false)
    {
        $this->isEditable = $isEditable;
    }

    /**
     * @return bool $isEditable flag
     */
    public function getEditable()
    {
        return $this->isEditable;
    }

    /**
     * Set Is Public flag for calendar instance
     *
     * @param bool $isPublic calendar flag
     * @return void
     */
    public function setIsPublic($isPublic = false)
    {
        $this->isPublic = $isPublic;
    }

    /**
     * @return bool isPublic flag
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }
}
