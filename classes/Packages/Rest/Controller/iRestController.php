<?php
namespace Core\Packages\Rest\Controller;
interface iRestController {
  public function post(array $data);
  public function delete($id);
  public function get($id);
  public function put();
}
?>