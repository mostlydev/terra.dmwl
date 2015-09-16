<?php

class WLMHelper extends GenericHelper
{
  private $dataPath;
  public $records = array();

  public function __construct( ) {
    parent::__construct();
    $this->dataPath = DMWL_DCM_PATH . '/' . DMWL_AE_TITLE . '/';
    $this->update();
  }

  public function update() {
    $dump_files = glob( $this->dataPath . '*.dump', GLOB_BRACE );
    foreach($dump_files as $dump_file) {
      $record = new WorklistRecord( $dump_file );
      array_push( $this->records, $record );
    }
  }

  public function prune() {
    foreach($this->records as $record) {
      $dcmPath = $record->path . '.dcm';
      if ($record->isStale())
      {
        if (file_exists($dcmPath))
        {
          $dcmName = basename($dcmPath);
          print "Deleting stale record {$dcmName}\n";
          unlink( $dcmPath );
        }
      }
    }
  }


}