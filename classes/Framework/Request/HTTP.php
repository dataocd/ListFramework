<?php
/**
 * Core\Framework\Request\HTTP.php
 * The request class is the base class for all types of requests.
 *
 * PHP Version 5.3.x+
 * @category  Requests
 * @package   Controller
 * @author    James Phillips <james@dataocd.com>
 * @author    Jonathon Hibbard <jon@dataocd.com>
 * @copyright 2012 DataOCD. All rights reserved.
 * @license   TBD
 * @version   1.1
 * @link      http://www.dataocd.com/
 */

/**
 * @namespace
 */
namespace Core\Framework\Request;
/**
 * @category  Requests
 * @package   Controller
 */
class HTTP extends Request {
  const HTTPS_PROTOCOL = 'https';
  const HTTP_PROTOCOL  = 'http';
  protected $is_https            = false;
  protected $request_keys_loaded = false;
  protected $valid_methods       = array('get','post','put','delete','head','options',);
  protected $http_info           = array();
  protected $request_info        = array();

  protected $request_uri;
  protected $request_method;
  protected $request_body;

  protected $http_accept;
  protected $content_type;

  protected $base_url;
  protected $base_path;

  protected $user_agent;
  protected $http_host;

  protected $server_signature;
  protected $server_protocol;

  protected $remote_port;

  protected $uri_parts    = array();
  protected $params       = array();
  protected $query_string = '';

  public function __construct($uri = NULL) {
    $this->loadRequestURI($uri);
    $this->loadRequestMethod();
    $this->loadRequestKeys();
    $this->loadUriParts();
    $this->getProtocol();
    $this->loadBaseURL();
    $this->loadBasePath();
    $this->server_signature = $this->getKey('SERVER_SIGNATURE');
    $this->user_agent       = $this->getKey('HTTP_USER_AGENT');
    $this->http_accept      = $this->getKey('HTTP_ACCEPT');
    $this->content_type     = $this->getKey('CONTENT_TYPE');
  }
  
  /**
   * Magic Method
   * @param type $key
   * @return type 
   */
  public function __get($key) {
    if(isset($this->$key)) {
      return $this->$key;
    } else {
      return $this->getKey($key);
    }
  }

  /**
   * Magic Method.
   * @param type $key
   * @param type $value 
   */
  public function __set($key, $value) {
    if(property_exists($this, $key)) {
      $this->$key = $value;
    }
  }

  /**
   * Magic Method for calling...
   * @param type $method
   * @param type $args
   * @return null 
   */
  public function __call($method, $args) {
    if(method_exists($this, $method)) {
      $this->$method($args);
    } elseif(in_array($method, array("isGet", "isPost", "isPut", "isDelete", "isHead", "isOptions"))) {
      // Little song and dannce to check for the request type...
      $method = strtolower(str_replace("is", $method));
      return ($method == strtolower($this->request_method) ?: false);
    } elseif(empty($args)) {
      return $this->getProperty($method);
    } else {
      return null;
    }
  }

  /**
   * If the request_uri hasn't been set, attemps to set it.
   * @param type $uri
   * @return type 
   */
  public function loadRequestURI($uri = NULL) {
    if(!isset($this->request_uri)) {
      $this->request_uri = (isset($uri) && !empty($uri) ? $uri : $this->getKey('REQUEST_URI'));
    }
    return $this;
  }

  /**
   * If the Request Type hasn't been set, attempts to set it.
   * @return type
   * @throws \Exception 
   */
  public function loadRequestMethod() {
    if(!isset($this->request_method)) {
      $this->request_method = strtolower($this->getKey('REQUEST_METHOD'));
      if(!in_array($this->request_method, $this->valid_methods)) {
        throw new \Exception("Invalid Request Type Submitted!");
      }
    }
    return $this;
  }

  /**
   * If the Request URI has params being sent in, then load them into the $_GET superglobal so 
   * we can access them and them alone... 
   * Based on Zend Framework.
   */
  public function loadRequestKeys($reload_request_keys = false) {
    if(false === $this->request_keys_loaded || $reload_request_keys === true) {
      $uri_pos = strpos($this->request_uri, '?');
      if(false !== $uri_pos) {
        $keys = array();
        $this->query_string = substr($this->request_uri, $uri_pos + 1);
        parse_str($this->query_string, $keys);
        if(!empty($keys)) {
          $this->setRequestKeys($keys);
        }
      }
      $this->request_keys_loaded = true;
    }
    return $this;
  }

  /**
   * Parses the Request URI and stores the output.
   * @throws \Exception 
   */
  public function loadUriParts() {
    $this->uri_parts = parse_url($this->request_uri);
    # Will only be false when the parse_url fails.
    if(false === $this->uri_parts) {
      throw new \Exception("Invalid URL Request!");
    }
  }

