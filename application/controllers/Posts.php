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
	}

	/** HTTP_GET: Get the details of the post for the given post id
   * @param int $id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_get() {
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

	/** HTTP_GET: Get all posts for the given user handle
   * @param int $user_handle
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function user_get() {
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

	/** HTTP_GET: Get all posts
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function all_get() {

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

	/** HTTP_POST: Create a post author with the provided details
   * @param string $user_handle
   * @param string $content
   * @param string $image_url
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_post() {
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

	/** HTTP_PUT: Update the details of the post with the provided details
   * @param int $id
   * @param string $content
   * @param string $image_url
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_put() {
		$post_id = $this->input->get('id');
		$content = $this->input->get('content');
		$image_url = $this->input->get('image_url');

		$data = array();
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

	/** HTTP_DELETE: Delete the post for the given id
   * @param int $id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function index_delete() {
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

	/** HTTP_GET: Upvotes for the post with the provided post ID
   * @param int $post_id
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function upvote_get() {
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

	/** HTTP_GET: All Upvotes
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function allupvotes_get() {
		
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

	/** HTTP_POST: Upvote the post with the provided user handle
   * @param int $post_id
   * @param string $user_handle
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function upvote_post() {
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

	/** HTTP_DELETE: Downvote the post with the provided user handle
   * @param int $post_id
   * @param string $user_handle
   * @return HTTP_Response The HTTP status code according to the result and the data body
   */
	public function downvote_delete() {
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
}
