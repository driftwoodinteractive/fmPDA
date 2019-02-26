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
define('CURL_USER_AGENT',              'fmCURLphp/1.0');          // Our user agent string

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

   public $file;                                                  // Calling downloadFile() stores the contents of the file here
                                                                  // rather than returning it as the result so as to reduce
                                                                  // copying/memory consumption.

   /***********************************************************************************************************************************
    *
    * __construct($options = array(), $curlOptions = array()
    *
    *    Constructor for fmCURL.
    *
    *    Parameters:
    *       (array)   $options          Optional parameters
    *       (array)   $curlOptions      Optional parameters passed to curl()
    *
    *    Returns:
    *       The newly created object.
    *
    *    Example:
    *       $curl = new fmCURL();
    */
   function __construct($options = array(), $curlOptions = array())
   {
      $this->options = array();
      $this->options['CURLOPT_HTTPHEADER']      = array();
      $this->options['CURLOPT_CONNECTTIMEOUT']  = CURL_CONNECTION_TIMEOUT;
      $this->options['CURLOPT_CAINFO']          = '';
      $this->options['CURLOPT_POST']            = '';
      $this->options['createCookieJar']         = false;
      $this->options['cookieFilePath']          = '';
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

      $this->file       = '';
      $this->curlInfo   = array();
      $this->curlErrNum = 0;
      $this->curlErrMsg = '';
      $this->httpCode   = '';
      $this->callTime   = 0;
   }

   /***********************************************************************************************************************************
    *
    * curl($url, $method = METHOD_GET, $data = '', $options = array())
    *
    *    Make a call to curl()
    *
    *    Parameters:
    *       (string)   $url         The URL
    *       (string)   $method      'GET', 'PUT', 'POST', 'PATCH', 'DELETE', etc.
    *       (string)   $data        Data to send
    *       (array)    $options     Optional parameters
    *
    *    Returns:
    *       The data sent back from the host. getErrorInfo() may be used to see if there was an error.
    *
    *    Example:
    *       $curl = new fmCURL();
    *       $curlResult = $curl->curl('https://www.example.com');
    */
   public function curl($url, $method = METHOD_GET, $data = '', $options = array())
   {
      $result = '';

      $options = array_merge($this->options, $options);

      $method = strtoupper($method);

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
      curl_setopt($ch, CURLOPT_MAXREDIRS, 20);
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

      if ($this->options['createCookieJar']) {
         $this->options['cookieFilePath'] = tempnam(sys_get_temp_dir(), 'fmCURLCookie_'. mt_rand());
         curl_setopt($ch, CURLOPT_COOKIEJAR, $this->options['cookieFilePath']);
      }
      else if ($this->options['cookieFilePath'] != '') {
         curl_setopt($ch, CURLOPT_COOKIEFILE, $this->options['cookieFilePath']);
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

  /***********************************************************************************************************************************
    *
    * getFile($url, $options = array())
    *
    *    Get the contents of a file, optionally downloading or displaying it inline in the browser.
    *
    *    Parameters:
    *       (string)  $url           The URL where the file is located
    *       (array)   $options       An array of options:
    *                                  ['action']           One of 3 choices:
    *                                                          get         - Return the file in $this->file (default)
    *                                                          download    - Send headers, then echo $this->file
    *                                                          inline      - Send headers for inline viewing, then echo $this->file
    *                                  ['fileName']         The filename to use in the Content-Disposition header if ['action'] == 'download'
    *                                                       By default the file name from the URL will be used.
    *                                  ['compress']         If 'gzip', compress the file. Can always do it if ['action'] == 'get'.
    *                                                       For ['action'] == 'download' or ['action'] == 'inline', only if caller
    *                                                       supports gzip compression.
    *                                                       Defaults to 'gzip' for ['action'] == 'download' or ['action'] == 'inline',
    *                                  ['compressionLevel'] If ['compress'] =='gzip', then gzip with this compression level
    *                                                       (only valid for 'action' = 'download' or 'inline')
    *                                  ['createCookieJar']  If true, a default cookie jar will be create to store any cookies
    *                                                       returned from the call. Defaults to true.
    *                                  ['retryOn401Error']  If true, a second attempt will be made with the cookie jar returned
    *                                                       from the first attempt. This is necessary for some hosts when 401
    *                                                       is returned the first time (sometimes with FM's Data API).
    *                                                       Defaults to false.
    *                                  ['getMimeType']      If true, the file will be written to the tmp directory so that
    *                                                       mime_content_type() can be used to properly determine the mime type.
    *                                                       Defaults to true.
    *
    *    Returns:
    *       An JSON-decoded associative array of the API result. Typically:
    *          ['response'] If successful:
    *                          ['url']                      The same URL passed in
    *                          ['mimeType']                 The computed mime type (when $options['getMimeType'] is true) otherwise
    *                                                       whatever curl determines
    *                          ['fileName']                 The file name passed in $options['fileName'] or retrieved from the url
    *                          ['extension']                The file's extension (may be empty)
    *                          ['size']                     The size of the file
    *          ['messages'] Array of messages. This mimics what the FM Data API returns.
    *
    *       ALSO: the contents of the file will be stored in $this->file to avoid copying/memory consumption
    *
    *    Examples:
    *       $curl = new fmCURL();
    *
    *       $result = $curl->getFile($url);
    *          or
    *       $result = $curl->getFile($url, array('action' => 'inline'));
    *          or
    *       $result = $curl->getFile($url, array('action' => 'download'));
    *
    *       if (! $curl->getIsError($result)) {
    *          file contents are in $curl->file *not* $result
    *          ...
    *       }
    */
   public function getFile($url, $options = array())
   {
      $defaultOptions = array('action' => 'get',
                              'compress' => 'gzip',
                              'compressionLevel' => '9',
                              'createCookieJar' => true,
                              'retryOn401Error' => false,
                              'getMimeType' => true);

      if ($options['action'] == 'get') {
         $defaultOptions['compression'] = '';
      }

      $options = array_merge($defaultOptions, $options);

      $this->file = '';

      $result = array();
      $result['response'] = array();

      $saveCreateCookieJar = $this->options['createCookieJar'];
      if ($options['createCookieJar']) {
         $this->options['createCookieJar'] = true;
      }

      $this->file = $this->curl($url);

      if (($this->httpCode == HTTP_UNAUTHORIZED) && $options['retryOn401Error']) {          // Typically 401 is returned since the cookie wasn't set

         fmLogger(__METHOD__ .'(): got HTTP 401 (HTTP_UNAUTHORIZED).');
         if ($this->options['cookieFilePath'] != '') {
            fmLogger('Contents of cookie jar ('. $this->options['cookieFilePath'] .'):');
            fmLogger(file_get_contents($this->options['cookieFilePath']));
         }

         if ($options['createCookieJar']) {
            $this->options['createCookieJar'] = false;
         }

         $this->file = $this->curl($url);                                                   // Try again with the cookie
      }

      $this->options['createCookieJar'] = $saveCreateCookieJar;


      if ($this->httpCode == HTTP_SUCCESSS) {
         $urlInfo = parse_url($url);
         $pathInfo = pathinfo($urlInfo['path']);

         // Certain APIs (Ahem - FM Data API) do not return the correct content_type for many files (MS Office, for example), and we can't
         // rely on the file extension as sometimes there isn't an one to work with (again, FM Data API and MS Office files). Sooooo, we'll
         // write the file to the tmp folder and then use mime_content_type() to get at the data.
         $mimeType = '';
         if ($options['getMimeType']) {
            $f = tempnam(sys_get_temp_dir(), 'mimeType'. mt_rand());
            if (file_put_contents($f, $this->file) !== false) {
               $mimeType = mime_content_type($f);
               unlink($f);
            }
         }

         if ($mimeType == '') {                                // If we didn't (or couldn't) get a mime type, fall back to what curl says
            $mimeType = $this->curlInfo['content_type'];
         }


         if (array_key_exists('fileName', $options)) {
            $fileName = $options['fileName'];
         }
         else {
            $fileName = array_key_exists('basename', $pathInfo) ? $pathInfo['basename'] : '';
         }
         $extension = array_key_exists('extension', $pathInfo) ? $pathInfo['extension'] : '';
         $size = array_key_exists('download_content_length', $this->curlInfo) ? $this->curlInfo['download_content_length'] : 0;

         $compress = true;
         if (($options['compress'] == 'gzip') && ($size > 0)) {

            if ((($options['action'] == 'download') || ($options['action'] == 'inline')) &&
                 (strpos(strtolower($_SERVER['HTTP_ACCEPT_ENCODING']), 'gzip') === false)) {
               $compress = false;
            }

            if ($compress) {
               $this->file = gzencode($this->file, $options['compressionLevel']);
               $size = strlen($this->file);
            }
         }

         switch ($options['action']) {
            case 'download':
            case 'inline': {

               header('Expires: 0');
               header('Pragma: no-cache');
               header('Cache-control: no-store, no-cache, private');

               if ($options['action'] == 'inline') {
                  header('Content-Disposition: inline');
               }
               else {
                  header('Content-Disposition: attachment; filename='. '"'. $fileName .'"');
               }

               if ($compress) {
                  header('Content-Encoding: gzip');
               }

               header('Content-type: '. $mimeType);
               header('Content-length: '. $size);

               echo $this->file;

               break;
            }

            case 'get':
            default: {
               break;
            }
         }

         $result['messages'] = array(
                                 array('code' => 0,
                                      'message' => 'OK')
                                 );

         $result['response'] = array('url' => $url,
                                     'mimeType' => $mimeType,
                                     'fileName' => $fileName,
                                     'extension' => $extension,
                                     'size' => $size
                                 );
      }
      else {
         $result['messages'] = array(array('code' => $this->curlErrNum, 'message' => $this->curlErrMsg));
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
