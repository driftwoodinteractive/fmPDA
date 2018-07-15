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
   public function log($data, $logLevel = null)
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

   // *********************************************************************************************************************************
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

      fmLogger('fmPDA: translateResult='. ($this->translateResult ? 'true' : 'false'));

      return;
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

   // *********************************************************************************************************************************
   // This is not needed anymore - just use the URL return in the field data directly. No more need for the 'Container Bridge' file!
   // Your existing code will continue to work but it would be best for performance if you modify your code to simply use the URL
   // returned from getFieldUnencoded() and place it in the <img tag directly.
   //
   public function getContainerData($containerURL)
   {
      return $containerURL;
   }

   // *********************************************************************************************************************************
   // This is not needed anymore - just use the URL return in the field data directly.
   //
   public function getContainerDataURL($graphicURL)
   {
      return $graphicURL;
   }

   // *********************************************************************************************************************************
   // Retrieve a record by the internal recordID. If you omit the recordID, the Data API returns the first record in the table.
   //
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

   // *********************************************************************************************************************************
   public function newFindCommand($layoutName)
   {
      return new fmFindQuery($this, $layoutName);
   }

   // *********************************************************************************************************************************
   public function newCompoundFindCommand($layoutName)
   {
      return new fmFindQuery($this, $layoutName);
   }

   // *********************************************************************************************************************************
   public function newFindRequest($layoutName)
   {
      return new fmFindRequest($this, $layoutName);
   }

   // *********************************************************************************************************************************
   public function newFindAllCommand($layoutName)
   {
      return new fmFind($this, $layoutName);
   }

   // *********************************************************************************************************************************
   public function newFindAnyCommand($layoutName)
   {
      return new fmFindAny($this, $layoutName);
   }

   // *********************************************************************************************************************************
   public function newAddCommand($layoutName)
   {
      return new fmAdd($this, $layoutName);
   }

   // *********************************************************************************************************************************
   public function newEditCommand($layoutName, $recordID)
   {
      return new fmEdit($this, $layoutName, $recordID);
   }

   // *********************************************************************************************************************************
   public function newDeleteCommand($layoutName, $recordID)
   {
      return new fmDelete($this, $layoutName, $recordID);
   }

   // *********************************************************************************************************************************
   public function newDuplicateCommand($layoutName, $recordID, $duplicateScript)
   {
      return new fmDuplicate($this, $layoutName, $recordID, $duplicateScript);
   }

   // *********************************************************************************************************************************
   // Execute a script. We do this by doing a apiGetRecords() call for the first record on the specified layout.
   // For this to work, *YOU MUST* have at least one record in this table or the script *WILL NOT EXECUTE*.
   // For efficiency, you may want to create a table with just one record and no fields.
   //
   public function newPerformScriptCommand($layout, $scriptName, $params = '')
   {
      return new fmScript($this, $layout, $scriptName, $params);
   }

   // *********************************************************************************************************************************
   public function newUploadContainerCommand($layout, $recordID, $fieldName, $fieldRepetition, $file)
   {
      return new fmUpload($this, $layout, $recordID, $fieldName, $fieldRepetition, $file);
   }

}

?>
