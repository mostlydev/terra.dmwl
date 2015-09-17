<?php
/*
 *  * The MIT License (MIT)
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
 */
/**
 * @defgroup Exceptions Exceptions
 *
 * @brief Contains exception classes defined and used by our application.
 *
 * As a general rule our exceptions store source of problem rather than message
 * describing it. Source is optional and should be a lowercase string
 * naming element on which program worked when problem occured. You can add
 * more detail, adding narrower elements after a hyphen.
 *
 * Examples of $source string:
 *   - 'user' (problem could occur in UsersMgmt or User service, but also
 *     some other class if it worked on data associated with user)
 *   - 'user-name' (same as above, but the problem was particularly with
 *     name property of user data)
 *
 * @see ServiceCaller::catchException()
 *
 * @{
 */

/**
 * @brief Parent class for all our exceptions.
 */
class FluxIncException extends Exception {
  public function __construct( $source = null ) {
    parent::__construct( $source, 0 );
  }

  /**
   *  @brief Allows changing of exception message.
   */
  public function setMessage( $newMsg ) {
    $this->message = $newMsg;
  }
}

/**
 * @brief Thrown when communication with database failed.
 */
class DatabaseException extends FluxIncException {
  public function __construct( $source = null ) {
    parent::__construct( $source, 0 );
  }
}

/**
 * @brief Thrown when transaction was a deadlock victim.
 */
class DeadlockException extends FluxIncException {
  public function __construct( $source = null ) {
    parent::__construct( $source, 0 );
  }
}

/**
 * @brief Thrown when transaction timed out waiting for necessary locks.
 */
class LockWaitTimeoutException extends FluxIncException {
  public function __construct( $source = null ) {
    parent::__construct( $source, 0 );
  }
}

/**
 * @brief Thrown when there's a problem performing network operation.
 */
class NetworkException extends FluxIncException {
  public function __construct( $source = null ) {
    parent::__construct( $source, 0 );
  }
}

/**
 * @brief Thrown when user tried to perform operation he's not permitted to.
 */
class PermissionException extends FluxIncException {
  public function __construct( $source = null ) {
    parent::__construct( $source, 0 );
  }
}

/**
 * @brief Thrown when element on which function should operate can not be found.
 */
class NotFoundException extends FluxIncException {
  public function __construct( $source = null ) {
    parent::__construct( $source, 0 );
  }
}

/**
 * @brief Usually thrown when trying to add item that already exists.
 */
class DuplicateException extends FluxIncException {
  public function __construct( $source = null ) {
    parent::__construct( $source, 0 );
  }
}

/**
 * @brief Thrown when function receives parameters it can't handle.
 */
class WrongDataException extends FluxIncException {
  public function __construct( $source = null ) {
    parent::__construct( $source, 0 );
  }
}

/**
 * @brief Thrown when some internal assert failed.
 */
class ApplicationException extends FluxIncException {
  public function __construct( $source = null ) {
    parent::__construct( $source, 0 );
  }
}
///@}

?>
