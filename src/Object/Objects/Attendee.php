<?php
namespace Qobo\Calendar\Object\Objects;

class Attendee extends AbstractObject
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
    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    /**
     * @return string $displayName of attendee
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * set Contact details of attendees
     *
     * @param mixed $contactDetails in longtext format
     * @return void
     */
    public function setContactDetails($contactDetails): void
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
}
