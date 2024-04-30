<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require_once(APPPATH . '/exceptions/UserExceptions.php'); 
use StorySphere\CustomException\UserHandleTakenException;
use StorySphere\CustomException\UserHandleRequiredException;
use StorySphere\CustomException\UserHandleDoesNotExistException;

class Users extends RestController {

	function __construct() {
		parent::__construct();

		$this->load->model('User');
	}

  public function index_get() {
		$user_handle = $this->input->get('user_handle');

		try {
			$user_record = $this->User->get_user($user_handle);
		}
		catch(UserHandleRequiredException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(UserHandleDoesNotExistException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(Exception $ex) {
      $error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
      $this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
    }

		$success_response = json_encode($user_record);
		$this->response($success_response, RestController::HTTP_OK);
	}

  public function index_post() {
		$user_handle = $this->input->get('user_handle');
		$first_name = $this->input->get('first_name');
		$middle_name = $this->input->get('middle_name');
		$last_name = $this->input->get('last_name');
		$email = $this->input->get('email');

    try {
      $result = $this->User->create($user_handle, $first_name, $middle_name, $last_name, $email);
    }
    catch(InvalidArgumentException $ex) {
      $error_response = json_encode(array('message' => 'Invlid or Missing Argument!'));
      $this->response($error_response, RestController::HTTP_OK);
    }
    catch(UserHandleTakenException $ex) {
      $error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
    }
    catch(Exception $ex) {
      $error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
      $this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
    }

		$success_response = json_encode(array('created_user_handle' => $user_handle));
		$result ? $this->response($success_response, RestController::HTTP_CREATED) : $this->response(null, RestController::HTTP_INTERNAL_ERROR);
	}

	public function index_put() {
		$user_handle = $this->input->get('user_handle');
		$first_name = $this->input->get('first_name');
		$middle_name = $this->input->get('middle_name');
		$last_name = $this->input->get('last_name');
		$email = $this->input->get('email');

		try {
			$result = $this->User->update($user_handle, $first_name, $middle_name, $last_name, $email);
		}
		catch(UserHandleRequiredException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(UserHandleDoesNotExistException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(InvalidArgumentException $ex) {
			$error_response = json_encode(array('message' => 'No updatable details provided!'));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(Exception $ex) {
      $error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
      $this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
    }

    $success_response = json_encode(array('message' => 'Changed the user details successfully!'));
		$result ? $this->response($success_response, RestController::HTTP_OK) : $this->response(null, RestController::HTTP_INTERNAL_ERROR);
	}

	public function index_delete() {
		$user_handle = $this->input->get('user_handle');

		try {
			$result = $this->User->delete($user_handle);
		}
		catch(UserHandleRequiredException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(UserHandleDoesNotExistException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(Exception $ex) {
      $error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
      $this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
    }

		$success_response = json_encode(array('message' => 'Deleted the user successfully!'));
		$result ? $this->response($success_response, RestController::HTTP_OK) : $this->response(null, RestController::HTTP_INTERNAL_ERROR);
	}
}
