<?php
/**
 * @authors James Phillips, Jonathon Hibbard
 * @version 1.1
 * Based on the original ListFramework.
 * The FrontController is the main controller that takes the initial requests.
 */

namespace Core\Framework;
class FrontController {
  /**
   * Contains the instance of this object for singleton.
   */
  private static $instance = null;

  /**
   * The package router used to find and load the packages. Parses the 
   * information contained in the Request to determine what package to use.
   */
  protected $router;

  /**
   * Holds the Reponse that will be built/returned back to the requester. 
   */
  public $response;

  public $request = null;

  /**
   * Pointer to the package dispatcher. The dispatcher is responsible for
   * finding the proper package/controller and creating an instance. Then
   * calling its execute() function.
   */
  protected $dispatcher = null;

  protected $base_uri = null;


  private function __construct() {}

  //Just make sure we cant do this... singleton
  private function __clone() {}

  /**
   * @edit Jonathon Hibbard
   * Updated to be a static and changed check from null to !isset
   */ 
  public static function getInstance() {
    if(!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * @author Jonathon Hibbard
   * Creatied a setter for defining the protected properties.
   * @todo determine if these vars really need to be protected or if instead they should be private/public...
   */
  public function __set($key, $value) {
    $this->$key = $value;
  }

  /**
   * @author Jonathon Hibbard
   * Created a getter for accessing the protected properties.
   * @todo @see self::__set
   */ 
  public function __get($key) {
    return $this->$key;
  }

  /**
   * Used to startup the server.
   * @param string $base_uri
   * @return Core\Framework\FrontController $front    Returns result of the
   *                                                  Core\Framework\FrontController::execute().
   */
  public static function run($base_uri = null) {
    $front = self::getInstance();
    $front->base_uri   = $base_uri;
    $front->router     = new Router\Rewrite();
    $front->dispatcher = new Dispatcher();
//    return $front->execute();
    $front->request = new Request\HTTP();
    $front->response = new Response\Response();
    try {
      $front->request = $front->router->route($front->request);       
      $front->dispatcher->execute($front->request, $front->response);
    } catch (\Exception $e) {
//      $front->request->addException($e);
      echo "\n<br />ERROR!: " . $e->getMessage() . "\n<br />";
    }
    return $front->response;

  }

  public function setRouter($router) {
    if (is_string($router)) {
      if(!class_exists($router)) {
        require_once 'Zend/Loader.php';
        Zend_Loader::loadClass($router);
      }

      $router = new $router();
    }

    if(!$router instanceof iRouter) {
      throw new \Exception('Invalid router class');
    }

    $router->setFrontController($this);
    $this->router = $router;

    return $this;
  }

  public function addControllerDirectory($path) {
    try {
      $dir = new DirectoryIterator($path);
    } catch(Exception $e) {
      require_once 'Zend/Controller/Exception.php';
      throw new Zend_Controller_Exception("Directory $path not readable", 0, $e);
    }
    foreach ($dir as $file) {
      if ($file->isDot() || !$file->isDir()) {
        continue;
      }

      $module    = $file->getFilename();
      // Don't use SCCS directories as modules
      if (preg_match('/^[^a-z]/i', $module) || ('CVS' == $module)) {
        continue;
      }
      $moduleDir = $file->getPathname() . DIRECTORY_SEPARATOR . $this->getModuleControllerDirectoryName();
      $this->addControllerDirectory($moduleDir, $module);
    }
    return $this;
  }

  /**
   * Takes a request, if null will create the default request using the HTTP info
   * @return object $response // Returns the response for the specified Request Type.
   */ 
  public function execute($request = null) {
//    if(!isset($request)) { 
//      $this->request = new Request\Http();
//    }
//
//    try {
//      $this->request = $this->router->route($this->request);       
//      $this->reponse = $this->request->package();
//    } catch (\Exception $e) {
//      $request->addException($e);
//    }
//    return $response;
  }
}
?>