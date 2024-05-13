<?php
namespace StorySphere\CustomException;
use Exception;

class CommentIdRequiredException extends Exception {
  public function errorMessage() {
    return 'Comment ID required!';
  }
}

class CommentDoesNotExistException extends Exception {
  public function errorMessage() {
    return 'Comment does not exist!';
  }
}

