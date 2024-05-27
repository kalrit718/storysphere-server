<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require_once(APPPATH . '/exceptions/ChatExceptions.php'); 
use StorySphere\CustomException\ChatIdRequiredException;
use StorySphere\CustomException\ChatDoesNotExistException;

require_once(APPPATH . '/exceptions/UserExceptions.php');
use StorySphere\CustomException\UserHandleRequiredException;

class Chats extends RestController {

	function __construct() {
		parent::__construct();

		$this->load->model('Chat');
		$this->load->model('User');
	}

	/** HTTP_GET: Get the details of the chat for the given chat id
   * @param int $id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_get() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$chat_id = $this->input->get('id');
		
				try {
					$chat_record = $this->Chat->get_chat($chat_id);
				}
				catch(ChatIdRequiredException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(ChatDoesNotExistException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				$success_response = json_encode($chat_record);
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

	/** HTTP_GET: Get a list of the chats for the given user handle
   * @param string $user_handle
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function user_get() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$user_handle = $this->input->get('user_handle');
		
				try {
					$chat_records = $this->Chat->get_user_chats($user_handle);
				}
				catch(UserHandleRequiredException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				$success_response = json_encode($chat_records);
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

	/** HTTP_POST: Create a new chat with the provided users
   * @param array $user_handles
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
  public function index_post() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$user_handles = $this->input->get('user_handles');
		
				$user_handles = json_decode($user_handles);
		
				try {
					$chat_id = $this->Chat->create($user_handles);
				}
				catch(InvalidArgumentException $ex) {
					$error_response = json_encode(array('message' => 'Invlid or Missing Argument!'));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				if(!$chat_id) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				$success_response = json_encode(array('chat_id' => $chat_id));
				$this->response($success_response, RestController::HTTP_CREATED);
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

	/** HTTP_DELETE: Delete the book for the given id
   * @param int $id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_delete() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$chat_id = $this->input->get('id');
				
				try {
					$result = $this->Chat->delete($chat_id);
				}
				catch(ChatIdRequiredException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(ChatDoesNotExistException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				if(!$result) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				$success_response = json_encode(array('message' => 'Deleted the chat and the related users successfully!'));
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

	/** HTTP_POST: Add a new user for the provided chat
   * @param string $chat_id
   * @param array $user_handles
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
  public function add_user_post() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$chat_id = $this->input->get('id');
				$user_handles = $this->input->get('user_handles');
		
				$user_handles = json_decode($user_handles);
		
				try {
					$result = $this->Chat->add_users($chat_id, $user_handles);
				}
				catch(ChatIdRequiredException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(ChatDoesNotExistException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				if(!$result) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				$success_response = json_encode(array('message' => 'Added the user to the chat successfully!'));
				$this->response($success_response, RestController::HTTP_CREATED);
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
