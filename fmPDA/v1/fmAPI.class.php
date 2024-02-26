<?php
// *********************************************************************************************************************************
//
// fmAPI.class.php
//
// fmAPI provides direct access to FileMaker's API (REST interface). This is a 'base' class that fmDataAPI and fmAdminAPI
// extend to provide access to the Data and Admin APIs. You will typically not instantiate this class directly.
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
// Copyright (c) 2017 - 2024 Mark DeNyse
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

define('FM_VERSION_0',                 '0');                       // Version 0 was released with FileMaker Server 16 (now removed)
define('FM_VERSION_1',                 '1');                       // Version 1 of the Data API was released with FileMaker Server 17
define('FM_VERSION_2',                 '2');                       // Version 2 of the Admin API was released with FileMaker Server 18
                                                                   // Version 2 of the Data API was released with FileMaker Server 19
                                                                   // (the version changed to 2 but there are no API changes).

define('FM_VERSION_LATEST',            'Latest');                  // The latest version


// *********************************************************************************************************************************
define('CONTENT_TYPE_JSON',            'application/json');       // For practically all requests
define('CONTENT_TYPE_MULTIPART_FORM',  'multipart/form-data');    // For uploading a container field

define('FM_CONTENT_TYPE',              'Content-Type:');           // For setting the content type of the payload
define('FM_AUTHORIZATION_BASIC',       'Authorization: Basic');    // For passing username:password
define('FM_AUTHORIZATION_BEARER',      'Authorization: Bearer');   // For passing token
define('FM_OAUTH_REQUEST_ID',          'X-FM-Data-OAuth-Request-Id: ');
define('FM_OAUTH_REQUEST_IDENTIFIER',  'X-FM-Data-OAuth-Identifier: ');

// *********************************************************************************************************************************
define('FM_TOKEN',                     'token');                    // Token passed back from FileMaker
define('FM_ACCESS_TOKEN',              'X-FM-Access-Token');        // Token when returned in HTTP response header

define('FM_RESULT',                    'result');
define('FM_ERROR_MESSAGE',             'errorMessage');

define('FM_MESSAGES',                  'messages');
define('FM_CODE',                      'code');
define('FM_MESSAGE',                   'message');
define('FM_TEXT',                      'text');

define('FM_RESPONSE',                  'response');


// *********************************************************************************************************************************
define('FM_MESSAGE_OK',                'OK');                      // All is good -- this is *not* an error

define('FM_ERROR_INVALID_ACCOUNT',      212);                      // Invalid user account and/or password
define('FM_ERROR_INVALID_TOKEN',        952);                      // Invalid FileMaker Data API token (Data API)
define('FM_ERROR_MAX_CALLS_EXCEEDED',   953);                      // Maximum number of FileMaker Data API calls exceeded

// *********************************************************************************************************************************
define('FM_API_SESSION_TOKEN',         'FM-API-Session-Token');    // Where we store the token in the PHP session


define('FM_MAX_TOKEN_AGE',              (60 * 30));                // How long (in seconds) to wait before we consider a token to
                                                                   // be too old. The Data and Admin APIs both time out the token
                                                                   // after 15 minutes, but we will set this higher, as it's possible
                                                                   // the value could change in the future without us knowing.

