<?php

/**
 *  @brief Provides method that can be used to replace php's default error
 *  handler to log all mesages to our own log files.
 *
 *  @ingroup Helpers
 */
class ErrorHandler extends GenericHelper {
  private static $registered = 0;
  private static $defaultHandler = null;

  /**
   *  @brief Starts handling php errors.
   *
   *  @return True on success, false otherwise.
   */
  public static function register() {
    ++self::$registered;
    if ( 1 < self::$registered ) {
      return true;
    }

    $callback = array( 'ErrorHandler', 'handleError' );
    self::$defaultHandler = set_error_handler( $callback );

    if ( is_null( self::$defaultHandler ) )
      return false;

    self::$registered = true;
    return true;
  }

  /**
   *  @brief Stops handling of php errors.
   *
   *  @return True on success, false otherwise.
   */
  public static function restoreDefault() {
    if ( self::$registered < 1 ) {
      return true;
    }
    --self::$registered;

    if ( 0 === self::$registered ) {
      if ( ! set_error_handler( self::$defaultHandler ) )
        return false;
    }

    return true;
  }

  /**
   *  @brief Handles php generated errors.
   *
   *  @return True to also call default php handler.
   */
  public static function handleError( $errNo, $msg, $file, $line ) {
    $msg = sprintf( "%s, line %d. %s.", $file, $line, $msg );
    self::writeToLogFile(
      'PHP\'s ' . self::errorCodeToConstantName( $errNo ), $msg
    );

    return false;
  }

  /**
   *  @brief Translates integer error code to string constant name.
   *
   *  @return String constant name or 'Error' if nothing matched.
   */
  public static function errorCodeToConstantName( $code ) {
    switch ( $code ) {
      case E_WARNING            : return 'E_WARNING';
      case E_NOTICE             : return 'E_NOTICE';
      case E_USER_ERROR         : return 'E_USER_ERROR';
      case E_USER_WARNING       : return 'E_USER_WARNING';
      case E_USER_NOTICE        : return 'E_USER_NOTICE';
      case E_RECOVERABLE_ERROR  : return 'E_RECOVERABLE_ERROR';
      /* Available since php 5.3.0. We support 5.2.4.
      case E_DEPRECATED         : return 'E_DEPRECATED';
      case E_USER_DEPRECATED    : return 'E_USER_DEPRECATED';
       */
      default                   : return 'Error';
    }
  }
}

?>
