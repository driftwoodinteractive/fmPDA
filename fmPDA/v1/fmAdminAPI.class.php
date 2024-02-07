<?php
// *********************************************************************************************************************************
//
// fmAdminAPI.class.php
//
// fmAdminAPI provides direct access to FileMaker's API (REST interface). This is a 'base' class that fmDataAPI and fmAdminAPI
// extend to provide access to the Data and Admin APIs.
//
// Admin Console API:
// Web: http://fmhelp.filemaker.com/cloud/17/en/adminapi/
// Mac FMS: /Library/FileMaker Server/Documentation/Admin API Documentation
// Windows FMS: [drive]:\Program Files\FileMaker\FileMaker Server\Documentation\Admin API Documentation
//
// Feature Requests for FileMaker:
// http://www.filemaker.com/company/contact/feature_request.html
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

require_once 'fmAPI.class.php';

// *********************************************************************************************************************************
define('DATA_ADMIN_API_USER_AGENT',     'fmAdminAPIphp/1.0');     // Our user agent string

// How to get server information. This does not require authentication - just go a GET
define('PATH_SERVER_INFO',             '/fmws/serverinfo');

define('PATH_FMS_SERVER_API_BASE',     '/fmi');                   // Cloud doesn't have this, only FMS on Mac/Windows

// Starting with the v1 API, this is the base path for the Admin API
define('PATH_ADMIN_API_BASE',          '%%%FMI%%%/admin/api/v%%%VERSION%%%');

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
function convertBoolean_arraywalk(&$value, $key)
{
   if (strtolower($value) == 'true') {
      $value = true;
   }
   else if (strtolower($value) == 'false') {
      $value = false;
   }

   return;
}


// *********************************************************************************************************************************
class fmAdminAPI extends fmAPI
{
   public   $cloud;
   public   $convertBooleanStrings;

   /***********************************************************************************************************************************
    *
    * __construct($host, $username, $password, $options = array())
    *
    *    Constructor for fmAdminAPI.
    *
    *    Parameters:
    *       (string)  $host             The host name typically in the format of https://HOSTNAME
    *       (string)  $username         The user name of the account to authenticate with
    *       (string)  $password         The password of the account to authenticate with
    *       (array)   $options          Optional parameters
    *                                       ['version'] Version of the API to use (1, 2, etc. or 'Latest')
    *                                       ['cloud'] Set to true if you're using FileMaker Cloud
    *
    *                                       Token management - typically you choose none or one of the following 3 options:
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
    *                                                                in the file, override getTokenFromStorage() and setToken().
    *
    *                                       ['token']                The token from a previous call. This will normally be pulled
    *                                                                from the $_SESSION[] or ['tokenFilePath'], but in cases where
    *                                                                you need to store it somewhere else, pass it here. You're responsible
    *                                                                for calling the getToken() method after a successful call to retrieve
    *                                                                it for your own storage.
    *
    *    Returns:
    *       The newly created object.
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    */
   function __construct($host, $username = '', $password = '', $options = array())
   {
      $options['logCURLResult']   = false;
      $options['host']            = $host;
      $options['sessionTokenKey'] = array_key_exists('sessionTokenKey', $options) ? $options['sessionTokenKey'] : FM_ADMIN_SESSION_TOKEN;
      $options['userAgent']       = array_key_exists('userAgent', $options) ? $options['userAgent'] : DATA_ADMIN_API_USER_AGENT;
      $options['version']         = array_key_exists('version', $options) ? $options['version'] : FM_VERSION_1;

      $options['authentication'] = array();
      $options['authentication']['username'] = $username;
      $options['authentication']['password'] = $password;

      parent::__construct($options);

      $this->cloud = array_key_exists('cloud', $options) ? $options['cloud'] : false;
      $this->convertBooleanStrings = array_key_exists('convertBooleanStrings', $options) ? $options['convertBooleanStrings'] : true;

      fmLogger('fmAdminAPI: v'. $this->version);

      if (time() > mktime(0, 0, 0, 9, 27, 2019)) {
         fmLogger('ALERT: The Admin API v1 is a beta which expired on September 27, 2019. Please upgrade to FMS 18 or later.');
      }
      else {
         fmLogger('WARNING: The Admin API v1 is a beta which will expire on September 27, 2019. Please upgrade to at least FMS 18 before that date.');
      }

      return;
   }

