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
    
    public function getInstance() {
        if (null == self::$instance) {
            self::$instance == new self();
        }
        return self::$instance;
    }
    
    public function setPackageDir($path) {
        $this->packageDir = $path;
    }
    
    public function getPackageDir() {
        return $this->packageDir;
    }
    
    public function setRouter($router) {
        $this->router = $router;
    }
    
    public function getRouter() {
        return $this->router;
    }
    
    public function setDispatcher($dispatcher) {
        $this->dispatcher = $dispatcher;
    }
    
    public function getDispatcher() {
        return $this->dispatcher;
    }
    
    //Used to startup the server.
    public static function run($path) {
        $front = self::getInstance();
        $front->setResponse(new Request\HTTP());
        $front->setRouter(new Router\Rewrite());
        $front->setDispatcher(new Dispatcher\Package());
        if (null !== $path) $front->setPackageDir($path); 
    }
    
    //Takes a request, if null will create the default request using the HTTP info
    //Returns a response
    public function execute($request) {
        //make sure we have a request of some kind. The request object 
        //is capable of loading all the HTTP stuff on its own, so generally,
        //you wouldn't send one in. Only send one in if you're doing something
        //more to it (or creating your own request object thats not from a
        //web user)
        if (null === $request) { 
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