<?php
namespace Qobo\Calendar\Objects;

use Cake\ORM\TableRegistry;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Inflector;

class Calendar extends BaseObject
{
    protected $id;

    protected $name;

    protected $source;

    protected $sourceId;

    protected $calendarType;

    protected $eventTypes = [];

    protected $isPublic;

    protected $editable;

    protected $active;

    protected $color;

    protected $icon;

    protected $timezone;

    protected $diffStatus = null;

    protected $events = [];

    public function __construct($entity = [])
    {
        if (empty($entity)) {
            return;
        }

        if ($entity instanceof EntityInterface) {
            $this->fromEntity($entity);
        }

        if (is_array($entity)) {
            $this->fromArray($entity);
        }
    }

    public function setId($id = null)
    {
        $this->id = $id;
    }

    public function setName($name = null)
    {
        $this->name = $name;
    }

    public function setSource($source = null)
    {
        $this->source = $source;
    }

    public function setSourceId($sourceId = null)
    {
        $this->sourceId = $sourceId;
    }

    public function setCalendarType($calendarType = null)
    {
        $this->calendarType = $calendarType;
    }

    public function setEventTypes($eventTypes = [])
    {
        $this->eventTypes = $eventTypes;
    }

    public function setIsPublic($isPublic = false)
    {
        $this->isPublic = $isPublic;
    }

    public function setEditable($editable = false)
    {
        $this->editable = $editable;
    }

    public function setActive($active = false)
    {
        $this->active = $active;
    }

    public function setColor($color = null)
    {
        $this->color = $color;
    }

    public function setIcon($icon = null)
    {
        $this->icon = $icon;
    }

    public function setTimezone($timezone = null)
    {
        $this->timezone = $timezone;
    }

    public function setEvents(array $events = [])
    {
        $this->events = $events;
    }

    // @TODO: remove these methods as obsolete
    public function fromArray(array $entity = [])
    {
        if (empty($entity)) {
            throw new \Exception("Entity variable is empty");
        }

        foreach ($entity as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }
    }

    public function fromEntity(EntityInterface $entity)
    {
        foreach ($this as $attribute => $value) {
            $underscored = Inflector::underscore($attribute);

            if (isset($entity->{$underscored})) {
                $this->setAttribute($attribute, $entity->{$underscored});
            }
        }
    }

    public function toEntity()
    {
        $item = [];

        $item = [
            'id' => $this->getAttribute('id'),
            'name' => $this->getAttribute('name'),
            'source' => $this->getAttribute('source'),
            'source_id' => $this->getAttribute('source_id'),
            'calendar_type' => $this->getAttribute('calendar_type'),
            'event_types' => $this->getAttribute('event_types'),
            'is_public' => $this->getAttribute('is_public'),
            'editable' => $this->getAttribute('editable'),
            'active' => $this->getAttribute('active'),
            'color' => $this->getAttribute('color'),
            'icon' => $this->getAttribute('icon'),
            'timezone' => $this->getAttribute('timezone'),
            'diff_status' => $this->getAttribute('diff_status'),
            'events' => $this->getAttribute('events'),
        ];

        $table = TableRegistry::get('Qobo/Calendar.Calendars');

        $entity = new \Qobo\Calendar\Model\Entity\Calendar();

        foreach ($item as $name => $val) {
            $entity->set($name, $val);
        }

        return $entity;
    }
}
