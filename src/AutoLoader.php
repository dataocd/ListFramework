<?php
namespace Lists;
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
        $this->registerNamespace('Lists', dirname(__DIR__));
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

    /**
     * @author Jonathon Hibbard
     * Parses out the namespace, obtains the dir from the namespaces array, and then calls the classnameToFilename to get the dir.
     *
     * @param string $class
     */
    public function loadClass($class) {
      $namespace = strstr("\\", $class, true);
      $this->classnameToFilename(str_replace($namespace, "", $class), $this->namespaces[$namespace]);
    }
    
    public function register() {
        spl_autoload_register(array($this, 'autoload'));
    }
    
    public function classnameToFilename($class, $dir) {
        return $dir . str_replace(self::NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, $class) . '.php';
    }
    
    public function fileExists($file) {
        return stream_resolve_include_path($filename);
    }
}
?>
