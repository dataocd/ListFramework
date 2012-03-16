<?php
namespace Core\Framework\Request;
abstract class Request {
  protected $package;
  protected $packageController = '';
  protected $packageCommand = '';

  abstract public function __construct($uri = NULL);
  abstract public function loadRequestURI($uri = NULL);
  abstract public function loadRequestMethod();
  abstract public function loadRequestKeys($reload_request_keys = false);
  abstract public function loadUriParts();
  abstract public function loadInfo();
  abstract public function loadBaseURL($uri = null);
  abstract public function loadBasePath($url = null);
  abstract public function setRequestKeys($keys, $value = null);
  abstract public function getProtocol();
  abstract public function getHost();
  abstract public function getRequestURI($uri = null);
  abstract public function getBaseURL();
  abstract public function getBasePath($url = null);
  abstract public function getRequestMethod();
  abstract public function getRequestInfo($uri = null);
  abstract public function getInfo();
  abstract public function getKey($key);

  public function setValidMethods(array $valid_methods) {
    foreach($valid_methods as $method) {
      
      if(!in_array($method, array('get','post','put','delete','head','options','trace','connect'))) {
        throw new \Exception("You're requesting to use an invalid request method.  See HTTP/1.1 RFC 2616 - http://www.w3.org/Protocols/rfc2616/rfc2616-sec5.html");
      }
    }
  }

  /**
   * Attemps to get a property for the unknown method found in 
   * the __call magic method.
   * 
   * Concept is based on jan.machala@email.cz
   * @link http://us.php.net/manual/en/language.oop5.overloading.php#103478
   * 
   * @param  string $method
   * @return mixed  Returns false if nothing found, otherwise returns the property's value.
   */
  protected function getProperty($method) {
    $object_property = '';
    $matches         = array();
    if(preg_match('~^(set|get)([a-z])(.*)$~', strtolower($method), $matches)) {
      if(strtolower($matches[1]) == 'get' && property_exists($this, $object_property)) {
        $object_property = strtolower($matches[2]) . $matches[3];
        return $this->$object_property;
      }
    }
    return null;
  }

  protected function getControllerObject() {
    return new $this->packageController();
  }
}
?>