<?php

// Exception.php defines a couple of classes so we have to load it manually
require_once( 'Exception.php' );

/**
 *  @defgroup Helpers Helper classes
 *
 *  @brief Classes performing some common tasks that can be shared across
 *  different Services or data managers (Mgmts).
 *
 *  Objects of this class can use data managers, but are not ready to use
 *  FluxIncPDO and execute queries themselves.
 *
 *  They can be used by services, but are not ready to provide remote
 *  procedures.
 */

/**
 *  @brief Generic object template that provides most basic functions that
 *  might be need anywhere.
 *
 *  @ingroup Helpers
 */
class GenericHelper {
  ///@privatesection

  public function __construct() {
    return;
  }

  /**
   * @name Logging levels
   * @{
   */
  const LOG_ERROR = 1;
  const LOG_INFO  = 2;
  const LOG_DEBUG = 4;
  ///@}

  /**
   * @name Date and timestamp regular expressions
   * @{
   */
  const PATTERN_DATE = '(\d{4})-(\d{2})-(\d{2})';
  const PATTERN_TIME = '([01]\d|2[0-3]):([0-5]\d):([0-5]\d)';
  ///@}

  /**
   * @name Date and datetime formats.
   * @brief As accepted by php's date() and DateTime::format() functions.
   * @{
   */
  const DATE_DB_FORMAT         = 'Y-m-d';
  const DATE_CLIENT_FORMAT     = 'Y-m-d';
  const DATETIME_DB_FORMAT     = 'Y-m-d H:i:s';
  const DATETIME_CLIENT_FORMAT = 'Y-m-d H:i:s';
  ///@}

  /**
   * @name Transaction handling
   * @{
   */
  const TRANSACTION_RETRIES = 9;
  const RETRY_POSTPONE_FACTOR = 400000; // 0.4 seconds, the first retry
  ///@}

  /**
   * @name Methods for validating/manipulating date-type data.
   * @{
   */

  /**
   *  @brief Converts sql DATETIME (or TIMESTAMP) to unix timestamp.
   *
   *  Throws WrongDataException on date conversion error.
   *
   *  @return Integer unix timestamp.
   */
  protected function sqlDatetimeToTimestamp( $timestamp, $source = null ) {
    return $this->regexToTimestamp( $timestamp,
      '/^' . self::PATTERN_DATE . ' ' . self::PATTERN_TIME . '$/', $source );
  }

  /**
   *  @brief Converts sql DATE to unix timestamp.
   *
   *  Throws WrongDataException on date conversion error.
   *
   *  @return Integer unix timestamp.
   */
  protected function sqlDateToTimestamp( $date, $source = null ) {
    return $this->regexToTimestamp( $date,
      '/^' . self::PATTERN_DATE . '$/', $source );
  }

  /**
   *  @brief Converts sql DATE or DATETIME to unix timestamp.
   *
   *  Does the work for both sqlDatetimeToTimestamp and sqlDateToTimestamp.
   *
   *  @return Integer - unix timestamp.
   */
  private function regexToTimestamp( $value, $regex, $source = null ) {
    do {
      $matches = array();
      if ( ! preg_match( $regex, $value, $matches ) )
        break;

      $y = (int) ltrim( $matches[ 1 ], '0' );
      $m = (int) ltrim( $matches[ 2 ], '0' );
      $d = (int) ltrim( $matches[ 3 ], '0' );

      if ( ! checkdate( $m, $d, $y ) )
        break;

      if ( count( $matches ) === 6 ) {
        // regex contained time values
        $return = mktime(
          $matches[ 4 ], // h
          $matches[ 5 ], // m
          $matches[ 6 ], // s
          $m, $d, $y );
      }
      else {
        // regex only contained date
        $return = mktime( 12, 0, 0, $m, $d, $y );
      }

      if ( false === $return )
        break;

      return $return;
    }
    while ( false );

    if ( empty( $source ) )
        $source = 'datevalue';

    throw new WrongDataException( $source );
  }
  ///@}

  private static $insideTransaction = false;

  /**
   *  @name Methods for handling transactions
   *
   *  @{
   */

