<?php
// *********************************************************************************************************************************
//
// fmAPI.class.php
//
// fmAPI provides direct access to FileMaker's API (REST interface). This is a 'base' class that fmDataAPI and fmAdminAPI
// extend to provide access to the Data and Admin APIs.
//
// FileMaker's Data API documentation can be found here:
// https://support.filemaker.com/s/answerview?language=en_US&anum=000025925#issues
// https://<your.FileMaker.Server>/fmi/data/apidoc/
// Web: http://fmhelp.filemaker.com/docs/17/en/dataapi/
// Mac FMS: /Library/FileMaker Server/Documentation/Data API Documentation
// Windows FMS: [drive]:\Program Files\FileMaker\FileMaker Server\Documentation\Data API Documentation
//
//
// Admin Console API:
// https://<your.FileMaker.Server>/fmi/admin/apidoc/
// Mac FMS: /Library/FileMaker Server/Documentation/Admin API Documentation
// Windows FMS: [drive]:\Program Files\FileMaker\FileMaker Server\Documentation\Admin API Documentation
//
// *********************************************************************************************************************************
//
// Copyright (c) 2017 - 2018 Mark DeNyse
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.
//
// *********************************************************************************************************************************

require_once 'fmCURL.class.php';

// *********************************************************************************************************************************
define('FM_API_USER_AGENT',            'fmAPIphp/1.0');            // Our user agent string

define('FM_VERSION_LATEST',            'Latest');                  // By default we'll always use the latest version
define('FM_VERSION_0',                 '0');                       // Version 0 was released with FileMaker Server 16
define('FM_VERSION_1',                 '1');                       // Version 1 was released with FileMaker Server 17


// *********************************************************************************************************************************
define('CONTENT_TYPE_JSON',            'application/json');       // For practically all requests
define('CONTENT_TYPE_MULTIPART_FORM',  'multipart/form-data');    // For uploading a container field

define('FM_CONTENT_TYPE',              'Content-Type:');           // For setting the content type of the payload
define('FM_AUTHORIZATION_BASIC',       'Authorization: Basic');    // For passing username:password
define('FM_AUTHORIZATION_BEARER',      'Authorization: Bearer');   // For passing token
define('FM_OAUTH_REQUEST_ID',          'X-FM-Data-OAuth-Request-Id: ');
define('FM_OAUTH_REQUEST_IDENTIFIER',  'X-FM-Data-OAuth-Identifier: ');

// *********************************************************************************************************************************
define('FM_TOKEN',                     'token');                  // Token passed back from FileMaker

define('FM_RESULT',                    'result');
define('FM_ERROR_MESSAGE',             'errorMessage');

define('FM_MESSAGES',                  'messages');
define('FM_CODE',                      'code');
define('FM_MESSAGE',                   'message');

define('FM_RESPONSE',                  'response');


// *********************************************************************************************************************************
define('FM_MESSAGE_OK',                   'OK');                   // All is good -- this is *not* an error

define('FM_ERROR_INVALID_ACCOUNT',         212);                   // Invalid user account and/or password
define('FM_ERROR_INVALID_TOKEN',           952);                   // Invalid FileMaker Data API token (Data API)
define('FM_ERROR_MAX_CALLS_EXCEEDED',      953);                   // Maximum number of FileMaker Data API calls exceeded

// *********************************************************************************************************************************
define('FM_API_SESSION_TOKEN',            'FM-API-Session-Token'); // Where we store the token in the PHP session


// *********************************************************************************************************************************
class fmAPI extends fmCURL
{
   public $host;                                                  // Host
   public $credentials;                                           // base64 encoded username/password
   public $token;                                                 // The security token returned by the API
   public $storeTokenInSession;                                   // Store the security token in a session variable?
   public $sessionTokenKey;                                       // The key value of where to store the token in the session
   public $tokenFilePath;                                         // The file path where the token is saved. This is useful when
                                                                  // your code is called as a web hook for example, where there is
                                                                  // not a session to store the token in.
   public $version;                                               // What version of the Data API should we use (1, 2, Latest)?


