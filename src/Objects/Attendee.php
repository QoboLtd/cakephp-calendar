<?php
namespace Qobo\Calendar\Objects;

use Cake\Datasource\EntityInterface;
use Cake\Utility\Inflector;

class Attendee extends BaseObject
{
    protected $id;

    protected $displayName;

    protected $contactDetails;

    protected $source;

    protected $sourceId;

    protected $responseStatus;

    protected $diffStatus = null;

    public function setId($id = null)
    {
        $this->id = $id;
    }

    public function setDisplayName($name = null)
    {
        $this->displayName = $name;
    }

    public function setContactDetails($details = null)
    {
        $this->contactDetails = $details;
    }

    public function setSource($source = null)
    {
        $this->source = $source;
    }

    public function setSourceId($sourceId = null)
    {
        $this->sourceId = $sourceId;
    }

    public function setReponseStatus($status = null)
    {
        $this->responseStatus = $status;
    }

    public function toEntity()
    {
        $item = [
            'id' => $this->getAttribute('id'),
            'display_name' => $this->getAttribute('display_name'),
            'contact_details' => $this->getAttribute('contact_details'),
            'source' => $this->getAttribute('source'),
            'source_id' => $this->getAttribute('source_id'),
            'response_status' => $this->getAttribute('response_status'),
        ];

        $table = TableRegistry::get('Qobo/Calendar.CalendarAttendees');

        $entity = new \Qobo\Calendar\Model\Entity\CalendarAttendee();

        foreach ($item as $name => $val) {
            $entity->set($name, $val);
        }

        return $entity;
    }
}
