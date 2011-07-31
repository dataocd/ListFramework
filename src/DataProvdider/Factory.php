<?php
/**
 * @author Jonathon Hibbard
 * The main Cache Factory for creating a Cache Object.
 *
 * @example $redis = new Cache_Factory("redis");
 * @example $eAccellerator = new Cache_Factory("eAccellerator");
 */
class Cache_Factory {
  private static $eaccelleratorObj = null;
  private static $redisObj = null;

  /**
   * Gets an Instance for any available Cache Systems we support...
   *
   * @param  string $cache_type // Can be (currently): redis, eAccellerator
   * @return Cache object
   */
  public function getInstance($cache_type = NULL) {
    if(!isset($cache_type) || !is_string($cache_type) || empty($cache_type)) {
      throw new Exception("Invalid Cache Type Requested!");
    }

    switch(strtolower($cache_type)) {
      case 'eaccelerator':
        if(null === self::$eaccelleratorObj) {
          self::$eaccelleratorObj = new Cache_EAccelerator_Instance();
        }
        return self::$eaccelleratorObj;
      break;
      case 'redis':
        if(null === self::$redisObj) {
          self::$redisObj = new Cache_Redis_Instance();
        }
        return self::$redisObj;
      break;
      default:
        throw new Exception("Invalid Cache Type Requested!");
    }
  }
}
?>