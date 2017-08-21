<?php
namespace Qobo\Calendar\Objects;

use Cake\Datasource\EntityInterface;
use Cake\Utility\Inflector;

class Event extends BaseObject
{
    protected $id;

    protected $calendarId;

    protected $sourceId;

    protected $source;

    protected $title;

    protected $content;

    protected $startDate;

    protected $endDate;

    protected $eventType;

    protected $isRecurring;

    protected $recurrence;

    protected $isAllday;

    protected $diffStatus = null;

    protected $attendees = [];


    public function setId($id = null)
    {
        $this->id = $id;
    }

    public function setCalendarId($calendarId = null)
    {
        $this->calendarId = $calendarId;
    }

    public function setSourceId($sourceId = null)
    {
        $this->sourceId = $sourceId;
    }

    public function setSource($source = null)
    {
        $this->source = $source;
    }

    public function setTitle($title = null)
    {
        $this->title = $title;
    }

    public function setContent($content = null)
    {
        $this->content = $content;
    }

    public function setStartDate($startDate = null)
    {
        $this->startDate = $startDate;
    }

    public function setEndDate($endDate = null)
    {
        $this->endDate = $endDate;
    }

    public function setEventType($eventType = null)
    {
        $this->eventType = $eventType;
    }

    public function setIsRecurring($isRecurring = false)
    {
        $this->isRecurring = $isRecurring;
    }

    public function setRecurrence($recurrence = [])
    {
        $this->recurrence = $recurrence;
    }

    public function setIsAllday($isAllday = false)
    {
        $this->isAllday = $isAllday;
    }

    public function setAttendees(array $attendees = [])
    {
        $this->attendees = $attendees;
    }
}
