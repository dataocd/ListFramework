<?php
namespace Lists;
include('Loader.php');

class AutoLoader {
    const NAMESPACE_SEPARATOR     = '\\';

    /**
     * @edit Jonathon Hibbard
     * Updated this property to be static for singleton storage.
     */
    protected static $instance;
    
    protected $namespaces = array();
    
    public static function getInstance() {
        if (null == self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    protected function __construct() {
        $this->registerNamespace('Lists', __DIR__);
    }
    
    public function registerNamespace($namespace, $dir) {
        $this->namespaces[$namespace] = $dir;
    }
    
    public function autoload($class) {
        if($this->loadClass($class)) {
            return $class;
        }
        return false;
    }
    
    public function loadClass($class) {
      $namespace = strstr($class, '\\', true);
      $class_name = str_replace($namespace, '', $class);
      $filename = $this->classnameToFilename(str_replace($namespace, '', $class), $this->namespaces[$namespace]);
      \Lists\Loader::loadFile($filename);
      
        //need to peel off the first part(s) and see if they are in our namespaces,
        // if so, thats the path your start from. If all else fails, try to load it
        // using the include path
    }
    
    public function register() {
        spl_autoload_register(array($this, 'autoload'));
    }
    
    public function classnameToFilename($class, $dir) {
        $output = $dir . str_replace(self::NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, $class) . '.php';
        return $output;
    }
    
    public function fileExists($file) {
        return stream_resolve_include_path($filename);
    }
}
?>
