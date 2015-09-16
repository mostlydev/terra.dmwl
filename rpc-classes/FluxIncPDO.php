<?php

/**
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Flux Inc
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 *
 * @brief Abstraction for manipulating database.
 *
 * It is and abstraction over PDO class and can be used exactly as PDO would.
 * PDO documentation can be found on php pages.
 *
 * For providing unique functionality some of the methods had to have
 * parameters changed compared to the PDO. This and only this methods
 * are described here. So you should probably read on.
 *
 * It is not a simple inheritance, as it implements singleton pattern.
 * Many instances of FluxIncPDO share the same PDO connection. Moreover
 * FluxIncPDO instances don't actually open PDO connection connection
 * until one of the PDO methods is called. This allows us to create
 * FluxIncPDO instance on the beginning of every service call without
 * worrying if we will be using database or not.
 */
class FluxIncPDO extends GenericHelper {
  /**
   * @brief connection supports cascading on foreign key change
   *
   * Support for cascading UPDATE/DELETE statements to tables that use foreign
   * key affected by executed queries. If connection doesn't support this
   * programmer has to delete/update other tables himself otherwise there will
   * be orphaned rows left over.
   */
  const FEAT_CASCADE_ON_FK_CONSTR = 1;

  private static $connectionArguments = array();
  private static $connectionPool = array();

  private $connection; ///< Name of object's connection.
  private $pdo = null; ///< PDO instance.
  private $engineName; ///< Name of database engine used in this instance.

  public function __construct( $connection = 'default' ) {
    self::initializeDefaultConnectionsArguments();

    if ( empty( self::$connectionArguments[ $connection ] ) ) {
      throw new WrongDataException( 'unknown-connection' );
    }

    $this->connection = $connection;

    $engine = explode( ':', self::$connectionArguments[ $connection ]->dsn );
    $this->engineName = $engine[ 0 ];
  }

  private static function initializeDefaultConnectionsArguments() {
    if ( ! empty( self::$connectionArguments[ 'default' ] ) ) {
      return;
    }

    self::$connectionArguments[ 'default' ] = new ConnectionArguments(
      'mysql:' . 'host=' . DATABASE_SERVER . ';' . 'dbname=' . DATABASE_NAME,
      DATABASE_USERNAME,
      DATABASE_PASSWORD
    );
  }

  public static function setDsn( $dsn = null, $connection = 'default' ) {
    if ( empty( self::$connectionArguments[ $connection ] ) ) {
      self::$connectionArguments[ $connection ] = new ConnectionArguments();
    }

    self::$connectionArguments[ $connection ]->dsn = $dsn;
  }

  public static function setUser( $user = null, $connection = 'default' ) {
    if ( empty( self::$connectionArguments[ $connection ] ) ) {
      self::$connectionArguments[ $connection ] = new ConnectionArguments();
    }

    self::$connectionArguments[ $connection ]->user = $user;
  }

  public static function setPassword(
    $password = null, $connection = 'default'
  ) {
    if ( empty( self::$connectionArguments[ $connection ] ) ) {
      self::$connectionArguments[ $connection ] = new ConnectionArguments();
    }

    self::$connectionArguments[ $connection ]->password = $password;
  }

  /**
   * @brief Tells whether some sql feature is provided by current connection
   * @details For a detailed description of features available read
   * documentation for FluxIncPDO::FEAT_* constants.
   * @param $feature one of the FluxIncPDO::FEAT_* constants
   */
  public function hasFeature( $feature ) {
    switch ( $feature ) {
      case self::FEAT_CASCADE_ON_FK_CONSTR:
        if ( 'mysql' === $this->engineName )
          return true;
        else
          return false;
      default:
        throw new Exception( 'FluxIncPDO::hasFeature() - Unknown feature' );
    }
  }

  public function mysqlRealEscapeString($str) {
    $this->estabilishConnection();
    return mysql_real_escape_string($str);
  }

  /**
   * @brief Prepares an SQL statement to be executed.
   * @details Works like PDO::Prepare, but accepts additional parameter, also
   * parameter order is changed.
   * @param $statement Body of query that should be prepared.
   * @param $replace Can take an array of key => value pairs to search for
   * them in the query and replace before preparing statements. Can be used
   * for adding prefix to table/columns names and generally for changing
   * those names as prepared statements don't allow binding tables/columns only
   * values.
   * @param $driver_options Array of additional options that shoul be passed
   * to db driver. Same as in PDO.
   * @return PDOStatement
   */
  public function prepare( $statement, $replace = null, $driver_options = null ){
    try {
      $this->estabilishConnection();

      if ( ! is_null( $replace ) )
        foreach ( $replace as $key => $value )
          $statement = str_replace( $key, $value, $statement );

      $statement = str_replace( '%TPX', TABLE_PREFIX, $statement );

      if ( is_null( $driver_options ) ) {
        $return = $this->pdo->prepare( $statement );
      }
      else {
        $return = $this->pdo->prepare( $statement, $driver_options );
      }

      $return = new FluxIncPDOStatement( $return, $this );
      $return->setFetchMode( PDO::FETCH_ASSOC );
    }
    catch ( Exception $e ) {
      $this->translateAndRethrow( $e );
    }
    return $return;
  }

