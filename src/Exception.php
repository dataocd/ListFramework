<?php
/**
 * Exception.php
 * The Exception class is our base Exception class. This will allow us
 *  to make changes to our Exceptions without having to rebase them later.
 *
 * PHP Version 5.2.0
 * @category  Core
 * @package   Loader
 * @author    James Phillips <james@dataocd.com>
 * @copyright 2011 DataOCD. All rights reserved.
 * @license   TBD
 * @version   SVN: $Id$
 * @link      http://www.dataocd.com/
 */

/**
 * @namespace
 */
namespace Listr;

/**
 * @uses       Exception
 * @category   Core
 * @package    List
 */
class Exception extends \Exception
{
}
