<?php
// *********************************************************************************************************************************
//
// fmAdminAPI.class.php
//
// fmAdminAPI provides direct access to FileMaker's API (REST interface). This is a 'base' class that fmDataAPI and fmAdminAPI
// extend to provide access to the Data and Admin APIs.
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
define('DATA_ADMIN_API_USER_AGENT',     'fmAdminAPI/1.0');         // Our user agent string


// Starting with the v1 API, this is the base path for the Admin API
define('PATH_ADMIN_API_BASE',          '/fmi/admin/api/v%%%VERSION%%%');

define('PATH_ADMIN_LOGIN',             PATH_ADMIN_API_BASE .'/user/login');
define('PATH_ADMIN_LOGOUT',            PATH_ADMIN_API_BASE .'/user/logout');
define('PATH_ADMIN_DATABASES',         PATH_ADMIN_API_BASE .'/databases');
define('PATH_ADMIN_SERVER_STATUS',     PATH_ADMIN_API_BASE .'/server/status');
define('PATH_ADMIN_SCHEDULES',         PATH_ADMIN_API_BASE .'/schedules');
define('PATH_ADMIN_CLIENT',            PATH_ADMIN_API_BASE .'/clients');
define('PATH_ADMIN_CONFIG_GENERAL',    PATH_ADMIN_API_BASE .'/server/config/general');
define('PATH_ADMIN_CONFIG_SECURITY',   PATH_ADMIN_API_BASE .'/server/config/security');
define('PATH_ADMIN_PHP',               PATH_ADMIN_API_BASE .'/php/config');
define('PATH_ADMIN_XML',               PATH_ADMIN_API_BASE .'/xml/config');


// *********************************************************************************************************************************
define('FM_ERROR_INSUFFICIENT_PRIVILEGES',   9);                       // Insufficient privileges (bad token for the Admin API)

// *********************************************************************************************************************************
define('FM_ADMIN_SESSION_TOKEN',      'FM-Admin-Session-token');       // Where we store the token in the PHP session


// *********************************************************************************************************************************
class fmAdminAPI extends fmAPI
{
   function __construct($host, $username, $password, $options = array())
   {
      $options['logCURLResult']   = false;
      $options['host']            = $host;
      $options['sessionTokenKey'] = FM_ADMIN_SESSION_TOKEN;
      $options['userAgent']       = array_key_exists('userAgent', $options) ? $options['userAgent'] : DATA_ADMIN_API_USER_AGENT;

      $options['authentication'] = array();
      $options['authentication']['username'] = $username;
      $options['authentication']['password'] = $password;

      parent::__construct($options);

      fmLogger('fmAdminAPI: v'. $this->version);

      return;
   }

