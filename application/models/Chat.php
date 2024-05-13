<?php
require_once(APPPATH . '/exceptions/ChatExceptions.php');
use StorySphere\CustomException\ChatIdRequiredException;
use StorySphere\CustomException\ChatDoesNotExistException;

require_once(APPPATH . '/exceptions/UserExceptions.php');
use StorySphere\CustomException\UserHandleRequiredException;

class Chat extends CI_Model {
  private $table = 'chat';
  private $chat_user_table = 'chat_user';

  public function __construct() {
    $this->load->database();
  }

  /** Get the details of the chat for the given chat id
   * @param int $chat_id
   * @return array Details of the chat
   */
  public function get_chat($chat_id) {
    if(!$chat_id) {
      throw new ChatIdRequiredException();
    }
    if(!$this->is_valid_id($chat_id)) {
      throw new ChatDoesNotExistException();
    }

    $this->db->select('chat_id, created_date');
    $this->db->from($this->table);
    $this->db->where('chat_id', $chat_id);

    $query = $this->db->get();
    $result = $query->row_array();

    return $result;
  }

  /** Get a list of the chats for the given user handle
   * @param string $user_handle
   * @return array List of chats
   */
  public function get_user_chats($user_handle) {
    if(!$user_handle) {
      throw new UserHandleRequiredException();
    }

    $this->db->select("{$this->table}.chat_id, {$this->table}.created_date");
    $this->db->from($this->table);
    $this->db->join($this->chat_user_table, "{$this->table}.chat_id = {$this->chat_user_table}.chat_id");
    $this->db->where("{$this->chat_user_table}.user_handle", $user_handle);

    $query = $this->db->get();
    $result = $query->result_array();

    return $result;
  }

  /** Create a new chat with given users
   * @param array $users
   * @return int ID of the created chat
   */
  public function create($users) {
    if(count($users) <= 0) {
      throw new InvalidArgumentException();
    }

    date_default_timezone_set("Asia/Colombo");
    $data = array(
      'created_date' => date("Y-m-d H:i:s"),
    );
    $this->db->insert($this->table, $data);
    $chat_id = $this->db->insert_id();
    $this->add_users($chat_id, $users);

    return $chat_id;
  }

  /** Delete the chat and chat users for the given id
   * @param int $id
   * @return int Number of rows affected by the SQL operation
   */
  public function delete($id) {
    if(!$id) {
      throw new ChatIdRequiredException();
    }
    if(!$this->is_valid_id($id)) {
      throw new ChatDoesNotExistException();
    }
    $this->delete_users($id);
    $this->db->where('chat_id', $id);
    $this->db->delete($this->table);

    return $this->db->affected_rows();
  }

  /** Add the users to the corresponding chat
   * @param int $chat_id
   * @param array $users
   * @return int Number of rows affected by the SQL operation
   */
  public function add_users($chat_id, $users) {
    if(!$chat_id) {
      throw new ChatIdRequiredException();
    }
    if(!$this->is_valid_id($chat_id)) {
      throw new ChatDoesNotExistException();
    }
    $data = array();
    foreach ($users as $user) {
      $data[] = array(
        "chat_id" => $chat_id,
        "user_handle" => $user
      );
    }
    $this->db->insert_batch($this->chat_user_table, $data);

    return $this->db->affected_rows();
  }

  /** Delete the users to the corresponding chat
   * @param int $chat_id
   * @return int Number of rows affected by the SQL operation
   */
  public function delete_users($chat_id) {
    $this->db->where('chat_id', $chat_id);
    $this->db->delete($this->chat_user_table);

    return $this->db->affected_rows();
  }

  /** Checks whether the chat for the given id exists
   * @param int $chat_id
   * @return boolean 'True' if the chat exists, and 'False' if isn't
   */
  private function is_valid_id($chat_id) {
    $this->db->from($this->table);
    $this->db->where('chat_id', $chat_id);
    $query = $this->db->get();
    
    return ($query->num_rows() > 0);
  }
}
