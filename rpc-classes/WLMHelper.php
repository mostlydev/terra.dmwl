<?php

class WLMHelper extends GenericHelper
{
  private $data_path;
  public $records;

  public function __construct( $path ) {
    parent::__construct();
    $this->data_path = $path;
    touch( $this->data_path . '/lockfile' );
    $this->load_records();
  }

  public function load_records() {
    $this->records = array();
    $dump_files = glob( $this->data_path . '*.dump', GLOB_BRACE );
    foreach($dump_files as $dump_file) {
      $record = new WorklistRecord( $dump_file );
      array_push( $this->records, $record );
    }
  }

  public function delete_stale_records( $max_age = DMWL_MAX_AGE ) {
    foreach($this->records as $record) {
      if ($record->is_older_than( $max_age ))
      {
        print "Deleting stale record {$record->uid}\n";
        $record->delete_files();
      }
    }
  }


}