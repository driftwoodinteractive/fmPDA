<?php
// *********************************************************************************************************************************
//
// fmDataAPI.class.php
//
// fmDataAPI provides direct access to FileMaker's Data API (REST interface). You can use this class alone to communicate
// with the Data API and not have any translation done on the data returned. If you want to send/receive data like the old
// FileMaker API For PHP, use the fmPDA class.
//
// FileMaker's Data API documentation can be found here:
// Web: http://fmhelp.filemaker.com/docs/17/en/dataapi/
// Mac FMS: /Library/FileMaker Server/Documentation/Data API Documentation
// Windows FMS: [drive]:\Program Files\FileMaker\FileMaker Server\Documentation\Data API Documentation
//
//
// Admin Console API:
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

require_once 'fmAPI.class.php';

// *********************************************************************************************************************************
define('DATA_API_USER_AGENT',          'fmDataAPI/1.0');  // Our user agent string


// Starting with the v1 API, this is the new base path
define('PATH_DATA_API_BASE',           '/fmi/data/v%%%VERSION%%%/databases');

// Paths for v1 (FMS 17+)
// The documentation (http://fmhelp.filemaker.com/docs/17/en/dataapi/#work-with-records_get-records)
// is missing the last "records/" for 'Format 1'. The docs show:
// Format 1 (returns up to the first 100 records):       /fmi/data/v1/databases/database-name/layouts/layout-name
// Should be:                                            /fmi/data/v1/databases/database-name/layouts/layout-name/records

define('PATH_AUTH',                    PATH_DATA_API_BASE .'/' .'%%%DATABASE%%%' . '/sessions');
define('PATH_FIND',                    PATH_DATA_API_BASE .'/' .'%%%DATABASE%%%' . '/layouts/%%%LAYOUTNAME%%%/_find');
define('PATH_GLOBAL',                  PATH_DATA_API_BASE .'/' .'%%%DATABASE%%%' . '/globals');
define('PATH_RECORD',                  PATH_DATA_API_BASE .'/' .'%%%DATABASE%%%' . '/layouts/%%%LAYOUTNAME%%%/records');
define('PATH_UPLOAD',                  PATH_DATA_API_BASE .'/' .'%%%DATABASE%%%' . '/layouts/%%%LAYOUTNAME%%%/records');


// *********************************************************************************************************************************
define('FM_DATASOURCE',                'fmDataSource');

// *********************************************************************************************************************************
define('FM_QUERY',                     'query');

define('FM_DATA',                      'data');
define('FM_FIELD_DATA',                'fieldData');
define('FM_PORTAL_DATA',               'portalData');
define('FM_RECORD_ID',                 'recordId');
define('FM_MOD_ID',                    'modId');

define('FM_FIELD_REPETITION_1',        1);


// *********************************************************************************************************************************
define('FM_ERROR_FIELD_IS_MISSING',    100);                      // Field is missing
define('FM_ERROR_RECORD_IS_MISSING',   101);                      // Record is missing
define('FM_ERROR_NO_RECORDS',          401);                      // No records returned (not really an error)

// *********************************************************************************************************************************
define('FM_DATA_SESSION_TOKEN',        'FM-Data-Session-token');  // Where we store the token in the PHP session


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
class fmDataAPI extends fmAPI
{
   public $database;                                              // Database name (should NOT include .fmp12)
   public $authenticationMethod;                                  // How to authenticate to the server
   public $oauth;                                                 // Where we store OAuth data
   public $dataSource;                                            // Where the external authentication/OAuth data is stored

   function __construct($database, $host, $username, $password, $options = array())
   {
      $this->database = $database;

      $options['host']            = $host;
      $options['sessionTokenKey'] = FM_DATA_SESSION_TOKEN;
      $options['userAgent']       = array_key_exists('userAgent', $options) ? $options['userAgent'] : DATA_API_USER_AGENT;

      $authentication = array_key_exists('authentication', $options) ? $options['authentication'] : array('method' => 'default');

      $authentication['username'] = array_key_exists('username', $authentication) ? $authentication['username'] : $username;
      $authentication['password'] = array_key_exists('password', $authentication) ? $authentication['password'] : $password;

      $options['authentication'] = $authentication;

      parent::__construct($options);

      fmLogger('fmDataAPI: v'. $this->version);

      return;
   }

   // *********************************************************************************************************************************
   public function apiCreateRecord($layout, $fields)
   {
      $data = array();
      $data[FM_FIELD_DATA] = $fields;

      return $this->fmAPI($this->getAPIPath(PATH_RECORD, $layout), METHOD_POST, $data);
   }

