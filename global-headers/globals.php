<?php

function ifndefdefine( $constant, $value ) {
  if ( ! defined( $constant ) )
    define( $constant, $value );
}

ini_set( 'display_errors', true );

// Load global configuration
require_once( 'configuration.inc.php' );

?>
