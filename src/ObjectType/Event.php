<?php
namespace Qobo\Calendar\ObjectType;

use Qobo\Calendar\Model\Table\Event as EventEntity;

class Event extends AbstractObjectType
{
    protected $id;

    protected $calendarId;

    protected $source;

    protected $sourceId;

    protected $title;

    protected $content;

    protected $startDate;

    protected $endDate;

    protected $eventType;

    protected $isRecurring;

    protected $recurrence;

    protected $isAllday;

    /**
     * Set Events Id
     *
     * @param mixed $id of the event
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed $id of event
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set Calendar Id key
     *
     * @param mixed $calendarId of related calendar
     * @return void
     */
    public function setCalendarId($calendarId)
    {
        $this->calendarId = $calendarId;
    }

    /**
     * @return mixed $calendarId for given event
     */
    public function getCalendarId()
    {
        return $this->calendarId;
    }

    /**
     * Set Source of the calendar
     *
     * @param string $source of calendar event
     * @return void
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed $source of the calendar event
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set Event title
     *
     * @param string $title of event
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string $title of the event
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content of event
     *
     * @param string $content of longtext for event
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string $content of calendar's event
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set Start Date of event
     *
     * @param string $startDate of event
     * @return void
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return string $startDate
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set End Date of event
     *
     * @param string $endDate for event
     * @return void
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return string $endDate of event
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set Event's Type
     *
     * @param string $eventType for the instance
     * @return void
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * @return string $eventType of object
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * Set recurrence flag for event
     *
     * @param bool $isRecurring flag for event
     * @return void
     */
    public function setIsRecurring($isRecurring)
    {
        $this->isRecurring = $isRecurring;
    }

    /**
     * @return bool $isRecurring flag
     */
    public function getIsRecurring()
    {
        return $this->isRecurring;
    }

    /**
     * Set Recurrence string for event
     *
     * @param string $recurrence for instance
     * @return void
     */
    public function setRecurrence($recurrence)
    {
        $this->recurrence = $recurrence;
    }

    /**
     * @return string $recurrence of the event
     */
    public function getRecurrence()
    {
        return $this->recurrence;
    }

    /**
     * Set Is All Day flag for the event
     *
     * @param bool $isAllday flag for the event
     * @return void
     */
    public function setIsAllday($isAllday)
    {
        $this->isAllday = $isAllday;
    }

    /**
     * @return bool $isAllday flag
     */
    public function getIsAllday()
    {
        return $this->isAllday;
    }
}