   /***********************************************************************************************************************************
    *
    * apiGetServerInfo()
    *
    *    Get information about the server. You do not need to authenticate to get this information.
    *
    *    Parameters:
    *       None
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['data']   An array of data
    *          ['result'] 0 if successful else an error code
    *
    *    Example:
    *       $fm = new fmAdminAPI($host);
    *       $apiResult = $fm->apiGetServerInfo();
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiGetServerInfo()
   {
      $apiOptions = array();
      $apiOptions[FM_CONTENT_TYPE]  = CONTENT_TYPE_JSON;
      $apiOptions['decodeAsJSON']   = true;

      return $this->curlAPI($this->getAPIPath(PATH_SERVER_INFO), METHOD_GET, '', $apiOptions);
   }

   /***********************************************************************************************************************************
    *
    * apiGetPHPConfiguration()
    *
    *    Get the PHP configuration for the server.
    *
    *    Parameters:
    *       None
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ['enabled'] 1 if PHP is enabled, 0 otherwise
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiGetPHPConfiguration();
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiGetPHPConfiguration()
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_PHP), METHOD_GET);
   }

   /***********************************************************************************************************************************
    *
    * apiSetPHPConfiguration($data)
    *
    *    Set the PHP configuration on the server.
    *
    *    Parameters:
    *       (array) $data An array of options to set in the PHP configuration.
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $option = array();
    *       $option[ ... ] = '';
    *       $apiResult = $fm->apiSetPHPConfiguration($data);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiSetPHPConfiguration($data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_PHP), METHOD_PATCH, $data);
   }

   /***********************************************************************************************************************************
    *
    * apiGetXMLConfiguration()
    *
    *    Get the XML configuration for the server.
    *
    *    Parameters:
    *       None
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ['enabled'] 1 if XML is enabled, 0 otherwise
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiGetXMLConfiguration();
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiGetXMLConfiguration()
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_XML), METHOD_GET);
   }

   /***********************************************************************************************************************************
    *
    * apiSetXMLConfiguration($data)
    *
    *    Get the XML configuration for the server.
    *
    *    Parameters:
    *       (array) $data An array of options to set in the XML configuration. 'enabled' is currently the only option.
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $option = array();
    *       $option['enabled'] = 'true';
    *       $apiResult = $fm->apiSetXMLConfiguration($data);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiSetXMLConfiguration($data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_XML), METHOD_PATCH, $data);
   }

   /***********************************************************************************************************************************
    *
    * apiListDatabases()
    *
    *    List the databases on the server
    *
    *    Parameters:
    *       None
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiListDatabases();
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiListDatabases()
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_DATABASES), METHOD_GET);
   }

   /***********************************************************************************************************************************
    *
    * apiOpenDatabase($databaseID, $password = '')
    *
    *    Open the database specified by $databaseID
    *
    *    Parameters:
    *       (integer)  $databaseID    The database ID
    *       (string)   $password      The encryption password for the database.
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiOpenDatabase($databaseID);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiOpenDatabase($databaseID, $password = '')
   {
      if ($password != '') {
         $data = array();
         $data['key'] = $password;
      }
      else {
         $data = '';
      }

      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_DATABASES) .'/'. $databaseID .'/open', METHOD_PUT, $data);
   }

   /***********************************************************************************************************************************
    *
    * apiCloseDatabase($databaseID, $message = '')
    *
    *    Close the database specified by $databaseID
    *
    *    Parameters:
    *       (integer)  $databaseID    The database ID
    *       (string)   $message       The message to display to users being disconnected
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiCloseDatabase($databaseID, 'Down for maintenance. Be back in an hour!');
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiCloseDatabase($databaseID, $message = '')
   {
      if ($message != '') {
         $data = array();
         $data['message'] = $message;
      }
      else {
         $data = '';
      }

      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_DATABASES) .'/'. $databaseID .'/close', METHOD_PUT, $data);
   }

   /***********************************************************************************************************************************
    *
    * apiPauseDatabase($databaseID)
    *
    *    Pause the database specified by $databaseID
    *
    *    Parameters:
    *       (integer)  $databaseID    The database ID
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiPauseDatabase($databaseID);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiPauseDatabase($databaseID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_DATABASES) .'/'. $databaseID .'/pause', METHOD_PUT);
   }

   /***********************************************************************************************************************************
    *
    * apiResumeDatabase($databaseID)
    *
    *    Resume a paused database specified by $databaseID
    *
    *    Parameters:
    *       (integer)  $databaseID    The database ID
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiResumeDatabase($databaseID);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiResumeDatabase($databaseID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_DATABASES) .'/'. $databaseID .'/resume', METHOD_PUT);
   }

   /***********************************************************************************************************************************
    *
    * apiGetServerGeneralConfiguration()
    *
    *    Get the server's general configuration
    *
    *    Parameters:
    *       None
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiGetServerGeneralConfiguration();
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiGetServerGeneralConfiguration()
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_CONFIG_GENERAL), METHOD_GET);
   }

   /***********************************************************************************************************************************
    *
    * apiGetServerSecurityConfiguration()
    *
    *    Get the server's security configuration
    *
    *    Parameters:
    *       None
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiGetServerSecurityConfiguration();
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiGetServerSecurityConfiguration()
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_CONFIG_SECURITY), METHOD_GET);
   }

   /***********************************************************************************************************************************
    *
    * apiSetServerGeneralConfiguration($data)
    *
    *    Get the server's security configuration
    *
    *    Parameters:
    *       (array) $data An array of options to set in the general configuration.
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiSetServerGeneralConfiguration($data);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiSetServerGeneralConfiguration($data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_CONFIG_GENERAL), METHOD_PATCH);
   }

   /***********************************************************************************************************************************
    *
    * apiSetServerSecurityConfiguration($data)
    *
    *    Get the server's security configuration
    *
    *    Parameters:
    *       (array) $data An array of options to set in the general configuration.
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiSetServerSecurityConfiguration($data);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiSetServerSecurityConfiguration($data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_CONFIG_SECURITY), METHOD_PATCH);
   }

   /***********************************************************************************************************************************
    *
    * apiGetServerStatus()
    *
    *    Get the status of the server
    *
    *    Parameters:
    *       (array) $data An array of options to set in the general configuration.
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ['running'] 1 if the server is running
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiGetServerStatus();
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiGetServerStatus()
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SERVER_STATUS), METHOD_GET);
   }

   /***********************************************************************************************************************************
    *
    * apiSetServerStatus($data)
    *
    *    Set the status of the server
    *
    *    Parameters:
    *       (array) $data An array of options to set. Currently 'running' is the only option.
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ['running'] 1 if the server is running
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiSetServerStatus($data);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiSetServerStatus($data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SERVER_STATUS), METHOD_PUT, $data);
   }

   /***********************************************************************************************************************************
    *
    * apiDisconnectClient($clientID, $message = '', $graceTime = '')
    *
    *    Disconnect a client
    *
    *    Parameters:
    *       (integer)   $clientID  The client ID
    *       (string)    The message to send
    *       (integer)   The number of seconds to wait before disconnecting (0-3600)
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiDisconnectClient($data);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiDisconnectClient($clientID, $message = '', $graceTime = '')
   {
      $data = array();
      if ($message != '') {
         $data['message'] = $message;
      }
      if ($gracetime != '') {
         $data['gracetime'] = $gracetime;
      }

      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_CLIENT) .'/'. $clientID .'/disconnect', METHOD_PUT, $data);
   }

   /***********************************************************************************************************************************
    *
    * apiSendMessageToClient($clientID, $message)
    *
    *    Send a messge to a client
    *
    *    Parameters:
    *       (integer)   $clientID  The client ID (set to 0 to send to all clients)
    *       (string)    The message to send
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiDisconnectClient($data);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiSendMessageToClient($clientID, $message)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_CLIENT) .'/'. $clientID .'/'. rawurlencode($message), METHOD_POST);
   }

   /***********************************************************************************************************************************
    *
    * apiListSchedules()
    *
    *    Get a list of schedules.
    *
    *    Parameters:
    *       None
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiListSchedules();
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiListSchedules()
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES), METHOD_GET);
   }

   /***********************************************************************************************************************************
    *
    * apiCreateSchedule($data)
    *
    *    Create a new schedule
    *
    *    Parameters:
    *       (array) $data An array of options to set specifying the schedule.
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiCreateSchedule($data);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiCreateSchedule($data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES), METHOD_POST, $data);
   }

   /***********************************************************************************************************************************
    *
    * apiDeleteSchedule($scheduleID)
    *
    *    Delete a schedule
    *
    *    Parameters:
    *       (integer) $scheduleID   The schedule ID
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ['schedules'] The deleted schedule
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiDeleteSchedule($scheduleID);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiDeleteSchedule($scheduleID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES) .'/'. $scheduleID, METHOD_DELETE);
   }

   /***********************************************************************************************************************************
    *
    * apiRunSchedule($scheduleID)
    *
    *    Run a schedule
    *
    *    Parameters:
    *       (integer) $scheduleID   The schedule ID
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ['schedules'] The schedule that was executed
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiRunSchedule($scheduleID);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiRunSchedule($scheduleID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES) .'/'. $scheduleID .'/run', METHOD_PUT);
   }

   /***********************************************************************************************************************************
    *
    * apiEnableSchedule($scheduleID)
    *
    *    Enable a schedule
    *
    *    Parameters:
    *       (integer) $scheduleID   The schedule ID
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ['schedules'] The schedule that was enabled
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiEnableSchedule($scheduleID);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiEnableSchedule($scheduleID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES) .'/'. $scheduleID .'/enable', METHOD_PUT);
   }

   /***********************************************************************************************************************************
    *
    * apiDisableSchedule($scheduleID)
    *
    *    Disable a schedule
    *
    *    Parameters:
    *       (integer) $scheduleID   The schedule ID
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ['schedules'] The schedule that was disabled
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiDisableSchedule($scheduleID);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiDisableSchedule($scheduleID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES) .'/'. $scheduleID .'/disable', METHOD_PUT);
   }

   /***********************************************************************************************************************************
    *
    * apiDuplicateSchedule($scheduleID)
    *
    *    Duplicate a schedule
    *
    *    Parameters:
    *       (integer) $scheduleID   The schedule ID
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ['schedules'] The schedule that was duplicated
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiDuplicateSchedule($scheduleID);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiDuplicateSchedule($scheduleID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES) .'/'. $scheduleID .'/duplicate', METHOD_POST);
   }

   /***********************************************************************************************************************************
    *
    * apiGetSchedule($scheduleID)
    *
    *    Get a schedule
    *
    *    Parameters:
    *       (integer) $scheduleID   The schedule ID
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ['schedules'] The schedule
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiGetSchedule($scheduleID);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiGetSchedule($scheduleID)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES) .'/'. $scheduleID, METHOD_GET);
   }

   /***********************************************************************************************************************************
    *
    * apiSetSchedule($scheduleID, $data)
    *
    *    Set a schedule
    *
    *    Parameters:
    *       (integer)   $scheduleID   The schedule ID
    *       (array)     $data         An array of options to set specifying the schedule.
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ['schedules'] The schedule
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiSetSchedule($scheduleID, $data);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiSetSchedule($scheduleID, $data)
   {
      return $this->fmAPI($this->getAPIPath(PATH_ADMIN_SCHEDULES) .'/'. $scheduleID, METHOD_PUT, $data);
   }

   /***********************************************************************************************************************************
    *
    * apiGetLog($url, $type, $format)
    *
    *    Get a FileMaker Server log.
    *
    *    This is an 'extension' to the Admin API.
    *    To make this work, you must be running the fmPDA class files on your FMS server *or* install
    *    the 'get_fms_log.php' file on your FMS server. Both this method and get_fms_log.php will authenticate
    *    through the Admin API by sending a apiGetServerStatus() request.
    *
    *    Parameters:
    *    (string) type           The type of log to return (default is 'event')
    *                                  'access'
    *                                  'event'
    *                                  'fmdapi'
    *                                  'stderr'
    *                                  'stdout'
    *                                  'topcallstats'
    *                                  'wpedebug'
    *                                  'wpe'
    *    (string) format         The format of the log data:
    *                                  'raw'            The contents of the file as read from disk (default)
    *                                  'html'           htmlspecialchars() encoded and new lines into <br> tags
    *                                  'html-table'     HTML table, htmlspecialchars() for the data
    *    (string) url            If blank, fmPDA must reside on the same server as FileMaker server
    *                            so that it has access to the log files directory. If fmPDA is on a separate server
    *                            you will need to install get_fms_log.php resides on your FMS server and then pass
    *                            the URL to that location here.
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result']         0 if successful else an error code
    *          ['errorMessage']   Any error message
    *          ['log']            The log file, in whatever form specified by type in the request payload
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiGetLog($url, $type, $format);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiGetLog($type, $format, $url = '')
   {
      if ($url == '') {
         $apiResult = $this->apiGetServerStatus();                                  // Get the server status which validates we're authenticated

         if (! $this->getIsError($apiResult)) {                                     // Were we able to authenticate with un/pw or token?
            $apiResult = $this->GetFileMakerLog($apiResult, $type, $format);        // Get the log
         }
      }
      else {
         $data = array();
         $data['host']     = $this->host;
         $data['username'] = $this->credentials['username'];
         $data['password'] = $this->credentials['password'];
         $data['token']    = $this->getToken();
         $data['type']     = $type;
         $data['format']   = $format;

         $apiResult = $this->fmAPI($url, METHOD_POST, $data);

         // get_fms_log.php may generate the token for the first time or get a new one. We want to use that token as well.
         if (array_key_exists('token', $apiResult)) {
            $this->setToken($apiResult['token']);
         }

         if (array_key_exists('fmlog', $apiResult)) {
            fmLogger($apiResult['fmlog']);                           // $apiResult['fmlog'] is the log data that get_fms_log.php generated
         }
      }

      return $apiResult;
   }

   /***********************************************************************************************************************************
    *
    * GetFileMakerLog($apiResult, $type, $format)
    *
    */
   protected function GetFileMakerLog($apiResult, $type, $format)
   {
      // Location of your logs directory. If it's different for your server, change it here.
      if (stristr(PHP_OS, 'darwin')) {		           										  // Check before 'win' since 'win' is in 'darwin'!
         $logBase = '/Library/FileMaker Server/Logs/';
      }
      else if (stristr(PHP_OS, 'win')) {
         $logBase = 'C:\Program Files\FileMaker\FileMaker Server\Logs\\';
      }
      else {
         $logBase = ''; // Cloud? Not sure what the path is...
      }

      if ($logBase != '') {                                                       // Define the paths to the log files in the OS
         $logPaths = array('fmdapi'       => $logBase .'fmdapi.log',
                           'access'       => $logBase .'Access.log',
                           'event'        => $logBase .'Event.log',
                           'stderr'       => $logBase .'stderr',
                           'stdout'       => $logBase .'stdout',
                           'topcallstats' => $logBase .'TopCallStats.log',
                           'wpedebug'     => $logBase .'wpe_debug.log',
                           'wpe'          => $logBase .'wpe.log'
                     );

         $logType = strtolower($type);
         $logPath = array_key_exists($logType, $logPaths) ? $logPaths[$logType] : '';

         if ($logPath != '') {
            $apiResult['log'] = file_get_contents($logPath);                      // Get the log file

            switch ($format) {

               case 'html-table': {

                  // The class names are defined to that the caller can substitue their own CSS styling
                  // for the table by defining these CSS styles:
                  $tableClass = 'fm-log-table';
                  $rowClass   = 'fm-log-table-row';
                  $cellClass  = 'fm-log-table-cell';

                  $apiResult['log'] = '<table class="'. $tableClass .'">'.
                                      '<tr class="'. $rowClass .'">'.
                                      '<td class="'. $cellClass .'">'.

                                      str_replace(array("\t",
                                                        "\r",
                                                        "\n"),
                                                  array('</td><td class="'. $cellClass .'">',
                                                        '</td></tr><tr class="'. $rowClass .'"><td class="'. $cellClass .'">',
                                                        '</td></tr><tr class="'. $rowClass .'"><td class="'. $cellClass .'">'),
                                                  htmlspecialchars($apiResult['log'])).

                                      '</td>'.
                                      '</tr>'.
                                      '</table>';
                  break;
               }

               case 'html': {
                  $apiResult['log'] = nl2br(htmlspecialchars($apiResult['log']));
                  break;
               }

               case 'raw':
               default: {
                  break;
               }
            }
         }
         else {
            $apiResult[FM_RESULT]        = '-5';
            $apiResult[FM_ERROR_MESSAGE] = 'Invalid log type';
         }
      }
      else {
         $apiResult[FM_RESULT]        = '-4';
         $apiResult[FM_ERROR_MESSAGE] = 'Unknown host type (cloud?)';
      }

      return $apiResult;
   }

