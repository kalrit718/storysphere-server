<?php
namespace StorySphere\CustomException;
use Exception;

class MessageIdRequiredException extends Exception {
  public function errorMessage() {
    return 'Message ID required!';
  }
}

class MessageDoesNotExistException extends Exception {
  public function errorMessage() {
    return 'Message does not exist!';
  }
}

