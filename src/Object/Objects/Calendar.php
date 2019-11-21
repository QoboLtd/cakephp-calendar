<?php
namespace Qobo\Calendar\Object\Objects;

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
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string $name of the calendar
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set Calendar Icon
     *
     * @param string $icon class for the calendar
     * @return void
     */
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return string $icon of the calendar
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Set color in hex or in string
     *
     * @param string $color of the calendar
     * @return void
     */
    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    /**
     * @return string $color of calendar
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Set Calendar Type
     *
     * @param string $calendarType for the calendar
     * @return void
     */
    public function setCalendarType(string $calendarType = 'default'): void
    {
        $this->calendarType = $calendarType;
    }

    /**
     * @return string $calendarType for calendar
     */
    public function getCalendarType(): string
    {
        return $this->calendarType;
    }

    /**
     * Set Calendar Active flag
     *
     * @param bool $active for the calendar
     * @return void
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return bool $active calendar flag
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * Set editable calendar flag
     *
     * @param bool $editable for the calendar
     * @return void
     */
    public function setEditable(bool $editable = false): void
    {
        $this->editable = $editable;
    }

    /**
     * @return bool $editable flag
     */
    public function getEditable(): bool
    {
        return $this->editable;
    }

    /**
     * Set Is Public flag for calendar instance
     *
     * @param bool $isPublic calendar flag
     * @return void
     */
    public function setIsPublic(bool $isPublic = false): void
    {
        $this->isPublic = $isPublic;
    }

    /**
     * @return bool isPublic flag
     */
    public function getIsPublic(): bool
    {
        return $this->isPublic;
    }
}
