<?php
namespace Qobo\Calendar\Object\Objects;

use Cake\I18n\Time;

class Event extends AbstractObject
{
    protected $entityProvider = '\Qobo\Calendar\Model\Entity\CalendarEvent';

    protected $id;

    protected $calendarId;

    protected $source;

    protected $sourceId;

    protected $title;

    protected $content;

    protected $startDate;

    protected $endDate;

    protected $eventType;

    protected $isRecurring = false;

    protected $recurrence;

    protected $isAllday;

    /**
     * Set Calendar Id key
     *
     * @param mixed $calendarId of related calendar
     * @return void
     */
    public function setCalendarId($calendarId): void
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
     * Set Event title
     *
     * @param string $title of event
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string $title of the event
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set content of event
     *
     * @param string $content of longtext for event
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string $content of calendar's event
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set Start Date of event
     *
     * @param \Cake\I18n\Time|string $startDate of event
     * @return void
     */
    public function setStartDate($startDate): void
    {
        if (is_string($startDate)) {
            $startDate = new Time($startDate);
        }

        $this->startDate = $startDate;
    }

    /**
     * @return \Cake\I18n\Time $startDate
     */
    public function getStartDate(): Time
    {
        return $this->startDate;
    }

    /**
     * Set End Date of event
     *
     * @param \Cake\I18n\Time|string $endDate for event
     * @return void
     */
    public function setEndDate($endDate): void
    {
        if (is_string($endDate)) {
            $endDate = new Time($endDate);
        }

        $this->endDate = $endDate;
    }

    /**
     * @return \Cake\I18n\Time $endDate of event
     */
    public function getEndDate(): Time
    {
        return $this->endDate;
    }

    /**
     * Set Event's Type
     *
     * @param string $eventType for the instance
     * @return void
     */
    public function setEventType(string $eventType): void
    {
        $this->eventType = $eventType;
    }

    /**
     * @return string|null $eventType of object
     */
    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    /**
     * Set recurrence flag for event
     *
     * @param bool $isRecurring flag for event
     * @return void
     */
    public function setIsRecurring(bool $isRecurring): void
    {
        $this->isRecurring = $isRecurring;
    }

    /**
     * @return bool $isRecurring flag
     */
    public function getIsRecurring(): bool
    {
        return $this->isRecurring;
    }

    /**
     * Set Recurrence string for event
     *
     * @param string $recurrence for instance
     * @return void
     */
    public function setRecurrence(string $recurrence): void
    {
        $this->recurrence = $recurrence;
    }

    /**
     * @return string $recurrence of the event
     */
    public function getRecurrence(): string
    {
        return $this->recurrence;
    }

    /**
     * Set Is All Day flag for the event
     *
     * @param bool $isAllday flag for the event
     * @return void
     */
    public function setIsAllday(bool $isAllday): void
    {
        $this->isAllday = $isAllday;
    }

    /**
     * @return bool $isAllday flag
     */
    public function getIsAllday(): bool
    {
        return $this->isAllday;
    }
}
