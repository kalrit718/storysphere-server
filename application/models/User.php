<?php
require_once(APPPATH . '/exceptions/UserExceptions.php'); 
use StorySphere\CustomException\UserHandleTakenException;
use StorySphere\CustomException\UserHandleRequiredException;
use StorySphere\CustomException\PasswordRequiredException;
use StorySphere\CustomException\UserHandleDoesNotExistException;
use StorySphere\CustomException\InvalidPasswordException;

include_once './vendor/autoload.php'; 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class User extends CI_Model {
  private $table = 'user';
  private $key = 'eyJhbGciOiJIUzI1NiJ9.ew0KICAibm9ubyI6ICJubyIsDQogICJ0aGFua3RoYW5rIjogInRoYW5rIiwNCiAgInlvdXlvdSI6ICd5b3UnDQp9.AwAwtd3Gh9uKOOkb2nrOpBG7zFAxnrLoj9C3-1yWSlk';

  public function __construct() {
    $this->load->database();
  }

  /** Get the details of the user for the given user handle
   * @param string $user_handle
   * @return array Details of the user
   */
  public function get_user($user_handle) {
    if(!$user_handle) {
      throw new UserHandleRequiredException();
    }
    if(!$this->is_user_exist($user_handle)) {
      throw new UserHandleDoesNotExistException();
    }

    $this->db->select('user_handle, first_name, middle_name, last_name, email, image_url');
    $this->db->from($this->table);
    $this->db->where('user_handle', $user_handle);

    $query = $this->db->get();
    $result = $query->row_array();

    return $result;
  }

  /** Create a new user with the provided details
   * @param string $user_handle
   * @param string $first_name
   * @param string $middle_name
   * @param string $last_name
   * @param string $email
   * @param string $image_url
   * @param string $password
   * @return int Number of rows affected by the SQL operation
   */
  public function create($user_handle, $first_name, $middle_name, $last_name, $email, $image_url, $password) {
    if(!($user_handle && $first_name && $last_name && $email && $image_url && $password) ) {
      throw new InvalidArgumentException();
    }
    if($this->is_user_exist($user_handle)) {
      throw new UserHandleTakenException();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $data = array(
      'user_handle' => $user_handle,
      'first_name' => $first_name,
      'last_name' => $last_name,
      'email' => $email,
      'password' => $password_hash
    );
    $middle_name && $data['middle_name'] = $middle_name;
    $image_url && $data['image_url'] = $image_url;
    $this->db->insert($this->table, $data);

    return $this->db->affected_rows();
  }

  /** Update the details of the user with the provided details
   * @param string $user_handle
   * @param string $first_name
   * @param string $middle_name
   * @param string $last_name
   * @param string $email
   * @param string $image_url
   * @param string $password
   * @return int Number of rows affected by the SQL operation
   */
  public function update($user_handle, $first_name, $middle_name, $last_name, $email, $image_url, $password) {
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
    $image_url && $data['image_url'] = $image_url;
    $password && $data['password'] = password_hash($password, PASSWORD_DEFAULT);

    if(count($data) <= 0) {
      throw new InvalidArgumentException();
    }

    $this->db->where('user_handle', $user_handle);
    $this->db->update($this->table, $data);

    return $this->db->affected_rows();
  }

  /** Delete the user for the given user handle
   * @param string $user_handle
   * @return int Number of rows affected by the SQL operation
   */
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

  /** Authenticate the user with provided credentials
   * @param string $user_handle
   * @param string $password
   * @return int Details of the authenticated user
   */
  public function authenticate($user_handle, $password) {
    if(!$user_handle) {
      throw new UserHandleRequiredException();
    }
    if(!$password) {
      throw new PasswordRequiredException();
    }
    if(!$this->is_user_exist($user_handle)) {
      throw new UserHandleDoesNotExistException();
    }

    $this->db->select('password');
    $this->db->from($this->table);
    $this->db->where('user_handle', $user_handle);

    $passhash_query = $this->db->get();
    $password_hash = $passhash_query->row_array()['password'];

    if (password_verify($password, $password_hash)) {
      $this->db->select('user_handle, first_name, middle_name, last_name, email, image_url');
      $this->db->from($this->table);
      $this->db->where('user_handle', $user_handle);

      $query = $this->db->get();
      $result = $query->row_array();

      if(count($result) > 0) {
        $payload = [
          'iss' => 'https://api.storysphere.com',
          'aud' => 'https://storysphere.com',
          'iat' => time() + 86400,
          'nbf' => time(),
          'data' => [
            'role' => 'user',
            'user_handle' => $result['user_handle']
          ]
        ];
        $jwt = JWT::encode($payload, $this->key, 'HS256');
        $result['auth_token'] = $jwt;
      }

      return $result;
    }

    throw new InvalidPasswordException();
  }

  public function is_valid_token($token) {
    try {
      $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
      if($this->is_user_exist($decoded->data->user_handle)) {
        return true;
      }
      return false;
    }
    catch(Exception $e) {
      return false;
    }
  }

  /** Checks whether the user for the given user handle exists
   * @param string $user_handle
   * @return boolean 'True' if the user exists, and 'False' if isn't
   */
  private function is_user_exist($user_handle) {
    $this->db->from($this->table);
    $this->db->where('user_handle', $user_handle);
    $query = $this->db->get();
    
    return ($query->num_rows() > 0);
  }
}
