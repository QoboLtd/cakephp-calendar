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
}
