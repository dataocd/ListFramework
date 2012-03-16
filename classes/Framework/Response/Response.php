<?php
namespace Core\Framework\Response;
class Response {
  protected $response_body = '';
  protected $response_code = 200;
  public function __construct() {}
  public function getAll() {
    return array("body" => $this->response_body, "code" => $response_code);
  }
}
?>