<?php

ini_set( 'display_errors', true );
error_reporting( E_ALL );
require_once( 'includes/header.inc.php' );


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
