<?php
namespace StorySphere\CustomException;
use Exception;

class UserHandleTakenException extends Exception {
  public function errorMessage() {
    return 'User Handle Already Taken!';
  }
}

class UserHandleRequiredException extends Exception {
  public function errorMessage() {
    return 'User handle required!';
  }
}

class UserHandleDoesNotExistException extends Exception {
  public function errorMessage() {
    return 'User handle does not exist!';
  }
}
