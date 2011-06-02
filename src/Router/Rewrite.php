<?php
namespace Lists\Router;
use \Lists\FrontController as FC;
class Rewrite implements IRouter {
    public function __construct() {}

    public function route(\Lists\Request\Request $request) {
        $FC = FC::getInstance();
        $uri = $request->get('PATH_INFO');  
        $paths = explode("/", $uri);
        $request->package = $paths[1];
        $request->packageController = $paths[2];
        $request->packageCommand = str_replace($paths[1].'/'.$paths[2].'/','',$uri);
        return $FC->dispatcher->execute($request);
    }
}
?>
