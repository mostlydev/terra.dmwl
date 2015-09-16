<?php

/*
 * NOT COMPLETE
 * This script deletes stale DMWL records and creates new ones using the contents of the exam table.
 */

ini_set( 'display_errors', true );
error_reporting( E_ALL );
require_once( 'includes/header.inc.php' );

$wl = new WLMHelper();
$wl->prune();

$mgr = new ExamsMgmt();
$startDate = new DateTime();
$startDate->sub( new DateInterval( 'P' . DMWL_MAX_AGE . 'D') );
$endDate = new DateTime();

$exams = $mgr->rowsToDataObjects( 'Exam', $mgr->getBetweenDates($startDate, $endDate ) );

foreach ($exams as $exam) {
  $record = new WorklistRecord( $exam );
  if (!$record->isStale())
  {
    if ($record->needsUpdate())
    {
      $record->update();
    }
  }
}

