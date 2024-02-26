<?php
// *********************************************************************************************************************************
//
// fmPDA.class.php
//
// FM PHP Data API
//
// Provides a 'bridge' between legacy FileMaker API For PHP code and the new REST/Data API.
//
// Not all PHP API calls are supported - only the most 'popular'. Run it against your code and see how it works.
// PHP will generate errors for methods not implemented.
//
// Objects returned do not use the same class name as FileMaker's API For PHP, so you will need to modify your code if you've been testing for this.
// There are two utility functions fmGetIsError() and fmGetIsValid() you can use to test for an error object or a 'good' result.
//
// Wherever you currently call:
//    $fm = new FileMaker(...)
// Instead call:
//    $fm = new fmPDA(... {, $options})
//
// Then use $fm as you have before.
//
// Additionally, the fmDataAPI class (which fmPDA derives from) can be used for direct Data API acccess - it will always return
// JSON data and you'll be expected to deal with those responses directly.
//
// Lastly: fmCURL can be instantiated to make 'raw' curl() calls that don't do any translation on the data return.
// Handy if you need to use curl() for other things in your project.
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

require_once 'fmDataAPI.class.php';

// *********************************************************************************************************************************
define('FMPDA_USER_AGENT',             'fmPDAphp/1.0');           // Our user agent string

define('FM_ERROR_COMMUNICATION',       22);                       // PHP API Error for lots of things


// *********************************************************************************************************************************
if (DEFINE_FILEMAKER_CLASS) {

   // See if the old API is included...
   if (class_exists('FileMaker')) {
      die('Old FileMaker API For PHP class files are included *and* you trying to redefine the FileMaker class. Please choose one or the other.');
   }

   class FileMaker extends fmDataAPI
   {
      static function isError($result)       { return fmGetIsError($result); }      // You can call fmGetIsError() instead of this
      static function getAPIVersion()        { return '1.0'; }                      // Just something 'new'
      static function getMinServerVersion()  { return '17.0.1.146'; }               // Needs FMS 17 for this version of the Data API
   }
   class fmPDAGlue extends FileMaker { }
}
else {
   class fmPDAGlue extends fmDataAPI { }
}

// *********************************************************************************************************************************
// Logging tool for use with fmPDA objects (specifically fmRecord).
//
class fmPDALogger extends fmLogger
{
   public function log($data = '', $logLevel = null)
   {
      if ($this->logging) {

         if ($data instanceof fmRecord) {                // If it's an fmRecord, use dumpRecord() to create the output
            $data = $data->dumpRecord();
         }

         parent::log($data, $logLevel);
      }

      return;
   }
}


