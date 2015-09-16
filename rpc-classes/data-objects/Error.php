<?php

/**
 *  @brief Objects of this class are returned when something goes wrong.
 *
 *  It defines a few error types a user can expect and sometimes provides
 *  him with source place where error occured.
 *
 *  Types so far are:
 *
 *  Type @b 'not-found' indicates that requested data was not in database.
 *
 *  Type @b 'permission' indicates that current user does not have permission
 *  level needed to perform this operation.
 *
 *  Type @b 'duplicate' indicates that user tried to add element that already
 *  existed in database. This usually means that there is element of the same
 *  name.
 *
 *  Type @b 'database-jammed' indicates that there was a problem commiting
 *  user requested changes to database, because someone else was modifying
 *  data at the same time. Request can be @b manually retried in a moment.
 *
 *  Type @b 'wrong-data' indicates that method expected diffrent type of data
 *  for one of it's arguments or that it did not passed some constraints on
 *  input.
 *
 *  Type @b 'database' indicates that some undefined and probably unexpected
 *  error occured while communicating with database. This usually can mean
 *  an error in program code or server state.
 *
 *  Type @b 'application' is returned when nothing can be said about error that
 *  occured and it always means that there is an error in application.
 *
 *
 *  @ingroup dos
 */
class Error extends GenericDataObject {
  public $type;
  public $source;

  ///@privatesection

  const TYPE_APPLICATION = 'application';
  const TYPE_PERMISSION = 'permission';
  const TYPE_DATABASE = 'database';
  const TYPE_DATABASE_JAMMED = 'database-jammed';
  const TYPE_NETWORK = 'network';
  const TYPE_NOT_FOUND = 'not-found';
  const TYPE_DUPLICATE = 'duplicate';
  const TYPE_WRONG_DATA = 'wrong-data';

  public function __construct( $type = self::TYPE_APPLICATION, $source = '' ) {
    parent::__construct();

    $this->type = $type;
    $this->source = $source;
  }
}

?>
