<?php
require_once(APPPATH . '/exceptions/UserExceptions.php'); 
use StorySphere\CustomException\UserHandleTakenException;
use StorySphere\CustomException\UserHandleRequiredException;
use StorySphere\CustomException\UserHandleDoesNotExistException;

class User extends CI_Model {
  private $table = 'user';

  public function __construct() {
    $this->load->database();
  }

  public function get_user($user_handle) {
    if(!$user_handle) {
      throw new UserHandleRequiredException();
    }
    if(!$this->is_user_exist($user_handle)) {
      throw new UserHandleDoesNotExistException();
    }

    $this->db->select('user_handle, first_name, middle_name, last_name, email');
    $this->db->from($this->table);
    $this->db->where('user_handle', $user_handle);

    $query = $this->db->get();
    $result = $query->row_array();

    return $result;
  }

  public function create($user_handle, $first_name, $middle_name, $last_name, $email) {
    if(!($user_handle && $first_name && $last_name && $email) ) {
      throw new InvalidArgumentException();
    }
    if($this->is_user_exist($user_handle)) {
      throw new UserHandleTakenException();
    }

    $data = array(
      'user_handle' => $user_handle,
      'first_name' => $first_name,
      'last_name' => $last_name,
      'email' => $email
    );
    $middle_name && $data['middle_name'] = $middle_name;
    $this->db->insert($this->table, $data);

    return $this->db->affected_rows();
  }

  public function update($user_handle, $first_name, $middle_name, $last_name, $email) {
    if(!$user_handle) {
      throw new UserHandleRequiredException();
    }
    if(!$this->is_user_exist($user_handle)) {
      throw new UserHandleDoesNotExistException();
    }

    $data = array();
    $first_name && $data['first_name'] = $first_name;
    $middle_name && $data['middle_name'] = $middle_name;
    $last_name && $data['last_name'] = $last_name;
    $email && $data['email'] = $email;

    if(count($data) <= 0) {
      throw new InvalidArgumentException();
    }

    $this->db->where('user_handle', $user_handle);
    $this->db->update($this->table, $data);

    return $this->db->affected_rows();
  }

  public function delete($user_handle) {
    if(!$user_handle) {
      throw new UserHandleRequiredException();
    }
    if(!$this->is_user_exist($user_handle)) {
      throw new UserHandleDoesNotExistException();
    }

    $this->db->where('user_handle', $user_handle);
    $this->db->delete($this->table);

    return $this->db->affected_rows();
  }

  private function is_user_exist($user_handle) {
    $this->db->from($this->table);
    $this->db->where('user_handle', $user_handle);
    $query = $this->db->get();
    
    return ($query->num_rows() > 0);
  }
}
