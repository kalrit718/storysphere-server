<?php
namespace StorySphere\CustomException;
use Exception;

class ChatIdRequiredException extends Exception {
  public function errorMessage() {
    return 'Chat ID required!';
  }
}

class ChatDoesNotExistException extends Exception {
  public function errorMessage() {
    return 'Chat does not exist!';
  }
}

