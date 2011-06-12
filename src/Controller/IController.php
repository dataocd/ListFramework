<?php
/**
 * Controller\IController.php
 * The controller interface is used to define the primary functionality 
 *  that all controllers must implement.
 *
 * PHP Version 5.2.0
 * @category  Interface
 * @package   Controller
 * @author    Jonathon Hibbard <jon@dataocd.com>
 * @copyright 2011 DataOCD. All rights reserved.
 * @license   TBD
 * @version   SVN: $Id$
 * @link      http://www.dataocd.com/
 */
 
 /**
 * @namespace
 */
namespace Lists\Controller;

/**
 * @category  Interface
 * @package   Controller
 */
 
interface IController {
    public function execute($request);
}