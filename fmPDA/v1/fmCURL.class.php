<?php
// *********************************************************************************************************************************
//
// fmCURL.class.php
//
// fmCURL is used to communicate with a host via curl(). This implementation is strictly for 'raw' curl data transmission.
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

require_once 'fmLogger.class.php';

// *********************************************************************************************************************************
define('CURL_USER_AGENT',              'fmCURL/1.0');             // Our user agent string

// *********************************************************************************************************************************
define('METHOD_DELETE',                'DELETE');
define('METHOD_GET',                   'GET');
define('METHOD_PATCH',                 'PATCH');
define('METHOD_PUT',                   'PUT');
define('METHOD_POST',                  'POST');

// *********************************************************************************************************************************
define('HTTP_SUCCESSS',                200);                      // Success
define('HTTP_BAD_REQUREST',            400);                      // Bad Request
define('HTTP_UNAUTHORIZED',            401);                      // Unauthorized (bad credentials)
define('HTTP_SERVICE_UNAVAILABLE',     503);                      // Service Unavailable

define('CURL_CONNECTION_TIMEOUT',      2);                        // Connection time out

// *********************************************************************************************************************************
class fmCURL
{
   public $userAgent;                                             // The User Agent
   public $curlOptions;                                           // Any additional curl options you need to pass/override
   public $options;                                               // Any additional options you need to pass/override

   public $curlErrNum;                                            // Response curl error number
   public $curlErrMsg;                                            // Response curl error message
   public $curlInfo;                                              // Response curl info
   public $httpCode;                                              // HTTP response code
   public $callTime;                                              // Roundtrip Time (in seconds) for call to Data API

   // *********************************************************************************************************************************
   function __construct($options = array(), $curlOptions = array())
   {
      $this->options = array();
      $this->options['CURLOPT_HTTPHEADER']      = array();
      $this->options['CURLOPT_CONNECTTIMEOUT']  = CURL_CONNECTION_TIMEOUT;
      $this->options['CURLOPT_CAINFO']          = '';
      $this->options['CURLOPT_POST']            = '';
      $this->options['maxAttempts']             = 1;
      $this->options['encodeAsJSON']            = false;
      $this->options['decodeAsJSON']            = false;
      $this->options['logCallInfo']             = true;
      $this->options['logSentHeaders']          = false;
      $this->options['logSentData']             = false;
      $this->options['logPostData']             = true;
      $this->options['logCURLResult']           = true;
      $this->options['logCURLInfo']             = false;
      $this->options['userAgent']               = CURL_USER_AGENT;

      $this->options = array_merge($this->options, $options);

      $this->curlOptions = $curlOptions;

      $this->curlInfo = array();
      $this->curlErrNum = 0;
      $this->curlErrMsg = '';
      $this->httpCode = '';
      $this->callTime = 0;
   }

   // *********************************************************************************************************************************
   // Use curl to call the API.
   // If the call was sucessful, this returns returns the jSON data decoded into a PHP structure.
   // If the call fails, an emptry array is returned and the caller should examine $this->curlErrNum and/or this->curlErrMsg.
   //
   public function curl($url, $method = METHOD_GET, $data = '', $options = array())
   {
      $result = array();

      $options = array_merge($this->options, $options);

      $postData = '';
      if ($data != '') {
         if (($method == METHOD_GET) || ($method == METHOD_DELETE)) {
            $url .= '?'. (($data[0] == '&') ? substr($data, 1) : $data);
         }
         else {
            if ($options['encodeAsJSON']) {
               $postData = json_encode($data);
            }
            else {
               $postData = $data;
            }
         }
      }

      $ch = curl_init();                                                      // Initialize curl and specify all options
      curl_setopt($ch, CURLOPT_URL, $url);
      if (is_array($options['CURLOPT_HTTPHEADER']) && array_key_exists('CURLOPT_HTTPHEADER', $options) && (count($options['CURLOPT_HTTPHEADER']) > 0)) {
         curl_setopt($ch, CURLOPT_HTTPHEADER, $options['CURLOPT_HTTPHEADER']);
      }
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

      if ($options['CURLOPT_CONNECTTIMEOUT'] != '') {
         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options['CURLOPT_CONNECTTIMEOUT']);
      }

      if ($options['userAgent'] != '') {
         curl_setopt($ch, CURLOPT_USERAGENT, $options['userAgent']);
      }

      if ($options['CURLOPT_CAINFO'] != '') {
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
         curl_setopt($ch, CURLOPT_CAINFO, $options['CURLOPT_CAINFO']);
      }
      else {
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      }

      if ($options['CURLOPT_POST'] != '') {
         curl_setopt($ch, CURLOPT_POST, $options['CURLOPT_POST']);
      }

      if (($postData != '') && ($method != METHOD_GET) && ($method != METHOD_DELETE)) {   // Most requests will have JSON data in the body
         curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
      }

