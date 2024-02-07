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
define('DATA_API_USER_AGENT',          'fmDataAPIphp/1.0');  // Our user agent string

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

define('FM_GLOBAL_FIELDS',             'globalFields');

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
   public $authenticationMethod;                                  // How to authenticate to the server
   public $database;                                              // Database name (do NOT include .fmp12)
   public $dataSources;                                           // Where the external authentication/OAuth data is stored
   public $oauth;                                                 // Where we store OAuth data

   /***********************************************************************************************************************************
    *
    * __construct($database, $host, $username, $password, $options = array())
    *
    *    Constructor for fmDataAPI.
    *
    *    Parameters:
    *       (string)  $database         The name of the database (do NOT include the .fmpNN extension)
    *       (string)  $host             The host name typically in the format of https://HOSTNAME
    *       (string)  $username         The user name of the account to authenticate with
    *       (string)  $password         The password of the account to authenticate with
    *       (array)   $options          Optional parameters
    *                                       ['version']              Version of the API to use (1, 2, etc. or 'Latest')
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
    *                                       ['authentication']       set to 'oauth' for oauth authentication
    *                                       ['oauthID']              oauthID
    *                                       ['oauthIdentifier']      oauth identifier
    *
    *                                       ['sources'] => array(
    *                                                        array(
    *                                                          'database'  => '',      // do NOT include .fmpNN
    *                                                          'username'  => '',
    *                                                          'password'  => ''
    *                                                        )
    *                                                      )
    *
    *    Returns:
    *       The newly created object.
    *
    *    Example:
    *       $fm = new fmDataAPI($database, $host, $username, $password);
    */
   function __construct($database, $host, $username, $password, $options = array())
   {
      $this->database = $database;

      $options['host']            = $host;
      $options['sessionTokenKey'] = array_key_exists('sessionTokenKey', $options) ? $options['sessionTokenKey'] : FM_DATA_SESSION_TOKEN;
      $options['userAgent']       = array_key_exists('userAgent', $options) ? $options['userAgent'] : DATA_API_USER_AGENT;
      $options['version']         = array_key_exists('version', $options) ? $options['version'] : FM_VERSION_1;

      $authentication = array_key_exists('authentication', $options) ? $options['authentication'] : array('method' => 'default');

      if (! array_key_exists('method', $authentication)) {
         $authentication['method'] = 'default';
      }

      $authentication['username'] = array_key_exists('username', $authentication) ? $authentication['username'] : $username;
      $authentication['password'] = array_key_exists('password', $authentication) ? $authentication['password'] : $password;

      $options['authentication'] = $authentication;

      parent::__construct($options);

      fmLogger('fmDataAPI: v'. $this->version);

      return;
   }

   /***********************************************************************************************************************************
    *
    * apiCreateRecord($layout, $fields, $options = array())
    *
    *    Create a new record.
    *
    *    Parameters:
    *       (string)  $layout           The name of the layout
    *       (array)   $fields           An array of field name/value pairs
    *       (array)   $options          Additional API parameters. Valid options:
    *                                     ['script']
    *                                     ['scriptParams']
    *                                     ['scriptPrerequest']
    *                                     ['scriptPrerequestParams']
    *                                     ['scriptPresort']
    *                                     ['scriptPresortParams']
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['response'] If the call succeeds:
    *                       ['modId'] Modification ID (should be 0)
    *                       ['recordId'] Record ID of the newly created record
    *          ['response']
    *          ['messages'] Array of code/message pairs
    *
    *    Example:
    *       $fm = new fmDataAPI($database, $host, $username, $password);
    *       $fields = array();
    *       $fields['Name'] = 'Test';
    *       $fields['ColorIndex'] = 999;
    *       $apiResult = $fm->apiCreateRecord('Web_Project', $fields);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiCreateRecord($layout, $fields, $options = array())
   {
      $data = $this->getAPIParams($options, METHOD_POST);
      $data[FM_FIELD_DATA] = $fields;

      return $this->fmAPI($this->getAPIPath(PATH_RECORD, $layout), METHOD_POST, $data);
   }

   /***********************************************************************************************************************************
    *
    * apiDeleteRecord($layout, $recordID, $options = array())
    *
    *    Delete a record.
    *
    *    Parameters:
    *       (string)  $layout           The name of the layout
    *       (integer) $recordID         The recordID of the record to delete
    *       (array)   $options          Additional API parameters. Valid options:
    *                                     ['script']
    *                                     ['scriptParams']
    *                                     ['scriptPrerequest']
    *                                     ['scriptPrerequestParams']
    *                                     ['scriptPresort']
    *                                     ['scriptPresortParams']
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['response'] Typically this will be empty
    *          ['messages'] Array of code/message pairs
    *
    *    Example:
    *       $fm = new fmDataAPI($database, $host, $username, $password);
    *       $apiResult = $fm->apiDeleteRecord('Web_Project', 5);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiDeleteRecord($layout, $recordID, $options = array())
   {
      return $this->fmAPI($this->getAPIPath(PATH_RECORD, $layout) .'/'. $recordID, METHOD_DELETE, $this->getAPIParams($options, METHOD_DELETE));
   }

  /***********************************************************************************************************************************
    *
    * apiEditRecord($layout, $recordID, $data, $options = array())
    *
    *    Edit a record.
    *
    *    Parameters:
    *       (string)  $layout           The name of the layout
    *       (integer) $recordID         The recordID of the record to edit
    *       (array)   $data             Array of field name/value pairs
    *       (array)   $options          Additional API parameters. Valid options:
    *                                     ['script']
    *                                     ['scriptParams']
    *                                     ['scriptPrerequest']
    *                                     ['scriptPrerequestParams']
    *                                     ['scriptPresort']
    *                                     ['scriptPresortParams']
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['response'] If the call succeeds:
    *                       ['modId'] The new modification ID of the edited record
    *          ['messages'] Array of code/message pairs
    *
    *    Example:
    *       $fm = new fmDataAPI($database, $host, $username, $password);
    *       $fields = array();
    *       $fields['ColorIndex'] = 48;
    *       $apiResult = $fm->apiEditRecord('Web_Project', 5, $fields);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiEditRecord($layout, $recordID, $data = array(), $options = array())
   {
      $payload = $this->getAPIParams($options, METHOD_PATCH);

      if (! array_key_exists(FM_FIELD_DATA, $data)) {        // If there's no ['fieldData'], add it now
         $payload[FM_FIELD_DATA] = $data;
      }
      else {
         // The caller passed in a structure that has the ['fieldData'] element and likely ['portalData'].
         // As such, there's nothing for us to do - the caller knows best.
         $payload = array_merge($data, $options);
      }

      return $this->fmAPI($this->getAPIPath(PATH_RECORD, $layout) .'/'. $recordID, METHOD_PATCH, $payload);
   }

  /***********************************************************************************************************************************
    *
    * apiFindRecords($layout, $data, $options = array())
    *
    *    Find record(s) with a query which can be a non-compound or a compound find.
    *
    *    Parameters:
    *       (string)  $layout           The name of the layout
    *       (array)   $data             An array of query parameters in the format the Data API expects.
    *                                   Currently this is the same format as $options['query'].
    *       (array)   $options          Additional API parameters. Valid options:
    *                                     ['limit']
    *                                     ['offset']
    *                                     ['sort']
    *                                     ['script']
    *                                     ['scriptParams']
    *                                     ['scriptPrerequest']
    *                                     ['scriptPrerequestParams']
    *                                     ['scriptPresort']
    *                                     ['scriptPresortParams']
    *                                     ['layoutResponse']
    *                                     ['portals']
    *                                     ['portalLimits']
    *                                     ['portalOffsets']
    *                                     ['query']
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['response'] The record(s) data
    *          ['messages'] Array of code/message pairs
    *
    *    Example:
    *       $fm = new fmDataAPI($database, $host, $username, $password);
    *       $data = array(
    *                'query'=> array(
    *                            array('Name' => 'Test', 'ColorIndex' => '5', 'omit' => 'true'),
    *                            array('ColorIndex' => '20')
    *                          )
    *               );
    *       $apiResult = $fm->apiFindRecords('Web_Project', $data);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiFindRecords($layout, $data, $options = array())
   {
      $payload = $this->getAPIParams($options, METHOD_POST);

      $payload = is_array($data) ? array_merge($data, $payload) : $payload;

      return $this->fmAPI($this->getAPIPath(PATH_FIND, $layout), METHOD_POST, $payload);
   }

   /***********************************************************************************************************************************
    *
    * apiGetRecord($layout, $recordID, $params = '', $options = array())
    *
    *    Get the record specified by $recordID.
    *
    *    Parameters:
    *       (string)  $layout           The name of the layout
    *       (integer) $recordID         The recordID of the record to retrieve
    *       (string)  $params           The raw GET parameters to modify the call. Typically done with $options [optional]
    *       (array)   $options          Additional API parameters. Valid options:
    *                                     ['limit']
    *                                     ['offset']
    *                                     ['sort']
    *                                     ['script']
    *                                     ['scriptParams']
    *                                     ['scriptPrerequest']
    *                                     ['scriptPrerequestParams']
    *                                     ['scriptPresort']
    *                                     ['scriptPresortParams']
    *                                     ['layoutResponse']
    *                                     ['portals']
    *                                     ['portalLimits']
    *                                     ['portalOffsets']
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['response'] The record data
    *          ['messages'] Array of code/message pairs
    *
    *    Example:
    *       $fm = new fmDataAPI($database, $host, $username, $password);
    *       $apiResult = $fm->apiGetRecord('Web_Project', 1);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiGetRecord($layout, $recordID, $params = '', $options = array())
   {
      $payload = $this->getAPIParams($options, METHOD_GET);

      $payload = $payload . ((($payload != '') && ($params != '')) ? '&' : '') . $params;

      return $this->fmAPI($this->getAPIPath(PATH_RECORD, $layout) .'/'. $recordID, METHOD_GET, $payload);
   }

   /***********************************************************************************************************************************
    *
    * apiGetRecords($layout, $params = '', $options = array())
    *
    *    Get a series of records. By default the first 100 records will be returned.
    *
    *    Parameters:
    *       (string)  $layout           The name of the layout
    *       (string)  $params           The raw GET parameters (ie: '&_limit=50&_offset=10')
    *                                   to modify the call. Typically done with
    *                                   $options [optional]
    *       (array)   $options          Additional API parameters. Valid options:
    *                                     ['script']
    *                                     ['scriptParams']
    *                                     ['scriptPrerequest']
    *                                     ['scriptPrerequestParams']
    *                                     ['scriptPresort']
    *                                     ['scriptPresortParams']
    *                                     ['layoutResponse']
    *                                     ['portals']
    *                                     ['portalLimits']
    *                                     ['portalOffsets']
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['response'] The record(s) data
    *          ['messages'] Array of code/message pairs
    *
    *    Example:
    *       $fm = new fmDataAPI($database, $host, $username, $password);
    *       $apiResult = $fm->apiGetRecords('Web_Project', array('limit' => 50, 'offset' => 10));
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiGetRecords($layout, $params = '', $options = array())
   {
      $payload = $this->getAPIParams($options, METHOD_GET);

      $payload = $payload . ((($payload != '') && ($params != '')) ? '&' : '') . $params;

      return $this->fmAPI($this->getAPIPath(PATH_RECORD, $layout), METHOD_GET, $payload);
   }

   /***********************************************************************************************************************************
    *
    * apiLogin()
    *
    *    Create a new session on the server. Authentication parameters were previously passed to the  __construct method.
    *    Normally you will not call this method as the other apiNNNNNN() methods take care of logging in when appropriate.
    *    By default, the authentication token is stored within this class and reused for all further calls. If the server
    *    replies that the token is not longer valid (FM_ERROR_INVALID_TOKEN - 952), this class will automatically login
    *    again to get a new token.
    *
    *    The server expires a timer after approximately 15 minutes, but the timer is reset each time you make a call to
    *    the server. You should not assume it is always 15 minutes; a later version of FileMaker Server may change this
    *    time. Let this class handle the expiration in a graceful manner.
    *
    *    Parameters:
    *       None
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['response'] If the call succeeds, the ['token'] element contains the authentication token.
    *          ['messages'] Array of code/message pairs
    *
    *    Example:
    *       $fm = new fmDataAPI($database, $host, $username, $password);
    *       $apiResult = $fm->apiLogin();
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiLogin()
   {
      return $this->login();
   }

   /***********************************************************************************************************************************
    *
    * apiLogout()
    *
    *    Logs out of the current session and clears the username, password, and authentication token.
    *    Any global variables previously set will be restored to their previous values by the server.
    *
    *    Normally you will not call this method so that you can keep re-using the authentication token for future calls.
    *    Only logout if you know you are completely done with the session. This will also clear the stored username/password.
    *
    *    Parameters:
    *       None
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['response'] Typically this will be empty
    *          ['messages'] Array of code/message pairs
    *
    *    Example:
    *       $fm = new fmDataAPI($database, $host, $username, $password);
    *       $apiResult = $fm->apiLogout();
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
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

   /***********************************************************************************************************************************
    *
    * apiPerformScript($layout, $scriptName, $params = '', $layoutResponse = '')
    *
    *    Execute a script. We do this by doing a apiGetRecords() call for the first record on the specified layout.
    *    For this to work, *YOU MUST* have at least one record in this table or the script *WILL NOT EXECUTE*.
    *    For efficiency, you may want to create a table with just one record and no fields.
    *    Typically you'll call this with $layout set to a layout with nothing on it and then use $layoutResponse
    *    to indicate where you expect the result from the script to have put the record(s).
    *
    *    Parameters:
    *       (string)  $layout           The name of the layout
    *       (string)  $scriptName       The name of the FileMaker script to execute
    *       (string)  $params           The script parameter
    *       (string)  $layoutResponse   The name of the layout that any found set will be returned from
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['response'] Returns any record(s) in ['data'] if there's a found set.
    *                       ['scriptError'] has any scripting error
    *                       ['scriptResult'] is the value returned from the Exit Script[] script step
    *          ['messages'] Array of code/message pairs
    *
    *    Example:
    *       $fm = new fmDataAPI($database, $host, $username, $password);
    *       $apiResult = $fm->apiPerformScript('Web_Global', 'Test', 'some', 'Web_Project');
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiPerformScript($layout, $scriptName, $params = '', $layoutResponse = '')
   {
      $options = array();
      $options['limit'] = 1;
      $options['script'] = $scriptName;

      if ($params != '') {
         $options['scriptParams'] = $params;
      }

      if ($layoutResponse != '') {
         $options['layoutResponse'] = $layoutResponse;
      }

      return $this->apiGetRecords($layout, '', $options);
   }

   /***********************************************************************************************************************************
    *
    * apiSetGlobalFields($layout, $data)
    *
    *    Set global variable(s). The values retain their value throughout the session until you logout or the token expires.
    *
    *    Parameters:
    *       (string)  $layout           The name of the layout
    *       (array)   $data             An array of field name/value pairs
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['response'] Typically this will be empty
    *          ['messages'] Array of code/message pairs
    *
    *    Example:
    *       $fm = new fmDataAPI($database, $host, $username, $password);
    *       $apiResult = $fm->apiSetGlobalFields('Web_Global', array('Project::gGlobal' => 5));
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiSetGlobalFields($layout, $data)
   {
      $payload = array();

      if (! array_key_exists(FM_GLOBAL_FIELDS, $data)) {            // If there's no ['globalFields'], add it now
         $payload[FM_GLOBAL_FIELDS] = $data;
      }
      else {
         // The caller passed in a structure that has the ['globalFields'] element.
         // As such, there's nothing for us to do - the caller knows best.
         $payload = $data;
      }

      return $this->fmAPI($this->getAPIPath(PATH_GLOBAL, $layout), METHOD_PATCH, $payload);
   }

  /***********************************************************************************************************************************
    *
    * apiGetContainer($layout, $recordID, $fieldName, $fieldRepetition = FM_FIELD_REPETITION_1, $options = array())
    *
    *    Get the contents of a container field for the specified field. This is more of a 'utility' method used
    *    in cases where you only have the recordID for the record and want the container contents. If you already have
    *    the record contents to access the URL for the field, call the getFile() method (which is a fmCURL method).
    *
    *    Parameters:
    *       (string)  $layout           The name of the layout
    *       (integer) $recordID         The recordID of the record to retrieve the container URL from field $fieldName
    *       (string)  $fieldName        The field name where the file will be stored
    *       (integer) $fieldRepetition  The field repetition number
    *       (array)   $options          The options array as defined by fmCURL::getFile(), additionally:
    *                                     ['fileNameField'] The field name where the file name is stored on the record
    *                                                       This lets the caller specify the downloaded filename
    *                                                       if [action'] = 'download'
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['response'] Typically this will be empty
    *          ['messages'] Array of code/message pairs
    *
    *       ALSO: the contents of the file will be stored in $this->file to avoid copying/memory consumption
    *
    *    Example:
    *       $fm = new fmDataAPI($database, $host, $username, $password);
    *       $apiResult = $fm->apiGetContainer(layout, $recordID, $fieldName);
    *       if (! $fm->getIsError($apiResult)) {
    *          file contents are in $curl->file *not* $result
    *          ...
    *       }
    */
   public function apiGetContainer($layout, $recordID, $fieldName, $fieldRepetition = FM_FIELD_REPETITION_1, $options = array())
   {
      $options = array_merge(array('retryOn401Error' => true), $options);

      $apiResult = $this->apiGetRecord($layout, $recordID);

      if (! $this->getIsError($apiResult)) {
         $responseData = $this->getResponseData($apiResult);

         $fieldName = ($fieldRepetition == FM_FIELD_REPETITION_1) ? $fieldName : $fieldName .'('. $fieldRepetition .')';

         $url = $responseData[0][FM_FIELD_DATA][$fieldName];
         if (array_key_exists('fileNameField', $options)) {
            $options['fileName'] = $responseData[0][FM_FIELD_DATA][$options['fileNameField']];
         }

         $apiResult = $this->getFile($url, $options);
      }

      return $apiResult;
   }

   /***********************************************************************************************************************************
    *
    * apiUploadContainer($layout, $recordID, $fieldName, $fieldRepetition, $file)
    *
    *    Upload a file to a container field.
    *
    *    Parameters:
    *       (string)  $layout           The name of the layout
    *       (integer) $recordID         The recordID of the record to store the file in
    *       (string)  $fieldName        The field name where the file will be stored
    *       (integer) $fieldRepetition  The field repetition number
    *       (string)  $file             An array of information about the file to be uploaded. You specify ['path'] or ['contents']:
    *                                      $file['path']       The path to the file to upload (use this or ['contents'])
    *                                      $file['contents']   The file contents (use this or ['path'])
    *                                      $file['name']       The file name (required if you use ['contents'] otherwise
    *                                                          it will be determined)
    *                                      $file['mimeType']   The MIME type
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['response'] Typically this will be empty
    *          ['messages'] Array of code/message pairs
    *
    *    Example:
    *       $fm = new fmDataAPI($database, $host, $username, $password);
    *       $file = array();
    *       $file['path'] = 'sample_files/sample.png';
    *       $apiResult = $fm->apiUploadContainer('Web_Project', 1, 'Photo', FM_FIELD_REPETITION_1, $file);
    *       if (! $fm->getIsError($apiResult)) {
    *          ...
    *       }
    */
   public function apiUploadContainer($layout, $recordID, $fieldName, $fieldRepetition, $file)
   {
      $data = '';

      if (array_key_exists('path', $file)) {
         $pathInfo = pathinfo($file['path']);

         $file['name']     = (array_key_exists('name', $file) && ($file['name'] != '')) ? $file['name'] : $pathInfo['basename'];
         $file['mimeType'] = (array_key_exists('mimeType', $file) && ($file['mimeType'] != '')) ? $file['mimeType'] : $this->get_mime_content_type($file['path']);
         $file['contents'] = file_get_contents($file['path']);
      }

      if ($fieldRepetition == '') {
         $fieldRepetition = FM_FIELD_REPETITION_1;
      }

      $boundaryID = "Container_Upload_fmDataAPI-" . uniqid();

      $data .= '--'. $boundaryID ."\r\n";

      $data .= 'Content-Disposition: form-data; name="upload"; filename="'. $file['name'] .'"' ."\r\n";
      if (array_key_exists('mimeType', $file) && ($file['mimeType'] != '')) {
         $data .= 'Content-Type: '. $file['mimeType'] ."\r\n";
      }
      $data .= "\r\n";

      $data .= $file['contents'] ."\r\n";
      $data .= "\r\n";

      $data .= '--'. $boundaryID .'--' ."\r\n";

      $options = array();
      $options[FM_CONTENT_TYPE]  = CONTENT_TYPE_MULTIPART_FORM .'; boundary='. $boundaryID;
      $options['CURLOPT_POST']   = 1;
      $options['encodeAsJSON']   = false;
      $options['decodeAsJSON']   = true;

      $path = $this->getAPIPath(PATH_UPLOAD, $layout) .'/'. $recordID .'/'. 'containers' .'/'. rawurlencode($fieldName) .'/'. $fieldRepetition;

      return $this->fmAPI($path, METHOD_POST, $data, $options);
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


   /***********************************************************************************************************************************
    *
    * Methods below are typically for internal use.
    *
    ***********************************************************************************************************************************
    */


   // *********************************************************************************************************************************
   // This method is called internally whenever no or an invalid token is passed to the Data API.
   //
   protected function login()
   {
      $data = '';

      $this->setToken();

      $options = array();

      switch ($this->authenticationMethod) {
         case 'default': {
            $options[FM_AUTHORIZATION_BASIC] = $this->credentials;

            if ($this->dataSources != '') {
               $data = $this->dataSources;
            }
            break;
         }
         case 'oauth': {
            $options[FM_OAUTH_REQUEST_ID]         = $this->oauth[FM_OAUTH_REQUEST_ID];
            $options[FM_OAUTH_REQUEST_IDENTIFIER] = $this->oauth[FM_OAUTH_REQUEST_IDENTIFIER];

            if ($this->dataSources != '') {
               $data = $this->dataSources;
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
   // getAPIParams
   //
   // Creates a list of parameters for the request to the Data API.
   //
   // $params is an array with any/all of the following indices.
   //       array(
   //          'limit'                  => <number>,
   //          'offset'                 => <number>,
   //          'sort'                   => array(array('fieldName' => '<string>'[, 'sortOrder' => '<ascend|descend>']), ... ),
   //          'script'                 => '<string>',
   //          'scriptParams'           => '<string>',
   //          'scriptPrerequest'       => '<string>',
   //          'scriptPrerequestParams' => '<string>',
   //          'scriptPresort'          => '<string>',
   //          'scriptPresortParams'    => '<string>',
   //          'layoutResponse'         => '<string>',
   //          'portal'                 => array('<string>', ...),
   //          'portals'                => array('<string>', ...),   <-- For backward compatibility, use 'portal' going forward
   //          'portalLimits'           => array(array('name' => '<string>', 'limit' => '<number>'), ...)
   //          'portalOffsets'          => array(array('name' => '<string>', 'offset' => '<number>'), ...)
   //          'query'                  => array(array('<fieldName>' => '<value>', '<fieldName>' => '<value>', ..., 'omit' => '<boolean>'), ... )
   //       )
   public function getAPIParams($params = array(), $method, $returnAs = '')
   {
      // Some GET parameters have underscores in front of them while POST never does, we need a mapping table
      $keys = array(
            'get'  => array('offset' => '_offset', 'limit' => '_limit', 'sort' => '_sort',
                           'script' => 'script', 'scriptParams' => 'script.param',
                           'scriptPrerequest' => 'script.prerequest', 'scriptPrerequestParams' => 'script.prerequest.param',
                           'scriptPresort' => 'script.presort', 'scriptPresortParams' => 'script.presort.param',
                           'layoutResponse' => 'layout.response',
                           'portals' => 'portal', /* For backward compatibility, use 'portal' going forward */
                           'portal' => 'portal', 'portalLimits' => '_limit', 'portalOffsets' => '_offset'
                     ),

            'post' => array('offset' => 'offset', 'limit' => 'limit', 'sort' => 'sort',
                           'script' => 'script', 'scriptParams' => 'script.param',
                           'scriptPrerequest' => 'script.prerequest', 'scriptPrerequestParams' => 'script.prerequest.param',
                           'scriptPresort' => 'script.presort', 'scriptPresortParams' => 'script.presort.param',
                           'layoutResponse' => 'layout.response',
                           'portals' => 'portal', /* For backward compatibility, use 'portal' going forward */
                           'portal' => 'portal', 'portalLimits' => 'limit', 'portalOffsets' => 'offset',
                           'query' => 'query'
                     )
      );

      $method = strtoupper($method);

      $key = (($method == METHOD_GET) || ($method == METHOD_DELETE)) ? 'get' : 'post';

      if (($returnAs == '') && ($key == 'get')) {
         $returnAs = 'text';
      }

      $data = array();

      if (array_key_exists('offset', $params) && ($params['offset'] != 0)) {
         $data[$keys[$key]['offset']] = $params['offset'];
      }

      if (array_key_exists('limit', $params) && ($params['limit'] != 0)) {
         $data[$keys[$key]['limit']] = $params['limit'];
      }

      if (array_key_exists('sort', $params) && (count($params['sort']) > 0)) {
         $sort = array();
         foreach ($params['sort'] as $sortItem) {
            $sort[] = array('fieldName' => $sortItem['fieldName'],
                            'sortOrder' => array_key_exists('sortOrder', $sortItem) ? $sortItem['sortOrder'] : 'ascend');
         }
         $data[$keys[$key]['sort']] = ($key == 'get') ? rawurlencode(json_encode($sort)) : $sort;
      }

      if (array_key_exists('script', $params) && ($params['script'] != '')) {
         $data[$keys[$key]['script']] = rawurlencode($params['script']);
         if (array_key_exists('scriptParams', $params) && ($params['scriptParams'] != '')) {
            $data[$keys[$key]['scriptParams']] = rawurlencode($params['scriptParams']);
         }
      }

      if (array_key_exists('scriptPrerequest', $params) && ($params['scriptPrerequest'] != '')) {
         $data[$keys[$key]['scriptPrerequest']] = rawurlencode($params['scriptPrerequest']);
         if (array_key_exists('scriptPrerequestParams', $params) && ($params['scriptPrerequestParams'] != '')) {
            $data[$keys[$key]['scriptPrerequestParams']] = rawurlencode($params['scriptPrerequestParams']);
         }
      }

      if (array_key_exists('scriptPresort', $params) && ($params['scriptPresort'] != '')) {
         $data[$keys[$key]['scriptPresort']] = rawurlencode($params['scriptPresort']);
         if (array_key_exists('scriptPresortParams', $params) && ($params['scriptPresortParams'] != '')) {
            $data[$keys[$key]['scriptPresortParams']] = rawurlencode($params['scriptPresortParams']);
         }
      }

      if (array_key_exists('layoutResponse', $params) && ($params['layoutResponse'] != '')) {
         $data[$keys[$key]['layoutResponse']] = rawurlencode($params['layoutResponse']);
      }

      if (array_key_exists('portal', $params) && (count($params['portal']) > 0)) {
         $portals = '';
         foreach ($params['portal'] as $portal) {
            $portals .= '"'. $portal .'",';                  // Wrap each portal within quotes
         }
         $data[$keys[$key]['portal']] = rawurlencode('['. rtrim($portals, ',') .']');
      }

      // This is for backward compatiblity Please use $params['portal'] moving forward as this matches the Data API.
      if (array_key_exists('portals', $params) && (count($params['portals']) > 0)) {
         $portals = '';
         foreach ($params['portals'] as $portal) {
            $portals .= '"'. $portal .'",';                  // Wrap each portal within quotes
         }
         $data[$keys[$key]['portals']] = rawurlencode('['. rtrim($portals, ',') .']');
      }

      if (array_key_exists('portalLimits', $params) && (count($params['portalLimits']) > 0)) {
         foreach ($params['portalLimits'] as $portal) {
            $data[$keys[$key]['portalLimits']. rawurlencode($portal['name'])] = rawurlencode($portal['limit']);
         }
      }

      if (array_key_exists('portalOffsets', $params) && (count($params['portalOffsets']) > 0)) {
         foreach ($params['portalOffsets'] as $portal) {
            $data[$keys[$key]['portalOffsets']. rawurlencode($portal['name'])] = rawurlencode($portal['offset']);
         }
      }

      if (array_key_exists('query', $params) && (count($params['query']) > 0)) {
         $data[$keys[$key]['query']] = $params['query'];
      }

      if ($returnAs == 'text') {
         $query = '';
         foreach ($data as $key => $value) {
           $query .= $key .'='. $value .'&';
         }
         $data = rtrim($query, '&');
      }
      else if ($returnAs == 'json') {
         $data = json_encode($data);
      }

      return $data;
   }

   // *********************************************************************************************************************************
   // Returns true if the error result indicates the token is bad.
   //
   protected function getIsBadToken($result)
   {
      $isBadToken = false;

      if ($this->getCodeExists($result, FM_ERROR_INVALID_TOKEN)) {
         fmLogger('FM_ERROR_INVALID_TOKEN');
         $isBadToken = true;
      }

      return $isBadToken;
   }

   // *********************************************************************************************************************************
   public function setAuthentication($data = array())
   {
      $this->authenticationMethod = array_key_exists('method', $data) ? strtolower($data['method']) : 'default';

      $this->credentials = '';
      $this->dataSources = '';
      $this->oauth = '';

      switch ($this->authenticationMethod) {

         case 'default': {
            if (array_key_exists('username', $data) && ($data['username'] != '') && array_key_exists('password', $data) && ($data['password'] != '')) {
               $this->credentials = base64_encode(utf8_decode($data['username']) .':'. utf8_decode($data['password']));

               // See if there's any external authentiation we need to add.
               // This allows us to access other database(s) on the same server in addition to the one we're connecting to.
               if (array_key_exists('sources', $data) && (count($data['sources']) > 0)) {
                  $this->dataSources = array();
                  foreach($data['sources'] as $source) {
                     $this->dataSources[FM_DATASOURCE][] = array('database' => $source['database'], 'username' => $source['username'], 'password' => $source['password']);
                  }
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

      $this->setToken();                                                                        // Invalidate token

      return;
   }

}

?>
