<?
class Model {

  protected $db;

  public function __construct() {
    $this->db = Database::getInstance();
  }

  protected function validate($data, $rules = false) {

    if ($rules === false) {
      return true;
    }

    $errors = array();
    foreach ($rules as $rule) {
      $field   = idx($rule, 'field');
      $value   = idx($data, $field);
      $message = idx($rule, 'message', 'Error, please try again');

      $required = idx($rule, 'required', false);
      if ($required !== false) {
        if ($value === '') {
          $errors[$field] = 'This is a required field.';
          continue;
        }
      }

      $min_length = idx($rule, 'minlength', false);
      if ($min_length !== false) {
        if (strlen($value) < $min_length) {
          $errors[$field] = 'You must enter at least ' . $min_length . ' characters';
          continue;
        }
      }

      $max_length = idx($rule, 'maxlength', false);
      if ($max_length !== false) {
        if (strlen($value) > $max_length) {
          $errors[$field] = 'You must enter at most ' . $max_length . ' characters';
          continue;
        }
      }

      $type = idx($rule, 'type');
      switch ($type) {
        case 'alphanumeric' :
          if (!preg_match('/^[A-Za-z][A-Za-z0-9]*(?:[A-Za-z0-9]+)*$/', $value)) {
            $errors[$field] = $message;
            continue;
          }
          break;
        case 'alphanumeric_extra' :
          if (!preg_match('/^[A-Za-z][A-Za-z0-9]*(?:[-_ ]*[A-Za-z0-9]+)*$/', $value)) {
            $errors[$field] = $message;
            continue;
          }
          break;
        case 'password' :
          if (!preg_match('/[a-zA-Z0-9._^%$#!~@,-]+/', $value)) {
            $errors[$field] = $message;
            continue;
          }
          break;
        case 'email' :
          if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$field] = $message;
            continue;
          }
          break;
        case 'matches' :
          $matches = idx($rule, 'matches');
          if ($value != idx($data, $matches)) {
            $errors[$field] = $message;
            continue;
          }
          break;
        case 'required' :
          if ($value === '') {
            $errors[$field] = $message;
            continue;
          }
          break;
      }
    }

    if (!$errors) {
      return true;
    } else {
      return $errors;
    }

  }

}