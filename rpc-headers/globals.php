<?php

// All rpc classes will be loaded when needed by this function
function __autoload( $class ) {
  if ( file_exists( RPC_CLASSES_PATH . $class . '.php' ) ) {
    require_once( RPC_CLASSES_PATH . $class . '.php' );
  }
  elseif (
    file_exists( RPC_CLASSES_PATH . '/data-objects/' . $class . '.php' )
  ) {
    require_once( RPC_CLASSES_PATH . '/data-objects/' . $class . '.php' );
  }
}

ErrorHandler::register();

// don't stop execution if user aborted http request
ignore_user_abort( true );