  public function loadInfo() {
    if(!isset($this->server_protocol)) {
      $this->server_protocol = (isset($_SERVER['HTTPS']) ? "https" : "http");
      $this->is_https = (strtolower($this->server_protocol) == 'http' ?: true);
      $this->http_host = $this->getKey('HTTP_HOST');
    }
    return $this;
  }

  public function loadBaseURL($uri = null) {
    if(!isset($this->base_url)) {
      $this->base_url = $this->getRequestURI($uri);
    }
    return $this;
  }

  /**
   * Borrowed from Zend Framework.
   * @param type $url
   * @return \Core\Framework\Request\HTTP 
   */
  public function loadBasePath($base_url = null) {
    if(!isset($base_url)) {
      $this->loadUriParts();
      $base_url = $this->uri_parts['path'];
      $filename = (isset($_SERVER['SCRIPT_FILENAME'])) ? basename($_SERVER['SCRIPT_FILENAME']) : '';
      if(empty($base_url)) {
        $base_url = $this->getBaseUrl();
        if(empty($base_url)) {
          $this->base_path = '';
          return $this;
        }
      }
      $base_path = (basename($base_url) === $filename ? dirname($base_url) : $base_url);
    }

    if(substr(PHP_OS, 0, 3) === 'WIN') {
      $base_path = str_replace('\\', '/', $base_path);
    }

    $this->base_path = rtrim($base_path, '/');
    return $this;
  }

  /**
   * Sets the keys of the $_GET superglobal.  Based on Zend Framework.
   * @param type $keys
   * @param type $value
   * @return \Core\Framework\Request\HTTP 
   */
  public function setRequestKeys($keys, $value = null) {
    if(!isset($value) && is_array($keys)) {
      foreach($keys as $key_name => $key_value) {
        $this->setRequestKeys($key_name, $key_value);
      }
      return $this;
    }
    $key_name = (string)$keys;
    $this->params[$key_name] = $value;
    $_GET[$key_name] = $value;
  }

  public function getProtocol() {
    $this->loadInfo();
    return ($this->is_https === true ? self::HTTPS_PROTOCOL : self::HTTP_PROTOCOL);
  }

  public function getHost() {
    $this->loadInfo();
    return $this->http_host;
  }

  public function getRequestURI($uri = null) {
    $this->loadRequestURI($uri);
    return $this->request_uri;
  }

  public function getBaseURL() {
    $this->loadBaseURL();
    return $this->base_url;
  }

  public function getBasePath($url = null) {
    $this->loadBasePath($url);
    return $this->base_path;
  }

  public function getRequestMethod() {
    $this->loadRequestMethod();
    return $this->request_method;
  }

  public function getRequestInfo($uri = null) {
    if(empty($this->request_info)) {
      $this->request_info = array("request_uri"    => $this->getRequestURI($uri),
                                  "base_uri"       => $this->getBasePath($uri),
                                  "request_method" => $this->getRequestMethod(),
                                  "base_url"       => $this->getBaseUrl(),
                                  "host"           => $this->getHost(),
                                  "protocol"       => $this->getProtocol(),
                                  "request_body"   => $this->request_body,
                                  "content_type"   => $this->content_type,
                                  "user_agent"     => $this->user_agent,
                                  "params"         => $this->params,
                                  "uri_parts"      => $this->uri_parts,
                                 );
    }
    return $this->request_info;
  }

  public function getInfo() {
    if(empty($this->http_info)) {
      $this->http_info = array("http_protocol"    => $this->getProtocol(),
                               "http_host"        => $this->getHost(),
                               "request_method"   => $this->getRequestMethod(),
                               "is_https"         => $this->is_https,
                               "server_signature" => $this->server_signature,
                               "http_accept"      => $this->http_accept,
                               "user_agent"       => $this->user_agent,
                               "valid_methods"    => $this->valid_methods,
                              );
    }
    return $this->http_info;
  }

  /**
   * @author James Phillips
   * Universal get function. This way, noone else outside of here ever,
   * need mess with the ugly server globals stuff.
   * 
   * @note: Be careful when using this method if you expect to find the 
   * same value in more than one global store.
   */
  public function getKey($key) {
    $key = (!empty($key) ? strtoupper($key) : null);
    $local_key = ($key !== null ? strtolower($key) : null);

    switch(true) {
      case isset($this->$local_key):
        return $this->$local_key;
      case isset($this->http_info[$local_key]):
        return $this->http_info[$local_key];
      case isset($this->request_info[$local_key]):
        return $this->request_info[$local_key];
      case isset($_GET[$key]):
        return $_GET[$key];
      case isset($_POST[$key]):
        return $_POST[$key];
      case isset($_COOKIE[$key]):
        return $_COOKIE[$key];
      case ($key == 'REQUEST_URL'):
        return $this->getRequestURI();
      case isset($_SERVER[$key]):
        return $_SERVER[$key];
      case isset($_ENV[$key]):
        return $_ENV[$key];
      default:
        return null;
    }
  }
}
?>