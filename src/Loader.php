<?php
/**
 * Loader.php
 * The Loader class is just a wrapper for the require/include functions 
 *   in php. If we only load files through here, and never call 
 *   require/include in the rest of the framework, we should be able to
 *   control directory sturcture without the need to do a bunch of 
 *   find/replace everytime we move things around.
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

 */

/**
 * @namespace
 */
namespace List;

/**
 * @category  Core
 * @package   Loader
 */
class Loader {
    /**
     * Just a wrapper around the include() func in php. This way, we can add
     *   special paths or whatever we need for lookups. That way, in a file,
     *   we do Loader::loadFile('myCoolSystemFunc'); and since its neither a 
     *   module, nor anything in the includes, we can catch it here and add 
     *   our dirs to the search.
     *
     * If $once is TRUE, it will use include_once() instead of include().
     *
     * @param  string        $filename
     * @param  string|array  $dirs either a path or array of paths to search.
     * @param  boolean       $once
     * @return boolean
     */
    public static function loadFile($filename, $dirs = null, $once = false)
    {
        $incPath = false;
        if (!empty($dirs) && (is_array($dirs) || is_string($dirs))) {
            if (is_array($dirs)) {
                $dirs = implode(PATH_SEPARATOR, $dirs);
            }
            $incPath = get_include_path();
            set_include_path($dirs . PATH_SEPARATOR . $incPath);
        }

        //Added $dirs to the include, see if we find the file now.
        if ($once) {
            include_once $filename;
        } else {
            include $filename;
        }

        //Now reset the include path
        if ($incPath) {
            set_include_path($incPath);
        }
        return true;
    }

    public static function isReadable($filename)
    {
        foreach (self::explodeIncludePath() as $path) {
            $file = ($path !== '.') ? $path.'/'.$filename : $filename;
            if (is_readable($file)) {
                return true;
            }
        }
        return false;
    }

    //Pulled from inet. Inside Zend framework
    public static function explodeIncludePath($path = null)
    {
        if (null === $path) {
            $path = get_include_path();
        }

        if (PATH_SEPARATOR == ':') {
            // On *nix systems, include_paths which include paths with a stream 
            // schema cannot be safely explode'd, so we have to be a bit more
            // intelligent in the approach.
            $paths = preg_split('#:(?!//)#', $path);
        } else {
            $paths = explode(PATH_SEPARATOR, $path);
        }
        return $paths;
    }
}