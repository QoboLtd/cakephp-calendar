<?php
namespace Qobo\Calendar\Model\Table\Traits;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Qobo\Calendar\Object\Objects\Event as EventObject;
use \ArrayObject;

trait ObjectTrait
{

    protected $defaultCalendar;

    /**
     * Get Object Type instance
     *
     * Return prepopulated Object instance for being later saved in the db.
     *
     * @param \Cake\Datasource\EntityInterface $entity to be converted
     * @param \stdClass $map containing ORM\Entity translation to Object instance
     * @param \ArrayObject $options with passed from the app
     *
     * @return \Qobo\Calendar\Object\ObjectInterface $object
     */
    public function getObjectInstance(EntityInterface $entity, $map, ArrayObject $options)
    {
        $object = null;

        $object = new EventObject();
        foreach ($map as $field => $config) {
            $method = Inflector::variable('set ' . Inflector::variable($field));

            if ('field' == $config->type) {
                $object->$method($entity->{$config->value});
            }

            if ('value' == $config->type) {
                $object->$method($config->value);
            }

            if ('callback' == $config->type && method_exists($this, $config->value)) {
                $calleeResult = $this->{$config->value}($entity, $options, $map);

                $object->$method($calleeResult);
            }
        }

        return $object;
    }

    /**
     * Get Calendar ID.
     *
     * @param \Cake\Datasource\EntityInterface $entity of the received record
     * @param \ArrayObject $options passed from the event
     * @param array $map of the object
     *
     * @return string|null $calendarId of the record
     */
    public function getCalendarId(EntityInterface $entity, ArrayObject $options, $map = null)
    {
        $calendarId = null;

        if (empty($options['calendar'])) {
            return $calendarId;
        }

        $calendarId = $options['calendar']->id;

        return $calendarId;
    }

    /**
     * Get Calendar Event end_date
     *
     * @param \Cake\Datasource\EntityInterface $entity of the event
     * @param \ArrayObject $options based on the configs
     * @param array $map of the config
     *
     * @return \Cake\I18n\Time $data with end_date value
     */
    public function getCalendarEventEndDate(EntityInterface $entity, ArrayObject $options, $map = null)
    {
        $source = $map->end_date->options->source;
        $data = Time::parse($entity->get($source));

        $data->modify('+ 1 hour');

        return $data;
    }

    /**
     * Get Calendar Event Title
     *
     * @param \Cake\Datasource\EntityInterface $entity of the event
     * @param \ArrayObject $options of the configs
     * @param array $map of the config fields
     *
     * @return string $data with title content
     */
    public function getCalendarEventTitle(EntityInterface $entity, ArrayObject $options, $map = null)
    {
        $table = TableRegistry::getTableLocator()->get($entity->source());

        $displayField = $entity->get($table->displayField());

        $data = sprintf("%s - %s", Inflector::humanize($entity->source()), $displayField);

        return $data;
    }

    /**
     * Get Calendar Event Content
     *
     * Prepopulate content of the calendar event with backlink to source
     *
     * @param \Cake\Datasource\EntityInterface $entity of the origin orm
     * @param \ArrayObject $options of the configs
     * @param array $map of the config conversion
     *
     * @return string $data containing calendar event text
     */
    public function getCalendarEventContent(EntityInterface $entity, ArrayObject $options, $map = null)
    {
        $source = $map->content->options->source;
        $data = $entity->get($source);

        if (!empty($options['viewEntity']))  {
            $url = $options['viewEntity']->Html->link(__('Source'), ['action' => 'view', $entity->get('id')]);

            $data .= "<br/><p>Reference: $url </p>";
        }

        return $data;
    }
}
