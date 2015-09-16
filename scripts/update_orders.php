<?php

/*
 * NOT COMPLETE
 * This script deletes stale DMWL records and creates new ones using the contents of the exam table.
 */

ini_set( 'display_errors', true );
error_reporting( E_ALL );
require_once( 'includes/header.inc.php' );
/*
$mgr = new ExamsMgmt();

$startDate = new DateTime();
$startDate->sub( new DateInterval( 'P' . DMWL_MAX_AGE . 'D') );
$endDate = new DateTime();

$exams = $mgr->getBetweenDates($startDate, $endDate );
var_dump( $exams );
*/

$rec = new WorklistRecord();
$rec->setTag( "0010,0010", "SMITH^VERNON" );
var_dump( $rec->dump );

exit;

$location = ORDER_INBOX . '*_*';
try {
  $time = new DateTime();
  //print "[{$time->format('U')}] Looking in {$location}\n";
  $order_files = glob( $location, GLOB_BRACE);
  $orders = array();
  foreach($order_files as $order_file) {
    $just_name = basename( $order_file );
    $order = new OrdersHelper($order_file);
    if ( $order->message_type == 'ORM' )
    {    
      array_push($orders, $order);
    }
  }
} catch ( exception $e ) {
  print_r($e);
}