   // *********************************************************************************************************************************
   public function apiDeleteRecord($layout, $recordID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_RECORD, $layout) .'/'. $recordID, METHOD_DELETE);
   }

   // *********************************************************************************************************************************
   public function apiEditRecord($layout, $recordID, $data)
   {
      $payload = array();

      if (! array_key_exists(FM_FIELD_DATA, $data)) {        // If there's no ['fieldData'], add it now
         $payload[FM_FIELD_DATA] = $data;
      }
      else {
         // The caller passed in a structure that has the ['fieldData'] element and likely ['portalData'].
         // As such, there's nothing for us to do - the caller knows best.
         $payload = $data;
      }

      return $this->fmAPI($this->getAPIPath(PATH_RECORD, $layout) .'/'. $recordID, METHOD_PATCH, $payload);
   }

   // *********************************************************************************************************************************
   public function apiFindRecords($layout, $data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_FIND, $layout), METHOD_POST, $data);
   }

   // *********************************************************************************************************************************
   public function apiGetRecord($layout, $recordID, $data = '')
   {
      $data = ($data != '') ? '?' . $data : '';

      return $this->fmAPI($this->getAPIPath(PATH_RECORD, $layout) .'/'. $recordID . $data, METHOD_GET);
   }

   // *********************************************************************************************************************************
   public function apiGetRecords($layout, $data = '')
   {
      return $this->fmAPI($this->getAPIPath(PATH_RECORD, $layout), METHOD_GET, $data);
   }

   // *********************************************************************************************************************************
   public function apiLogin()
   {
      return $this->login();
   }

   // *********************************************************************************************************************************
   // Typically you do not need/want to log out - you want to keep using your token for best performance.
   // Only logout if you know you're completely done with the session. This will also clear the stored username/password.
   public function apiLogout()
   {
      if ($this->getToken() == '') {
         fmLogger(__METHOD__ .'(): Token is empty!');
      }

      $apiResult = $this->curlAPI($this->getAPIPath(PATH_AUTH) .'/'. $this->getToken(), METHOD_DELETE);

      if (! $this->getIsError($apiResult)) {
         $this->setAuthentication();
      }

      return $apiResult;
   }

   // *********************************************************************************************************************************
   // apiPerformScript
   //
   // Execute a script. We do this by doing a apiGetRecords() call for the first record on the specified layout.
   // For this to work, *YOU MUST* have at least one record in this table or the script *WILL NOT EXECUTE*.
   // For efficiency, you may want to create a table with just one record and no fields.
   //
   public function apiPerformScript($layout, $scriptName, $params = '')
   {
      $data = '&script='. rawurlencode($scriptName);

      if ($params != '') {
         $data .= '&script.param='. rawurlencode($params);
      }

      return $this->apiGetRecords($layout, $data);
   }

   // *********************************************************************************************************************************
   public function apiSetGlobalFields($layout, $data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_GLOBAL, $layout), METHOD_PATCH, $data);
   }

   // *********************************************************************************************************************************
   // apiUploadContainer
   //
   // Upload a file to a container field.
   //
   // $file is an array of information about the file to be uploaded. You specify ['path'] or ['contents']:
   //       $file['path']       The path to the file to upload (use this or ['contents'])
   //       $file['contents']   The file contents (use this or ['path'])
   //       $file['name']       The file name (required if you use ['contents'] otherwise it will be determined)
   //       $file['mimeType']   The MIME type
   //
   public function apiUploadContainer($layout, $recordID, $fieldName, $fieldRepetition, $file)
   {
      $data = '';

      if (array_key_exists('path', $file)) {
         $pathInfo = pathinfo($file['path']);

         $file['name']     = (array_key_exists('name', $file) && ($file['name'] != '')) ? $file['name'] : $pathInfo['basename'];
         $file['mimeType'] = (array_key_exists('mimeType', $file) && ($file['mimeType'] != '')) ? $file['mimeType'] : mime_content_type($file['path']);
         $file['contents'] = file_get_contents($file['path']);
      }

      if ($fieldRepetition == '') {
         $fieldRepetition = FM_FIELD_REPETITION_1;
      }

      $boundaryID = "Container_Upload_fmDataAPI-" . uniqid();

      $data .= '--'. $boundaryID ."\r\n";

      $data .= 'Content-Disposition: name="upload" filename="'. $file['name'] .'"' ."\r\n";
      if (array_key_exists('mimeType', $file) && ($file['mimeType'] != '')) {
         $data .= 'Content-Type: '. $file['mimeType'] ."\r\n";
      }
      $data .= "\r\n";

      $data .= $file['contents'] ."\r\n";
      $data .= "\r\n";

      $data .= '--'. $boundaryID .'--' ."\r\n";

      $options = array();
      $options[FM_CONTENT_TYPE]  = CONTENT_TYPE_MULTIPART_FORM .'; boundary='. $boundaryID;
      $options['CURLOPT_POST']     = 1;
      $options['encodeAsJSON']   = false;
      $options['decodeAsJSON']   = true;

      $path = $this->getAPIPath(PATH_UPLOAD, $layout) .'/'. $recordID .'/'. 'containers' .'/'. rawurlencode($fieldName) .'/'. $fieldRepetition;

      return $this->fmAPI($path, METHOD_POST, $data, $options);
   }


   // *********************************************************************************************************************************
   // Returns true if the error result indicates the token is bad.
   //
   public function getIsBadToken($result)
   {
      $isBadToken = false;

      if ($this->getCodeExists($result, FM_ERROR_INVALID_TOKEN)) {
         fmLogger('FM_ERROR_INVALID_TOKEN');
         $isBadToken = true;
      }

      return $isBadToken;
   }

   // *********************************************************************************************************************************
   // Returns an array of response messages returned in the result from the server. It's possible that more than one error could be
   // returned, so you'll either need to walk the array or look for a specific code with the getCodeExists() method.
   //
   public function getMessages($result)
   {
      $messages = array();

      if (($result != '') && (is_array($result) && array_key_exists(FM_MESSAGES, $result))) {
         $messages = $result[FM_MESSAGES];
      }

      return $messages;
   }

   // *********************************************************************************************************************************
   // Returns the response returned in the result from the server. This is where the data gets returned.
   //
   public function getResponse($result)
   {
      $response = array();

      if (($result != '') && (is_array($result) && array_key_exists(FM_RESPONSE, $result))) {
         $response = $result[FM_RESPONSE];
      }

      return $response;
   }

   // *********************************************************************************************************************************
   // Returns the response data (only) returned in the result from the server.
   //
   public function getResponseData($result)
   {
      $responseData = array();

      if (($result != '') && (is_array($result) && array_key_exists(FM_RESPONSE, $result)) && array_key_exists(FM_DATA, $result[FM_RESPONSE])) {
         $responseData = $result[FM_RESPONSE][FM_DATA];
      }

      return $responseData;
   }

   // *********************************************************************************************************************************
   // This method is called internally whenever no or an invalid token is passed to the Data API.
   //
   protected function login()
   {
      $data = array();

      $this->setToken('');

      $options = array();

      switch ($this->authenticationMethod) {
         case 'default': {
            $options[FM_AUTHORIZATION_BASIC] = $this->credentials;

            if ($this->dataSource != '') {
               $data = $this->dataSource;
            }
            break;
         }
         case 'oauth': {
            $options[FM_OAUTH_REQUEST_ID]         = $this->oauth[FM_OAUTH_REQUEST_ID];
            $options[FM_OAUTH_REQUEST_IDENTIFIER] = $this->oauth[FM_OAUTH_REQUEST_IDENTIFIER];

            if ($this->dataSource != '') {
               $data = $this->dataSource;
            }
            break;
         }
         default: {
            break;
         }
      }

      $options[FM_CONTENT_TYPE] = CONTENT_TYPE_JSON;
      $options['encodeAsJSON']  = true;
      $options['decodeAsJSON']  = true;

      $result = $this->curlAPI($this->getAPIPath(PATH_AUTH), METHOD_POST, $data, $options);

      if (! $this->getIsError($result)) {
         $response = $this->getResponse($result);
         if (array_key_exists(FM_TOKEN, $response)) {
            $this->setToken($response[FM_TOKEN]);
         }
      }

      return $result;
   }

   // *********************************************************************************************************************************
   public function getAPIPath($requestPath, $layout = '')
   {
      $search  = array('%%%VERSION%%%',      '%%%DATABASE%%%',              '%%%LAYOUTNAME%%%');
      $replace = array($this->version,       rawurlencode($this->database), rawurlencode($layout));

      $path = $this->host . str_replace($search, $replace, $requestPath);

      return $path;
   }

   // *********************************************************************************************************************************
   public function setAuthentication($data = array())
   {
      $this->authenticationMethod = array_key_exists('method', $data) ? strtolower($data['method']) : 'default';

      $this->credentials = '';
      $this->dataSource = '';
      $this->oauth = '';

      switch ($this->authenticationMethod) {

         case 'default': {
            if (array_key_exists('username', $data) && ($data['username'] != '') && array_key_exists('password', $data) && ($data['password'] != '')) {
               $this->credentials = base64_encode(utf8_decode($data['username']) .':'. utf8_decode($data['password']));

               // See if there's any external authentiation we need to add.
               // This allows us to access another database on the same server in addition to the one we're connecting to.
               $externalDataBase = array_key_exists('externalDataBase', $data) ? $data['externalDataBase'] : '';
               $externalUserName = array_key_exists('externalUserName', $data) ? $data['externalUserName'] : '';
               $externalPassword = array_key_exists('externalPassword', $data) ? $data['externalPassword'] : '';

               if (($externalDataBase != '') && ($externalUserName != '') && ($externalPassword != '')) {
                  $source = array('database' => $externalDataBase, 'username' => $externalUserName, 'password' => $externalPassword);
                  $this->dataSource = array();
                  $this->dataSource[FM_DATASOURCE][] = $source;
               }
            }
            break;
         }

         case 'oauth': {
            $oauthID          = array_key_exists('oauthID', $data) ? $data['oauthID'] : '';
            $oauthIdentifier  = array_key_exists('oauthIdentifier', $data) ? $data['oauthIdentifier'] : '';

            if (($oauthID != '') && ($oauthIdentifier != '')) {
               $this->oauth = array();
               $this->oauth[FM_OAUTH_REQUEST_ID] = $oauthID;
               $this->oauth[FM_OAUTH_REQUEST_IDENTIFIER] = $oauthIdentifier;
            }
            break;
         }

         default: {
            break;
         }
      }

      $this->setToken('');                                                                        // Invalidate token

      return;
   }

}

?>
