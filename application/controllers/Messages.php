<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require_once(APPPATH . '/exceptions/MessageExceptions.php'); 
use StorySphere\CustomException\MessageIdRequiredException;
use StorySphere\CustomException\MessageDoesNotExistException;

require_once(APPPATH . '/exceptions/ChatExceptions.php');
use StorySphere\CustomException\ChatIdRequiredException;

class Messages extends RestController {

	function __construct() {
		parent::__construct();

		$this->load->model('Message');
	}

	/** HTTP_GET: Get the details of the message for the given message id
   * @param int $id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_get() {
		$message_id = $this->input->get('id');

		try {
			$message_record = $this->Message->get_message($message_id);
		}
		catch(MessageIdRequiredException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(MessageDoesNotExistException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(Exception $ex) {
      $error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
      $this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
    }
		
		$success_response = json_encode($message_record);
		$this->response($success_response, RestController::HTTP_OK);
	}

	/** HTTP_GET: Get a list of the messages for the given chat id
   * @param int $chat_id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function chat_get() {
		$chat_id = $this->input->get('chat_id');

		try {
			$message_records = $this->Message->get_chat_messages($chat_id);
		}
		catch(ChatIdRequiredException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(Exception $ex) {
      $error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
      $this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
    }
		
		$success_response = json_encode($message_records);
		$this->response($success_response, RestController::HTTP_OK);
	}

	/** HTTP_POST: Create a new message with the provided details
   * @param int $chat_id
   * @param string $message_body
   * @param string $sender_handle
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_post() {
		$chat_id = $this->input->get('chat_id');
		$message_body = $this->input->get('message_body');
		$sender_handle = $this->input->get('sender_handle');
		
		try {
			$message_id = $this->Message->create($chat_id, $message_body, $sender_handle);
		}
		catch(InvalidArgumentException $ex) {
			$error_response = json_encode(array('message' => 'Invlid or Missing Argument!'));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(Exception $ex) {
      $error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
      $this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
    }

		if(!$message_id) {
			$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
      $this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
		}

		$success_response = json_encode(array('created_message_id' => $message_id));
		$this->response($success_response, RestController::HTTP_CREATED);
	}
}
