<?php
namespace StorySphere\CustomException;
use Exception;

class PostIdRequiredException extends Exception {
  public function errorMessage() {
    return 'Post ID required!';
  }
}

class PostDoesNotExistException extends Exception {
  public function errorMessage() {
    return 'Post does not exist!';
  }
}

