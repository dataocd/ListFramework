<?php
namespace Lists\Router;
class Rewrite implements IRouter {
    public function __construct() {}

    public function route(\Lists\Request\Request $request) {
        $FC = \Lists\FrontController::getInstance();
        $uri = str_replace($FC->base_uri,'',$request->URLParts['path']);  
        $paths = explode("/", $uri);
        $request->package = $paths[1];
        $request->packageController = $paths[2];
        $request->packageCommand = str_replace($paths[1].'/'.$paths[2].'/','',$uri);
        return $FC->dispatcher->execute($request);
    }
}
?>
