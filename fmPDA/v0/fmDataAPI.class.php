<?php
// *********************************************************************************************************************************
//
// fmDataAPI.class.php
//
// fmDataAPI provides direct access to FileMaker's Data API (REST interface). You can use this class alone to communicate
// with the Data API and not have any translation done on the data returned. If you want to send/receive data like the old
// FileMaker API For PHP, use the fmPDA class.
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
define('PATH_AUTH',                    '/fmi/rest/api/auth/');
define('PATH_RECORD',                  '/fmi/rest/api/record/');
define('PATH_FIND',                    '/fmi/rest/api/find/');
define('PATH_GLOBAL',                  '/fmi/rest/api/global/');

// *********************************************************************************************************************************
define('CONTENT_TYPE_JSON',            'application/json');

// *********************************************************************************************************************************
// For internal use - indices in the JSON returned by the Data API.
define('FM_ERROR_CODE',                'errorCode');
define('FM_ERROR_MESSAGE',             'errorMessage');
define('FM_RESULT',                    'result');
define('FM_DATA',                      'data');
define('FM_FIELD_DATA',                'fieldData');
define('FM_PORTAL_DATA',               'portalData');
define('FM_RECORD_ID',                 'recordId');
define('FM_MOD_ID',                    'modId');

// *********************************************************************************************************************************
define('FM_DATA_TOKEN',                'FM-Data-token');

// *********************************************************************************************************************************
define('FM_ERROR_FIELD_IS_MISSING',    100);                      // Field is missing
define('FM_ERROR_RECORD_IS_MISSING',   101);                      // Reecord is missing
define('FM_ERROR_INVALID_ACCOUNT',     212);                      // Invalid user account and/or password
define('FM_ERROR_INVALID_TOKEN',       952);                      // Invalid FileMaker Data API token
define('FM_ERROR_MAX_CALLS_EXCEEDED',  953);                      // Maximum number of FileMaker Data API calls exceeded

// *********************************************************************************************************************************
if (! defined('FILEMAKER_FIND_LT')) {
// From FileMaker's FileMaker.php
define('FILEMAKER_FIND_LT', '<');
define('FILEMAKER_FIND_LTE', '<=');
define('FILEMAKER_FIND_GT', '>');
define('FILEMAKER_FIND_GTE', '>=');
define('FILEMAKER_FIND_RANGE', '...');
define('FILEMAKER_FIND_DUPLICATES', '!');
define('FILEMAKER_FIND_TODAY', '//');
define('FILEMAKER_FIND_INVALID_DATETIME', '?');
define('FILEMAKER_FIND_CHAR', '@');
define('FILEMAKER_FIND_DIGIT', '#');
define('FILEMAKER_FIND_CHAR_WILDCARD', '*');
define('FILEMAKER_FIND_LITERAL', '""');
define('FILEMAKER_FIND_RELAXED', '~');
define('FILEMAKER_FIND_FIELDMATCH', '==');

define('FILEMAKER_FIND_AND', 'and');
define('FILEMAKER_FIND_OR', 'or');

define('FILEMAKER_SORT_ASCEND', 'ascend');
define('FILEMAKER_SORT_DESCEND', 'descend');
}

// *********************************************************************************************************************************
class fmDataAPI extends fmCURL
{
   public $host;                                                  // Host
   public $database;                                              // Database name (should NOT include .fmp12)
   public $username;                                              // Account username
   public $password;                                              // Account password
   public $loginLayout;                                           // The layout we use to login 'on'
   public $token;                                                 // The security token returned by the Data API
   public $storeTokenInSession;                                   // Store the security token in a session variable?

   function __construct($database, $host, $username, $password, $loginLayout, $options = array())
   {
      parent::__construct($options);

      $this->database = $database;
      $this->host = $host;
      $this->username = $username;
      $this->password = $password;
      $this->loginLayout = $loginLayout;

      $this->storeTokenInSession = array_key_exists('storeTokenInSession', $options) ? $options['storeTokenInSession'] : true;
      $this->token = array_key_exists('token', $options) ? $options['token'] : '';

      // If the caller passses in a token we will use that and not attmept to store it in the session.
      // If the caller says don't stored the token in the session, it'll be up to them to store the token
      // somewhere appropriate and to grab the value from this object after a successful call so that it
      // can be passed in again (assuming you aren't resuing this object, which is fine to do).
      if ($this->storeTokenInSession) {
         if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            if (session_id() == '') {
                session_start();
            }
         }
         else {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
         }

         if (($this->token == '') && array_key_exists(FM_DATA_TOKEN, $_SESSION) && ($_SESSION[FM_DATA_TOKEN] != '')) {
            $this->token = $_SESSION[FM_DATA_TOKEN];
         }
      }