  /**
   * @brief Automatically call PDO methods.
   */
  public function __call( $method, $args ) {
    try {
      $this->estabilishConnection();

      if ( ! method_exists( $this->pdo, $method ) ) {
        throw new Exception( "FluxIncPDO - unknown method [$method]" );
      }

      return call_user_func_array(
        array( $this->pdo, $method ),
        $args
      );
    }
    catch ( Exception $e ) {
      $this->translateAndRethrow( $e );
    }
  }

  private function estabilishConnection() {
    // is this instance already connected?
    if ( ! empty( $this->pdo ) ) {
      return;
    }

    // did some other instance initialize connection?
    if ( ! empty( self::$connectionPool[ $this->connection ] ) ) {
      $this->pdo = self::$connectionPool[ $this->connection ];
      return;
    }

    // then open new connection.
    $options = array();

    if ( 'mysql' === $this->engineName ) {
      $options[ PDO::ATTR_PERSISTENT ] = false;
      $options[ PDO_MYSQL_ATTR_USE_BUFFERED_QUERY ] = true;
    }

    $args = self::$connectionArguments[ $this->connection ];

    try {
      $this->pdo = new PDO( $args->dsn, $args->user, $args->password );
      self::$connectionPool[ $this->connection ] = $this->pdo;
    }
    catch ( Exception $e ) {
      throw new Exception( "Could not connect to database: {$e->getMessage()}" );
    }

    $this->pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

    if ( 'mysql' === $this->engineName ) {
      $this->pdo->setAttribute( PDO::ATTR_AUTOCOMMIT, true );
      $this->pdo->exec( 'SET NAMES utf8 COLLATE utf8_general_ci' );
      $this->pdo->exec( 'SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
    }
  }

  /**
   *  @brief Changes any engine specific exceptions to exceptions accepted by
   *  higher levels of our application.
   *
   *  @return Void, this method either throws new exception or rethrows the one
   *  given as parameter.
   */
  public static function translateAndRethrow( $e, $source = '' ) {
    if ( $e instanceof PDOException ) {
      $sqlstate  = $e->getCode();
      $mysqlCode = $e->errorInfo;
      $mysqlCode = (int) $mysqlCode[ 1 ];

      // deadlock?
      if ( '40001' === $sqlstate ) { // err 1213
        throw new DeadlockException( $source );
      }
      // lock wait timeout?
      elseif ( 1205 === $mysqlCode ) {
        throw new LockWaitTimeoutException( $source );
      }
      elseif ( 1452 === $mysqlCode || 1216 === $mysqlCode ) { // state 23000
        throw new NotFoundException( $source );
      }
      elseif ( 1022 === $mysqlCode || 1062 === $mysqlCode ) {
        throw new DuplicateException( $source );
      }
      else {
        self::logError(
          "Caught unknown database exception.\n%s\n%s",
          $e->getMessage(),
          $e->getTraceAsString()
        );

        if ( FLUX_INC_DEBUG_MODE ) {
          echo( $e );
        }

        throw new DatabaseException( $source );
      }
    }

    throw $e;
  }
}

/**
 * @brief Our adoption of PDOStatement.
 */
class FluxIncPDOStatement {
  private $stmt = null;
  private $pdo = null;
  private $exceptionSource = '';

  public function __construct( $statement, $pdo ) {
    $this->stmt = $statement;
    $this->pdo = $pdo;
  }

  public function setExceptionSource( $source ) {
    $this->exceptionSource = $source;
  }

  public function bindValue( $param, $value, $type = null ) {
    try {
      $intermediate = $value;

      if ( is_null( $type ) ) {
        if ( is_int( $intermediate ) )
          $type = PDO::PARAM_INT;
        elseif ( is_null( $intermediate ) )
          $type = PDO::PARAM_NULL;
        elseif ( is_bool( $intermediate ) ) {
          $intermediate = (int) $intermediate;
          $type = PDO::PARAM_INT;
        }
        elseif ( is_string( $intermediate ) ) {
          $type = PDO::PARAM_STR;
        }
      }

      $this->stmt->bindValue( $param, $intermediate, $type );
    }
    catch ( Exception $e ) {
      $this->pdo->translateAndRethrow( $e, $this->exceptionSource );
    }
  }

  /**
   * @brief Automatically call PDOStatement methods.
   */
  public function __call( $method, $args ) {
    try {
      if ( ! method_exists( $this->stmt, $method ) ) {
        throw new Exception( "FluxIncPDOStatement - unknown method [$method]" );
      }

      return call_user_func_array(
        array( $this->stmt, $method ),
        $args
      );
    }
    catch ( Exception $e ) {
      $this->pdo->translateAndRethrow( $e, $this->exceptionSource );
    }
  }
}

/**
 * @brief A structure to hold connection arguments (dsn, username, password).
 */
class ConnectionArguments {
  public $dsn = null;
  public $user = null;
  public $password = null;

  public function __construct( $dsn = null, $user = null, $password = null ) {
    $this->dsn = $dsn;
    $this->user = $user;
    $this->password = $password;
  }
}

// to support older versions of php:
if ( defined( 'PDO::MYSQL_ATTR_USE_BUFFERED_QUERY' ) ) {
  define(
    'PDO_MYSQL_ATTR_USE_BUFFERED_QUERY', PDO::MYSQL_ATTR_USE_BUFFERED_QUERY
  );
}
else {
  define( 'PDO_MYSQL_ATTR_USE_BUFFERED_QUERY', 1000 );
}