   /***********************************************************************************************************************************
    *
    * __construct($options = array())
    *
    *    Constructor for fmAPI. Normally you will not instatiate an object of this class directly.
    *
    *       (array)   $options          Optional parameters
    *                                       ['version']              Version of the API to use (1, 2, etc. or 'Latest')
    *                                       ['host']                 The host to connect to. Normally this is passed directly to
    *                                                                the fmDataAPI or fmAdminAPI constructor.
    *
    *                                       Token management - typically you choose one of the following 3 options:
    *                                            ['storeTokenInSession'] and ['sessionTokenKey']
    *                                            ['tokenFilePath']
    *                                            ['token']
    *
    *                                       ['storeTokenInSession']  If true, the token is stored in the $_SESSION[] array (defaults to true)
    *                                       ['sessionTokenKey']      If ['storeTokenInSession'] is true, this is the key field to store
    *                                                                the token in the $_SESSION[] array. Defaults to FM_API_SESSION_TOKEN,
    *                                                                but fmDataAPI and fmAdminAPI set their own value so you can store
    *                                                                tokens to each API.
    *
    *                                       ['tokenFilePath']        Where to read/write a file containing the token. This is useful
    *                                                                when you're called as a web hook and don't have a typical
    *                                                                browser-based session to rely on. You should specify a path that
    *                                                                is NOT visible to the web. If you need to encrypt/decrypt the token
    *                                                                in the file, override decryptToken() and encryptToken().
    *
    *                                       ['token']                The token from a previous call. This will normally be pulled
    *                                                                from the $_SESSION[] or ['tokenFilePath'], but in cases where
    *                                                                you need to store it somewhere else, pass it here. You're responsible
    *                                                                for calling the getToken() method after a successful call to retrieve
    *                                                                it for your own storage.
    *
    *                                       ['authentication']       set to 'oauth' for oauth authentication
    *
    *    Returns:
    *       The newly created object.
    */
   function __construct($options = array())
   {
      $options['userAgent'] = array_key_exists('userAgent', $options) ? $options['userAgent'] : FM_API_USER_AGENT;

      parent::__construct($options);

      $this->host                   = array_key_exists('host', $options) ? $options['host'] : '';
      $this->sessionTokenKey        = array_key_exists('sessionTokenKey', $options) ? $options['sessionTokenKey'] : FM_API_SESSION_TOKEN;
      $this->storeTokenInSession    = array_key_exists('storeTokenInSession', $options) ? $options['storeTokenInSession'] : true;
      $this->tokenFilePath          = array_key_exists('tokenFilePath', $options) ? $options['tokenFilePath'] : '';
      $this->version                = array_key_exists('version', $options) ? $options['version'] : FM_VERSION_LATEST;

      $token = array_key_exists('token', $options) ? $options['token'] : '';

      if ($this->storeTokenInSession) {
         if ((version_compare(PHP_VERSION, '5.4.0', '<') && (session_id() == '')) || (session_status() == PHP_SESSION_NONE)) {
            session_start();
         }
      }

      if ($token == '') {
         $token = $this->getTokenFromStorage();
      }

      $authentication = array_key_exists('authentication', $options) ? $options['authentication'] : array();

      $this->setAuthentication($authentication);

      $this->setToken($token);

      fmLogger('fmAPI: PHP v'. PHP_VERSION);

      return;
   }

   // *********************************************************************************************************************************
   public function apiLogin()
   {
      return $this->login();
   }

   // *********************************************************************************************************************************
   // Typically you do not need/want to log out - you want to keep using your token for best performance.
   // Only logout if you know you're completely done with the session. This will also clear the stored credentials.
   public function apiLogout()
   {
      $this->setAuthentication();

      return null;
   }

   // *********************************************************************************************************************************
   // Use curl to call the API.
   //
   // We add in the standard headers here for the typical call to the API.
   //
   // If the call was sucessful, this returns returns the JSON data decoded into an array.
   // If the call fails, the caller should call getIsError() or getMessageInfo() to determine what happened.
   //
   // While 'public', it's really intended only to be called by the fmAPI() and login() methods.
   //
   public function curlAPI($url, $method = METHOD_GET, $data = '', $options = array())
   {
      // Start with any headers the caller may have set in 'CURLOPT_HTTPHEADER', then add in the standard ones.
      $header = (array_key_exists('CURLOPT_HTTPHEADER', $options) && is_array($options['CURLOPT_HTTPHEADER'])) ? $options['CURLOPT_HTTPHEADER'] : array();

      if (array_key_exists(FM_CONTENT_TYPE, $options) && ($options[FM_CONTENT_TYPE] != '')) {
         $header[] = FM_CONTENT_TYPE .' '. $options[FM_CONTENT_TYPE];
      }

      if (array_key_exists(FM_AUTHORIZATION_BASIC, $options) && ($options[FM_AUTHORIZATION_BASIC] != '')) {
         $header[] = FM_AUTHORIZATION_BASIC .' '. $options[FM_AUTHORIZATION_BASIC];
      }
      else if (array_key_exists(FM_TOKEN, $options) && ($options[FM_TOKEN] != '')) {
         $header[] = FM_AUTHORIZATION_BEARER .' '. $options[FM_TOKEN];
      }
      else if (array_key_exists(FM_OAUTH_REQUEST_ID, $options) && ($options[FM_OAUTH_REQUEST_ID] != '')) {
         $header[] = FM_OAUTH_REQUEST_ID .' '. $options[FM_OAUTH_REQUEST_ID];
         $header[] = FM_OAUTH_REQUEST_IDENTIFIER .' '. $options[FM_OAUTH_REQUEST_IDENTIFIER];
      }

      $options['CURLOPT_HTTPHEADER'] = $header;

      $result = $this->curl($url, $method, $data, $options);

      return $result;
   }