      return;
   }

   // *********************************************************************************************************************************
   function apiCreateRecord($layout, $fields)
   {
      $data = array();
      $data[FM_DATA] = $fields;

      return $this->dataAPI($this->getAPIPath(PATH_RECORD) .'/'. rawurlencode($layout), METHOD_POST, $data);
   }

   // *********************************************************************************************************************************
   function apiDeleteRecord($layout, $recordID)
   {
      return $this->dataAPI($this->getAPIPath(PATH_RECORD) .'/'. rawurlencode($layout) .'/'. $recordID, METHOD_DELETE);
   }

   // *********************************************************************************************************************************
   function apiEditRecord($layout, $recordID, $fields, $modificationID = '')
   {
      $data = array();
      $data[FM_DATA] = $fields;
      if ($modificationID != '') {
         $data[FM_MOD_ID] = $modificationID;
      }

      return $this->dataAPI($this->getAPIPath(PATH_RECORD) .'/'. rawurlencode($layout) .'/'. $recordID, METHOD_PUT, $data);
   }

   // *********************************************************************************************************************************
   function apiFindRecords($layout, $data)
   {
      return $this->dataAPI($this->getAPIPath(PATH_FIND) .'/'. rawurlencode($layout), METHOD_POST, $data);
   }

   // *********************************************************************************************************************************
   function apiGetRecord($layout, $recordID)
   {
      return $this->dataAPI($this->getAPIPath(PATH_RECORD) .'/'. rawurlencode($layout) .'/'. $recordID, METHOD_GET);
   }

   // *********************************************************************************************************************************
   function apiGetRecords($layout, $data)
   {
      return $this->dataAPI($this->getAPIPath(PATH_RECORD) .'/'. rawurlencode($layout), METHOD_GET, $data);
   }

   // *********************************************************************************************************************************
   function apiLogout()
   {
      return $this->dataAPI($this->getAPIPath(PATH_AUTH), METHOD_POST);
   }

   // *********************************************************************************************************************************
   function apiSetGlobalFields($layout, $data)
   {
      return $this->dataAPI($this->getAPIPath(PATH_GLOBAL) .'/'. rawurlencode($layout), METHOD_PUT, $data);
   }

   // *********************************************************************************************************************************
   // Use curl to call the Data API.
   // If the call was sucessful, this returns returns the jSON data decoded into a PHP structure.
   // If the call fails, an emptry array is returned and the caller should examine $this->curlErrNum and/or this->curlErrMsg.
   //
   public function curlAPI($url, $method = METHOD_GET, $data = '', $options = array())
   {
      $header = array('Content-Type:'. CONTENT_TYPE_JSON);                                            // Form the header

      if (array_key_exists(FM_DATA_TOKEN, $options) && ($options[FM_DATA_TOKEN] != '')) {
         $header[] = FM_DATA_TOKEN .':'. $options[FM_DATA_TOKEN];
      }
      $options[CURLOPT_HTTPHEADER] = $header;

      $result = $this->curl($url, $method, $data, $options);

      return $result;
   }

   // *********************************************************************************************************************************
   // Make a call into the API. Returns the json result *or* an empty array and the caller should call getErrorInfo() to find out why.
   //
   public function dataAPI($url, $method = METHOD_GET, $data = '', $token = '')
   {
      if ($token == '') {                                                        // Nothing passed, use what we've stored
         $token = $this->token;
      }

      if ($token == '') {                                                        // No token, first time caller (love your show!)
         $result = $this->login();                                               // login and save token if valid credentials
         if ($this->getIsError($result)) {
            return $result;
         }
         $token = $this->token;                                                  // logged in, so grab our token
      }

      $options = array();
      $options[FM_DATA_TOKEN] = $token;
      $options['encodeDecodeAsJSON'] = true;

      $result = $this->curlAPI($url, $method, $data, $options);                  // Call into FileMaker's Data API...

      // If the Data API told us the token is invalid it's typically going to be that it 'timed out',
      // so we will request a new token by logging in again, then reissuing our request to the Data API.
      if (($result != '') && array_key_exists(FM_ERROR_CODE, $result) && ($result[FM_ERROR_CODE] == FM_ERROR_INVALID_TOKEN)) {
//       fmLogger('FM_ERROR_INVALID_TOKEN, asking for new token...');
         $this->setToken('');
         $result = $this->login();
         if (! $this->getIsError($result)) {
            $options[FM_DATA_TOKEN] = $this->token;
            $result = $this->curlAPI($url, $method, $data, $options);            // Send the request (again)
         }
      }

      // If you're using this class directly (not fmPDA), remember to check for
      // $result[FM_ERROR_CODE] *or* $result[FM_ERROR_MESSAGE]. The data API may return one or both for an error.
      // getErrorInfo() will return either a curl or Data API error.


      // For those wanting to accumulate statstics of round trip times into the Data API, this is where you could do it easily.
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
   // Returns true if there's an error from the Data API or curl().
   //
   function getIsError($result = '')
   {
      $isError = false;

      if ($result != '') {
         if (is_array($result) && array_key_exists(FM_ERROR_CODE, $result) && ($result[FM_ERROR_CODE] != 0)) {
            $isError = true;
         }
         else if ($this->curlErrNum != '') {
            $isError = true;
         }

         if (is_array($result) && array_key_exists(FM_ERROR_MESSAGE, $result) && ($result[FM_ERROR_MESSAGE] != '')) {
            $isError = true;
         }
         else if ($this->curlErrMsg != '') {
            $isError = true;
         }
      }

      return $isError;
   }

   // *********************************************************************************************************************************
   // Returns any error from the Data API, then checks to see if there is a curl error to return.
   //
   function getErrorInfo($result)
   {
      $errorInfo = array('code' => '', 'message' => '');

      if (($result != '') && is_array($result)) {
         if (array_key_exists(FM_ERROR_CODE, $result) && ($result[FM_ERROR_CODE] != 0)) {
            $errorInfo['code'] = $result[FM_ERROR_CODE];
         }
         if (array_key_exists(FM_ERROR_MESSAGE, $result) && ($result[FM_ERROR_MESSAGE] != '')) {
            $errorInfo['message'] = $result[FM_ERROR_MESSAGE];
         }
      }

      if (($errorInfo['code'] == '') && ($errorInfo['message'] == '')) {
         if ($this->curlErrNum != '') {
            $errorInfo['code'] = $this->curlErrNum;
         }
         if ($this->curlErrMsg != '') {
            $errorInfo['message'] = $this->curlErrMsg;
         }
      }

      // Uncomment these lines if you want the HTTP result to be returned. Note that these codes
      // may conflict with FileMaker error codes, so be aware.
//      if (($errorInfo['code'] == '') && ($this->curlInfo['http_code'] != HTTP_SUCCESSS)) {
//         $errorInfo['code'] = $this->curlInfo['http_code'];
//      }

      return $errorInfo;
   }


   // *********************************************************************************************************************************
   // This method is called internally whenever there is no token passed to the Data API or the API has returned FM_ERROR_INVALID_TOKEN (952).
   //
   private function login()
   {
      $data = array();
      $data['user'] = $this->username;
      $data['password'] = $this->password;
      $data['layout'] = $this->loginLayout;

      $options = array();
      $options[FM_DATA_TOKEN] = $this->token;
      $options['encodeDecodeAsJSON'] = true;

      $result = $this->curlAPI($this->getAPIPath(PATH_AUTH), METHOD_POST, $data, $options);

      if (($result != '') && array_key_exists(FM_ERROR_CODE, $result) && ($result[FM_ERROR_CODE] == 0)) {
         $this->setToken($result['token']);
      }

      return $result;
   }

   // *********************************************************************************************************************************
   public function getAPIPath($requestPath)
   {
      $path = $this->host . $requestPath . rawurlencode($this->database);

      return $path;
   }

   // *********************************************************************************************************************************
   public function getToken()
   {
      return $this->token;
   }

   // *********************************************************************************************************************************
   public function setCredentials($userName, $password)
   {
      $this->username = $username;
      $this->password = $password;
      $this->setToken('');                                        // Invalidate token

      return;
   }

   // *********************************************************************************************************************************
   private function setToken($token)
   {
      $this->token = $token;

      if ($this->storeTokenInSession) {
         $_SESSION[FM_DATA_TOKEN] = $this->token;
      }

      return;
   }

}

?>