// *********************************************************************************************************************************
class fmAPI extends fmCURL
{
   public $host;                                                  // Host
   public $credentials;                                           // base64 encoded username/password
   public $sendCredentialsIfNoToken;                              // If true and there's no token send credentials on call without
                                                                  // doing a login. Admin API v2 supports this (Data API v1 doesn't)
   public $token;                                                 // The security token returned by the API
   public $tokenTimeStamp;                                        // The last time the token was used.
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
    *                                       ['version']                    Version of the API to use (1, 2, etc. or 'Latest')
    *                                       ['host']                       The host to connect to. Normally this is passed directly to
    *                                                                      the fmDataAPI or fmAdminAPI constructor.
    *
    *                                       Token management - typically you choose one of the following 3 options:
    *                                            ['storeTokenInSession'] and ['sessionTokenKey']
    *                                            ['tokenFilePath']
    *                                            ['token']
    *
    *                                       ['sendCredentialsIfNoToken']   If true and no token is passed or can be retrieved, the fmAPI()
    *                                                                      will send a Authorization: Basic header with the credentials.
    *                                                                      The Admin API v2 supports this while the Data API v1 does not.
    *                                                                      If the call is valid, the token is returned by the host and it
    *                                                                      will be stored for you.
    *
    *
    *                                       ['storeTokenInSession']        If true, the token is stored in the $_SESSION[] array (defaults to true)
    *                                       ['sessionTokenKey']            If ['storeTokenInSession'] is true, this is the key field to store
    *                                                                      the token in the $_SESSION[] array. Defaults to FM_API_SESSION_TOKEN,
    *                                                                      but fmDataAPI and fmAdminAPI set their own value so you can store
    *                                                                      tokens to each API.
    *                                       ['sessionTokenKeyTime']        If ['storeTokenInSession'] is true, this is the key field to store
    *                                                                      the last used time stamp of the token.
    *
    *                                       ['tokenFilePath']              Where to read/write a file containing the token. This is useful
    *                                                                      when you're called as a web hook and don't have a typical
    *                                                                      browser-based session to rely on. You should specify a path that
    *                                                                      is NOT visible to the web. If you need to encrypt/decrypt the token
    *                                                                      in the file, override decryptTokenData() and encryptTokenData().
    *
    *                                       ['token']                      The token from a previous call. This will normally be pulled
    *                                                                      from the $_SESSION[] or ['tokenFilePath'], but in cases where
    *                                                                      you need to store it somewhere else, pass it here. You're responsible
    *                                                                      for calling the getToken() method after a successful call to retrieve
    *                                                                      it for your own storage.
    *                                       ['tokenTimeStamp']             The last used timestamp for the token.
    *
    *                                       ['authentication']             set to 'oauth' for oauth authentication
    *
    *    Returns:
    *       The newly created object.
    */
   function __construct($options = array())
   {
      $options['userAgent'] = array_key_exists('userAgent', $options) ? $options['userAgent'] : FM_API_USER_AGENT;

      parent::__construct($options);

      $this->host                      = array_key_exists('host', $options) ? $options['host'] : '';
      $this->sendCredentialsIfNoToken  = array_key_exists('sendCredentialsIfNoToken', $options) ? $options['sendCredentialsIfNoToken'] : false;
      $this->sessionTokenKey           = array_key_exists('sessionTokenKey', $options) ? $options['sessionTokenKey'] : FM_API_SESSION_TOKEN;
      $this->storeTokenInSession       = array_key_exists('storeTokenInSession', $options) ? $options['storeTokenInSession'] : true;
      $this->tokenFilePath             = array_key_exists('tokenFilePath', $options) ? $options['tokenFilePath'] : '';
      $this->version                   = array_key_exists('version', $options) ? $options['version'] : FM_VERSION_LATEST;

      if ($this->storeTokenInSession) {
         if ((version_compare(PHP_VERSION, '5.4.0', '<') && (session_id() == '')) || (session_status() === PHP_SESSION_NONE)) {
            session_start();
         }
      }

      $token = array_key_exists('token', $options) ? $options['token'] : '';
      $tokenTimeStamp = array_key_exists('tokenTimeStamp', $options) ? $options['tokenTimeStamp'] : '';

      if ($token == '') {                                            // Token wasn't passed in, see if we have it stored
         $tokenData = $this->getTokenDataFromStorage();
         if (is_array($tokenData)) {
            $token = array_key_exists('token', $tokenData) ? $tokenData['token'] : '';
            $tokenTimeStamp = array_key_exists('tokenTimeStamp', $tokenData) ? $tokenData['tokenTimeStamp'] : '';
         }
      }

      $authentication = array_key_exists('authentication', $options) ? $options['authentication'] : array();

      $this->setAuthentication($authentication);

      $this->setToken($token, $tokenTimeStamp);

      fmLogger('fmAPI: PHP v'. PHP_VERSION);

      if (strpos(strtolower($this->host), 'http:') !== false) {
         fmLogger('*********************************************************************************************************************.');
         fmLogger("Warning: You passed 'http://' and the Data/Admin API require https:// so your request may fail.");
         fmLogger('Warning: Your server may redirect to https but you SHOULD change your code to avoid an extra roundtrip.');
         fmLogger('Host = '. $this->host);
         fmLogger('*********************************************************************************************************************.');
      }
      if ((strpos(strtolower($this->host), 'localhost') !== false) || (strpos(strtolower($this->host), '127.0.0.1') !== false)) {
         fmLogger('*********************************************************************************************************************.');
         fmLogger("Warning: You are passing '". $this->host ."' as the host name.");
         fmLogger('Warning: This might fail with your SSL certificate so you should use the Fully Qualified Domain Name (FQDN) for the host.');
         fmLogger('Host = '. $this->host);
         fmLogger('*********************************************************************************************************************.');
      }

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

      if (! array_key_exists('CURLOPT_POST', $options) && $this->getIsMethodPost($method)) {
         $options['CURLOPT_POST'] = 1;
      }

      // The Data API is very finicky if passed an empty array as 'no' data.
      // In this case we'll set it to an empty string so nothing will be sent.
      if (is_array($data) && (count($data) == 0)) {
         $data = '';
      }

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

      // If the token is too old, throw it away and request a new one. Typically speaking we should let the server tell us
      // this rather than trying to do this on our own. If the server changes the time out value, or code could become
      // confused and request a token too early or too late.
      //
      // For the v1 and v2 Data API we do not time out our token.
      //
      // For the v2 Admin API we can since we're able to pass the credentials directly in the call and get a new token back.
      // We puroposely use a time out value of 30 minutes which is longer than what FileMaker is currently using. This
      // allows us to err on the side of having the server tell us the token is no longer valid, but it's better than being
      // too quick and needlessly requesting a new token when not needed. When we do detect our token has aged out, we will
      // save two calls (normal API call which fails with bad token and the request for new token) since we're able to directly
      // pass the credentials to the API call we're trying to make.
      $tokenAge = $this->getTokenAge();
      if ($this->sendCredentialsIfNoToken && ($tokenAge > 0) && ($tokenAge > FM_MAX_TOKEN_AGE)) {  // If the token is too old, don't use it.
         $this->setToken();
         $apiOptions[FM_TOKEN] = '';
      }

      if ($apiOptions[FM_TOKEN] == '') {                                         // No token, first time caller (love your show!)
         if ($this->sendCredentialsIfNoToken && ($this->credentials != '')) {    // AdminAPI v2 supports sending credentials without login
            $apiOptions[FM_AUTHORIZATION_BASIC] = $this->credentials;
         }
         else {
            $result = $this->login();                                            // Login and save token if valid credentials
            if ($this->getIsError($result)) {
               return $result;
            }
            $apiOptions[FM_TOKEN] = $this->getToken();                           // Logged in, so grab our token
         }
      }

      $result = $this->curlAPI($url, $method, $data, $apiOptions);               // Call into FileMaker's API...

      // If the API told us the token is invalid it's typically going to be that it 'timed out' (approximately 15 minutes of no use),
      // so request a new token by logging in again, then reissuing our request to the API.
      if ($this->getIsBadToken($result)) {
         $result = $this->login();
         if (! $this->getIsError($result)) {
            $apiOptions[FM_TOKEN] = $this->getToken();
            $result = $this->curlAPI($url, $method, $data, $apiOptions);         // Send the request (again)
         }
      }

      // If there's a token in the HTTP header, update our token with it. This covers the case where we don't have it
      // stored or it changed(!) on the server. Either way we're covered. setToken() will also update the time stamp.
      else if (! $this->getIsError($result)) {
         if (array_key_exists(FM_ACCESS_TOKEN, $this->httpHeaders) && array_key_exists(0, $this->httpHeaders[FM_ACCESS_TOKEN])) {
            $this->setToken($this->httpHeaders[FM_ACCESS_TOKEN][0]);
         }
         else {
            $this->setToken($this->getToken());                                 // Update the time stamp
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
      // $this->httpHeaders

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
      $path = $this->host . str_replace('%%%VERSION%%%', $this->version, $requestPath);

      return $path;
   }

   // *********************************************************************************************************************************
   public function getToken()
   {
      return $this->token;
   }

   // *********************************************************************************************************************************
   public function getTokenAge()
   {
      $age = -1;

      if (($this->token != '') && ($this->tokenTimeStamp != '')) {
         $age = time() - $this->tokenTimeStamp;
      }

      return $age;
   }

   // *********************************************************************************************************************************
   public function getTokenDataFromStorage()
   {
      $tokenData = array();

      if (isset($_SESSION) && array_key_exists($this->sessionTokenKey, $_SESSION) && ($_SESSION[$this->sessionTokenKey] != '')) {
         $theData = $this->decryptTokenData(unserialize($_SESSION[$this->sessionTokenKey]));
         if (is_array($theData)) {
            $tokenData = $theData;
         }
      }

      if ((count($tokenData) == 0) and ($this->tokenFilePath != '') && file_exists($this->tokenFilePath)) {
         $theData = $this->decryptTokenData(unserialize(file_get_contents($this->tokenFilePath)));
         if (is_array($theData)) {
            $tokenData = $theData;
         }
      }

      return $tokenData;
   }

   // *********************************************************************************************************************************
   public function setAuthentication($data = array())
   {
      $this->credentials = '';
      $this->setToken();                                                                        // Invalidate token

      return;
   }

   // *********************************************************************************************************************************
   protected function setToken($token = '', $tokenTimeStamp = '')
   {
      $this->token = $token;

      if ($tokenTimeStamp != '') {
         $this->tokenTimeStamp = $tokenTimeStamp;
      }
      else {
         $this->tokenTimeStamp = ($token != '') ? time() : 0;
      }

      $this->setTokenDataInStorage();

      return;
   }

   // *********************************************************************************************************************************
   // Override this method if you want to store the token somewhere else, such as a MySQL database.
   //
   protected function setTokenDataInStorage()
   {
      $tokenData = array();
      $tokenData['token']          = $this->token;
      $tokenData['tokenTimeStamp'] = $this->tokenTimeStamp;
      $serialized = serialize($tokenData);

      if (isset($_SESSION) && $this->storeTokenInSession) {
         $_SESSION[$this->sessionTokenKey] = $this->encryptTokenData($serialized);
      }

      if ($this->tokenFilePath != '') {
         file_put_contents($this->tokenFilePath, $this->encryptTokenData($serialized));
      }

      return;
   }

   // *********************************************************************************************************************************
   // Override this and decryptTokenData if you want the authentication token encrypted in the $_SESSION and/or local file.
   //
   protected function encryptTokenData($tokenData)
   {
      return $tokenData;
   }

   // *********************************************************************************************************************************
   protected function decryptTokenData($tokenData)
   {
      return $tokenData;
   }

   // *********************************************************************************************************************************
   // encodeParameter
   //
   // rawurlencode a value if it's a GET/DELETE method.
   //
   protected function encodeParameter($value, $method)
   {
      if (($method == METHOD_GET) || ($method == METHOD_DELETE)) {
         $value = rawurlencode($value);
      }

      return $value;
   }

}

?>
