<?php
/**
 * Controller/Dispatcher.php
 * The Dispatcher class is responsible for taking the web client's request, and dispatching that request
 *  to the appropriate API controller.
 *
 * PHP Version 5.3.x
 * @category  Dispatcher
 * @package   Dispatcher
 * @author    Jon Hibbard <jon@dataocd.com>
 * @copyright 2012 DataOCD. All rights reserved.
 * @license   TBD
 * @version   1.1
 * @link      http://www.dataocd.com/
 */
namespace Core\Framework;
class Dispatcher {
  # Sets the default action for use with the controllers.
  protected $action = 'all';
  protected $controller;
  protected $frontController;
  protected $className;
  protected $class_directory = array();
  protected $class_objects = array();

  # arguments to pass to the controller's constructor.
  protected $constructor_arguments = array();
  # the response object we'll overload and return when the controller finishes.
  protected $responseObj;

  # Get the controller we're after from the request object.
  public function getControllerName(Request\Request $request) {
    if(true === $this->isValidPackage($request->package)) {
      $controller = implode("\\", array($request->package, 'Controller', $request->packageController));
      return $controller;
    }
  }

  //This class should create the package->controller and call the execute function
  public function execute(Request\Request $request, Response\Response $response) {
    $controllerName = implode("\\", array("Core", "Packages", $request->package, 'Controller', $request->packageController));
    $controller = new $controllerName();
    return $controller->execute($request, $response);
  }

  private function isValidPackage($request_package = null) {
    return (isset($request_package) && !empty($request_package) ?: false);
  }

  private function isValidPackageController($request_package_controller = null) {
    return (isset($request_package_controller) && !empty($request_package_controller) ?: false);
  }
}
?>