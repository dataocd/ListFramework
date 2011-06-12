<?php
/**
 * Version.php
 * The Version class is used to verfiy the current version of the List framework. This will be useful when 
 *  writing new packages. We may want to deploy support for a new API, but not be able to upgrade the whole
 *  framework. In which case, the new package could check the framework version to prevent calling any
 *  unavailable functions.
 *
 * PHP Version 5.2.0
 * @category  Core
 * @package   List
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
 * Used to get and verify the current version of the List framework.
 *
 * @category   Core
 * @package    List\Version
 * @copyright  2011 DataOCD. All rights reserved.
 * @license    TBD
 */
final class Version {
    const VERSION = '0.1.0';

    public static function isVersion($version) {
        return version_compare($version, self::VERSION);
    }
    
    public static function atleastVersion($version) {
        return version_compare($version, self::VERSION, '>=');
    }
}
