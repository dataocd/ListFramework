<?php
/**
 * @author Jonathon Hibbard
 * Based on https://github.com/dataocd/ListFramework/blob/master/src/Request/Http.php
 */
namespace NS_HERE;

class Request {
    private static $variables_order  = array( 'E', 'G', 'P', 'C', 'S' );

    public static function setVariablesOrder( $variables_order = 'EGPCS' ) {
        if( !is_string( $variables_order ) || empty( $variables_order ) ) {
            $variables_order = strtoupper( ini_get( 'variables_order' ) );
        }

        self::$variables_order = str_split( $variables_order );
    }

    private static function E() {
        return $_ENV;
    }

    private static function G() {
        return $_GET;
    }

    private static function P() {
        return $_POST;
    }

    private static function C() {
        return $_COOKIE;
    }

    private static function S() {
        return $_SERVER;
    }

    public static function valueForKey( $key ) {
        if( !is_string( $key ) || empty( $key ) ) {
            return null;
        }

        foreach( self::$variables_order as $prop_type ) {
            $_prop = self::$prop_type();

            if( isset( $_prop[$key] ) ) {
                return $_prop[$key];
            }
        }

        return null;
    }
}
