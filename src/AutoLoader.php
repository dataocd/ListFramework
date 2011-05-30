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
    
    /**
     * @edit Jonathon Hibbard
     * Is this class being extended?  if not, then this should be private instead of protected.
     */
    protected function __construct() {
        $this->registerNamespace('List', dirname(__DIR__));
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
        //need to peel off the first part(s) and see if they are in our namespaces,
        // if so, thats the path your start from. If all else fails, try to load it
        // using the include path
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
