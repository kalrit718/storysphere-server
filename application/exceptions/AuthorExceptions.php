<?php
namespace StorySphere\CustomException;
use Exception;

class AuthorIdRequiredException extends Exception {
  public function errorMessage() {
    return 'Author ID required!';
  }
}

class AuthorDoesNotExistException extends Exception {
  public function errorMessage() {
    return 'Author does not exist!';
  }
}

