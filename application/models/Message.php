<?php
require_once(APPPATH . '/exceptions/MessageExceptions.php');
use StorySphere\CustomException\MessageIdRequiredException;
use StorySphere\CustomException\MessageDoesNotExistException;

require_once(APPPATH . '/exceptions/ChatExceptions.php');
use StorySphere\CustomException\ChatIdRequiredException;

class Message extends CI_Model {
  private $table = 'message';

  public function __construct() {
    $this->load->database();
  }

  /** Get the details of the message for the given message id
   * @param int $message_id
   * @return array Details of the message
   */
  public function get_message($message_id) {
    if(!$message_id) {
      throw new MessageIdRequiredException();
    }
    if(!$this->is_valid_id($message_id)) {
      throw new MessageDoesNotExistException();
    }

    $this->db->select('message_id, chat_id, message_body, sender_handle, time_stamp');
    $this->db->from($this->table);
    $this->db->where('message_id', $message_id);

    $query = $this->db->get();
    $result = $query->row_array();

    return $result;
  }

  /** Get a list of the messages for the given chat id
   * @param int $chat_id
   * @return array List of messages
   */
  public function get_chat_messages($chat_id) {
    if(!$chat_id) {
      throw new ChatIdRequiredException();
    }

    $this->db->select('message_id, chat_id, message_body, sender_handle, time_stamp');
    $this->db->from($this->table);
    $this->db->where('chat_id', $chat_id);

    $query = $this->db->get();
    $result = $query->result_array();

    return $result;
  }

  /** Create a new message with the provided details
   * @param int $chat_id
   * @param string $message_body
   * @param string $sender_handle
   * @return int ID of the created message
   */
  public function create($chat_id, $message_body, $sender_handle) {
    if(!$chat_id || !$message_body || !$sender_handle) {
      throw new InvalidArgumentException();
    }
    date_default_timezone_set("Asia/Colombo");
    $data = array(
      'chat_id' => $chat_id,
      'message_body' => $message_body,
      'sender_handle' => $sender_handle,
      'time_stamp' => date("Y-m-d H:i:s") 
    );
    $this->db->insert($this->table, $data);

    return $this->db->insert_id();
  }

  /** Checks whether the message for the given id exists
   * @param string $message_id
   * @return boolean 'True' if the message exists, and 'False' if isn't
   */
  private function is_valid_id($message_id) {
    $this->db->from($this->table);
    $this->db->where('message_id', $message_id);
    $query = $this->db->get();
    
    return ($query->num_rows() > 0);
  }
}
