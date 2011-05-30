<?php
namespace Lists\Router;
class Rewrite implements IRouter {
    public function __construct() {}

    public function route(Request $request) {
      $paths = explode("/", $request['URLParts']);
      $request->package = $parts[0];
      $request->packageController = $parts[1];
      return \Lists\FrontController::getInstance()->dispatcher->execute($request);
    }
}
?>