   /***********************************************************************************************************************************
    *
    * apiLogout()
    *
    *    Logs out of the current session and clears the username, password, and authentication token.
    *
    *    Normally you will not call this method so that you can keep re-using the authentication token for future calls.
    *    Only logout if you know you are completely done with the session. This will also clear the stored username/password.
    *
    *    Parameters:
    *       None
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['result'] 0 if successful else an error code
    *          ...
    *
    *    Example:
    *       $fm = new fmAdminAPI($host, $username, $password);
    *       $apiResult = $fm->apiLogout();
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
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
   // Returns an array of response messages returned in the result from the server. It's possible that more than one error could be
   // returned, so you'll either need to walk the array or look for a specific code with the getCodeExists() method.
   //
   public function getMessages($result)
   {
      $messages = array();

      if (($result != '') && (is_array($result) && array_key_exists(FM_RESULT, $result) && ($result[FM_RESULT] != 0))) {
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


   /***********************************************************************************************************************************
    *
    * Methods below are typically for internal use.
    *
    ***********************************************************************************************************************************
    */


   // *********************************************************************************************************************************
   public function fmAPI($url, $method = METHOD_GET, $data = '', $options = array())
   {
      $apiResult = parent::fmAPI($url, $method, $data, $options);

      // The Admin API returns Booleans as quoted strings (ugh). This can really mess with your code so we
      // recursively iterate across all array elements to change 'true' and 'false' into true boolean values.
      if ($this->convertBooleanStrings && is_array($apiResult)) {
         array_walk_recursive($apiResult, 'convertBoolean_arraywalk');
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

      $this->setToken();

      $data = $this->credentials;

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
   // Returns true if the error result indicates the token is bad.
   //
   protected function getIsBadToken($result)
   {
      $isBadToken = false;

      if ($this->getCodeExists($result, FM_ERROR_INSUFFICIENT_PRIVILEGES)) {
         fmLogger('FM_ERROR_INSUFFICIENT_PRIVILEGES');
         $isBadToken = true;
      }

      return $isBadToken;
   }

   // *********************************************************************************************************************************
   public function getAPIPath($requestPath)
   {
      $path = parent::getAPIPath($requestPath);

      $search  = array('%%%FMI%%%');
      $replace = (! $this->cloud ) ? PATH_FMS_SERVER_API_BASE : '';

      $path = str_replace($search, $replace, $path);

      return $path;
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

      $this->setToken();                                                                        // Invalidate token

      return;
   }

}

?>
