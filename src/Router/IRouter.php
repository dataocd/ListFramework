<?php
namespace Lists\Router;

/**
  * Processes the information in the $Request to determine what package
  *  to load. Optionally, pass in the Dispatcher to use to call the request.
  *  if no dispatcher is supplied, the router must load one. Returns the 
  *  $Response object.
  */
interface IRouter {
    public function route(Request $request);
}