      if (count($this->curlOptions) > 0) {                                    // In case caller needs to tweak the options
         curl_setopt_array($ch, $this->curlOptions);                          // Do this last to allow override of what we set directly.
      }

      $attemptNum = 0;
      do {
         $attemptNum++;

         $curlResult = curl_exec($ch);                                        // Make the request

         $this->curlErrNum = curl_errno($ch);
         $this->curlErrMsg = ($this->curlErrNum != 0) ? curl_error($ch) : '';
         $this->curlInfo = curl_getinfo($ch);
         $this->httpCode = $this->curlInfo['http_code'];
         $this->callTime = $this->curlInfo['total_time'];                     // Get our round trip time

         if ($this->curlErrNum != 0) {
            if ($options['maxAttempts'] > 1) {
               fmLogger('curl('. $url .') [Attempt '. $attemptNum. ' of '. $options['maxAttempts'] .'] Error: {HTTP '. $this->httpCode .'} '. $this->curlErrNum .' '. $this->curlErrMsg);
            }
         }
   	} while (($attemptNum < $options['maxAttempts']) && ($this->curlErrNum != 0));

      curl_close($ch);                                                        // All done with curl for now

      // Map a few common HTTP response codes into a curl error field
      if ($this->curlErrNum == 0) {
         if ($this->curlInfo['http_code'] == HTTP_SERVICE_UNAVAILABLE) {     // One way to get this is to turn off Data API in FM Admin Console
            $this->curlErrNum = $this->curlInfo['http_code'];
            $this->curlErrMsg = 'Service Unavailable';
         }
         else if ($this->curlInfo['http_code'] == HTTP_BAD_REQUREST) {
            $this->curlErrNum = $this->curlInfo['http_code'];
            $this->curlErrMsg = 'Bad Request';
         }
         else if ($this->curlInfo['http_code'] == HTTP_UNAUTHORIZED) {
            $this->curlErrNum = $this->curlInfo['http_code'];
            $this->curlErrMsg = 'Unauthorized';
         }
      }

      if ($curlResult != '') {
         $result = $options['decodeAsJSON'] ? json_decode($curlResult, true) : $curlResult;
      }

      $length = number_format(strlen($curlResult), 0, '.', ',');
      $bytesSec = ($this->callTime > 0) ? number_format((floatval(strlen($curlResult) / $this->callTime)) / 1024, 0, '.', ',') : '0';

      if ($options['logCallInfo']) {
         fmLogger('————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————');
         fmLogger('{HTTP: '. $this->httpCode .'} ['. $method .'] ('. round($this->callTime, 3) .'s&#8644;, '. $length .' bytes, '. $bytesSec .'k/s) '. $url);
      }

      if ($options['logSentHeaders']) {
         fmLogger('HTTP header:'. '<br>'. print_r($this->curlInfo['request_header'], true)); // CURLOPT_HTTPHEADER only gives what we add
      }

      if ($options['logSentData']) {
         fmLogger('data='. print_r($data, true));
      }

      if ($options['logPostData'] && ($postData != '')) {
         fmLogger('<b>&#9658;</b>'. htmlspecialchars($postData));
      }

      if ($options['logCURLResult'] && ($curlResult != '')) {
         fmLogger('<b>&#9664;</b>'. htmlspecialchars($curlResult));
      }

      if (($this->curlErrNum != 0) || ($this->curlErrMsg != '')) {
         fmLogger('curl/HTTP code='. $this->curlErrNum .' curlErrMsg='. $this->curlErrMsg);
      }

      if ($options['logCURLInfo']) {
         fmLogger('curlInfo='. print_r($this->curlInfo, true));
      }

      if ($options['logCallInfo']) {
         fmLogger('————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————————');
      }

      return $result;
   }

   // *********************************************************************************************************************************
   // Returns true if there's an error from curl.
   //
   function getIsError($result = '')
   {
      $isError = false;

      if ($this->curlErrNum != '') {
         $isError = true;
      }

      if ($this->curlErrMsg != '') {
         $isError = true;
      }

      return $isError;
   }

   // *********************************************************************************************************************************
   // Returns any error from curl.
   //
   function getErrorInfo($result = '')
   {
      $errorInfo = array('code' => '', 'message' => '');

      if ($this->curlErrNum != '') {
         $errorInfo['code'] = $this->curlErrNum;
      }
      if ($this->curlErrMsg != '') {
         $errorInfo['message'] = $this->curlErrMsg;
      }

      return $errorInfo;
   }

   // *********************************************************************************************************************************
   // Use this function to specify addition options (or override what this class does by default) for the curl() call if your
   // configuration requires it.
   //
   public function setCURLOptions($curlOptions = array())
   {
      $this->curlOptions = $curlOptions;

      return;
   }

}

?>
