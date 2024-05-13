<?php
require_once(APPPATH . '/exceptions/CommentExceptions.php');
use StorySphere\CustomException\CommentIdRequiredException;
use StorySphere\CustomException\CommentDoesNotExistException;

require_once(APPPATH . '/exceptions/PostExceptions.php');
use StorySphere\CustomException\PostIdRequiredException;

class Comment extends CI_Model {
  private $table = 'comment';
  private $comment_upvote_table = 'comment_upvote';

  public function __construct() {
    $this->load->database();
  }

  /** Get the details of the comment for the given comment id
   * @param string $comment_id
   * @return array Details of the comment
   */
  public function get_comment($comment_id) {
    if(!$comment_id) {
      throw new CommentIdRequiredException();
    }
    if(!$this->is_valid_id($comment_id)) {
      throw new CommentDoesNotExistException();
    }

    $this->db->select('comment_id, post_id, comment_body, user_handle, time_stamp');
    $this->db->from($this->table);
    $this->db->where('comment_id', $comment_id);

    $query = $this->db->get();
    $result = $query->row_array();

    return $result;
  }

  /** Get the list of the comments for the given post id
   * @param int $post_id
   * @return array List of comments
   */
  public function get_post_comments($post_id) {
    if(!$post_id) {
      throw new PostIdRequiredException();
    }

    $this->db->select('comment_id, post_id, comment_body, user_handle, time_stamp');
    $this->db->from($this->table);
    $this->db->where('post_id', $post_id);
    

    $query = $this->db->get();
    $result = $query->result_array();

    return $result;
  }

  /** Create a new comment with the provided details
   * @param int $post_id
   * @param string $comment_body
   * @param string $user_handle
   * @return int ID of the created comment
   */
  public function create($post_id, $comment_body, $user_handle) {
    if(!$post_id || !$comment_body || !$user_handle) {
      throw new InvalidArgumentException();
    }
    date_default_timezone_set("Asia/Colombo");
    $data = array(
      'post_id' => $post_id,
      'comment_body' => $comment_body,
      'user_handle' => $user_handle,
      'time_stamp' => date("Y-m-d H:i:s")
    );
    $this->db->insert($this->table, $data);

    return $this->db->insert_id();
  }

  /** Update the content of the comment body
   * @param int $comment_id
   * @param string $comment_body
   * @return int Number of rows affected by the SQL operation
   */
  public function update($comment_id, $comment_body) {
    if(!$comment_id || !$comment_body) {
      throw new InvalidArgumentException();
    }
    if(!$this->is_valid_id($comment_id)) {
      throw new CommentDoesNotExistException();
    }
    $data = array(
      'comment_id' => $comment_id,
      'comment_body' => $comment_body
    );

    $this->db->where('comment_id', $comment_id);
    $this->db->update($this->table, $data);

    return $this->db->affected_rows();
  }

  /** Delete the comment for the given id
   * @param string $comment_id
   * @return int Number of rows affected by the SQL operation
   */
  public function delete($comment_id) {
    if(!$comment_id) {
      throw new CommentIdRequiredException();
    }
    if(!$this->is_valid_id($comment_id)) {
      throw new CommentDoesNotExistException();
    }

    $this->db->where('comment_id', $comment_id);
    $this->db->delete($this->table);

    return $this->db->affected_rows();
  }

  /** Upvote the comment with the provided user handle
   * @param string $comment_id
   * @param string $user_handle
   * @return int Number of rows affected by the SQL operation
   */
  public function upvote($comment_id, $user_handle) {
    if(!$comment_id || !$user_handle) {
      throw new InvalidArgumentException();
    }
    $data = array(
      'comment_id' => $comment_id,
      'user_handle' => $user_handle
    );
    $this->db->insert($this->comment_upvote_table, $data);

    return $this->db->affected_rows();
  }

  /** Downvote the comment with the provided user handle
   * @param int $comment_id
   * @param int $user_handle
   * @return int Number of rows affected by the SQL operation
   */
  public function downvote($comment_id, $user_handle) {
    if(!$comment_id || !$user_handle) {
      throw new InvalidArgumentException();
    }

    $this->db->where('comment_id', $comment_id);
    $this->db->where('user_handle', $user_handle);
    $this->db->delete($this->comment_upvote_table);

    return $this->db->affected_rows();
  }

  /** Checks whether the comment for the given id exists
   * @param string $comment_id
   * @return boolean 'True' if the comment exists, and 'False' if isn't
   */
  private function is_valid_id($comment_id) {
    $this->db->from($this->table);
    $this->db->where('comment_id', $comment_id);
    $query = $this->db->get();
    
    return ($query->num_rows() > 0);
  }
}
