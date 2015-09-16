<?php

/*
 * Updates worklist records using data source class defined in DMWL_SOURCE_CLASS configuration parameter
 */

ini_set( 'display_errors', true );
error_reporting( E_ALL );
require_once( 'includes/header.inc.php' );

$wl = new WLMHelper();
$wl->prune();

// Change this if you decide to use a different data source
$exams = call_user_func( DMWL_SOURCE_CLASS . '::recent');

foreach ($exams as $exam) {

  $record = new WorklistRecord( $exam );

  if (!$record->isStale())
  {
    if ($record->fileNeedsUpdate())
    {
      print "Updating record for {$record->uid}\n";
      $record->updateDumpFile();
    }
  }
}

