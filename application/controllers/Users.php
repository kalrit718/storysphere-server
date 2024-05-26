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

	/** HTTP_GET: Get the details of the user for the given user handle
   * @param string $user_handle
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
  public function index_get() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
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
			else {
				$error_response = json_encode(array('status' => 'error', 'message' => 'Unauthorized!'));
				$this->response($error_response, RestController::HTTP_UNAUTHORIZED);
			}
		}
		else {
			$error_response = json_encode(array('status' => 'error', 'message' => 'No authorization header found!'));
			$this->response($error_response, RestController::HTTP_UNAUTHORIZED);
		}
	}

	/** HTTP_POST: Create a new user with the provided details
   * @param string $user_handle
   * @param string $first_name
   * @param string $middle_name
   * @param string $last_name
   * @param string $email
   * @param string $image_url
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
  public function index_post() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$user_handle = $this->input->get('user_handle');
				$first_name = $this->input->get('first_name');
				$middle_name = $this->input->get('middle_name');
				$last_name = $this->input->get('last_name');
				$email = $this->input->get('email');
				$image_url = $this->input->get('image_url');
				$password = $this->input->get('password');

				try {
					$result = $this->User->create($user_handle, $first_name, $middle_name, $last_name, $email, $image_url, $password);
				}
				catch(InvalidArgumentException $ex) {
					$error_response = json_encode(array('message' => 'Invlid or Missing Argument!'));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
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
			else {
				$error_response = json_encode(array('status' => 'error', 'message' => 'Unauthorized!'));
				$this->response($error_response, RestController::HTTP_UNAUTHORIZED);
			}
		}
		else {
			$error_response = json_encode(array('status' => 'error', 'message' => 'No authorization header found!'));
			$this->response($error_response, RestController::HTTP_UNAUTHORIZED);
		}
	}

	/** HTTP_PUT: Update the details of the user with the provided details
   * @param string $user_handle
   * @param string $first_name
   * @param string $middle_name
   * @param string $last_name
   * @param string $email
   * @param string $image_url
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_put() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$user_handle = $this->input->get('user_handle');
				$first_name = $this->input->get('first_name');
				$middle_name = $this->input->get('middle_name');
				$last_name = $this->input->get('last_name');
				$email = $this->input->get('email');
				$image_url = $this->input->get('image_url');
				$password = $this->input->get('password');
		
				try {
					$result = $this->User->update($user_handle, $first_name, $middle_name, $last_name, $email, $image_url, $password);
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
			else {
				$error_response = json_encode(array('status' => 'error', 'message' => 'Unauthorized!'));
				$this->response($error_response, RestController::HTTP_UNAUTHORIZED);
			}
		}
		else {
			$error_response = json_encode(array('status' => 'error', 'message' => 'No authorization header found!'));
			$this->response($error_response, RestController::HTTP_UNAUTHORIZED);
		}
	}

	/** HTTP_DELETE: Delete the user for the given user handle
   * @param string $user_handle
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_delete() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
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
			else {
				$error_response = json_encode(array('status' => 'error', 'message' => 'Unauthorized!'));
				$this->response($error_response, RestController::HTTP_UNAUTHORIZED);
			}
		}
		else {
			$error_response = json_encode(array('status' => 'error', 'message' => 'No authorization header found!'));
			$this->response($error_response, RestController::HTTP_UNAUTHORIZED);
		}
	}
}
