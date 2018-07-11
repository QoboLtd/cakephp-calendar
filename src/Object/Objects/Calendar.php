<?php
namespace Qobo\Calendar\Object\Objects;

use Cake\Utility\Inflector;

class Calendar extends AbstractObject
{
    protected $entityProvider = '\Qobo\Calendar\Model\Entity\Calendar';

    protected $id;

    protected $name;

    protected $icon;

    protected $color;

    protected $calendarType;

    protected $source;

    protected $sourceId;

    protected $active;

    protected $editable;

    protected $isPublic;

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
     * Set Calendar Active flag
     *
     * @param bool $active for the calendar
     * @return void
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return bool $active calendar flag
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set editable calendar flag
     *
     * @param bool $editable for the calendar
     * @return void
     */
    public function setEditable($editable = false)
    {
        $this->editable = $editable;
    }

    /**
     * @return bool $editable flag
     */
    public function getEditable()
    {
        return $this->editable;
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