  /**
   *  @brief Manages database-transaction logic reruning it if necessary.
   *
   *  @param $callback Valid callback.
   *  @param $arguments Array containing all arguments
   *  @param $transactionName String identifying transaction.
   *
   *  @return Return value of callback function.
   */
  protected function encapsulateTransaction(
    $callback, $arguments, $transactionName = ''
  ) {
    //print_r( $callback );
    if ( self::$insideTransaction ) {
      self::logDebug(
        'Transaction %s requested inside existing transaction.',
        $transactionName
      );
      return call_user_func_array( $callback, $arguments );
    }

    self::$insideTransaction = true;

    $mgr = new FluxIncPDO();
    $retriesLeft = self::TRANSACTION_RETRIES;
    $postponeTime = 0;
    $retriedTotal = $postponedTotal = 0;
    while ( true ) {
      try {
        $mgr->beginTransaction();
        $return = call_user_func_array( $callback, $arguments );
        $mgr->commit();
        self::$insideTransaction = false;

        if ( $retriedTotal ) {
          self::logInfo(
            'Transaction %s completed successfully after retrying %d times. '.
            'It had to wait a total of %F seconds.',
            $transactionName, $retriedTotal, $postponedTotal / 1000000
          );
        }

        return $return;
      }
      catch ( DeadlockException $e ) {
        $mgr->rollback();

        if ( ! $retriesLeft-- ) {
          self::logTransactionFailure(
            $e, $transactionName, $retriedTotal, $postponedTotal
          );
          self::$insideTransaction = false;
          throw $e;
        }

        $postponeTime +=
          rand( 0, 2 * self::RETRY_POSTPONE_FACTOR );
        usleep( $postponeTime );

        ++$retriedTotal;
        $postponedTotal += $postponeTime;
      }
      catch ( LockWaitTimeoutException $e ) {
        $mgr->rollback();

        // set database server wait timeout to appropriate value
        // we don't want to wait any longer

        self::logTransactionFailure(
          $e, $transactionName, $retriedTotal, $postponedTotal
        );
        self::$insideTransaction = false;
        throw $e;
      }
      catch ( Exception $e ) {
        $mgr->rollback();

        self::logTransactionFailure(
          $e, $transactionName, $retriedTotal, $postponedTotal
        );
        self::$insideTransaction = false;
        throw $e;
      }
    }
  }

  private function logTransactionFailure(
    $exception, $transactionName, $retriedTotal, $postponedTotal
  ) {
    if ( $exception instanceof DeadlockException ||
         $exception instanceof LockWaitTimeoutException
    ) {
      if ( $retriedTotal ) {
        self::logError(
          'Transaction %s was rolled back because %s was caught. ' .
          'Transaction was already retried %d times with a total of %F ' .
          'seconds of separation.',
          $transactionName, get_class( $exception ), $retriedTotal,
          $postponedTotal / 1000000
        );
      }
      else {
        self::logError(
          'Transaction %s was rolled back because %s was caught.',
          $transactionName, get_class( $exception )
        );
      }
    }
    else {
      if ( $retriedTotal ) {
        self::logInfo(
          'Transaction %s was rolled back because %s ( %s ) was caught. ' .
          'Transaction was already retried %d times with a total of %F ' .
          'seconds of separation.',
          $transactionName, get_class( $exception ), $exception->getMessage(),
          $retriedTotal, $postponedTotal / 1000000
        );
      }
      else {
        self::logDebug(
          'Transaction %s was rolled back because %s ( %s ) was caught.',
          $transactionName, get_class( $exception ), $exception->getMessage()
        );
      }
    }
  }
  ///@}

  /**
   *  @name Logging methods
   *
   *  @{
   */
  protected static function logDebug( $format ) {
    if ( LOGGING_LEVEL & self::LOG_INFO ) {
      $arguments = func_get_args();
      $msg = call_user_func_array( 'sprintf', $arguments );
      self::writeToLogFile( 'Debug', $msg );
    }
  }

  protected static function logInfo( $format ) {
    if ( LOGGING_LEVEL & self::LOG_INFO ) {
      $arguments = func_get_args();
      $msg = call_user_func_array( 'sprintf', $arguments );
      self::writeToLogFile( 'Info', $msg );
    }
  }

  protected static function logError( $format ) {
    if ( LOGGING_LEVEL & self::LOG_INFO ) {
      $arguments = func_get_args();
      $msg = call_user_func_array( 'sprintf', $arguments );
      self::writeToLogFile( 'Error', $msg );
    }
  }

  protected static function writeToLogFile( $type, $msg, $filename = 'default' ) {
      $indent = '                     ';
      $header = date( 'Y-m-d H:i:s: ' ) . $type . " message\n";
      $msg = wordwrap( $msg, 59, "\n", true );
      $msg = explode( "\n", $msg );
      $msg = implode( "\n" . $indent, $msg );
      $msg = $indent . $msg;
      $logsFile = LOGS_PATH . $filename . '.log';
      if ( ! file_exists( $logsFile ) ) {
        touch( $logsFile );
        chmod( $logsFile, 0666 );
      }
      error_log( $header . $msg . "\n", 3, $logsFile );
  }
  ///@}
}

?>