   // *********************************************************************************************************************************
   // Make a call into the API. Returns the json result *or* an empty array and the caller should call getMessageInfo() to find out why.
   //
   // A typical reply will be a JSON encoded object containing a response and messages array. messages is an array of code/message.
   // response is where the data is returned.
   //
   // Typically you will use one of the apiXXXX() methods to talk to the API instead of calling this directly.
   // You could override this method to do some post processing of the data if the need arises.
   //
   public function fmAPI($url, $method = METHOD_GET, $data = '', $options = array())
   {
      $apiOptions[FM_CONTENT_TYPE]  = CONTENT_TYPE_JSON;
      $apiOptions[FM_TOKEN]         = $this->getToken();
      $apiOptions['encodeAsJSON']   = true;
      $apiOptions['decodeAsJSON']   = true;

      $apiOptions = array_merge($apiOptions, $options);

      if ($apiOptions[FM_TOKEN] == '') {                                         // No token, first time caller (love your show!)
         $result = $this->login();                                               // Login and save token if valid credentials
         if ($this->getIsError($result)) {
            return $result;
         }
         $apiOptions[FM_TOKEN] = $this->getToken();                              // Logged in, so grab our token
      }

      $result = $this->curlAPI($url, $method, $data, $apiOptions);              // Call into FileMaker's API...

      // If the API told us the token is invalid it's typically going to be that it 'timed out' (approximately 15 minutes of no use),
      // so request a new token by logging in again, then reissuing our request to the API.
      if ($this->getIsBadToken($result)) {
         $result = $this->login();
         if (! $this->getIsError($result)) {
            $apiOptions[FM_TOKEN] = $this->getToken();
            $result = $this->curlAPI($url, $method, $data, $apiOptions);        // Send the request (again)
         }
      }


      // If you're using this class directly (not fmPDA), remember to check for an error code *OR* message as the API
      // may return one or both for an error. getMessageInfo() will return either a curl or Data API error.


      // For those wanting to accumulate statstics of round trip times into the API, this is where you could do it easily.
      // You have available to you:
      // $method
      // $url
      // $data
      // $this->callTime
      // $this->curlErrNum
      // $this->curlErrMsg
      // $this->curlInfo
      // $this->httpCode

      return $result;
   }

   // *********************************************************************************************************************************
   // Returns true if the error result indicates the token is bad.
   //
   protected function getIsBadToken($result)
   {
      $isBadToken = false;

      return $isBadToken;
   }

   // *********************************************************************************************************************************
   // Returns an array of response messages returned in the result from the server. It's possible that more than one error could be
   // returned, so you'll either need to walk the array or look for a specific code with the getCodeExists() method.
   //
   public function getMessages($result)
   {
      $messages = array();

      return $messages;
   }

   // *********************************************************************************************************************************
   // Returns the response returned in the result from the server. This is where the data gets returned.
   //
   public function getResponse($result)
   {
      $response = array();

      return $response;
   }

   // *********************************************************************************************************************************
   // Returns the response data (only) returned in the result from the server.
   //
   public function getResponseData($result)
   {
      $responseData = array();

      return $responseData;
   }

   // *********************************************************************************************************************************
   // Returns true if the specified code exists in the messages array.
   //
   public function getCodeExists($result, $code)
   {
      $codeExists = false;

      $messages = $this->getMessages($result);

      foreach ($messages as $message) {
         if (array_key_exists(FM_CODE, $message) && ($message[FM_CODE] == $code)) {
            $codeExists = true;
         }
      }

      return $codeExists;
   }

