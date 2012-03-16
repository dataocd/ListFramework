<?php
namespace Core\Packages\Rest\Controller;
/**
 * See the following URL's for direction that we're going here...
 * http://www.ics.uci.edu/~fielding/pubs/dissertation/rest_arch_style.htm
 * http://www.infoq.com/articles/rest-introduction 
 * http://en.wikipedia.org/wiki/Clean_URL
 * 
 */
class v1 {
  public $resourceController;

  public function __construct() {
  }

  public function execute(\Core\Framework\Request\Request $request, \Core\Framework\Response\Response $response) {
    echo "<pre>";
    print_r($request);
    echo "</pre>";
  }

  public function getClassObject(\Core\Framework\Request\Request $request) {
    
  }

  public function delete() {
    
  }

  public function get() {

  }

  public function post() {

  }

  public function put() {

  }
}
?>