// *********************************************************************************************************************************
class fmPDA extends fmPDAGlue
{
   public $translateResult;                            // If true, this class will translate the data returned from the Data API
                                                       // into the 'old' FileMaker API For PHP objects. The default is true.

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
    *                                       ['version'] Version of the API to use (1, 2, etc. or 'Latest')
    *
    *                                       ['authentication'] set to 'oauth' for oauth authentication
    *                                       ['oauthID'] oauthID
    *                                       ['oauthIdentifier'] oauth identifier
    *
    *                                       ['sources'] => array(          External database authentication
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
    *       $fm = new fmPDA($database, $host, $username, $password);
    */
   function __construct($database, $host, $username, $password, $options = array())
   {
      // Create our override of the standard logging object so we get the one that knows about fmRecord.
      new fmPDALogger();

      // If the old API is included, warn the user that there might be conflicts.
      if (! DEFINE_FILEMAKER_CLASS && class_exists('FileMaker')) {
         fmLogger('<br><br>*** Old FileMaker API For PHP class files are still included. You *may* have conflicts. ***<br><br>');
      }

      $options['userAgent']   = array_key_exists('userAgent', $options) ? $options['userAgent'] : FMPDA_USER_AGENT;

      parent::__construct($database, $host, $username, $password, $options);

      $this->translateResult = array_key_exists('translateResult', $options) ? $options['translateResult'] : true;

      fmLogger('fmPDA: v'. $this->version);

      return;
   }

   /***********************************************************************************************************************************
    *
    * createRecord($layout, $fieldValues = array())
    *
    *    Create a record object. The record's Commit() method will send the data to the server.
    *
    *    Parameters:
    *       (string)  $layout           The name of the layout
    *       (array)   $fieldValues      An array of field name/value pairs
    *
    *    Returns:
    *       The newly created record object.
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $record = $fm->createRecord($layout, $fieldValues);
    */
   public function createRecord($layout, $fieldValues = array())
   {
      $data = array();
      $data[FM_FIELD_DATA] = $fieldValues;

      if ($this->translateResult) {
         return new fmRecord($this, $layout, $data);
      }
      else {
         return $data;
      }
   }

   /***********************************************************************************************************************************
    *
    * getContainerData($containerURL, $options)
    *
    *    Returns the contents of the container field. Given the changes with the Data API, you can bypass this entirely if you are
    *    simply wanting to display the container with an <img tag. Just take the url returned in the field and feed that into the
    *    <img tag.
    *
    *    Note that the $options parameter is new to fmPDA - it does not exist in the FileMaker API for PHP.
    *
    *    Parameters:
    *       (string)  $containerURL      The URL to the container
    *       (array)   $options           The options array as defined by fmCURL::getFile(), additionally:
    *                                     ['fileName'] The name of the file to download (only used if ['action'] == 'download'
    *
    *    Returns:
    *       The contents of the container field
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $contents = $fm->getContainerData($containerURL);
    */
   public function getContainerData($containerURL, $options = array())
   {
      $options = array_merge(array('retryOn401Error' => true), $options);

      $result = $this->getFile($containerURL, $options);

      return $this->file;
   }

   /***********************************************************************************************************************************
    *
    * getContainerDataURL($graphicURL)
    *
    *    This is not needed anymore - just use the URL return in the field data directly. Your existing code will continue to work
    *    but it would be best for performance if you modify your code to simply use the URL returned from getFieldUnencoded() and
    *    place it in the <img tag directly.
    *
    *    Parameters:
    *       (string)  $graphicURL           The URL to the container
    *
    *    Returns:
    *       The same URL passed in
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $url = $fm->getContainerDataURL($graphicURL);
    */
   public function getContainerDataURL($graphicURL)
   {
      return $graphicURL;
   }

   /***********************************************************************************************************************************
    *
    * getRecordById($layout, $recordID = '')
    *
    *    Get the record specified by $recordID. If you omit the recordID, the first record in the table is returned.
    *
    *    Parameters:
    *       (string)  $layout           The name of the layout
    *       (integer) $recordID         The recordID of the record to retrieve
    *
    *    Returns:
    *       An fmRecord or fmError object
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $result = $fm->getRecordById('Web_Project', 1);
    *       if (! $fm->getIsError($result)) {
    *          ...
    *       }
    */
   public function getRecordById($layout, $recordID = '')
   {
      $apiResult = $this->apiGetRecord($layout, $recordID);

      if ($this->translateResult) {
         if (fmGetIsError($apiResult)) {
            if ($this->getCodeExists($apiResult, FM_ERROR_RECORD_IS_MISSING)) {
               $result = null;                                                      // Old API returns null for a missing record
            }
            else {
               $result = $apiResult;
            }
         }
         else {
            $responseData = $this->getResponseData($apiResult);
            $result = new fmRecord($this, $layout, array_key_exists(0, $responseData) ? $responseData[0] : array());
         }
      }
      else {
         $result = $apiResult;
      }

      return $result;
   }

   /***********************************************************************************************************************************
    *
    * newFindCommand($layoutName)
    *
    *    Create a new find object.
    *
    *    Parameters:
    *       (string)  $layoutName           The name of the layout
    *
    *    Returns:
    *       A fmFindQuery object
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $findCommand = $fm->newFindCommand($layoutName);
    */
   public function newFindCommand($layoutName)
   {
      return new fmFindQuery($this, $layoutName);
   }

   /***********************************************************************************************************************************
    *
    * newCompoundFindCommand($layoutName)
    *
    *    Create a new compound find object.
    *
    *    Parameters:
    *       (string)  $layoutName           The name of the layout
    *
    *    Returns:
    *       A fmFindQuery object
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $findCommand = $fm->newCompoundFindCommand($layoutName);
    */
   public function newCompoundFindCommand($layoutName)
   {
      return new fmFindQuery($this, $layoutName);
   }

   /***********************************************************************************************************************************
    *
    * newFindRequest($layoutName)
    *
    *    Create a find request object.
    *
    *    Parameters:
    *       (string)  $layoutName           The name of the layout
    *
    *    Returns:
    *       A fmFindRequest object
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $findCommand = $fm->newFindRequest($layoutName);
    */
   public function newFindRequest($layoutName)
   {
      return new fmFindRequest($this, $layoutName);
   }

   /***********************************************************************************************************************************
    *
    * newFindAllCommand($layoutName)
    *
    *    Create a new find all object.
    *
    *    Parameters:
    *       (string)  $layoutName           The name of the layout
    *
    *    Returns:
    *       A fmFind object
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $findAllCommand = $fm->newFindAllCommand($layoutName);
    */
   public function newFindAllCommand($layoutName)
   {
      return new fmFind($this, $layoutName);
   }

    /***********************************************************************************************************************************
    *
    * newFindAnyCommand($layoutName)
    *
    *    Create a new find any object.
    *
    *    Parameters:
    *       (string)  $layoutName           The name of the layout
    *
    *    Returns:
    *       A fmFind object
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $findAnyCommand = $fm->newFindAnyCommand($layoutName);
    */
   public function newFindAnyCommand($layoutName)
   {
      return new fmFindAny($this, $layoutName);
   }

    /***********************************************************************************************************************************
    *
    * newAddCommand($layoutName, $fieldValues)
    *
    *    Create a new add record object.
    *
    *    Parameters:
    *       (string)  $layoutName           The name of the layout
    *       (array)   $fieldValues          An array of field name/value pairs
    *
    *    Returns:
    *       A fmAdd object
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $addCommand = $fm->newAddCommand($layoutName);
    */
   public function newAddCommand($layoutName, $fieldValues = array())
   {
      return new fmAdd($this, $layoutName, $fieldValues);
   }

    /***********************************************************************************************************************************
    *
    * newEditCommand($layoutName, $recordID, $fieldValues)
    *
    *    Create a new edit record object.
    *
    *    Parameters:
    *       (string)  $layoutName           The name of the layout
    *       (integer) $recordID             The recordID of the record to edit
    *       (array)   $fieldValues          An array of field name/value pairs
    *
    *    Returns:
    *       A fmEdit object
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $findAnyCommand = $fm->newEditCommand($layoutName, $recordID);
    */
   public function newEditCommand($layoutName, $recordID, $fieldValues = array())
   {
      return new fmEdit($this, $layoutName, $recordID, $fieldValues);
   }

    /***********************************************************************************************************************************
    *
    * newDeleteCommand($layoutName, $recordID)
    *
    *    Create a new delete record object.
    *
    *    Parameters:
    *       (string)  $layoutName           The name of the layout
    *       (integer) $recordID             The recordID of the record to delete
    *
    *    Returns:
    *       A fmDelete object
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $findAnyCommand = $fm->newDeleteCommand($layoutName, $recordID);
    *       $result = $findAnyCommand->execute();
    *       if (! $fm->getIsError($result)) {
    *          ...
    *       }
    */
   public function newDeleteCommand($layoutName, $recordID)
   {
      return new fmDelete($this, $layoutName, $recordID);
   }

    /***********************************************************************************************************************************
    *
    * newDuplicateCommand($layoutName, $recordID, $duplicateScript)
    *
    *    Create a new duplicate record object. The Data API does not support record duplication so we simulate this by using a
    *    caller-provided FileMaker script that performs the actual duplication and returns the recordID of the newly duplicated record.
    *
    *    Parameters:
    *       (string)  $layoutName           The name of the layout
    *       (integer) $recordID             The recordID of the record to duplicate
    *       (integer) $duplicateScript      The FileMaker script name that handles the actual duplication.
    *
    *    Returns:
    *       A fmDuplicate object
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $newDuplicateCommand = $fm->newDuplicateCommand($layoutName, $recordID, $duplicateScript);
    *       $result = $newDuplicateCommand->execute();
    *       if (! $fm->getIsError($result)) {
    *          ...
    *       }
    */
   public function newDuplicateCommand($layoutName, $recordID, $duplicateScript)
   {
      return new fmDuplicate($this, $layoutName, $recordID, $duplicateScript);
   }

    /***********************************************************************************************************************************
    *
    * newPerformScriptCommand($layout, $scriptName, $params = '')
    *
    *    Execute a script. For this to work, *YOU MUST* have at least one record in this table or the script *WILL NOT EXECUTE*.
    *    For efficiency, you may want to create a table with just one record and no fields.
    *
    *    Parameters:
    *       (string)  $layoutName           The name of the layout
    *       (string)  $scriptName           The name of the FileMaker script to execute
    *       (string)  $params               The script parameter
    *
    *    Returns:
    *       A fmScript object
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $performScriptCommand = $fm->newPerformScriptCommand($layout, $scriptName, $params);
    *       $result = $performScriptCommand->execute();
    *       if (! $fm->getIsError($result)) {
    *          ...
    *       }
    */
   public function newPerformScriptCommand($layout, $scriptName, $params = '')
   {
      return new fmScript($this, $layout, $scriptName, $params);
   }

    /***********************************************************************************************************************************
    *
    * newUploadContainerCommand($layout, $recordID, $fieldName, $fieldRepetition, $file)
    *
    *    Upload a file to a container field.
    *    This method does not exist in FileMaker's API For PHP but is provided as the Data API now directly supports this.
    *
    *    Parameters:
    *       (string)  $layout               The name of the layout
    *       (integer) $recordID             The recordID of the record to edit
    *       (string)  $fieldName            The field name where the file will be stored
    *       (integer) $fieldRepetition      The field repetition number
    *       (string)  $file                 An array of information about the file to be uploaded. You specify ['path'] or ['contents']:
    *                                           $file['path']       The path to the file to upload (use this or ['contents'])
    *                                           $file['contents']   The file contents (use this or ['path'])
    *                                           $file['name']       The file name (required if you use ['contents'] otherwise
    *                                                               it will be determined)
    *                                           $file['mimeType']   The MIME type
    *
    *    Returns:
    *       A fmUpload object
    *
    *    Example:
    *       $fm = new fmPDA($database, $host, $username, $password);
    *       $file = array();
    *       $file['path'] = 'sample_files/sample.png';
    *       $uploadContainerCommand = $fm->newUploadContainerCommand($layout, $recordID, $fieldName, $fieldRepetition, $file);
    *       $result = $uploadContainerCommand->execute();
    *       if (! $fm->getIsError($result)) {
    *          ...
    *       }
    */
   public function newUploadContainerCommand($layout, $recordID, $fieldName, $fieldRepetition, $file)
   {
      return new fmUpload($this, $layout, $recordID, $fieldName, $fieldRepetition, $file);
   }

   // *********************************************************************************************************************************
   // Create the fmError object. Override this to use your own class and/or throw the error from here.
   //
   public function newError($message = null, $code = null)
   {
      return new fmError($this, $message, $code);
   }

   // *********************************************************************************************************************************
   // Create the standard fmResult object to store the response from FileMaker. Override this if you want to return a different structure.
   //
   public function newResult($layout, $data = array())
   {
      return new fmResult($this, $layout, $data);
   }

   // *********************************************************************************************************************************
   // Override of fmDataAPI to return fmError to eumulate errors returned by the old FileMaker API For PHP
   //
   public function fmAPI($url, $method = METHOD_GET, $data = '', $options = array())
   {
      $result = parent::fmAPI($url, $method, $data, $options);

      // If the Data API returns an error, convert it to an fmError object.
      //
      // The Data API *can* return multiple messages but we can only return one fmError object,
      // so we use the first one in the array (for better or worse). This can always be revised later
      // to loop through the responses and choose the 'best' one to return to our caller.
      //
      // Sometimes the Data API returns only an error message (but no error code).
      // One example is 'connect ECONNREFUSED'. This can happen when the Data API engine crashes
      // (this is *not* an 503 error). With FMS 16, I get this after multiple lid close/opens
      // on a MacBook. Obviously not a production set up, but worth looking out for regardless.
      //
      if ($this->translateResult) {
         $message = $this->getFirstMessage($result);
         if (($message != null) && ($message[FM_MESSAGE] != FM_MESSAGE_OK) &&
             (($message[FM_CODE] != 0) || ($message[FM_MESSAGE] != ''))) {
            $result = $this->newError($message[FM_MESSAGE], $message[FM_CODE]);
         }
      }

      return $result;
   }

   // *********************************************************************************************************************************
   // The 'old' PHP API returned 22 whenever the account credenticals were wrong, so we'll swap out FM_ERROR_INVALID_ACCOUNT (212) for 22.
   //
   // Map any other errors returned by fmCURL or fmDataAPI by overriding this method.
   //
   function getMessageInfo($result)
   {
      $messageInfo = parent::getMessageInfo($result);
      $numMessages = count($messageInfo);

      if ($numMessages > 0) {
         for ($index = 0; $index < $numMessages; $index++) {
            if ($messageInfo[$index][FM_CODE] == FM_ERROR_INVALID_ACCOUNT) {
               $messageInfo[$index][FM_CODE] = FM_ERROR_COMMUNICATION;
               $messageInfo[$index][FM_MESSAGE] = 'Invalid user account and/or password';
            }
         }
      }

      return $messageInfo;
   }

   // *********************************************************************************************************************************
   function getTranslateResult()
   {
      return $this->translateResult;
   }

   // *********************************************************************************************************************************
   // Returns known properties. All others are ignored and a log message is generated.
   //
   function getProperty($prop)
   {
      switch ($prop) {

         case 'hostspec': {
            $value = $this->host;
            break;
         }

         case 'database': {
            $value = $this->database;
            break;
         }

         case 'username': {
            $credentials = explode(':', base64_decode($this->credentials));
            $value = utf8_encode($credentials[0]);
            break;
         }

         case 'password': {
            $credentials = explode(':', base64_decode($this->credentials));
            $value = utf8_encode($credentials[1]);
            break;
         }

         case 'charset': {
            $value = 'UTF-8';
            break;
         }

         case 'locale': {
            $value = 'en';
            break;
         }

         default: {
            fmLogger('<br><br>*** getProperty('. $prop .') is not supported. ***<br><br>');
            $value = '';
            break;
         }

      }

      return $value;
   }

   // *********************************************************************************************************************************
   // Return all known properties.
   //
   function getProperties()
   {
      $properties = array();
      $properties['hostspec']    = $this->getProperty('hostspec');
      $properties['database']    = $this->getProperty('database');
      $properties['username']    = $this->getProperty('username');
      $properties['password']    = $this->getProperty('password');
      $properties['charset']     = $this->getProperty('charset');
      $properties['locale']      = $this->getProperty('locale');

      return $properties;
   }

   // *********************************************************************************************************************************
   // Set a known property. All others are ignored and a log message is generated.
   // Caution: setting any of these properties will invalide the Data API token so the
   // next request will force a new token to be generated.
   //
   function setProperty($prop, $value)
   {
      switch ($prop) {

         case 'hostspec': {
            $this->host = $value;
            $this->setToken();
            break;
         }

         case 'database': {
            $this->database = $value;
            $this->setToken();
            break;
         }

         case 'username': {
            $credentials = array();
            $credentials['username'] = $value;
            $credentials['password'] = $this->getProperty('password');
            $this->setAuthentication($credentials);
            break;
         }

         case 'password': {
            $credentials = array();
            $credentials['username'] = $this->getProperty('username');
            $credentials['password'] = $value;
            $this->setAuthentication($credentials);
            break;
         }

         default: {
            fmLogger('<br><br>*** setProperty('. $prop .') is not supported. ***<br><br>');
            $value = '';
            break;
         }

      }

      return;
   }

   // *********************************************************************************************************************************
   function getLayout($prop, $value)
   {
      fmLogger('<br><br>*** getLayout is not supported. ***<br><br>');
      return NULL;
   }

   // *********************************************************************************************************************************
   public function listDatabases()
   {
      fmLogger('<br><br>*** listDatabases is not supported. ***<br><br>');
      return NULL;
   }

   // *********************************************************************************************************************************
   public function listScripts($prop, $value)
   {
      fmLogger('<br><br>*** listScripts is not supported. ***<br><br>');
      return;
   }

   // *********************************************************************************************************************************
  public  function listLayouts($prop, $value)
   {
      fmLogger('<br><br>*** listLayouts is not supported. ***<br><br>');
      return;
   }

   // *********************************************************************************************************************************
   function setLogger($prop, $value)
   {
      fmLogger('<br><br>*** setLogger is not supported. ***<br><br>');
      return;
   }


}

?>
