<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require_once(APPPATH . '/exceptions/PostExceptions.php'); 
use StorySphere\CustomException\PostIdRequiredException;
use StorySphere\CustomException\PostDoesNotExistException;

require_once(APPPATH . '/exceptions/UserExceptions.php');
use StorySphere\CustomException\UserHandleRequiredException;

class Posts extends RestController {

	function __construct() {
		parent::__construct();

		$this->load->model('Post');
		$this->load->model('User');
	}

	/** HTTP_GET: Get the details of the post for the given post id
   * @param int $id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_get() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$post_id = $this->input->get('id');
		
				try {
					$post_record = $this->Post->get_post($post_id);
				}
				catch(PostIdRequiredException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(PostDoesNotExistException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
				
				$success_response = json_encode($post_record);
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

	/** HTTP_GET: Get all posts for the given user handle
   * @param int $user_handle
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
					$post_records = $this->Post->get_user_posts($user_handle);
				}
				catch(UserHandleRequiredException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
				
				$success_response = json_encode($post_records);
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

	/** HTTP_GET: Get all posts
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function all_get() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				// Token is valid, proceed with the request
				try {
					$post_records = $this->Post->get_all_posts();
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
				
				$success_response = json_encode($post_records);
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

	/** HTTP_POST: Create a post author with the provided details
   * @param string $user_handle
   * @param string $content
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
				$content = $this->input->get('content');
				$image_url = $this->input->get('image_url');
				
				try {
					$post_id = $this->Post->create($user_handle, $content, $image_url);
				}
				catch(InvalidArgumentException $ex) {
					$error_response = json_encode(array('message' => 'Invlid or Missing Argument!'));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				if(!$post_id) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				$success_response = json_encode(array('created_post_id' => $post_id));
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

	/** HTTP_PUT: Update the details of the post with the provided details
   * @param int $id
   * @param string $content
   * @param string $image_url
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_put() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$post_id = $this->input->get('id');
				$title = $this->input->get('title');
				$content = $this->input->get('content');
				$image_url = $this->input->get('image_url');
		
				$data = array();
				$title && $data['title'] = $title;
				$content && $data['content'] = $content;
				$image_url && $data['image_url'] = $image_url;
		
				try {
					$result = $this->Post->update($post_id, $data);
				}
				catch(PostIdRequiredException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(PostDoesNotExistException $ex) {
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
		
				$success_response = json_encode(array('message' => 'Changed the post details successfully!'));
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

	/** HTTP_DELETE: Delete the post for the given id
   * @param int $id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_delete() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$post_id = $this->input->get('id');
		
				try {
					$result = $this->Post->delete($post_id);
				}
				catch(PostIdRequiredException $ex) {
					$error_response = json_encode(array('message' => $ex->errorMessage()));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(PostDoesNotExistException $ex) {
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
		
				$success_response = json_encode(array('message' => 'Deleted the post successfully!'));
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

	/** HTTP_GET: Upvotes for the post with the provided post ID
   * @param int $post_id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function upvote_get() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$post_id = $this->input->get('post_id');
				
				try {
					$post_upvotes = $this->Post->get_upvotes($post_id);
				}
				catch(InvalidArgumentException $ex) {
					$error_response = json_encode(array('message' => 'Invlid or Missing Argument!'));
					$this->response($error_response, RestController::HTTP_BAD_REQUEST);
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				if(!$post_upvotes) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				$success_response = json_encode($post_upvotes);
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

	/** HTTP_GET: All Upvotes
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function allupvotes_get() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				try {
					$post_upvotes = $this->Post->get_all_upvotes();
				}
				catch(Exception $ex) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				if(!$post_upvotes) {
					$error_response = json_encode(array('message' => 'Oops! Something went wrong :/'));
					$this->response($error_response, RestController::HTTP_INTERNAL_ERROR);
				}
		
				$success_response = json_encode($post_upvotes);
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

	/** HTTP_POST: Upvote the post with the provided user handle
   * @param int $post_id
   * @param string $user_handle
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function upvote_post() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$post_id = $this->input->get('post_id');
				$user_handle = $this->input->get('user_handle');
				
				try {
					$result = $this->Post->upvote($post_id, $user_handle);
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
		
				$success_response = json_encode(array('message' => 'Upvoted the post successfully!'));
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

	/** HTTP_DELETE: Downvote the post with the provided user handle
   * @param int $post_id
   * @param string $user_handle
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function downvote_delete() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$post_id = $this->input->get('post_id');
				$user_handle = $this->input->get('user_handle');
		
				try {
					$result = $this->Post->downvote($post_id, $user_handle);
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
		
				$success_response = json_encode(array('message' => 'Downvoted the post successfully!'));
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

	/** HTTP_DELETE: Togglevote the post with the provided user handle
   * @param int $post_id
   * @param string $user_handle
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function togglevote_post() {
		$headers = $this->input->request_headers();
		if (isset($headers['Authorization'])) {
			$auth_header = $headers['Authorization'];
			$auth_token = preg_replace('/^Bearer\s*/', '', $auth_header);
			
			if ($this->User->is_valid_token($auth_token)) {
				$post_id = $this->input->get('post_id');
				$user_handle = $this->input->get('user_handle');
		
				try {
					$result = $this->Post->togglevote($post_id, $user_handle);
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
		
				$success_response = json_encode(array('message' => 'Togglevoted the post successfully!'));
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
