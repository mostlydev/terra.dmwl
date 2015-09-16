<?php

class WorklistRecord extends GenericDICOMDataObject
{
  const UID_TAG = "0020,000d";

  private $source;

  public function __construct( $source = null )
  {
    if (is_string($source) == 'String' )
    {
      $this->fromPath($source);
    }
    elseif (strstr(get_class($source), 'Exam'))
    {
      $this->source = $source;
      $this->path = DMWL_DCM_PATH . '/' . DMWL_AE_TITLE . '/' . $source->uid . '.dump';
    } else {
      throw new Exception('Worklist record must have a recognized source');
    }

    parent::__construct();
  }

  public function updateDump()
  {
    if ($this->source)
      $this->dump = $this->source->dump;
  }

  public function fileNeedsUpdate() {
    if (!file_exists($this->path)) return true;
    if ($this->dump !== file_get_contents($this->path)) return true;
    return false;
  }

  public function updateDumpFile() {
    file_put_contents($this->path, $this->dump);
    $cmd = escapeshellcmd(DCMTK_BIN_PATH . 'dump2dcm' );
    $arg1 = escapeshellarg( $this->path );
    $arg2 = escapeshellarg( $this->path . '.wl' );
    exec( "$cmd --write-xfer-little $arg1 $arg2" );
  }

  public function isStale() {
    $cutoff = new DateTime();
    $cutoff = $cutoff->sub( new DateInterval('P'. DMWL_MAX_AGE .'D') );
    return ( $this->getDate("0040,0002") < $cutoff );
  }
}