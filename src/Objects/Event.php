<?php
namespace Qobo\Calendar\Objects;

use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Qobo\Calendar\Model\Entity\CalendarEvent;

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

    public function toEntity()
    {
        $item = [];
        $item = [
            'id' => $this->getAttribute('id'),
            'calendar_id' => $this->getAttribute('calendar_id'),
            'source' => $this->getAttribute('source'),
            'source_id' => $this->getAttribute('source_id'),
            'title' => $this->getAttribute('title'),
            'content' => $this->getAttribute('content'),
            'start_date' => $this->getAttribute('start_date'),
            'end_date' => $this->getAttribute('end_date'),
            'event_type' => $this->getAttribute('event_type'),
            'is_recurring' => $this->getAttribute('is_recurring'),
            'recurrence' => $this->getAttribute('recurrence'),
            'is_allday' => $this->getAttribute('is_allday'),
            'diff_status' => $this->getAttribute('diff_status'),
            'attendees' => $this->getAttribute('attendees'),
        ];

        $table = TableRegistry::get('Qobo/Calendar.CalendarEvents');

        $entity = new CalendarEvent();

        foreach ($item as $name => $val) {
            $entity->set($name, $val);
        }

        return $entity;
    }
}
