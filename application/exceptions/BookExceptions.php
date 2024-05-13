<?php
namespace StorySphere\CustomException;
use Exception;

class BookIdRequiredException extends Exception {
  public function errorMessage() {
    return 'Book ID required!';
  }
}

class BookDoesNotExistException extends Exception {
  public function errorMessage() {
    return 'Book does not exist!';
  }
}

