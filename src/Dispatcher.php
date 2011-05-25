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

namespace List\Controller;

/**
 * Defines autoload strategy for classes
 */
function list_autoload($class) {
  $classfile = '/var/www/lists/classes/'.strtr($class, '_', '/').'.php';
  if (file_exists($classfile)) {
    include($classfile);
  }
}
spl_autoload_register('list_autoload');

$a = new Controller();