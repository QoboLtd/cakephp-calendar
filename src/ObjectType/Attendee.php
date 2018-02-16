<?php
namespace Qobo\Calendar\ObjectType;

use Cake\Utility\Inflector;

class Attendee extends AbstractObjectType
{

    protected $entityProvider = '\Qobo\Calendar\Model\Entity\CalendarAttendee';

    protected $id;

    protected $displayName;

    protected $contactDetails;

    protected $source;

    protected $sourceId;

    /**
     * Set Display Name of Attendee
     *
     * @param string $displayName of the attendee
     * @return void
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * @return string $displayName of attendee
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * set Contact details of attendees
     *
     * @param mixed $contactDetails in longtext format
     * @return void
     */
    public function setContactDetails($contactDetails)
    {
        $this->contactDetails = $contactDetails;
    }

    /**
     * @return mixed $contactDetails of attendee instance
     */
    public function getContactDetails()
    {
        return $this->contactDetails;
    }

    /**
     * Set Source of Attendee
     *
     * @param string $source of attendee instance
     * @return void
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string $source of attendee
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set Source Id of attendee received
     *
     * @param string $sourceId of attendee
     * @return void
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;
    }

    /**
     * @return string $sourceId of attendee
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }
}
