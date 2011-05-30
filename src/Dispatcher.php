<?php
/**
 * Controller/Dispatcher.php
 * The Dispatcher class is responsible for taking the web client's request, and dispatching that request
 *  to the appropriate API controller.
 *
 * PHP Version 5.2.0
 * @category  Dispatcher
 * @package   Dispatcher
 * @author    Jon Hibbard <jon@dataocd.com>
 * @copyright 2011 DataOCD. All rights reserved.
 * @license   TBD
 * @version   SVN: $Id$
 * @link      http://www.dataocd.com/
 */

namespace Listr;

class Dispatcher {
   //This class should create the package->controller and call the execute function
   public function execute(Request $request) {
       $controller_string = implode("\\", array($request->package, $request->pageController));
       $controller = new $controller_string();
       return $controller->execute();
   }
}