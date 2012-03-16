<?php
namespace Core\Framework\Controller;
interface iController {
  // Get something by ID
  public function get($id);
  // create something something.
  public function post(array $data);
  // update something by id with the following data.
  public function put($id, array $data);
  // DELETE something with the id.
  public function delete($id);
}
?>