<?php
namespace Core\Packages;
/**
 * @author Jonathon Hibbard
 * 
 * An Authentication Object to validate usage with the REST API... 
 * 
 * @uses the PBKDF2 HASH generation method created by Andrew Johnson
 * @see Auth::PBKDF2()
 * 
 * @example
 * <?php
 * // include our autoloader..
 * include_once("init.php");
 * 
 * $client_id      = 55;
 * $api_key        = MySanitizer::sanitize_alpha_numeric_only($api_key);
 * $nonce          = MySanitizer::sanitize_md5($nonce);
 * $HMAC_signature = MySanitizer::sanitize_base64($HMAC_String);
 * 
 * $RESTAuth = new Core\Packages\Auth($client_id, $api_key, $nonce, $HMAC_signature);
 * if($RESTAuth->is_authorized === true) {
 *   // do some fun stuff with the API
 * } else {
 *   // ruh roh raggy.  Lets do something useful like track this request, who sent it, and maybe even check if we need to lock this account
 * }
 */
class Auth {
  private $is_authorized  = false,
          $client_id      = 0,
          $public_api_key = null;

  /**
   * Verifies that the client_id is valid, and that the api_key being used is valid for this client_id.
   * 
   * @param  integer $client_id
   * @param  string $api_key
   * @return boolean // Returns true on success.
   * 
   * @throws Exception // Exceptions are thrown when either the client_oid or the api_key is found to be invalid.
   */
  private function isValidPublicKey($client_id, $api_key) {
    $validate_client_id = true;
    $validate_api_key   = true;

    if($validate_client_id === false) {
      throw new Exception("Invalid Client ID!");
    }

    if($validate_api_key === false) {
      throw new Exception("Invalid API Key for this client!");
    }

    $this->client_id      = $client_id;
    $this->public_api_key = $api_key;
    return true;
  }

  /**
   * Gets the private key for the current client.
   * 
   * @return string $private_key  // Returns the private key for the client being requested.
   * @throws Exception   // Exceptions are thrown when the client_id hasn't been setup yet (we can't get a private key without a client_id!)
   */
  private function getPrivateKey() {
    if(intval($this->client_id) < 1) {
      throw new Exception("Cannot get private key for an invalid client!");
    }
    // do some magic to findout who the client is, and then lookup who the client belongs to.
    $client_private_key = 'PrivateKeyGoesHere';
    return $client_private_key;
  }

  /**
   * @author Andrew Johnson
   * @source http://www.itnewb.com/tutorial/Encrypting-Passwords-with-PHP-for-Storage-Using-the-RSA-PBKDF2-Standard
   * 
   * PBKDF2 Implementation (described in RFC 2898 - http://www.ietf.org/rfc/rfc2898.txt)
   *
   * @param string $private_key       // The private_key salt.
   * @param int $count                // iteration count - use 1024 or higher)
   * @param int $key_length           // derived key length in bytes
   * @param boolean $return_as_base64 // Returns the pbkdf2 value in base64 when set to true (default).  Returns as binary if not.
   *
   * @return string The pbkdf2 value in binary or base64 (based on $return_as_base64)...
   */
  private function PBKDF2($private_key, $count = 1024, $key_length = 16, $return_as_base64 = true) {
    # force sha1 as the algorithm...
    $algorithm = 'sha1';

    if(!isset($this->public_api_key) || !is_string($this->public_api_key)) {
      throw new Exception("The API Key must be a valid string!");
    }

    if(empty($private_key) || !is_string($private_key)) {
      throw new Exception("The Private Key must be a valid string!");
    }

    if(intval($count) < 1024) {
      throw new Exception("Count must be at least 1024...");
    }

    if(intval($key_length) != 16 && intval($key_length) != 32) {
      throw new Exception("The key length must either be 16 or 32!");
    }

    # Hash length
    $hl = strlen(hash($algorithm, null, true));
    # Key blocks to compute
    $kb = ceil($key_length / $hl);
    # Derived key
    $dk = '';

    # Create key
    for($block = 1; $block <= $kb; $block ++ ) {
      # Initial hash for this block
      $ib = $b = hash_hmac($algorithm, $private_key . pack('N', $block), $this->public_api_key, true);
      # Perform block iterations
      for($i = 1; $i < $count; $i ++) {
        # XOR each iterate
        $ib ^= ($b = hash_hmac($algorithm, $b, $this->public_api_key, true));
      }
      $dk .= $ib; # Append iterated block
    }

    # Return derived key of correct length
    $pbkdf2_hash = substr($dk, 0, $key_length);
    if($return_as_base64 === true) {
      return base64_encode($pbkdf2_hash);
    } else {
      return $pbkdf2_hash;
    }
  }


  /**
   * Contructor for the RESTAuth object.  Validates the HMAC signature being used is valid
   * for the passed api_key, and that the api_key is valid for the client attemping to use it.
   * 
   * @param integer $client_id     // The client that is making the request.
   * @param string $api_key        // The Public API assigned to the client.
   * @param string $nonce          // The nonce (md5(time() . rand()) generated by the client) to reduce relay/replay attack
   * @param string $HMAC_signature // The PBKDF2 HASH signature submitted which claims to be the valid key.
   * 
   * @return boolean $this->is_authenticated  // If authentication is passed, true is returned.  Otherwise, false is returned.
   */
  public function __construct($client_id, $api_key, $nonce, $HMAC_signature) {
    if($this->isValidPublicKey($client_id, $api_key) === true) {
      // append the nonce to the API key so we can test the signature being sent with what we get here.
      $this->public_api_key .= $nonce;

      $challenge_password = $this->PBKDF2($this->getPrivateKey());
      if($challenge_password == $HMAC_signature) {
        $this->is_authorized = true;
      }
    }
  }

  public function isAuthorized() {
    return $this->is_authorized;
  }
}
?>