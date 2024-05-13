<?php
require_once(APPPATH . '/exceptions/BookExceptions.php'); 
use StorySphere\CustomException\BookIdRequiredException;
use StorySphere\CustomException\BookDoesNotExistException;

class Book extends CI_Model {
  private $table = 'book';

  public function __construct() {
    $this->load->database();
  }

  /** Get the details of the book for the given book id
   * @param string $book_id
   * @return array Details of the book
   */
  public function get_book($book_id) {
    if(!$book_id) {
      throw new BookIdRequiredException();
    }
    if(!$this->is_valid_id($book_id)) {
      throw new BookDoesNotExistException();
    }

    $this->db->select('title, author_id, year');
    $this->db->from($this->table);
    $this->db->where('book_id', $book_id);

    $query = $this->db->get();
    $result = $query->row_array();

    return $result;
  }

  /** Create a new book with the provided details
   * @param string $book_title
   * @param string $author_id
   * @param int $year
   * @return int ID of the created book
   */
  public function create($book_title, $author_id, $year) {
    if(!$book_title || !$author_id || !$year) {
      throw new InvalidArgumentException();
    }
    $data = array(
      'title' => $book_title,
      'author_id' => $author_id,
      'year' => $year
    );
    $this->db->insert($this->table, $data);

    return $this->db->insert_id();
  }

  /** Update the details of the book with the provided details
   * @param string $id
   * @param array $data
   * @return int Number of rows affected by the SQL operation
   */
  public function update($id, $data) {
    if(!$id) {
      throw new BookIdRequiredException();
    }
    if(!$this->is_valid_id($id)) {
      throw new BookDoesNotExistException();
    }
    if(count($data) <= 0) {
      throw new InvalidArgumentException();
    }
    $this->db->where('book_id', $id);
    $this->db->update($this->table, $data);

    return $this->db->affected_rows();
  }

  /** Delete the book for the given id
   * @param int $id
   * @return int Number of rows affected by the SQL operation
   */
  public function delete($id) {
    if(!$id) {
      throw new BookIdRequiredException();
    }
    if(!$this->is_valid_id($id)) {
      throw new BookDoesNotExistException();
    }
    $this->db->where('book_id', $id);
    $this->db->delete($this->table);

    return $this->db->affected_rows();
  }

  /** Checks whether the book for the given id exists
   * @param string $book_id
   * @return boolean 'True' if the book exists, and 'False' if isn't
   */
  private function is_valid_id($book_id) {
    $this->db->from($this->table);
    $this->db->where('book_id', $book_id);
    $query = $this->db->get();
    
    return ($query->num_rows() > 0);
  }
}