   // *********************************************************************************************************************************
   public function apiGetXMLConfiguration()
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_XML), METHOD_GET);
   }

   // *********************************************************************************************************************************
   public function apiSetXMLConfiguration($data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_XML), METHOD_PATCH, $data);
   }

   // *********************************************************************************************************************************
   public function apiGetPHPConfiguration()
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_PHP), METHOD_GET);
   }

   // *********************************************************************************************************************************
   public function apiSetPHPConfiguration($data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_PHP), METHOD_PATCH, $data);
   }

   // *********************************************************************************************************************************
   public function apiListDatabases()
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_DATABASES), METHOD_GET);
   }

   // *********************************************************************************************************************************
   public function apiOpenDatabase($databaseID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_DATABASES) .'/'. $databaseID .'/open', METHOD_PUT);
   }

   // *********************************************************************************************************************************
   public function apiCloseDatabase($databaseID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_DATABASES) .'/'. $databaseID .'/close', METHOD_PUT);
   }

   // *********************************************************************************************************************************
   public function apiPauseDatabase($databaseID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_DATABASES) .'/'. $databaseID .'/pause', METHOD_PUT);
   }

   // *********************************************************************************************************************************
   public function apiResumeDatabase($databaseID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_DATABASES) .'/'. $databaseID .'/resume', METHOD_PUT);
   }

   // *********************************************************************************************************************************
   public function apiGetServerGeneralConfiguration()
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_CONFIG_GENERAL), METHOD_GET);
   }

   // *********************************************************************************************************************************
   public function apiGetServerSecurityConfiguration()
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_CONFIG_SECURITY), METHOD_GET);
   }

   // *********************************************************************************************************************************
   public function apiSetServerGeneralConfiguration($data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_CONFIG_GENERAL), METHOD_PATCH);
   }

   // *********************************************************************************************************************************
   public function apiSetServerSecurityConfiguration($data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_CONFIG_SECURITY), METHOD_PATCH);
   }

   // *********************************************************************************************************************************
   public function apiGetServerStatus()
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SERVER_STATUS), METHOD_GET);
   }

   // *********************************************************************************************************************************
   public function apiSetServerStatus($data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SERVER_STATUS), METHOD_PUT, $data);
   }

   // *********************************************************************************************************************************
   public function apiDisconnectClient($clientID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_CLIENT) .'/'. $clientID .'/disconnect', METHOD_GET);
   }

   // *********************************************************************************************************************************
   public function apiSendMessageToClient($clientID, $message)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_CLIENT) .'/'. $clientID .'/'. rawurlencode($message), METHOD_POST);
   }

   // *********************************************************************************************************************************
   public function apiListSchedules()
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES), METHOD_GET);
   }

   // *********************************************************************************************************************************
   public function apiCreateSchedule($data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES), METHOD_POST, $data);
   }

   // *********************************************************************************************************************************
   public function apiDeleteSchedule($scheduleID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES) .'/'. $scheduleID, METHOD_DELETE);
   }

   // *********************************************************************************************************************************
   public function apiRunSchedule($scheduleID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES) .'/'. $scheduleID .'/run', METHOD_PUT);
   }

   // *********************************************************************************************************************************
   public function apiEnableSchedule($scheduleID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES) .'/'. $scheduleID .'/enable', METHOD_PUT);
   }

   // *********************************************************************************************************************************
   public function apiDisableSchedule($scheduleID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES) .'/'. $scheduleID .'/disable', METHOD_PUT);
   }

   // *********************************************************************************************************************************
   public function apiDuplicateSchedule($scheduleID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES) .'/'. $scheduleID .'/duplicate', METHOD_POST);
   }

   // *********************************************************************************************************************************
   public function apiGetSchedule($scheduleID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES) .'/'. $scheduleID, METHOD_GET);
   }

   // *********************************************************************************************************************************
   public function apiSetSchedule($scheduleID, $data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES) .'/'. $scheduleID, METHOD_PUT, $data);
   }


   // *********************************************************************************************************************************
   // Typically you do not need/want to log out - you want to keep using your token for best performance.
   // Only logout if you know you're completely done with the session. This will also clear the stored username/password.
   public function apiLogout()
   {
      $options = array();
      $options[FM_TOKEN]        = $this->getToken();
      $options[FM_CONTENT_TYPE] = CONTENT_TYPE_JSON;

      $apiResult = $this->curlAPI($this->getAPIPath(PATH_ADMIN_LOGOUT), METHOD_POST, '', $options);

      if (! $this->getIsError($apiResult)) {
         $this->setAuthentication();
      }

      return $apiResult;
   }

   // *********************************************************************************************************************************
   // This method is called internally whenever no or an invalid token is passed to the Data API.
   //
   protected function login()
   {
      $data = array();
      $options = array();

      $this->setToken('');

      $data = $this->credentials;

      $options[FM_CONTENT_TYPE] = CONTENT_TYPE_JSON;
      $options['encodeAsJSON']  = true;
      $options['decodeAsJSON']  = true;

      $result = $this->curlAPI($this->getAPIPath(PATH_ADMIN_LOGIN), METHOD_POST, $data, $options);

      if (! $this->getIsError($result)) {
         $response = $this->getResponse($result);
         if (array_key_exists(FM_TOKEN, $response)) {
            $this->setToken($response[FM_TOKEN]);
         }
      }

      return $result;
   }

   // *********************************************************************************************************************************
   public function setAuthentication($data = array())
   {
      $this->credentials = '';

      if (array_key_exists('username', $data) && ($data['username'] != '') && array_key_exists('password', $data) && ($data['password'] != '')) {
         $this->credentials = array();
         $this->credentials['username'] = $data['username'];
         $this->credentials['password'] = $data['password'];
      }

      $this->setToken('');                                                                        // Invalidate token

      return;
   }

   // *********************************************************************************************************************************
   // Returns true if the error result indicates the token is bad.
   //
   public function getIsBadToken($result)
   {
      $isBadToken = false;

      if ($this->getCodeExists($result, FM_ERROR_INSUFFICIENT_PRIVILEGES)) {
         fmLogger('FM_ERROR_INSUFFICIENT_PRIVILEGES');
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

      if (($result != '') && (is_array($result) && array_key_exists(FM_RESULT, $result))) {
         $message = array();
         $message[FM_CODE]    = array_key_exists(FM_RESULT, $result) ? $result[FM_RESULT] : '';
         $message[FM_MESSAGE] = array_key_exists(FM_ERROR_MESSAGE, $result) ? $result[FM_ERROR_MESSAGE] : '';
         $messages[] = $message;
      }

      return $messages;
   }

   // *********************************************************************************************************************************
   // Returns the response returned in the result from the server. This is where the data gets returned.
   //
   public function getResponse($result)
   {
      $response = ($result != null) ? $result : array();

      return $response;
   }

   // *********************************************************************************************************************************
   // Returns the response data (only) returned in the result from the server.
   //
   public function getResponseData($result)
   {
      $responseData = $this->getResponse();

      return $responseData;
   }

}

?>
