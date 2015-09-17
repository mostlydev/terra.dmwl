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
 *  @defgroup dos Data Objects
 *  @brief Data returned by services.
 *
 *  @ingroup Helpers
 */

/**
 *  @brief A class that should be inherited by all data objects returned from
 *  services.
 *
 *  @ingroup dos
 */
class GenericDataObject extends GenericHelper {
  public $_explicitType = null;

  public function __construct() {
    $this->_explicitType = get_class( $this );
  }
  /**
   *  @brief Format date received from db to what client expects.
   *
   *  Currently this does nothing.
   */
  protected function dbDateToClientFormat( $date ) {
    return $date;
  }

  /**
   *  @brief Format date time received from db to what client expects.
   *
   *  Currently this does nothing.
   */
  protected function dbDatetimeToClientFormat( $date ) {
    return $date;
  }


}

?>
