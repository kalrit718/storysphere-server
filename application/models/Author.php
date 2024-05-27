<?php
require_once(APPPATH . '/exceptions/AuthorExceptions.php'); 
use StorySphere\CustomException\AuthorIdRequiredException;
use StorySphere\CustomException\AuthorDoesNotExistException;

class Author extends CI_Model {
  private $table = 'book_author';

  public function __construct() {
    $this->load->database();
  }

  /** Get the details of the author for the given book author id
   * @param string $author_id
   * @return array Details of the book author
   */
  public function get_author($author_id) {
    if(!$author_id) {
      throw new AuthorIdRequiredException();
    }
    if(!$this->is_valid_id($author_id)) {
      throw new AuthorDoesNotExistException();
    }

    $this->db->select('author_id, name');
    $this->db->from($this->table);
    $this->db->where('author_id', $author_id);

    $query = $this->db->get();
    $result = $query->row_array();

    return $result;
  }

  /** Create a new book author with the provided name
   * @param string $author_name
   * @return int ID of the created book author
   */
  public function create($author_name) {
    if(!$author_name) {
      throw new InvalidArgumentException();
    }
    $data = array(
      'name' => $author_name
    );
    $this->db->insert($this->table, $data);

    return $this->db->insert_id();
  }

  /** Update the details of the book author with the provided details
   * @param string $author_id
   * @param string $data
   * @return int Number of rows affected by the SQL operation
   */
  public function update($author_id, $data) {
    if(!$author_id) {
      throw new AuthorIdRequiredException();
    }
    if(!count($data)) {
      throw new InvalidArgumentException();
    }
    if(!$this->is_valid_id($author_id)) {
      throw new AuthorDoesNotExistException();
    }

    $this->db->where('author_id', $author_id);
    $this->db->update($this->table, $data);

    return $this->db->affected_rows();
  }

  /** Delete the book author for the given id
   * @param string $author_id
   * @return int Number of rows affected by the SQL operation
   */
  public function delete($author_id) {
    if(!$author_id) {
      throw new AuthorIdRequiredException();
    }
    if(!$this->is_valid_id($author_id)) {
      throw new AuthorDoesNotExistException();
    }

    $this->db->where('author_id', $author_id);
    $this->db->delete($this->table);

    return $this->db->affected_rows();
  }

  /** Checks whether the book author for the given id exists
   * @param string $author_id
   * @return boolean 'True' if the author exists, and 'False' if isn't
   */
  private function is_valid_id($author_id) {
    $this->db->from($this->table);
    $this->db->where('author_id', $author_id);
    $query = $this->db->get();
    
    return ($query->num_rows() > 0);
  }
}
