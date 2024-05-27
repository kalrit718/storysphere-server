<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require_once(APPPATH . '/exceptions/UserExceptions.php'); 
use StorySphere\CustomException\UserHandleRequiredException;
use StorySphere\CustomException\PasswordRequiredException;
use StorySphere\CustomException\UserHandleDoesNotExistException;
use StorySphere\CustomException\InvalidPasswordException;

include_once './vendor/autoload.php'; 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


// require_once(APPPATH . '/exceptions/UserExceptions.php'); 
// use StorySphere\CustomException\AuthorIdRequiredException;
// use StorySphere\CustomException\AuthorDoesNotExistException;

class Auth extends RestController {

	private $key = 'eyJhbGciOiJIUzI1NiJ9.ew0KICAibm9ubyI6ICJubyIsDQogICJ0aGFua3RoYW5rIjogInRoYW5rIiwNCiAgInlvdXlvdSI6ICd5b3UnDQp9.AwAwtd3Gh9uKOOkb2nrOpBG7zFAxnrLoj9C3-1yWSlk';

	function __construct() {
		parent::__construct();

		$this->load->model('User');
	}

	public function authenticate_post() {
		$user_handle = $this->input->get('user_handle');
		$password = $this->input->get('password');

		try {
			$user_record = $this->User->authenticate($user_handle, $password);
		}
		catch(UserHandleRequiredException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(PasswordRequiredException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(UserHandleDoesNotExistException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(InvalidPasswordException $ex) {
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

	public function hash_post() {
		$hash = password_hash('mellojello', PASSWORD_DEFAULT);

		// $success_response = json_encode(array('message' => $hash));
		$this->response($hash, RestController::HTTP_OK);
	}

	public function verify_post() {
		if (password_verify('mellojello', '$2y$10$z3heYJjAZ6UxoM1cYuWks.2G6bL1wcwSIkZvMsERopIwPx/GxFCDK')) {
			$success_response = json_encode(array('message' => 'YAY!'));
		}
		else {
			$success_response = json_encode(array('message' => "OH!NO!"));
		}
		$this->response($success_response, RestController::HTTP_OK);
	}
}