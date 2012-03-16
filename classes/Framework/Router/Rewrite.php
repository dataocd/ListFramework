<?php
/**
 * @authors James Phillips, Jonathon Hibbard
 * @version 1.1
 * Original source is on the ListsFramework.
 * Removing the HTTP/Request operations of parsing/editing the URL.
 * 
 * Instead, will just take in a Request Object and report back to the FrontController which
 * Controller it is that corresponds to the Request being sent in.
 * 
 * At this time, this object will not be updated/managed as the current project development
 * is strictly for REST API interactions.
 */
namespace Core\Framework\Router;

class Rewrite implements iRouter {
  protected $routes          = array();
  protected $dispatchers     = array();
  protected $frontController = null;

  public function addRoute($route, \Core\Framework\Dispatcher $dispatcher) {
    if(isset($this->routes[$route])) {
      // maybe append to an error handler collection, saying that we didn't create a new route?
      // useful for things like "I just got a false... why?" without having to use exceptions.
      return false;
    }
    $this->routes[$route] = $dispatcher;
    return true;
  }

  public function route(\Core\Framework\Request\Request $request) {
    $this->frontController = \Core\Framework\FrontController::getInstance();
//    $uri = $request->get('PATH_INFO');  
    $paths = explode("/", $request->getRequestURI());
//    if(empty($paths)) {
      // Either load the homepage, report back with an error, or call the Contorller for
      // giving details about this service.
      // For now, just make a dummy call here.  website package doesn't exist, nor does a 
      // homepage controller.  but it could... right? :)
      $request->package = 'Rest';
      $request->packageController = 'v1';
      $request->packageCommand = $request->query_string;
//    } else {
//      $request->package = $paths[0];
//      $request->packageController = $paths[1];
//      $request->packageCommand = str_replace($paths[0] . '/' . $paths[1] . '/', '', $uri);
//    }
//    return $this->frontController->dispatcher->execute($request);
    return $request;
  }

  /**
   * Currently only works with a 2 dimensional array... feel free to make this recursive...
   * @param  array $array  // The array to remove empty keys from.
   * @return array $array  // Returns an array with the keys that are empty removed.  Indexes are 
   *                          not preserved with this method.
   */
  public function unsetEmptyKeys(array $array) {
    if(!empty($array)) {
      array_walk($array, function($value, $key) use(&$array){ 
        if(empty($value)) {
          unset($array[$key]);
        }
      });
    }
    return $array;
  }

  public function getAction() {
  }

  public function getParams() {
  }

  public function setFrontController(\Core\Framework\FrontController $frontController) {
  }
}
?>