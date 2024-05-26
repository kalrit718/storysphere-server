<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require_once(APPPATH . '/exceptions/BookExceptions.php'); 
use StorySphere\CustomException\BookIdRequiredException;
use StorySphere\CustomException\BookDoesNotExistException;

class Books extends RestController {

	function __construct() {
		parent::__construct();

		$this->load->model('Book');
    $this->load->model('Author');
    $this->load->model('User');
	}

	/** HTTP_GET: Get the details of the book for the given book id
   * @param int $id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_get() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$book_id = $this->input->get('id');
		
				try {
					$book_record = $this->Book->get_book($book_id);
				}
				catch(BookIdRequiredException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(BookDoesNotExistException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				$author_record = $this->Author->get_author($book_record['author_id']);
		
				$success_response = json_encode(
					array(
						'book_id' => $book_id, 
						'title' => $book_record['title'],
						'author' => $author_record['name'],
						'year' => $book_record['year']
					)
				);
				$this->response($success_response, 200);
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

	/** HTTP_POST: Create a new book with the provided details
   * @param string $title
   * @param string $author_id
   * @param int $year
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
  public function index_post() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$book_title = $this->input->get('title');
				$author_id = $this->input->get('author_id');
				$year = $this->input->get('year');
		
				try {
					$book_id = $this->Book->create($book_title, $author_id, $year);
				}
				catch(InvalidArgumentException $ex) {
					$error_response = json_encode(array('message' => 'Invlid or Missing Argument!'));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				if(!$book_id) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				$success_response = json_encode(array('created_book_id' => $book_id));
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

	/** HTTP_PUT: Update the details of the book with the provided details
   * @param int $id
   * @param string $title
   * @param int $author_id
   * @param int $year
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
  public function index_put() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$book_id = $this->input->get('id');
				$book_title = $this->input->get('title');
				$author_id = $this->input->get('author_id');
				$year = $this->input->get('year');
		
				$data = array();
				$book_title && $data['title'] = $book_title;
				$author_id && $data['author_id'] = $author_id;
				$year && $data['year'] = $year;
		
				try {
					$result = $this->Book->update($book_id, $data);
				}
				catch(BookIdRequiredException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(BookDoesNotExistException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(InvalidArgumentException $ex) {
					$error_response = json_encode(array('message' => 'Invlid or Missing Argument!'));
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
		
				$success_response = json_encode(array('message' => 'Changed the book details successfully!'));
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
				$book_id = $this->input->get('id');
				
				try {
					$result = $this->Book->delete($book_id);
				}
				catch(BookIdRequiredException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(BookDoesNotExistException $ex) {
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
		
				$success_response = json_encode(array('message' => 'Deleted the book successfully!'));
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
}
