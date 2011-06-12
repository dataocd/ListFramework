<?php
namespace Lists\Router;
class Rewrite implements IRouter {
    public function __construct() {}

    public function route(\Lists\Request\Request $request) {
      $paths = explode("/", $request->URLParts['path']);
      $request->package = $paths[1];
      $request->packageController = $paths[2];
      return \Lists\FrontController::getInstance()->dispatcher->execute($request);
    }
}
?>
