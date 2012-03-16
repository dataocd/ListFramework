<?php
namespace Core\Packages\User;
class User {
  # holds all users into an array if called.
  private $users = array();

  # holds the current user being requested/managed.
  private $user_info = array();

  private $required_user_info = array("username","password","email_address","full_name");
  private $valid_user_fields  = array("user_id", "username","password","email_address","full_name");

  private function getDuplicate($username, $email, $ignore_id = false) {
    if(true === $this->isDuplicateUsername($username, $ignore_id)) {
      return "Username (" . $username . " already exists!";
    }

    if(true === $this->isDuplicateEmail($email, $ignore_id)) {
      return "Email Address (" . $email . " already exists!";
    }

    return false;
  }

  private function isDuplicateUsername($username, $ignore_id = false) {
    $ignore_id = (intval($ignore_id) < 1 ? false : intval($ignore_id));
    $user_exists = false;
    // >>>TEMP UNTIL DB STUFF<<<
    $this->getAll();
    foreach($this->users as $user) {
      if($ignore_id != false && $ignore_id == $user['user_id']) continue;

      if(strtolower($user['username']) == strtolower($username)) {
        $user_exists = true;
        break;
      }
    }

    return $user_exists;
  }

  private function isDuplicateEmail($email, $ignore_id = false) {
    $ignore_id = (intval($ignore_id) < 1 ? false: intval($ignore_id));
    $email_exists = false;
    // >>>TEMP UNTIL DB STUFF<<<
    $this->getAll();
    foreach($this->users as $user) {
      if($ignore_id != false && $ignore_id == $user['user_id']) continue;
      if(strtolower($user['email_address']) == strtolower($email)) {
        $email_exists = true;
        break;
      }
    }
    return $email_exists;
  }

  public function __construct($method = null, $args = null) {
    if(isset($method)) {
      if(method_exists($this, $method)) {
        return call_user_func_array(array($this, $method), $args);
      }
    }
  }

  /**
   * Gets a user by the specified id.
   * @param  integer $id
   * @return mixed   $user_info
   * @throws \Exception 
   */
  public function get($id) {
    $id = intval($id);
    # Not a valid user id eh?  This isn't an error though, so no exception...this time...
    if($id < 0) {
      # don't reset the user_info array though... just in case it was a mistake.  just return array.
      trigger_error('ID passed into ' . __FUNCTION__ . ' was 0.  Blank array returned.', \E_USER_WARNING);
      return array();
    }

    # Make sure we aren't requesting the same user_id as last time...
    if(!empty($this->user_info) && $this->user_info['user_id'] == intval($id)) {
      return $this->user_info;
    }

    # This is a new user request.  Get the info.
    // >>>BEGIN TEMP UNTIL DB STUFF<<<
    $this->getAll();
    if(isset($this->users[$id])) {
      $this->user_info = $this->users[$id];
    }
    // >>>END TEMP UNTIL DB STUFF<<<

    # if we made it here, then we have nothing to show.
    return $this->user_info;
  }

  /**
   * Gets all users
   * @return mixed   $user_info
   * @throws \Exception 
   */
  public function getAll() {
    if(empty($this->users)) {
      // load all of our users, and store them so we can use them later.
      // >>>TEMP UNTIL DB STUFF<<<
      // Load some dummy data..  use the user_id as the key
      $this->users = array(1 => array("user_id" => 1,
                                      "username" => "user1",
                                      "password" => "abc123",
                                      "email_address" => "user1@example.com",
                                      "full_name" => "First User",
                                      ),
                           2 => array("user_id" => 2,
                                      "username" => "user2",
                                      "password" => "abc123",
                                      "email_address" => "user2@example.com",
                                      "full_name" => "Second User",
                                      ),
                           3 => array("user_id" => 3,
                                      "username" => "user3",
                                      "password" => "abc123",
                                      "email_address" => "user3@example.com",
                                      "full_name" => "Third User",
                                      ),
                          );
    }
    return $this->users;
  }

  /**
   * Creates a user and returns its user_id
   * @param  mixed   $data 
   * @return integer $user_id
   * @throws \Exception 
   */
  public function create(array $data) {
    // >>>TEMP UNTIL DB STUFF<<<
    $this->getAll();
    $tmp = $this->valid_user_fields;
    $user_id = false;
    foreach($data as $field => $value) {
      if(!in_array($field, $this->valid_user_fields)) {
        trigger_error("Unknown field " . $field . " with value " . $value . ".  It has been removed for the update.", \E_USER_NOTICE);
        unset($data[$field]);
      } else {
        if($field == 'user_id') {
          if(intval($value) < 1) {
            unset($data['user_id']);
          } else {
            $user_id = intval($value);
          }
        }
        unset($tmp[$field]);
      }
    }

    $duplicate = $this->getDuplicate($data['username'], $data['email_address'], $user_id);
    if(false !== $duplicate) {
      throw new \Exception("ERROR:  Duplicate found!  " . $duplicate);
    }

    if(!empty($tmp)) {
      throw new \Exception("ERRROR Updating the user!  The following fields were missing!" . var_export($tmp, true));
    }

    if($user_id === false) {
      $user_id = count($this->users);
    }

    $this->users[$user_id] = $data;
    return $user_id;
  }

  /**
   * Updates a user's record by the specified id.
   * @param  integer $id
   * @param  mixed   $data
   * @return boolean true on success.  returns false and triggers a user warning on error.
   */
  public function update($id, array $data) {
    $id = intval($id);
// uncomment this when we need to do mysql stuff..
//    if(isset($data['user_id'])) {
//      unset($data['user_id']);
//    }

    // >>>TEMP UNTIL DB STUFF<<<
    $this->getAll();
    if($id < 0 || !isset($this->users[$id])) {
      throw new \Exception("Invalid user_id (" . $id . ") Passed!");
    }

    $tmp = $this->required_user_info;
    foreach($data as $field => $value) {
      if(!in_array($field, $this->required_user_info)) {
        trigger_error("Unknown field " . $field . " with value " . $value . ".  It has been removed for the update.", \E_USER_NOTICE);
        unset($data[$field]);
      } else {
        unset($tmp[$field]);
      }
    }

    if(!empty($tmp)) {
      trigger_error("ERRROR Updating the user!  The following fields were missing!" . var_export($tmp, true), \E_USER_ERROR);
      return false;
    }

    $duplicate = $this->getDuplicate($data['username'], $data['email_address'], $id);
    if(false !== $duplicate) {
      trigger_error("ERROR:  Duplicate found!  " . $duplicate, \E_USER_ERROR);
      return false;
    }
    
    $this->users[$id] = $data;
    return true;
  } 

  /**
   * Deletes a user's record by the specified id.
   * @param  integer $id
   * @return boolean true on success.  returns false and triggers a user warning on error.
   */
  public function delete($id) {
    // >>>TEMP UNTIL DB STUFF<<<
    $this->getAll();
    $id = intval($id);
    if(!isset($this->users[$id])) {
      trigger_error("Invalid user_id (" . $id . ") Passed!", \E_USER_ERROR);
      return false;
    }
    unset($this->users[$id]);
    return true;
  }
}
?>