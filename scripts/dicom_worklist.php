<?php

ini_set( 'display_errors', true );
error_reporting( E_ALL );
require_once( 'includes/header.inc.php' );

while (1) {
  echo "DICOM worklist is started ...";
  $wl_cmd = escapeshellcmd( DCMTK_BIN_PATH . 'wlmscpfs' );
  $wl_args = array();
  array_push( $wl_args, '--disable-host-lookup');
  array_push( $wl_args, '-v');
  array_push( $wl_args, '-dfp');
  array_push( $wl_args, escapeshellarg(DMWL_DCM_PATH) );
  array_push( $wl_args, DMWL_PORT );
  $wl_args = join( ' ', $wl_args );

  $process = new ProcessHelper( $wl_cmd, $wl_args, DMWL_DCM_PATH );
  $process->run();

  sleep( 5 );
}