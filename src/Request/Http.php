<?php
/**
 * @author Jonathon Hibbard
 * Based on https://github.com/dataocd/ListFramework/blob/master/src/Request/Http.php
 */
namespace AVBC;

class Request {
    private $variables_order  = array( 'E', 'G', 'P', 'C', 'S' );

    private static $instance = null;

    // Prevent Clone... Singleton...
    private function __clone() {}

    private function __construct( $variables_order ) {
        if( !is_string( $variables_order ) || empty( $variables_order ) ) {
            $variables_order = strtoupper( ini_get( 'variables_order' ) );
        }

        $this->variables_order = str_split( $variables_order );
    }

    private function E() {
        return $_ENV;
    }

    private function G() {
        return $_GET;
    }

    private function P() {
        return $_POST;
    }

    private function C() {
        return $_COOKIE;
    }

    private function S() {
        return $_SERVER;
    }

    public static function getInstance( $variables_order = 'EGPCS' ) {
        if( !isset( self::$instance ) ) {
            self::$instance = new Request( $variables_order );
        }

        return self::$instance;
    }

    public function valueForKey( $key ) {
        if( !is_string( $key ) || empty( $key ) ) {
            return null;
        }

        foreach( $this->variables_order as $prop_type ) {
            $_prop = $this->$prop_type();

            if( isset( $_prop[$key] ) ) {
                return $_prop[$key];
            }
        }

        return null;
    }
}
