<?php
namespace Core\Framework;

class Utils {
  const RESPONSE_CODES = '/www/code/config/response_codes.ini';

  public static function processRequest() {
    $request = new Request\HTTP();

    echo "\nRequest is : " . var_export($request, true) . "\n";
    // get our verb
    echo "\nGET Data was : " . var_export($_GET, true) . "\n";

    $request_method = strtolower($_SERVER['REQUEST_METHOD']);
    $return_obj     = new Request($request_method);
    // we'll store our data here
    $data     = array();
    $put_vars = array();
    switch($request_method) {
      // gets are easy...
      case 'get':
        $data = $_GET;
        echo "\nGET Data was : " . var_export($_GET, true) . "\n";
      break;
      // so are posts
      case 'post':
        $data = $_POST;
      break;
      // here's the tricky bit...
      case 'put':
        // basically, we read a string from PHP's special input location,
        // and then parse it out into an array via parse_str... per the PHP docs:
        // Parses str  as if it were the query string passed via a URL and sets
        // variables in the current scope.
        parse_str(file_get_contents('php://input'), $put_vars);
        $data = $put_vars;
      break;
      case 'delete':
      break;
      default:
        // return that they have made an invald request...
      break;
    }

    // store the method
    $return_obj->setMethod($request_method);

    // set the raw data, so we can access it if needed (there may be other pieces to your requests)
    $return_obj->setRequestVars($data);

    if(isset($data['data'])) {
      // translate the JSON to an Object for use however you want
      $return_obj->setData(json_decode($data['data']));
    }
 
    return $return_obj;
  }

  public static function sendResponse($status = 200, $body = '', $content_type = 'text/html') {
    $status_header = 'HTTP/1.1 ' . $status . ' ' . Utils::getStatusCodeMessage($status);
    // set the status
    header($status_header);
    // set the content type
    header('Content-type: ' . $content_type);
    // pages with body are easy
    if(!empty($body)) {
      echo $body . "\n";
      return false;
    } else {
      // we need to create the body if none is passed
      // create some body messages
      $message = '';

      // this is purely optional, but makes the pages a little nicer to read
      // for your users.  Since you won't likely send a lot of different status codes,
      // this also shouldn't be too ponderous to maintain
      switch($status) {
        case 401:
          $message = 'You must be authorized to view this page.';
        break;
        case 404:
          $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
        break;
        case 500:
          $message = 'The server encountered an error processing your request.';
        break;
        case 501:
          $message = 'The requested method is not implemented.';
        break;
      }

      // servers don't always have a signature turned on (this is an apache directive "ServerSignature On")
      $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

      // this should be templatized in a real-world solution
      echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
      <html>
      <head>
      <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
      <title>' . $status . ' ' . Utils::getStatusCodeMessage($status) . '</title>
      </head>
      <body>
      <h1>' . Utils::getStatusCodeMessage($status) . '</h1>
      <p>' . $message . '</p>
      <hr />
      <address>' . $signature . '</address>
      </body>
      </html>';
      return true;
    }
  }

  public static function getStatusCodeMessage($status) {
    $status = intval($status);
    $codes = parse_ini_file(Utils::RESPONSE_CODES);
    return (isset($codes[$status])) ? $codes[$status] : '';
  }
}
?>