   // *********************************************************************************************************************************
   // Returns true if there's an error from the Data API or curl(). An 'error' from the Data API is a non-zero value in the FM_CODE
   // or a non-empty and not 'OK' value in FM_MESSAGE.
   //
   public function getIsError($result = '')
   {
      $isError = false;

      $messages = $this->getMessages($result);
      foreach ($messages as $message) {
         if (array_key_exists(FM_CODE, $message) && ($message[FM_CODE] != 0)) {
            $isError = true;
         }
         else if (array_key_exists(FM_MESSAGE, $message) && ($message[FM_MESSAGE] != '') && ($message[FM_MESSAGE] != FM_MESSAGE_OK)) {
            $isError = true;
         }
      }

      if (! $isError) {
         if (($this->curlErrNum != '') && ($this->curlErrMsg != '')) {
            $isError = true;
         }
      }

      return $isError;
   }

   // *********************************************************************************************************************************
   // Returns the *first* code/message pair in the messages response. USE CAREFULLY as you could be ignoring a potentially more
   // important error messsage.
   //
   public function getFirstMessage($result)
   {
      $message = null;

      $messages = $this->getMessageInfo($result);

      if (count($messages) > 0) {
         $message = $messages[0];
      }

      return $message;
   }

   // *********************************************************************************************************************************
   // Returns any error from the Data API; if none it then checks to see if there is a curl error to return.
   // Caution: that the result returned is an *array* of code/message pairs. The Data API may returned multiple pairs.
   // We only return code/message pairs that are non-zero codes and non-empty and not 'OK' messages.
   //
   public function getMessageInfo($result)
   {
      $messageInfo = array();

      $messages = $this->getMessages($result);

      foreach ($messages as $message) {
         $theCode    = (array_key_exists(FM_CODE, $message) && ($message[FM_CODE] != 0)) ? $message[FM_CODE] : '';
         $theMessage = (array_key_exists(FM_MESSAGE, $message) && ($message[FM_MESSAGE] != '')) ? $message[FM_MESSAGE] : '';

         if ($theCode != 0) {
            $messageInfo[] = $message;
         }
         else if (($theMessage != '') && ($theMessage != FM_MESSAGE_OK)) {
            $messageInfo[] = $message;
         }
      }

      if (count($messageInfo) == 0) {
         if (($this->curlErrNum != '') && ($this->curlErrMsg != '')) {
            $messageInfo[] = array(FM_CODE => $this->curlErrNum, FM_MESSAGE => $this->curlErrMsg);
         }
      }

      // Uncomment these lines if you want the HTTP result to be returned.
      // These codes may conflict with FileMaker error codes, so beware.
//    if ((count($messageInfo) == 0) && ($this->curlInfo['http_code'] != HTTP_SUCCESSS)) {
//       $messageInfo[] = array(FM_CODE => $this->curlInfo['http_code'], FM_MESSAGE => '');
//    }

      return $messageInfo;
   }

   // *********************************************************************************************************************************
   // This method is called internally whenever no or an invalid token is passed to the Data API.
   //
   protected function login()
   {
      return null;
   }

   // *********************************************************************************************************************************
   public function getAPIPath($requestPath)
   {
      $search  = array('%%%VERSION%%%');
      $replace = array($this->version);

      $path = $this->host . str_replace($search, $replace, $requestPath);

      return $path;
   }

   // *********************************************************************************************************************************
   public function getToken()
   {
      return $this->token;
   }

   // *********************************************************************************************************************************
   public function getTokenFromStorage()
   {
      $token = '';

      if (isset($_SESSION) && array_key_exists($this->sessionTokenKey, $_SESSION) && ($_SESSION[$this->sessionTokenKey] != '')) {
         $token = $_SESSION[$this->sessionTokenKey];
      }

      if (($token == '') and ($this->tokenFilePath != '') && file_exists($this->tokenFilePath)) {
         $token = file_get_contents($this->tokenFilePath);
      }

      return $this->decryptToken($token);
   }

   // *********************************************************************************************************************************
   public function setAuthentication($data = array())
   {
      $this->credentials = '';
      $this->setToken('');                                                                        // Invalidate token

      return;
   }

   // *********************************************************************************************************************************
   protected function setToken($token)
   {
      $this->token = $token;

      if (isset($_SESSION) && $this->storeTokenInSession) {
         $_SESSION[$this->sessionTokenKey] = $this->encryptToken($token);
      }

      if ($this->tokenFilePath != '') {
         file_put_contents($this->tokenFilePath, $this->encryptToken($token));
      }

      return;
   }

   // *********************************************************************************************************************************
   // Override this and decryptToken if you want the authentication token encrypted in the $_SESSION and/or local file.
   //
   protected function encryptToken($token)
   {
      return $token;
   }

   // *********************************************************************************************************************************
   protected function decryptToken($token)
   {
      return $token;
   }

}

?>
