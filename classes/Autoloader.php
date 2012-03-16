<?php
/**
 * @authors James Phillips, Jonathon Hibbard
 * @copyright 2011-2012 dataocd
 * @license GNU Public License (see LICENSE)
 * @version 1.1 - This is an updated version.  Original can be found in the ListFramework github.
 * @link http://www.dataocd.com
 *
 *
 * An autoloader class for managing the autoloading of classes as done in the past, and also a new method of autoloading via Namespaces.
 * 
 * This is also where the Core namespace is initially defined..
 * 
 * @example 1
 * include_once('autoloader.php');
 * // Loading only the autoload folder and registers the MyNamespace namespace.
 * MyNamespace\autoloader::getInstance();
 *
 * @example 2
 * // Registers the myExtendedNamespace namespace and defines the location of it's directory.
 * // Note: This is NOT necessary if you do not wish to keep your new namespace in the "myNamespace" namespace
 * // However, it is being shown this way to provide visibility of the freemdom to create your own personal namespace should you want to do so.
 * myNamespace::getInstance()->register("myExtendedNamespace", "/path/to/myExtendedNamespace");
 */
namespace Core;
class autoloader {
  private static $common_autoloader_loaded = false;
  private static $instance                 = NULL;

  private $namespaces = array();

  /**
   * @access private
   * Registers the Core namespace with the root directory of __DIR__.
   */
  private function __construct() {
    $this->register('Core', __DIR__);
  }

  /**
   * Registers the common_autoload method (below) to be used with autoloading of classes without namespace support. 
   */
  private static function start_common_autoload(autoloader $self) {
    if(false === self::$common_autoloader_loaded) {
      spl_autoload_register(array($self, 'common_autoload'));
      self::$common_autoloader_loaded = true;
    }
  }

  /**
   * Basic Autoloading without namespaces.
   * Classes should be named with underscores (_) to indicate nested directory levels.
   * 
   * @example
   * /classes/my/class.php
   * <?php
   * class my_class {
   * }
   * ?>
   * 
   * /path/to/somefile.php
   * <?php
   * $obj = autoloader::common_autoload('my_class');
   * ?>
   */
  private static function common_autoload($class) {
    // replaced strtr with str_replace since we're only replacing _ with /.
    $classfile = __DIR__ . DIRECTORY_SEPARATOR . str_replace($class, '_', DIRECTORY_SEPARATOR) . '.php';
    if(file_exists($classfile)) {
      self::loadFile($classfile);
    }
  }

  /**
   * This is the namespace-based autoloader.
   * It is called by the public register() method (below) either on the first getInstance request (which calls our constructor),
   * or when the registerNamespace method (below) is called to create a new namespace declaration.
   * 
   * @param string $class // Name of the class to load.
   * @return boolean // Returns true if the file load was successful, false if not.
   */
  private function autoload($class) {
    if($this->loadClass($class)) {
      return $class;
    }
    return false;
  }

  /**
   * Parses out the Namespace from the file, gets the path from the namespaces array, and then attempts to loadit.
   * @param string $class  // Class to be loaded.
   * @return boolean // Returns true if succeeds, false if not.
   */
  private function loadClass($class) {
    // get the namespace we're detaling with (Bob\CalledMe will return Bob due to 3rd param boolean true).
    $namespace = strstr($class, '\\', true);

    if(!empty($namespace) && false !== $namespace && isset($this->namespaces[$namespace])) {
      $classPeeled = preg_replace("/$namespace/", "", $class, 1);
      $filename = $this->classnameToFilename($classPeeled, $this->namespaces[$namespace]);
    } else {
      $filename = $class . ".php";
    }
    return \Core\autoloader::loadFile($filename);
  }

  /**
   * Takes in a class name and a directory and returns it as a full path.
   * @param string $class // Name of the class we want to load.
   * @param string $dir // Directory to append to the class
   * @return string // Path to the class
   */
  private function classnameToFilename($class, $dir) {
    return $dir . str_replace("\\", DIRECTORY_SEPARATOR, $class) . '.php';
  }

  /**
   * Handles loads of files.
   * Optional settings allows us to maintains the active paths included in php's include_path.
   *
   * @param string $filename  // The filename we want to load
   * @param boolean $once // If set to false, this will cause the loader to include the file rather than include_once.
   */
  private static function loadFile($filename, $once = true) {
    if($once === true) {
      include_once($filename);
    } else {
      include($filename);
    }
  }

  /**
   * Loads the common_autoloader on the first call.  This allows all existing code to work who don't use namespaces.
   * This also registers the main namespace with the root classes folder being the initial directory...
   * @return type object autoloader
   */
  public static function getInstance() {
    if(!isset(self::$instance)) {
      self::$instance = new self();
      self::start_common_autoload(self::$instance);
    }
    return self::$instance;
  }

  /**
   * Defines the location of a namespace
   * @param string $namespace
   * @param string $dir
   */
  public function registerNamespace($namespace, $dir) {
    $this->namespaces[$namespace] = $dir;
  }

  /**
   * Registers a namespace and also loads the namespace directory into the autoload register.
   * @param type $namespace
   * @param type $dir
   */
  public function register($namespace, $dir) {
    // regsiter the namespace first...
    $this->registerNamespace($namespace, $dir);
    // then, rester the autoload method within this object's scope
    spl_autoload_register(array($this, 'autoload'));
  }
}
?>