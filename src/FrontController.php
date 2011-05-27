<?php
/**
 * FrontController.php
 * The FrontController is the main controller that takes the initial requests.
 *
 * PHP Version 5.2.0
 * @category  Controller
 * @package   Controller
 * @author    James Phillips <james@dataocd.com>
 * @copyright 2011 DataOCD. All rights reserved.
 * @license   TBD
 * @version   SVN: $Id$
 * @link      http://www.dataocd.com/
 */

namespace Listr;
/**
 * @category  Core
 * @package   Loader
 */
class FrontController {
    /**
      * Directories where packages are stored
      * @var string|array
      */
    protected $packageDir = null;
    
    /**
      * Subdir in the package where we start looking for controllers, default is 'controllers'
      * @var string
      */
    protected $packageControllerDir = 'controllers';    
    
    /**
      * The package router used to find and load the packages. Parses the 
      *  information contained in the Request to determine what package to use.
      */
    protected $router;
 
    /**
     * Holds the Reponse that will be built/returned back to the requester. 
     */
    protected $response;
 
    /**
      * Pointer to the package dispatcher. The dispatcher is responsible for
      *  finding the proper package/controller and creating an instance. Then
      *  calling its execute() function.
      * @var \List\Controller\Dispatcher
      */
    protected $dispatcher = null;
    
    /**
      * We only want one of these guys.
      * @var \List\Controller\FrontController
      */
    protected static $instance = null;
   
    protected function __construct() {
        $this->$packages = new Package\Broker();
    }
    
    //Just make sure we cant do this... singleton
    private function __clone() {}
   
    /**
     * @edit Jonathon Hibbard
     * Updated to be a static and changed check from null to !isset
     */ 
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance == new self();
        }
        return self::$instance;
    }

    public function __set($key, $value) {
      $this->$key = $value;
    }

    public function __get($key) {
      return $this->$key;
    }

    /**
     * @author James Phillips
     * Used to startup the server.
     *
     * @edit Jonathon Hibbard
     * changed all set methods to instead directly set the local object's variable to the value.
     * changed the default value of path to be null by default.
     */
    public static function run($path = null) {
        $front = self::getInstance();
        $front->response = new Request\HTTP();
        $front->router = new Router\Rewrite();
        $front->dispatcher = new Dispatcher\Package();
        if (isset($path)) $front->packageDir = $path; 
    }
   
    /**
     * @author James Phillips
     * Takes a request, if null will create the default request using the HTTP info
     * Changed default value of request to be null.  Changed check to see if it is set or not instead of explicit null check.
     * @return unknown $response // Returns the response.
     */ 
    public function execute($request) {
        //make sure we have a request of some kind. The request object 
        //is capable of loading all the HTTP stuff on its own, so generally,
        //you wouldn't send one in. Only send one in if you're doing something
        //more to it (or creating your own request object thats not from a
        //web user)
        if (isset($request)) { 
            $request = new Request\HTTP();
        }

        try {
            //Not sure how I feel about this, should it call the dispatcher. I don't think so
            //  so this updates the package name in the $request.
            $this->getRouter()->route($request);       
        
            $response = $this->getDispatcher()->dispatch($request);
        } catch (\Exception $e) {
            $response->addException($e);
        }
        
        
        return $response;
    }
}
