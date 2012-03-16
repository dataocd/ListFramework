<?php
namespace Core\Framework\Dispatcher;
class RestDispatcher implements iDispatcher {
  # the package we're dealing with.
  protected $package;
  # key value store of package => directory.
  protected $package_directory = array();
  protected $controller;

  public function __construct() {
    $this->controller = new \Core\Packages\Rest\Controller\RestController();
  }

  public function getAction() {
  }
  public function getController() {
    return $this->controller;
  }

  public function getParams() {
  }
  public function setRouter($router) {
  }
}
?>