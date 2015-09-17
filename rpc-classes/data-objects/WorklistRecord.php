<?php

class WorklistRecord extends GenericDICOMDataObject
{
  const UID_TAG = "0020,000d";
  const DCM_EXTENSION = 'wl';

  private $source;

  public function __construct( $source = null )
  {
    $this->source = $source;
    parent::__construct();
  }

  public function update_dump()
  {
    if (is_string($this->source) == 'String' )
    {
      $this->read_dump_from_path($this->source);
    }
    elseif (strstr(get_class($this->source), 'Exam'))
    {
      $this->dump = $this->source->dump;
    } else {
      throw new Exception('Worklist record must have a coherent source');
    }
  }

  public function is_older_than( $days ) {
    $cutoff = new DateTime();
    $days_interval = new DateInterval( 'P'. $days .'D');
    $cutoff = $cutoff->sub( $days_interval );

    return ( $this->getDate("0040,0002") < $cutoff );
  }
}