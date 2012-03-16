<?php
include_once("/www/code/includes/init.php");
//
//$paramString = "";
//if (isset($post_fields) && is_array( $post_fields ) ) {
//  $paramString = "?";
//  foreach( $post_fields as $param => $val ) {
//    $paramString .= $param . '='. urlencode( $val ) . "&";
//  }
//  $paramString = substr($paramString,0,-1);
//}
$method = '/user';
$curl = curl_init();
// we want the output here...
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
// commenting out using sessions for now...
//curl_setopt( $curl, CURLOPT_URL, ('http://192.168.1.105/' . $method . $paramString . '&' . session_name().'='.session_id()) );
// can just use the url in curl_init if we want....
// for now, just call the method we define...
curl_setopt( $curl, CURLOPT_URL, 'http://192.168.1.105' . $method);
// Uncomment this when ready to test post.
//curl_setopt( $curl, CURLOPT_POST, false );
// Uncomment this to ignore the header...
//curl_setopt( $curl, CURLOPT_HEADER, false );
// Uncomment this to test the certificate...
//curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($curl, CURLOPT_TIMEOUT, 10);

$raw_json = curl_exec( $curl );
curl_close( $curl );
echo "\nJSON OUTPUT = " . print_r(json_decode($raw_json, true));
exit;
?>