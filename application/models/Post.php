<?php
require_once(APPPATH . '/exceptions/PostExceptions.php');
use StorySphere\CustomException\PostIdRequiredException;
use StorySphere\CustomException\PostDoesNotExistException;

require_once(APPPATH . '/exceptions/UserExceptions.php');
use StorySphere\CustomException\UserHandleRequiredException;

class Post extends CI_Model {
  private $table = 'post';
  private $post_upvote_table = 'post_upvote';

  public function __construct() {
    $this->load->database();
  }

  /** Get the details of the post for the given post id
   * @param string $post_id
   * @return array Details of the post
   */
  public function get_post($post_id) {
    if(!$post_id) {
      throw new PostIdRequiredException();
    }
    if(!$this->is_valid_id($post_id)) {
      throw new PostDoesNotExistException();
    }

    $this->db->select('post_id, user_handle, content, image_url, time_stamp');
    $this->db->from($this->table);
    $this->db->where('post_id', $post_id);

    $query = $this->db->get();
    $result = $query->row_array();

    return $result;
  }

  /** Get the list of the posts for the given user handle
   * @param string $user_handle
   * @return array List the posts
   */
  public function get_user_posts($user_handle) {
    if(!$user_handle) {
      throw new UserHandleRequiredException();
    }

    $this->db->select('post_id, user_handle, content, image_url, time_stamp');
    $this->db->from($this->table);
    $this->db->where('user_handle', $user_handle);

    $query = $this->db->get();
    $result = $query->result_array();

    return $result;
  }

  /** Get the list of all posts
   * @return array List the posts
   */
  public function get_all_posts() {

    $this->db->select('post_id, user_handle, content, image_url, time_stamp');
    $this->db->from($this->table);

    $query = $this->db->get();
    $result = $query->result_array();

    return $result;
  }

  /** Create a new post with the provided details
   * @param string $user_handle
   * @param string $content
   * @param string $image_url
   * @return int ID of the created post
   */
  public function create($user_handle, $content, $image_url) {
    if(!$user_handle || !$content || !$image_url) {
      throw new InvalidArgumentException();
    }
    date_default_timezone_set("Asia/Colombo");
    $data = array(
      'user_handle' => $user_handle,
      'content' => $content,
      'image_url' => $image_url,
      'time_stamp' => date("Y-m-d H:i:s")
    );
    $this->db->insert($this->table, $data);

    return $this->db->insert_id();
  }

  /** Update the details of the post author with the provided details
   * @param string $post_id
   * @param array $data
   * @return int Number of rows affected by the SQL operation
   */
  public function update($post_id, $data) {
    if(!$post_id) {
      throw new PostIdRequiredException();
    }
    if(!$this->is_valid_id($post_id)) {
      throw new PostDoesNotExistException();
    }
    if(count($data) <= 0) {
      throw new InvalidArgumentException();
    }
    $this->db->where('post_id', $post_id);
    $this->db->update($this->table, $data);

    return $this->db->affected_rows();
  }

  /** Delete the post for the given id
   * @param int $post_id
   * @return int Number of rows affected by the SQL operation
   */
  public function delete($post_id) {
    if(!$post_id) {
      throw new PostIdRequiredException();
    }
    if(!$this->is_valid_id($post_id)) {
      throw new PostDoesNotExistException();
    }

    $this->db->where('post_id', $post_id);
    $this->db->delete($this->table);

    return $this->db->affected_rows();
  }

  /** Get the upvotes for the post with the provided post ID
   * @param string $post_id
   * @return array The list of upvotes
   */
  public function get_upvotes($post_id) {
    if(!$post_id) {
      throw new InvalidArgumentException();
    }
    $this->db->select('post_id, user_handle');
    $this->db->from($this->post_upvote_table);
    $this->db->where('post_id', $post_id);

    $query = $this->db->get();
    $result = $query->result_array();

    return $result;
  }

  /** Get all upvotes
   * @return array The list of upvotes
   */
  public function get_all_upvotes() {
    $this->db->select('post_id, user_handle');
    $this->db->from($this->post_upvote_table);

    $query = $this->db->get();
    $result = $query->result_array();

    return $result;
  }

  /** Upvote the post with the provided user handle
   * @param string $post_id
   * @param string $user_handle
   * @return int Number of rows affected by the SQL operation
   */
  public function upvote($post_id, $user_handle) {
    if(!$post_id || !$user_handle) {
      throw new InvalidArgumentException();
    }
    $data = array(
      'post_id' => $post_id,
      'user_handle' => $user_handle
    );
    $this->db->insert($this->post_upvote_table, $data);

    return $this->db->affected_rows();
  }

  /** Downvote the post with the provided user handle
   * @param int $post_id
   * @param int $user_handle
   * @return int Number of rows affected by the SQL operation
   */
  public function downvote($post_id, $user_handle) {
    if(!$post_id || !$user_handle) {
      throw new InvalidArgumentException();
    }

    $this->db->where('post_id', $post_id);
    $this->db->where('user_handle', $user_handle);
    $this->db->delete($this->post_upvote_table);

    return $this->db->affected_rows();
  }

  /** Checks whether the post for the given id exists
   * @param string $post_id
   * @return boolean 'True' if the post exists, and 'False' if isn't
   */
  private function is_valid_id($post_id) {
    $this->db->from($this->table);
    $this->db->where('post_id', $post_id);
    $query = $this->db->get();
    
    return ($query->num_rows() > 0);
  }
}
