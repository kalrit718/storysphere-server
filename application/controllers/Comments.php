<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require_once(APPPATH . '/exceptions/CommentExceptions.php'); 
use StorySphere\CustomException\CommentIdRequiredException;
use StorySphere\CustomException\CommentDoesNotExistException;

require_once(APPPATH . '/exceptions/PostExceptions.php');
use StorySphere\CustomException\PostIdRequiredException;

class Comments extends RestController {

	function __construct() {
		parent::__construct();

		$this->load->model('Comment');
		$this->load->model('User');
	}

	/** HTTP_GET: Get the details of the comment for the given comment id
   * @param int $id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_get() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$comment_id = $this->input->get('id');
		
				try {
					$comment_record = $this->Comment->get_comment($comment_id);
				}
				catch(CommentIdRequiredException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(CommentDoesNotExistException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
				
				$success_response = json_encode($comment_record);
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

	/** HTTP_GET: Get all comments for the given post
   * @param int $post_id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function post_get() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$post_id = $this->input->get('post_id');
		
				try {
					$comment_records = $this->Comment->get_post_comments($post_id);
				}
				catch(PostIdRequiredException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
				
				$success_response = json_encode($comment_records);
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

	/** HTTP_GET: Get all comments
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function all_get() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				try {
					$comment_records = $this->Comment->get_all_comments();
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
				
				$success_response = json_encode($comment_records);
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

	/** HTTP_POST: Create a new comment with the provided details
   * @param int $post_id
   * @param string $comment_body
   * @param string $user_handle
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_post() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$post_id = $this->input->get('post_id');
				$comment_body = $this->input->get('comment_body');
				$user_handle = $this->input->get('user_handle');
				
				try {
					$comment_id = $this->Comment->create($post_id, $comment_body, $user_handle);
				}
				catch(InvalidArgumentException $ex) {
					$error_response = json_encode(array('message' => 'Invlid or Missing Argument!'));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				if(!$comment_id) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				$success_response = json_encode(array('created_comment_id' => $comment_id));
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

	/** HTTP_PUT: Update the conent of the comment body
   * @param int $id
   * @param string $comment_body
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_put() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$comment_id = $this->input->get('id');
				$comment_body = $this->input->get('comment_body');
		
				try {
					$result = $this->Comment->update($comment_id, $comment_body);
				}
				catch(InvalidArgumentException $ex) {
					$error_response = json_encode(array('message' => 'Invlid or Missing Argument!'));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(CommentDoesNotExistException $ex) {
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
		
				$success_response = json_encode(array('message' => 'Updated the comment body successfully!'));
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

	/** HTTP_DELETE: Delete the comment for the given id
   * @param int $id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_delete() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$comment_id = $this->input->get('id');
		
				try {
					$result = $this->Comment->delete($comment_id);
				}
				catch(CommentIdRequiredException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(CommentDoesNotExistException $ex) {
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
		
				$success_response = json_encode(array('message' => 'Deleted the comment successfully!'));
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

	/** HTTP_POST: Upvote the comment with the provided user handle
   * @param int $comment_id
   * @param string $user_handle
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function upvote_post() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$comment_id = $this->input->get('comment_id');
				$user_handle = $this->input->get('user_handle');
				
				try {
					$result = $this->Comment->upvote($comment_id, $user_handle);
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
		
				$success_response = json_encode(array('message' => 'Upvoted the comment successfully!'));
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

	/** HTTP_DELETE: Downvote the comment with the provided user handle
   * @param int $comment_id
   * @param string $user_handle
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function downvote_delete() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$comment_id = $this->input->get('comment_id');
				$user_handle = $this->input->get('user_handle');
		
				try {
					$result = $this->Comment->downvote($comment_id, $user_handle);
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
		
				$success_response = json_encode(array('message' => 'Downvoted the comment successfully!'));
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

	/** HTTP_POST: Togglevote the comment with the provided user handle
   * @param int $comment_id
   * @param string $user_handle
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function togglevote_post() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$comment_id = $this->input->get('comment_id');
				$user_handle = $this->input->get('user_handle');
		
				try {
					$result = $this->Comment->togglevote($comment_id, $user_handle);
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
		
				$success_response = json_encode(array('message' => 'Togglevoted the comment successfully!'));
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
