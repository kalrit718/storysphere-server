<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require_once(APPPATH . '/exceptions/UserExceptions.php'); 
use StorySphere\CustomException\AuthorIdRequiredException;
use StorySphere\CustomException\AuthorDoesNotExistException;

class Authors extends RestController {

	function __construct() {
		parent::__construct();

		$this->load->model('Author');
	}

	/** HTTP_GET: Get the details of the author for the given book author id
   * @param int $id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_get() {
		$author_id = $this->input->get('id');

		try {
			$author_record = $this->Author->get_author($author_id);
		}
		catch(AuthorIdRequiredException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(AuthorDoesNotExistException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(Exception $ex) {
      $error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
      $this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
    }
		
		$success_response = json_encode($author_record);
		$this->response($success_response, RestController::HTTP_OK);
	}

	/** HTTP_POST: Create a new book author with the provided name
   * @param string $name
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_post() {
		$author_name = $this->input->get('name');
		
		try {
			$author_id = $this->Author->create($author_name);
		}
		catch(InvalidArgumentException $ex) {
			$error_response = json_encode(array('message' => 'Invlid or Missing Argument!'));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(Exception $ex) {
      $error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
      $this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
    }

		if(!$author_id) {
			$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
      $this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
		}

		$success_response = json_encode(array('created_author_id' => $author_id));
		$this->response($success_response, RestController::HTTP_CREATED);
	}

	/** HTTP_PUT: Update the details of the book author with the provided details
   * @param int $id
   * @param string $new_name
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_put() {
		$author_id = $this->input->get('id');
		$new_name = $this->input->get('new_name');

		$data = array();
    $new_name && $data['name'] = $new_name;

		try {
			$result = $this->Author->update($author_id, $data);
		}
		catch(AuthorIdRequiredException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(AuthorDoesNotExistException $ex) {
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

		$success_response = json_encode(array('message' => 'Changed the author details successfully!'));
		$this->response($success_response, RestController::HTTP_OK);
	}

	/** HTTP_DELETE: Delete the book author for the given id
   * @param int $id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_delete() {
		$author_id = $this->input->get('id');

		try {
			$result = $this->Author->delete($author_id);
		}
		catch(AuthorIdRequiredException $ex) {
			$error_response = json_encode(array('message' => $ex->errorMessage()));
      $this->response($error_response, RestController::HTTP_BAD_REQUEST);
		}
		catch(AuthorDoesNotExistException $ex) {
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

		$success_response = json_encode(array('message' => 'Deleted the author successfully!'));
		$this->response($success_response, RestController::HTTP_OK);
	}
}
