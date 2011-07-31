<?php
/**
 * @author Jonathon Hibbard
 * Class for creating a Redis Instance Object.
 *
 * @uses Cache_Redis_Operations | Cache
 * @final
 */
final class Cache_Redis_Instance extends Cache_Redis_Operations implements Cache {
  /**
   * Create an instance of RedisCache and prepare to connect to an instance of the
   * cache engine specified by the passed in host and port.
   *
   * @access Public
   * @return Boolean - False if error, True if succss
   */
  public function __construct() {
    if(false === $this->connect()) {
      throw new Exception("Failed to connect to Redis!");
    }
  }

  /**
   * Set a value for a specific key in the cache, with a given time to live (in seconds).
   * After the time to live has expired, the key/value pair will be deleted from the cache.
   * Because of the frequency that this method is used, no internal logging or tracing will be done at this level.
   *
   * @access Public
   *
   * @param String $key
   * @param String $value
   * @param Integer $age (optional) - The time to live in seconds (-1 means do not expire. Defaults to 12 hours).
   *
   * @return Boolean - True on success, False on failure.
   */
  public function put($key, $value, $age = 43200) {
    if(!isset($key) || !is_string($key)) {
      throw new Exception('$key must be a string');
    }
    if(!isset( $value ) || !is_string($value)) {
      throw new Exception('$value must be a string');
    }
    if(!isset($age)) {
      throw new Exception('$age must be a positive integer');
    }

    $age = intval($age);
    if($age < 0) {
      return $this->redis->set($key, $value);
    } else {
      return $this->redis->setex($key, $age, $value);
    }
  }

  /**
   * Retrieve a value from the cache with the given key.
   * Because of the frequency that this method is used, no internal logging or tracing will be done at this level.
   *
   * @access Public
   *
   * @param String $key
   *
   * @return String or Boolean - The string value associated with the key if it exists, or False if the key does not exist.
   */
  public function get($key) {
    if(!isset($key) || !is_string($key) || empty($key)) {
      throw new Exception('key must be a string');
    }

    $retis_val = $this->redis->get($key);
    return $retis_val;
  }

  /**
   * Delete a value from the cache with the given key.
   * Because of the frequency that this method is used, no internal logging or tracing will be done at this level.
   *
   * @access Public
   *
   * @param String or Array of String $key - One or more keys to delete from the cache.
   *
   * @return Integer - The number of keys deleted from the cache.
   */
  public function rm($key) {
    if(!isset($key) || !is_string($key) || empty($key)) {
      throw new Exception('key must be a string');
    }

    return $this->redis->delete($key);
  }

  /**
   * Checks to see if a key exists.
   *
   * @param  string $key
   * @return boolean
   */
  public function isExpired($key) {
    if(!isset($key) || !is_string($key) || empty($key)) {
      throw new Exception('key must be a string');
    }
    return $this->redis->exists($key);
  }
}
?>