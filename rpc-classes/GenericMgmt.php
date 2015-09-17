<?php

/**
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Wojtek Grabski
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
 *  @defgroup Mgmt Data Managment
 *
 *  @brief Data manipulation api.
 *
 *  This module provides data manipulation layers. It does not care of things
 *  like permissions or handling errors, but if something goes wrong it will
 *  throw one of it's exceptions.
 *
 *  It's api is sql free.
 *
 *  @ingroup Helpers
 */

/**
 *  @brief Provides methods shared between different managers.
 *
 *  @ingroup Mgmt
 */
class GenericMgmt extends GenericHelper {
  ///@privatesection
  protected $dbh;

  public function __construct() {
    $this->dbh = new FluxIncPDO();
  }

  protected function assertRows( FluxIncPDOStatement $stmt, $source = null ) {
    if ( ! $stmt->rowCount() )
      throw new NotFoundException( $source );
  }

  /**
   *  @brief Converts array of rows to array of data objects.
   */
  public function rowsToDataObjects( $objectType, $rows ) {
    $return = array();
    foreach( $rows as $row ) {
      $return[] = new $objectType( $row );
    }
    return $return;
  }

  /**
   *  @brief Begins transaction with currently opened connection.
   *
   *  @return Boolean.
   */
  public function beginTransaction() {
    return $this->dbh->beginTransaction();
  }

  /**
   *  @brief Commits transaction with currently opened connection.
   *
   *  @return Boolean.
   */
  public function commit() {
    return $this->dbh->commit();
  }

  /**
   *  @brief Rolls back transaction with currently opened connection.
   *
   *  @return Boolean.
   */
  public function rollback() {
    return $this->dbh->rollback();
  }
}

?>
