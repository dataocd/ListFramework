<?php
namespace Core\Packages\User;
class UserController implements \Core\Framework\Controller\iController {
  // Get something by ID
  public function get($id){
    return $id;
  }
  // get all of something.
  public function getAll(){
    return true;
  }
  // create something with this data.
  public function create(array $data){
    return $data;
  }
  // update something by id with the following data.
  public function update($id, $data){
    return array($id, $data);
  }
  // DELETE something with the id.
  public function delete($id){
    return $id;
  }

  public function post(array $data) {
    
  }

  public function put($id, array $data) {
    
  }
}
?>