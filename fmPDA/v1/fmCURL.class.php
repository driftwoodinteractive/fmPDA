<?php
// *********************************************************************************************************************************
//
// fmCURL.class.php
//
// fmCURL is used to communicate with a host via curl(). This implementation is strictly for 'raw' curl data transmission.
// It has been tuned for specific use with the Data and Admin API's but can be used for any type of curl() related traffic.
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
define('HTTP_REDIRECT_PERMANENT',      301);                      // HTTP Header when there's a permanent redirect
define('HTTP_REDIRECT_TEMPORARY',      302);                      // HTTP Header when there's a temporary redirect
define('HTTP_NO_DATA',                 204);                      // No content ('success', but no data)
define('HTTP_BAD_REQUREST',            400);                      // Bad Request
define('HTTP_UNAUTHORIZED',            401);                      // Unauthorized (bad credentials)
define('HTTP_NOT_FOUND',               404);                      // Not found
define('HTTP_REQUEST_TIMEOUT',         408);                      // Request time out
define('HTTP_SERVICE_UNAVAILABLE',     503);                      // Service Unavailable

define('CURL_CONNECTION_TIMEOUT',      2);                        // Connection time out

define('MAX_RESPONSE_DUMP_SIZE',       2000);                     // Max chars will display in log from curl result

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
   public $httpHeaders;                                           // Returned HTTP headers
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
    *                                      For Windows FMS 19.4+ you may need to enforce HTTP 1.1. You can uncomment the
    *                                      the CURL_HTTP_VERSION define() in fmPDA.conf.php OR pass this in $options:
    *                                      $options['CURLOPT_HTTP_VERSION'] = CURL_HTTP_VERSION_1_1
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
      $this->options['encodeAsString']          = false;
      $this->options['encodeAsJSON']            = false;
      $this->options['decodeAsJSON']            = false;
      $this->options['logCallInfo']             = true;
      $this->options['logSentHeaders']          = false;
      $this->options['logReceivedHeaders']      = false;
      $this->options['logSentData']             = false;
      $this->options['logPostData']             = true;
      $this->options['logCURLResult']           = true;
      $this->options['logCURLInfo']             = false;
      $this->options['userAgent']               = CURL_USER_AGENT;

      if (!array_key_exists('CURL_HTTP_VERSION', $options) and defined('k_CURL_HTTP_VERSION')) {  // Option not passed but set in fmPDA.conf.php?
         $options['CURL_HTTP_VERSION'] = k_CURL_HTTP_VERSION;
      }

      $this->options = array_merge($this->options, $options);

      $this->curlOptions = $curlOptions;

      $this->file          = '';
      $this->curlInfo      = array();
      $this->curlErrNum    = 0;
      $this->curlErrMsg    = '';
      $this->httpCode      = '';
      $this->httpHeaders   = array();
      $this->callTime      = 0;

      $curlVersion = curl_version();
      fmLogger('fmCURL: curl v'. $curlVersion['version'] .'; OpenSSL v'. $curlVersion['ssl_version'] .'; Libz v'. $curlVersion['libz_version'] .'; Host '. $curlVersion['host'] .' '. $this->options['userAgent']);
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

      $this->curlErrNum    = 0;
      $this->curlErrMsg    = '';
      $this->httpCode      = '';
      $this->httpHeaders   = array();
      $this->callTime      = 0;

      $options = array_merge($this->options, $options);

      $method = strtoupper($method);

      $postData = '';
      if ($data != '') {
         if (! $this->getIsMethodPost($method)) {
            $url .= '?'. ((substr($data, 0, 1) == '&') ? substr($data, 1) : $data);
         }
         else {
            if (array_key_exists('encodeAsJSON', $options) && $options['encodeAsJSON']) {
               $postData = json_encode($data);
            }
            else if (array_key_exists('encodeAsString', $options) && $options['encodeAsString']) {
               $postData = http_build_query($data);
            }
            else {
               $postData = $data;
            }
         }
      }

      $ch = curl_init();                                                      // Initialize curl and specify all options
      curl_setopt($ch, CURLOPT_URL, $url);

      // The Data API has some issues if there isn't a Content-Length: 0 on a POST with no content (ie: Login) so we explictly set the length to 0.
      if ($this->getIsMethodPost($method)) {                                  // Patch, Put, or Post?
         if (! is_array($options['CURLOPT_HTTPHEADER']) || ! array_key_exists('CURLOPT_HTTPHEADER', $options)) {
            $options['CURLOPT_HTTPHEADER'] = array();
         }
         if (is_string($postData)) {
            $options['CURLOPT_HTTPHEADER'][] = 'Content-Length: '. strlen($postData);
         }
      }

      if (is_array($options['CURLOPT_HTTPHEADER']) && array_key_exists('CURLOPT_HTTPHEADER', $options) && (count($options['CURLOPT_HTTPHEADER']) > 0)) {
         curl_setopt($ch, CURLOPT_HTTPHEADER, $options['CURLOPT_HTTPHEADER']);
      }
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this, 'captureHTTPHeader'));
      curl_setopt($ch, CURLOPT_MAXREDIRS, 20);
      curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

      if (array_key_exists('CURLOPT_HEADER', $options) && $options['CURLOPT_HEADER']) {
         curl_setopt($ch, CURLOPT_HEADER, $options['CURLOPT_HEADER']);
      }
      if (array_key_exists('CURLOPT_NOBODY', $options) && $options['CURLOPT_NOBODY']) {
         curl_setopt($ch, CURLOPT_NOBODY, $options['CURLOPT_NOBODY']);
      }

      if (array_key_exists('CURLOPT_CONNECTTIMEOUT', $options) && (strlen($options['CURLOPT_CONNECTTIMEOUT']) > 0)) {
         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options['CURLOPT_CONNECTTIMEOUT']);
      }

      if (array_key_exists('CURLOPT_HTTP_VERSION', $options)) {
         curl_setopt($ch, CURLOPT_HTTP_VERSION, $options['CURLOPT_HTTP_VERSION']);
      }

      if (array_key_exists('userAgent', $options) && (strlen($options['userAgent']) > 0)) {
         curl_setopt($ch, CURLOPT_USERAGENT, $options['userAgent']);
      }

      if (array_key_exists('CURLOPT_CAINFO', $options) && (strlen($options['CURLOPT_CAINFO']) > 0)) {
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
         curl_setopt($ch, CURLOPT_CAINFO, $options['CURLOPT_CAINFO']);
      }
      else {
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      }

      if (array_key_exists('CURLOPT_POST', $options) && $options['CURLOPT_POST']) {
         curl_setopt($ch, CURLOPT_POST, 1);
      }

      if ($this->getIsMethodPost($method)) {                                   // Patch, Put, or Post?
         curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);                      // Always set even if no data so Content-Length is set!
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
         else if ($this->curlInfo['http_code'] == HTTP_NOT_FOUND) {
            $this->curlErrNum = $this->curlInfo['http_code'];
            $this->curlErrMsg = 'Not Found';
         }
         else if ($this->curlInfo['http_code'] == HTTP_REQUEST_TIMEOUT) {
            $this->curlErrNum = $this->curlInfo['http_code'];
            $this->curlErrMsg = 'Request Timeout';
         }
         else if (($this->curlInfo['http_code'] >= 400) && ($this->curlInfo['http_code'] < 600)) {
            $this->curlErrNum = $this->curlInfo['http_code'];
            $this->curlErrMsg = 'HTTP code = '. $this->curlInfo['http_code'];
         }
      }

      // See if we had any redirects. Could be useful to let the caller know especially if it's permanent.
      $didRedirect = false;
      $isPermanenttRedirect = false;
      if (count($this->httpHeaders) > 0) {
         foreach ($this->httpHeaders as $httpHeader) {

            if (is_array($httpHeader)) {
               foreach ($httpHeader as $httpElement) {
                  if (strpos(strtolower($httpElement), strval(HTTP_REDIRECT_PERMANENT)) !== false) {
                     $didRedirect = true;
                     $isPermanenttRedirect = true;
                     break;
                 }
                 else if (strpos(strtolower($httpElement), strval(HTTP_REDIRECT_TEMPORARY)) !== false) {
                     $didRedirect = true;
                     break;
                  }
               }
            }
            else if (strpos(strtolower($httpHeader), strval(HTTP_REDIRECT_PERMANENT)) !== false) {
               $didRedirect = true;
               $isPermanenttRedirect = true;
               break;
            }
            else if (strpos(strtolower($httpHeader), strval(HTTP_REDIRECT_TEMPORARY)) !== false) {
               $didRedirect = true;
               break;
            }

            if ($didRedirect) {
               break;
            }
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

      if ($didRedirect) {
         fmLogger('Request was '. ($isPermanenttRedirect ? 'permanently' : 'temporarily') .' redirected to: '. $this->curlInfo['url']);
      }

      if ($options['logSentHeaders'] && array_key_exists('request_header', $this->curlInfo)) {
         fmLogger('HTTP header:'. '<br>'. print_r($this->curlInfo['request_header'], true)); // CURLOPT_HTTPHEADER only gives what we add
      }

      if ($options['logSentData']) {
         fmLogger('data='. print_r($data, true));
      }

      if ($options['logPostData'] && ($postData != '')) {
         fmLogger('<b>&#9658;</b>'. htmlspecialchars($postData));
      }

      if ($options['logReceivedHeaders'] && (count($this->httpHeaders) > 0)) {
         fmLogger('Received Headers='. print_r($this->httpHeaders, true));
      }

      if ($options['logCURLResult'] && ($curlResult != '')) {
         if (strlen($curlResult) > MAX_RESPONSE_DUMP_SIZE) {                              // Don't dump out gobs of response data
            fmLogger('<b>&#9664;</b>'. htmlspecialchars(substr($curlResult, 0, MAX_RESPONSE_DUMP_SIZE)) .' ...');
            fmLogger('<i>Response truncated at '. number_format(MAX_RESPONSE_DUMP_SIZE, 0, '.', ',') .' characters.</i>');
         }
         else {
            fmLogger('<b>&#9664;</b>'. htmlspecialchars($curlResult));
         }
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
    *                                  ['createCookieJar']  If true, a default cookie jar will be created to store any cookies
    *                                                       returned from the call. Defaults to true.
    *                                  ['retryOn401Error']  If true, a second attempt will be made with the cookie jar returned
    *                                                       from the first attempt. This is necessary for some hosts when 401
    *                                                       is returned the first time (sometimes with FM's Data API).
    *                                                       Defaults to false.
    *                                  ['getMimeType']      If true, the file will be written to the tmp directory so that
    *                                                       fmCURL::get_mime_content_type() can be used to properly determine the mime type.
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

      // If options wasn't defined or the action is a 'get', set our default compress to off.
      // If options defines a compression with get we will pick this up.
      if (! array_key_exists('action', $options) || ($options['action'] == 'get')) {
         $defaultOptions['compress'] = '';
         $defaultOptions['compressionLevel'] = '';
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
         // write the file to the tmp folder and then use get_mime_content_type() to get at the data.
         $mimeType = '';
         if ($options['getMimeType']) {
            $f = tempnam(sys_get_temp_dir(), 'mimeType'. mt_rand());
            if (file_put_contents($f, $this->file) !== false) {
               $mimeType = $this->get_mime_content_type($f);
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
   // curl() calls this function after the call completes so that we can process the HTTP headers.
   // As there can be multiple headers of the same key, we always create an array for each key so that the caller can interrogate all
   // the possible key/value pairs to find the one that makes the most sense to use. If the keys are different in case this will result
   // in separate array indices. We could use strtolower() but then the caller would have to realize that the keys are all lowercase
   // as opposed to what they're used to processing.
   //
   function captureHTTPHeader($curl, $headerLine)
   {
      $header = str_replace(array("\r", "\n"), array('', ''), $headerLine);

      if ($header != '') {
         $parts = explode(':', $header);

         if (isset($parts[1])) {
            $this->httpHeaders[ ltrim($parts[0]) ][] = ltrim($parts[1]);
         } else {
            $this->httpHeaders[count($this->httpHeaders)] = [$headerLine];    // No colon, so just include entire line
         }
      }

      return strlen($headerLine);
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
   // Returns true if the method is PATCH, PUT, or POST
   //
   function getIsMethodPost($method)
   {
      if (($method == METHOD_PATCH) ||  ($method == METHOD_PUT) ||  ($method == METHOD_POST)) {
         return true;
      }
      else {
         return false;
      }
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

   /***********************************************************************************************************************************
   // get_mime_content_type
   //
   // Can be used with any version of PHP (some versions of PHP had mime_content_type() deprecated).
   //
   // there's a bug that doesn't properly detect
   // the mime type of css files
   // https://bugs.php.net/bug.php?id=53035
   // so the following is used, instead
   // src: http://www.freeformatter.com/mime-types-list.html#mime-types-list
   //
   /**
    *                  **DISCLAIMER**
    * This will just match the file extension to the following
    * array. It does not guarantee that the file is TRULY that
    * of the extension that this function returns.
    */
   //
   public function get_mime_content_type($filename)
   {
      if (function_exists('mime_content_type')) {
         return mime_content_type($filename);
      }
      else {

         $mime_type = array(
            "3dml"		   =>	"text/vnd.in3d.3dml",
            "3g2"			   =>	"video/3gpp2",
            "3gp"			   =>	"video/3gpp",
            "7z"			   =>	"application/x-7z-compressed",
            "aab"			   =>	"application/x-authorware-bin",
            "aac"			   =>	"audio/x-aac",
            "aam"			   =>	"application/x-authorware-map",
            "aas"			   =>	"application/x-authorware-seg",
            "abw"			   =>	"application/x-abiword",
            "ac"			   =>	"application/pkix-attr-cert",
            "acc"			   =>	"application/vnd.americandynamics.acc",
            "ace"			   =>	"application/x-ace-compressed",
            "acu"			   =>	"application/vnd.acucobol",
            "adp"			   =>	"audio/adpcm",
            "aep"			   =>	"application/vnd.audiograph",
            "afp"			   =>	"application/vnd.ibm.modcap",
            "ahead"		   =>	"application/vnd.ahead.space",
            "ai"			   =>	"application/postscript",
            "aif"			   =>	"audio/x-aiff",
            "air"			   =>	"application/vnd.adobe.air-application-installer-package+zip",
            "ait"			   =>	"application/vnd.dvb.ait",
            "ami"			   =>	"application/vnd.amiga.ami",
            "apk"			   =>	"application/vnd.android.package-archive",
            "application"	=>	"application/x-ms-application",
            "apr"			   =>	"application/vnd.lotus-approach",
            "asf"			   =>	"video/x-ms-asf",
            "aso"			   =>	"application/vnd.accpac.simply.aso",
            "atc"			   =>	"application/vnd.acucorp",
            "atom"			=>	"application/atom+xml",
            "atomcat"		=>	"application/atomcat+xml",
            "atomsvc"		=>	"application/atomsvc+xml",
            "atx"			   =>	"application/vnd.antix.game-component",
            "au"			   =>	"audio/basic",
            "avi"			   =>	"video/x-msvideo",
            "aw"			   =>	"application/applixware",
            "azf"			   =>	"application/vnd.airzip.filesecure.azf",
            "azs"			   =>	"application/vnd.airzip.filesecure.azs",
            "azw"			   =>	"application/vnd.amazon.ebook",
            "bcpio"			=>	"application/x-bcpio",
            "bdf"			   =>	"application/x-font-bdf",
            "bdm"			   =>	"application/vnd.syncml.dm+wbxml",
            "bed"			   =>	"application/vnd.realvnc.bed",
            "bh2"			   =>	"application/vnd.fujitsu.oasysprs",
            "bin"			   =>	"application/octet-stream",
            "bmi"			   =>	"application/vnd.bmi",
            "bmp"			   =>	"image/bmp",
            "box"			   =>	"application/vnd.previewsystems.box",
            "btif"			=>	"image/prs.btif",
            "bz"			   =>	"application/x-bzip",
            "bz2"			   =>	"application/x-bzip2",
            "c"			   =>	"text/x-c",
            "c11amc"		   =>	"application/vnd.cluetrust.cartomobile-config",
            "c11amz"		   =>	"application/vnd.cluetrust.cartomobile-config-pkg",
            "c4g"			   =>	"application/vnd.clonk.c4group",
            "cab"			   =>	"application/vnd.ms-cab-compressed",
            "car"			   =>	"application/vnd.curl.car",
            "cat"			   =>	"application/vnd.ms-pki.seccat",
            "ccxml"			=>	"application/ccxml+xml,",
            "cdbcmsg"		=>	"application/vnd.contact.cmsg",
            "cdkey"			=>	"application/vnd.mediastation.cdkey",
            "cdmia"			=>	"application/cdmi-capability",
            "cdmic"			=>	"application/cdmi-container",
            "cdmid"			=>	"application/cdmi-domain",
            "cdmio"			=>	"application/cdmi-object",
            "cdmiq"			=>	"application/cdmi-queue",
            "cdx"			   =>	"chemical/x-cdx",
            "cdxml"			=>	"application/vnd.chemdraw+xml",
            "cdy"			   =>	"application/vnd.cinderella",
            "cer"			   =>	"application/pkix-cert",
            "cgm"			   =>	"image/cgm",
            "chat"			=>	"application/x-chat",
            "chm"			   =>	"application/vnd.ms-htmlhelp",
            "chrt"			=>	"application/vnd.kde.kchart",
            "cif"			   =>	"chemical/x-cif",
            "cii"			   =>	"application/vnd.anser-web-certificate-issue-initiation",
            "cil"			   =>	"application/vnd.ms-artgalry",
            "cla"			   =>	"application/vnd.claymore",
            "class"			=>	"application/java-vm",
            "clkk"			=>	"application/vnd.crick.clicker.keyboard",
            "clkp"			=>	"application/vnd.crick.clicker.palette",
            "clkt"			=>	"application/vnd.crick.clicker.template",
            "clkw"			=>	"application/vnd.crick.clicker.wordbank",
            "clkx"			=>	"application/vnd.crick.clicker",
            "clp"			   =>	"application/x-msclip",
            "cmc"			   =>	"application/vnd.cosmocaller",
            "cmdf"			=>	"chemical/x-cmdf",
            "cml"			   =>	"chemical/x-cml",
            "cmp"			   =>	"application/vnd.yellowriver-custom-menu",
            "cmx"			   =>	"image/x-cmx",
            "cod"			   =>	"application/vnd.rim.cod",
            "cpio"			=>	"application/x-cpio",
            "cpt"			   =>	"application/mac-compactpro",
            "crd"			   =>	"application/x-mscardfile",
            "crl"			   =>	"application/pkix-crl",
            "cryptonote"   =>	"application/vnd.rig.cryptonote",
            "csh"			   =>	"application/x-csh",
            "csml"			=>	"chemical/x-csml",
            "csp"			   =>	"application/vnd.commonspace",
            "css"			   =>	"text/css",
            "csv"			   =>	"text/csv",
            "cu"			   =>	"application/cu-seeme",
            "curl"			=>	"text/vnd.curl",
            "cww"			   =>	"application/prs.cww",
            "dae"			   =>	"model/vnd.collada+xml",
            "daf"			   =>	"application/vnd.mobius.daf",
            "davmount"		=>	"application/davmount+xml",
            "dcurl"			=>	"text/vnd.curl.dcurl",
            "dd2"			   =>	"application/vnd.oma.dd2+xml",
            "ddd"			   =>	"application/vnd.fujixerox.ddd",
            "deb"			   =>	"application/x-debian-package",
            "der"			   =>	"application/x-x509-ca-cert",
            "dfac"			=>	"application/vnd.dreamfactory",
            "dir"			   =>	"application/x-director",
            "dis"			   =>	"application/vnd.mobius.dis",
            "djvu"			=>	"image/vnd.djvu",
            "dna"			   =>	"application/vnd.dna",
            "doc"			   =>	"application/msword",
            "docm"			=>	"application/vnd.ms-word.document.macroenabled.12",
            "docx"			=>	"application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "dotm"			=>	"application/vnd.ms-word.template.macroenabled.12",
            "dotx"			=>	"application/vnd.openxmlformats-officedocument.wordprocessingml.template",
            "dp"			   =>	"application/vnd.osgi.dp",
            "dpg"			   =>	"application/vnd.dpgraph",
            "dra"			   =>	"audio/vnd.dra",
            "dsc"			   =>	"text/prs.lines.tag",
            "dssc"			=>	"application/dssc+der",
            "dtb"			   =>	"application/x-dtbook+xml",
            "dtd"			   =>	"application/xml-dtd",
            "dts"			   =>	"audio/vnd.dts",
            "dtshd"			=>	"audio/vnd.dts.hd",
            "dvi"			   =>	"application/x-dvi",
            "dwf"			   =>	"model/vnd.dwf",
            "dwg"			   =>	"image/vnd.dwg",
            "dxf"			   =>	"image/vnd.dxf",
            "dxp"			   =>	"application/vnd.spotfire.dxp",
            "ecelp4800"		=>	"audio/vnd.nuera.ecelp4800",
            "ecelp7470"		=>	"audio/vnd.nuera.ecelp7470",
            "ecelp9600"		=>	"audio/vnd.nuera.ecelp9600",
            "edm"			   =>	"application/vnd.novadigm.edm",
            "edx"			   =>	"application/vnd.novadigm.edx",
            "efif"			=>	"application/vnd.picsel",
            "ei6"			   =>	"application/vnd.pg.osasli",
            "eml"			   =>	"message/rfc822",
            "emma"			=>	"application/emma+xml",
            "eol"			   =>	"audio/vnd.digital-winds",
            "eot"			   =>	"application/vnd.ms-fontobject",
            "epub"			=>	"application/epub+zip",
            "es"			   =>	"application/ecmascript",
            "es3"			   =>	"application/vnd.eszigno3+xml",
            "esf"			   =>	"application/vnd.epson.esf",
            "etx"			   =>	"text/x-setext",
            "exe"			   =>	"application/x-msdownload",
            "exi"			   =>	"application/exi",
            "ext"			   =>	"application/vnd.novadigm.ext",
            "ez2"			   =>	"application/vnd.ezpix-album",
            "ez3"			   =>	"application/vnd.ezpix-package",
            "f"			   =>	"text/x-fortran",
            "f4v"			   =>	"video/x-f4v",
            "fbs"			   =>	"image/vnd.fastbidsheet",
            "fcs"			   =>	"application/vnd.isac.fcs",
            "fdf"			   =>	"application/vnd.fdf",
            "fe_launch"		=>	"application/vnd.denovo.fcselayout-link",
            "fg5"			   =>	"application/vnd.fujitsu.oasysgp",
            "fh"			   =>	"image/x-freehand",
            "fig"			   =>	"application/x-xfig",
            "fli"			   =>	"video/x-fli",
            "flo"			   =>	"application/vnd.micrografx.flo",
            "flv"			   =>	"video/x-flv",
            "flw"			   =>	"application/vnd.kde.kivio",
            "flx"			   =>	"text/vnd.fmi.flexstor",
            "fly"			   =>	"text/vnd.fly",
            "fm"			   =>	"application/vnd.framemaker",
            "fnc"			   =>	"application/vnd.frogans.fnc",
            "fpx"			   =>	"image/vnd.fpx",
            "fsc"			   =>	"application/vnd.fsc.weblaunch",
            "fst"			   =>	"image/vnd.fst",
            "ftc"			   =>	"application/vnd.fluxtime.clip",
            "fti"			   =>	"application/vnd.anser-web-funds-transfer-initiation",
            "fvt"			   =>	"video/vnd.fvt",
            "fxp"			   =>	"application/vnd.adobe.fxp",
            "fzs"			   =>	"application/vnd.fuzzysheet",
            "g2w"			   =>	"application/vnd.geoplan",
            "g3"			   =>	"image/g3fax",
            "g3w"			   =>	"application/vnd.geospace",
            "gac"			   =>	"application/vnd.groove-account",
            "gdl"			   =>	"model/vnd.gdl",
            "geo"			   =>	"application/vnd.dynageo",
            "gex"			   =>	"application/vnd.geometry-explorer",
            "ggb"			   =>	"application/vnd.geogebra.file",
            "ggt"			   =>	"application/vnd.geogebra.tool",
            "ghf"			   =>	"application/vnd.groove-help",
            "gif"			   =>	"image/gif",
            "gim"			   =>	"application/vnd.groove-identity-message",
            "gmx"			   =>	"application/vnd.gmx",
            "gnumeric"		=>	"application/x-gnumeric",
            "gph"			   =>	"application/vnd.flographit",
            "gqf"			   =>	"application/vnd.grafeq",
            "gram"			=>	"application/srgs",
            "grv"			   =>	"application/vnd.groove-injector",
            "grxml"			=>	"application/srgs+xml",
            "gsf"			   =>	"application/x-font-ghostscript",
            "gtar"			=>	"application/x-gtar",
            "gtm"			   =>	"application/vnd.groove-tool-message",
            "gtw"			   =>	"model/vnd.gtw",
            "gv"			   =>	"text/vnd.graphviz",
            "gxt"			   =>	"application/vnd.geonext",
            "h261"			=>	"video/h261",
            "h263"			=>	"video/h263",
            "h264"			=>	"video/h264",
            "hal"			   =>	"application/vnd.hal+xml",
            "hbci"			=>	"application/vnd.hbci",
            "hdf"			   =>	"application/x-hdf",
            "hlp"			   =>	"application/winhlp",
            "hpgl"			=>	"application/vnd.hp-hpgl",
            "hpid"			=>	"application/vnd.hp-hpid",
            "hps"			   =>	"application/vnd.hp-hps",
            "hqx"			   =>	"application/mac-binhex40",
            "htke"			=>	"application/vnd.kenameaapp",
            "html"			=>	"text/html",
            "hvd"			   =>	"application/vnd.yamaha.hv-dic",
            "hvp"			   =>	"application/vnd.yamaha.hv-voice",
            "hvs"			   =>	"application/vnd.yamaha.hv-script",
            "i2g"			   =>	"application/vnd.intergeo",
            "icc"			   =>	"application/vnd.iccprofile",
            "ice"			   =>	"x-conference/x-cooltalk",
            "ico"			   =>	"image/x-icon",
            "ics"			   =>	"text/calendar",
            "ief"			   =>	"image/ief",
            "ifm"			   =>	"application/vnd.shana.informed.formdata",
            "igl"			   =>	"application/vnd.igloader",
            "igm"			   =>	"application/vnd.insors.igm",
            "igs"			   =>	"model/iges",
            "igx"			   =>	"application/vnd.micrografx.igx",
            "iif"			   =>	"application/vnd.shana.informed.interchange",
            "imp"			   =>	"application/vnd.accpac.simply.imp",
            "ims"			   =>	"application/vnd.ms-ims",
            "ipfix"			=>	"application/ipfix",
            "ipk"			   =>	"application/vnd.shana.informed.package",
            "irm"			   =>	"application/vnd.ibm.rights-management",
            "irp"			   =>	"application/vnd.irepository.package+xml",
            "itp"			   =>	"application/vnd.shana.informed.formtemplate",
            "ivp"			   =>	"application/vnd.immervision-ivp",
            "ivu"			   =>	"application/vnd.immervision-ivu",
            "jad"			   =>	"text/vnd.sun.j2me.app-descriptor",
            "jam"			   =>	"application/vnd.jam",
            "jar"			   =>	"application/java-archive",
            "java"			=>	"text/x-java-source,java",
            "jisp"			=>	"application/vnd.jisp",
            "jlt"			   =>	"application/vnd.hp-jlyt",
            "jnlp"			=>	"application/x-java-jnlp-file",
            "joda"			=>	"application/vnd.joost.joda-archive",
            "jpeg"			=>	"image/jpeg",
            "jpg"			   =>	"image/jpeg",
            "jpgv"			=>	"video/jpeg",
            "jpm"			   =>	"video/jpm",
            "js"			   =>	"application/javascript",
            "json"			=>	"application/json",
            "karbon"		   =>	"application/vnd.kde.karbon",
            "kfo"			   =>	"application/vnd.kde.kformula",
            "kia"			   =>	"application/vnd.kidspiration",
            "kml"			   =>	"application/vnd.google-earth.kml+xml",
            "kmz"			   =>	"application/vnd.google-earth.kmz",
            "kne"			   =>	"application/vnd.kinar",
            "kon"			   =>	"application/vnd.kde.kontour",
            "kpr"			   =>	"application/vnd.kde.kpresenter",
            "ksp"			   =>	"application/vnd.kde.kspread",
            "ktx"			   =>	"image/ktx",
            "ktz"			   =>	"application/vnd.kahootz",
            "kwd"			   =>	"application/vnd.kde.kword",
            "lasxml"		   =>	"application/vnd.las.las+xml",
            "latex"			=>	"application/x-latex",
            "lbd"			   =>	"application/vnd.llamagraphics.life-balance.desktop",
            "lbe"			   =>	"application/vnd.llamagraphics.life-balance.exchange+xml",
            "les"			   =>	"application/vnd.hhe.lesson-player",
            "link66"		   =>	"application/vnd.route66.link66+xml",
            "lrm"			   =>	"application/vnd.ms-lrm",
            "ltf"			   =>	"application/vnd.frogans.ltf",
            "lvp"			   =>	"audio/vnd.lucent.voice",
            "lwp"			   =>	"application/vnd.lotus-wordpro",
            "m21"			   =>	"application/mp21",
            "m3u"			   =>	"audio/x-mpegurl",
            "m3u8"			=>	"application/vnd.apple.mpegurl",
            "m4v"			   =>	"video/x-m4v",
            "ma"			   =>	"application/mathematica",
            "mads"			=>	"application/mads+xml",
            "mag"			   =>	"application/vnd.ecowin.chart",
            "map"			   =>	"application/json",
            "mathml"		   =>	"application/mathml+xml",
            "mbk"			   =>	"application/vnd.mobius.mbk",
            "mbox"			=>	"application/mbox",
            "mc1"			   =>	"application/vnd.medcalcdata",
            "mcd"			   =>	"application/vnd.mcd",
            "mcurl"			=>	"text/vnd.curl.mcurl",
            "md"			   =>	"text/x-markdown", // http://bit.ly/1Kc5nUB
            "mdb"			   =>	"application/x-msaccess",
            "mdi"			   =>	"image/vnd.ms-modi",
            "meta4"			=>	"application/metalink4+xml",
            "mets"			=>	"application/mets+xml",
            "mfm"			   =>	"application/vnd.mfmp",
            "mgp"			   =>	"application/vnd.osgeo.mapguide.package",
            "mgz"			   =>	"application/vnd.proteus.magazine",
            "mid"			   =>	"audio/midi",
            "mif"			   =>	"application/vnd.mif",
            "mj2"			   =>	"video/mj2",
            "mlp"			   =>	"application/vnd.dolby.mlp",
            "mmd"			   =>	"application/vnd.chipnuts.karaoke-mmd",
            "mmf"			   =>	"application/vnd.smaf",
            "mmr"			   =>	"image/vnd.fujixerox.edmics-mmr",
            "mny"			   =>	"application/x-msmoney",
            "mods"			=>	"application/mods+xml",
            "movie"			=>	"video/x-sgi-movie",
            "mp1"			   =>	"audio/mpeg",
            "mp2"			   =>	"audio/mpeg",
            "mp3"			   =>	"audio/mpeg",
            "mp4"			   =>	"video/mp4",
            "mp4a"			=>	"audio/mp4",
            "mpc"			   =>	"application/vnd.mophun.certificate",
            "mpeg"			=>	"video/mpeg",
            "mpga"			=>	"audio/mpeg",
            "mpkg"			=>	"application/vnd.apple.installer+xml",
            "mpm"			   =>	"application/vnd.blueice.multipass",
            "mpn"			   =>	"application/vnd.mophun.application",
            "mpp"			   =>	"application/vnd.ms-project",
            "mpy"			   =>	"application/vnd.ibm.minipay",
            "mqy"			   =>	"application/vnd.mobius.mqy",
            "mrc"			   =>	"application/marc",
            "mrcx"			=>	"application/marcxml+xml",
            "mscml"			=>	"application/mediaservercontrol+xml",
            "mseq"			=>	"application/vnd.mseq",
            "msf"			   =>	"application/vnd.epson.msf",
            "msh"			   =>	"model/mesh",
            "msl"			   =>	"application/vnd.mobius.msl",
            "msty"			=>	"application/vnd.muvee.style",
            "mts"			   =>	"model/vnd.mts",
            "mus"			   =>	"application/vnd.musician",
            "musicxml"		=>	"application/vnd.recordare.musicxml+xml",
            "mvb"			   =>	"application/x-msmediaview",
            "mwf"			   =>	"application/vnd.mfer",
            "mxf"			   =>	"application/mxf",
            "mxl"			   =>	"application/vnd.recordare.musicxml",
            "mxml"			=>	"application/xv+xml",
            "mxs"			   =>	"application/vnd.triscape.mxs",
            "mxu"			   =>	"video/vnd.mpegurl",
            "n3"			   =>	"text/n3",
            "nbp"			   =>	"application/vnd.wolfram.player",
            "nc"			   =>	"application/x-netcdf",
            "ncx"			   =>	"application/x-dtbncx+xml",
            "n-gage"		   =>	"application/vnd.nokia.n-gage.symbian.install",
            "ngdat"			=>	"application/vnd.nokia.n-gage.data",
            "nlu"			   =>	"application/vnd.neurolanguage.nlu",
            "nml"			   =>	"application/vnd.enliven",
            "nnd"			   =>	"application/vnd.noblenet-directory",
            "nns"			   =>	"application/vnd.noblenet-sealer",
            "nnw"			   =>	"application/vnd.noblenet-web",
            "npx"			   =>	"image/vnd.net-fpx",
            "nsf"			   =>	"application/vnd.lotus-notes",
            "oa2"			   =>	"application/vnd.fujitsu.oasys2",
            "oa3"			   =>	"application/vnd.fujitsu.oasys3",
            "oas"			   =>	"application/vnd.fujitsu.oasys",
            "obd"			   =>	"application/x-msbinder",
            "oda"			   =>	"application/oda",
            "odb"			   =>	"application/vnd.oasis.opendocument.database",
            "odc"			   =>	"application/vnd.oasis.opendocument.chart",
            "odf"			   =>	"application/vnd.oasis.opendocument.formula",
            "odft"			=>	"application/vnd.oasis.opendocument.formula-template",
            "odg"			   =>	"application/vnd.oasis.opendocument.graphics",
            "odi"			   =>	"application/vnd.oasis.opendocument.image",
            "odm"			   =>	"application/vnd.oasis.opendocument.text-master",
            "odp"			   =>	"application/vnd.oasis.opendocument.presentation",
            "ods"			   =>	"application/vnd.oasis.opendocument.spreadsheet",
            "odt"			   =>	"application/vnd.oasis.opendocument.text",
            "oga"			   =>	"audio/ogg",
            "ogv"			   =>	"video/ogg",
            "ogx"			   =>	"application/ogg",
            "onetoc"		   =>	"application/onenote",
            "opf"			   =>	"application/oebps-package+xml",
            "org"			   =>	"application/vnd.lotus-organizer",
            "osf"			   =>	"application/vnd.yamaha.openscoreformat",
            "osfpvg"		   =>	"application/vnd.yamaha.openscoreformat.osfpvg+xml",
            "otc"			   =>	"application/vnd.oasis.opendocument.chart-template",
            "otf"			   =>	"application/x-font-otf",
            "otg"			   =>	"application/vnd.oasis.opendocument.graphics-template",
            "oth"			   =>	"application/vnd.oasis.opendocument.text-web",
            "oti"			   =>	"application/vnd.oasis.opendocument.image-template",
            "otp"			   =>	"application/vnd.oasis.opendocument.presentation-template",
            "ots"			   =>	"application/vnd.oasis.opendocument.spreadsheet-template",
            "ott"			   =>	"application/vnd.oasis.opendocument.text-template",
            "oxt"			   =>	"application/vnd.openofficeorg.extension",
            "p"			   =>	"text/x-pascal",
            "p10"			   =>	"application/pkcs10",
            "p12"			   =>	"application/x-pkcs12",
            "p7b"			   =>	"application/x-pkcs7-certificates",
            "p7m"			   =>	"application/pkcs7-mime",
            "p7r"			   =>	"application/x-pkcs7-certreqresp",
            "p7s"			   =>	"application/pkcs7-signature",
            "p8"			   =>	"application/pkcs8",
            "par"			   =>	"text/plain-bas",
            "paw"			   =>	"application/vnd.pawaafile",
            "pbd"			   =>	"application/vnd.powerbuilder6",
            "pbm"			   =>	"image/x-portable-bitmap",
            "pcf"			   =>	"application/x-font-pcf",
            "pcl"			   =>	"application/vnd.hp-pcl",
            "pclxl"			=>	"application/vnd.hp-pclxl",
            "pcurl"			=>	"application/vnd.curl.pcurl",
            "pcx"			   =>	"image/x-pcx",
            "pdb"			   =>	"application/vnd.palm",
            "pdf"			   =>	"application/pdf",
            "pfa"			   =>	"application/x-font-type1",
            "pfr"			   =>	"application/font-tdpfr",
            "pgm"			   =>	"image/x-portable-graymap",
            "pgn"			   =>	"application/x-chess-pgn",
            "pgp"			   =>	"application/pgp-signature",
            "pic"			   =>	"image/x-pict",
            "pki"			   =>	"application/pkixcmp",
            "pkipath"		=>	"application/pkix-pkipath",
            "plb"			   =>	"application/vnd.3gpp.pic-bw-large",
            "plc"			   =>	"application/vnd.mobius.plc",
            "plf"			   =>	"application/vnd.pocketlearn",
            "pls"			   =>	"application/pls+xml",
            "pml"			   =>	"application/vnd.ctc-posml",
            "png"			   =>	"image/png",
            "pnm"			   =>	"image/x-portable-anymap",
            "portpkg"		=>	"application/vnd.macports.portpkg",
            "potm"			=>	"application/vnd.ms-powerpoint.template.macroenabled.12",
            "potx"			=>	"application/vnd.openxmlformats-officedocument.presentationml.template",
            "ppam"			=>	"application/vnd.ms-powerpoint.addin.macroenabled.12",
            "ppd"			   =>	"application/vnd.cups-ppd",
            "ppm"			   =>	"image/x-portable-pixmap",
            "ppsm"			=>	"application/vnd.ms-powerpoint.slideshow.macroenabled.12",
            "ppsx"			=>	"application/vnd.openxmlformats-officedocument.presentationml.slideshow",
            "ppt"			   =>	"application/vnd.ms-powerpoint",
            "pptm"			=>	"application/vnd.ms-powerpoint.presentation.macroenabled.12",
            "pptx"			=>	"application/vnd.openxmlformats-officedocument.presentationml.presentation",
            "prc"			   =>	"application/x-mobipocket-ebook",
            "pre"			   =>	"application/vnd.lotus-freelance",
            "prf"			   =>	"application/pics-rules",
            "psb"			   =>	"application/vnd.3gpp.pic-bw-small",
            "psd"			   =>	"image/vnd.adobe.photoshop",
            "psf"			   =>	"application/x-font-linux-psf",
            "pskcxml"		=>	"application/pskc+xml",
            "ptid"			=>	"application/vnd.pvi.ptid1",
            "pub"			   =>	"application/x-mspublisher",
            "pvb"			   =>	"application/vnd.3gpp.pic-bw-var",
            "pwn"			   =>	"application/vnd.3m.post-it-notes",
            "pya"			   =>	"audio/vnd.ms-playready.media.pya",
            "pyv"			   =>	"video/vnd.ms-playready.media.pyv",
            "qam"			   =>	"application/vnd.epson.quickanime",
            "qbo"			   =>	"application/vnd.intu.qbo",
            "qfx"			   =>	"application/vnd.intu.qfx",
            "qps"			   =>	"application/vnd.publishare-delta-tree",
            "qt"			   =>	"video/quicktime",
            "qxd"			   =>	"application/vnd.quark.quarkxpress",
            "ram"			   =>	"audio/x-pn-realaudio",
            "rar"			   =>	"application/x-rar-compressed",
            "ras"			   =>	"image/x-cmu-raster",
            "rcprofile"		=>	"application/vnd.ipunplugged.rcprofile",
            "rdf"			   =>	"application/rdf+xml",
            "rdz"			   =>	"application/vnd.data-vision.rdz",
            "rep"			   =>	"application/vnd.businessobjects",
            "res"			   =>	"application/x-dtbresource+xml",
            "rgb"			   =>	"image/x-rgb",
            "rif"			   =>	"application/reginfo+xml",
            "rip"			   =>	"audio/vnd.rip",
            "rl"			   =>	"application/resource-lists+xml",
            "rlc"			   =>	"image/vnd.fujixerox.edmics-rlc",
            "rld"			   =>	"application/resource-lists-diff+xml",
            "rm"			   =>	"application/vnd.rn-realmedia",
            "rmp"			   =>	"audio/x-pn-realaudio-plugin",
            "rms"			   =>	"application/vnd.jcp.javame.midlet-rms",
            "rnc"			   =>	"application/relax-ng-compact-syntax",
            "rp9"			   =>	"application/vnd.cloanto.rp9",
            "rpss"			=>	"application/vnd.nokia.radio-presets",
            "rpst"			=>	"application/vnd.nokia.radio-preset",
            "rq"			   =>	"application/sparql-query",
            "rs"			   =>	"application/rls-services+xml",
            "rsd"			   =>	"application/rsd+xml",
            "rss"			   =>	"application/rss+xml",
            "rtf"			   =>	"application/rtf",
            "rtx"			   =>	"text/richtext",
            "s"			   =>	"text/x-asm",
            "saf"			   =>	"application/vnd.yamaha.smaf-audio",
            "sbml"			=>	"application/sbml+xml",
            "sc"			   =>	"application/vnd.ibm.secure-container",
            "scd"			   =>	"application/x-msschedule",
            "scm"			   =>	"application/vnd.lotus-screencam",
            "scq"			   =>	"application/scvp-cv-request",
            "scs"			   =>	"application/scvp-cv-response",
            "scurl"			=>	"text/vnd.curl.scurl",
            "sda"			   =>	"application/vnd.stardivision.draw",
            "sdc"			   =>	"application/vnd.stardivision.calc",
            "sdd"			   =>	"application/vnd.stardivision.impress",
            "sdkm"			=>	"application/vnd.solent.sdkm+xml",
            "sdp"			   =>	"application/sdp",
            "sdw"			   =>	"application/vnd.stardivision.writer",
            "see"			   =>	"application/vnd.seemail",
            "seed"			=>	"application/vnd.fdsn.seed",
            "sema"			=>	"application/vnd.sema",
            "semd"			=>	"application/vnd.semd",
            "semf"			=>	"application/vnd.semf",
            "ser"			   =>	"application/java-serialized-object",
            "setpay"		   =>	"application/set-payment-initiation",
            "setreg"		   =>	"application/set-registration-initiation",
            "sfd-hdstx"		=>	"application/vnd.hydrostatix.sof-data",
            "sfs"			   =>	"application/vnd.spotfire.sfs",
            "sgl"			   =>	"application/vnd.stardivision.writer-global",
            "sgml"			=>	"text/sgml",
            "sh"			   =>	"application/x-sh",
            "shar"			=>	"application/x-shar",
            "shf"			   =>	"application/shf+xml",
            "sis"			   =>	"application/vnd.symbian.install",
            "sit"			   =>	"application/x-stuffit",
            "sitx"			=>	"application/x-stuffitx",
            "skp"			   =>	"application/vnd.koan",
            "sldm"			=>	"application/vnd.ms-powerpoint.slide.macroenabled.12",
            "sldx"			=>	"application/vnd.openxmlformats-officedocument.presentationml.slide",
            "slt"			   =>	"application/vnd.epson.salt",
            "sm"			   =>	"application/vnd.stepmania.stepchart",
            "smf"			   =>	"application/vnd.stardivision.math",
            "smi"			   =>	"application/smil+xml",
            "snf"			   =>	"application/x-font-snf",
            "spf"			   =>	"application/vnd.yamaha.smaf-phrase",
            "spl"			   =>	"application/x-futuresplash",
            "spot"			=>	"text/vnd.in3d.spot",
            "spp"			   =>	"application/scvp-vp-response",
            "spq"			   =>	"application/scvp-vp-request",
            "src"			   =>	"application/x-wais-source",
            "sru"			   =>	"application/sru+xml",
            "srx"			   =>	"application/sparql-results+xml",
            "sse"			   =>	"application/vnd.kodak-descriptor",
            "ssf"			   =>	"application/vnd.epson.ssf",
            "ssml"			=>	"application/ssml+xml",
            "st"			   =>	"application/vnd.sailingtracker.track",
            "stc"			   =>	"application/vnd.sun.xml.calc.template",
            "std"			   =>	"application/vnd.sun.xml.draw.template",
            "stf"			   =>	"application/vnd.wt.stf",
            "sti"			   =>	"application/vnd.sun.xml.impress.template",
            "stk"			   =>	"application/hyperstudio",
            "stl"			   =>	"application/vnd.ms-pki.stl",
            "str"			   =>	"application/vnd.pg.format",
            "stw"			   =>	"application/vnd.sun.xml.writer.template",
            "sub"			   =>	"image/vnd.dvb.subtitle",
            "sus"			   =>	"application/vnd.sus-calendar",
            "sv4cpio"		=>	"application/x-sv4cpio",
            "sv4crc"		   =>	"application/x-sv4crc",
            "svc"			   =>	"application/vnd.dvb.service",
            "svd"			   =>	"application/vnd.svd",
            "svg"			   =>	"image/svg+xml",
            "swf"			   =>	"application/x-shockwave-flash",
            "swi"			   =>	"application/vnd.aristanetworks.swi",
            "sxc"			   =>	"application/vnd.sun.xml.calc",
            "sxd"			   =>	"application/vnd.sun.xml.draw",
            "sxg"			   =>	"application/vnd.sun.xml.writer.global",
            "sxi"			   =>	"application/vnd.sun.xml.impress",
            "sxm"			   =>	"application/vnd.sun.xml.math",
            "sxw"			   =>	"application/vnd.sun.xml.writer",
            "t"			   =>	"text/troff",
            "tao"			   =>	"application/vnd.tao.intent-module-archive",
            "tar"			   =>	"application/x-tar",
            "tcap"			=>	"application/vnd.3gpp2.tcap",
            "tcl"			   =>	"application/x-tcl",
            "teacher"		=>	"application/vnd.smart.teacher",
            "tei"			   =>	"application/tei+xml",
            "tex"			   =>	"application/x-tex",
            "texinfo"		=>	"application/x-texinfo",
            "tfi"			   =>	"application/thraud+xml",
            "tfm"			   =>	"application/x-tex-tfm",
            "thmx"			=>	"application/vnd.ms-officetheme",
            "tiff"			=>	"image/tiff",
            "tmo"			   =>	"application/vnd.tmobile-livetv",
            "torrent"		=>	"application/x-bittorrent",
            "tpl"			   =>	"application/vnd.groove-tool-template",
            "tpt"			   =>	"application/vnd.trid.tpt",
            "tra"			   =>	"application/vnd.trueapp",
            "trm"			   =>	"application/x-msterminal",
            "tsd"			   =>	"application/timestamped-data",
            "tsv"			   =>	"text/tab-separated-values",
            "ttf"			   =>	"application/x-font-ttf",
            "ttl"			   =>	"text/turtle",
            "twd"			   =>	"application/vnd.simtech-mindmapper",
            "txd"			   =>	"application/vnd.genomatix.tuxedo",
            "txf"			   =>	"application/vnd.mobius.txf",
            "txt"			   =>	"text/plain",
            "ufd"			   =>	"application/vnd.ufdl",
            "umj"			   =>	"application/vnd.umajin",
            "unityweb"		=>	"application/vnd.unity",
            "uoml"			=>	"application/vnd.uoml+xml",
            "uri"			   =>	"text/uri-list",
            "ustar"			=>	"application/x-ustar",
            "utz"			   =>	"application/vnd.uiq.theme",
            "uu"			   =>	"text/x-uuencode",
            "uva"			   =>	"audio/vnd.dece.audio",
            "uvh"			   =>	"video/vnd.dece.hd",
            "uvi"			   =>	"image/vnd.dece.graphic",
            "uvm"			   =>	"video/vnd.dece.mobile",
            "uvp"			   =>	"video/vnd.dece.pd",
            "uvs"			   =>	"video/vnd.dece.sd",
            "uvu"			   =>	"video/vnd.uvvu.mp4",
            "uvv"			   =>	"video/vnd.dece.video",
            "vcd"			   =>	"application/x-cdlink",
            "vcf"			   =>	"text/x-vcard",
            "vcg"			   =>	"application/vnd.groove-vcard",
            "vcs"			   =>	"text/x-vcalendar",
            "vcx"			   =>	"application/vnd.vcx",
            "vis"			   =>	"application/vnd.visionary",
            "viv"			   =>	"video/vnd.vivo",
            "vsd"			   =>	"application/vnd.visio",
            "vsf"			   =>	"application/vnd.vsf",
            "vtu"			   =>	"model/vnd.vtu",
            "vxml"			=>	"application/voicexml+xml",
            "wad"			   =>	"application/x-doom",
            "wav"			   =>	"audio/x-wav",
            "wax"			   =>	"audio/x-ms-wax",
            "wbmp"			=>	"image/vnd.wap.wbmp",
            "wbs"			   =>	"application/vnd.criticaltools.wbs+xml",
            "wbxml"			=>	"application/vnd.wap.wbxml",
            "weba"			=>	"audio/webm",
            "webm"			=>	"video/webm",
            "webp"			=>	"image/webp",
            "wg"			   =>	"application/vnd.pmi.widget",
            "wgt"			   =>	"application/widget",
            "wm"			   =>	"video/x-ms-wm",
            "wma"			   =>	"audio/x-ms-wma",
            "wmd"			   =>	"application/x-ms-wmd",
            "wmf"			   =>	"application/x-msmetafile",
            "wml"			   =>	"text/vnd.wap.wml",
            "wmlc"			=>	"application/vnd.wap.wmlc",
            "wmls"			=>	"text/vnd.wap.wmlscript",
            "wmlsc"			=>	"application/vnd.wap.wmlscriptc",
            "wmv"			   =>	"video/x-ms-wmv",
            "wmx"			   =>	"video/x-ms-wmx",
            "wmz"			   =>	"application/x-ms-wmz",
            "woff"			=>	"application/x-font-woff",
            "woff2"			=>	"application/font-woff2",
            "wpd"			   =>	"application/vnd.wordperfect",
            "wpl"			   =>	"application/vnd.ms-wpl",
            "wps"			   =>	"application/vnd.ms-works",
            "wqd"			   =>	"application/vnd.wqd",
            "wri"			   =>	"application/x-mswrite",
            "wrl"			   =>	"model/vrml",
            "wsdl"			=>	"application/wsdl+xml",
            "wspolicy"		=>	"application/wspolicy+xml",
            "wtb"			   =>	"application/vnd.webturbo",
            "wvx"			   =>	"video/x-ms-wvx",
            "x3d"			   =>	"application/vnd.hzn-3d-crossword",
            "xap"			   =>	"application/x-silverlight-app",
            "xar"			   =>	"application/vnd.xara",
            "xbap"			=>	"application/x-ms-xbap",
            "xbd"			   =>	"application/vnd.fujixerox.docuworks.binder",
            "xbm"			   =>	"image/x-xbitmap",
            "xdf"			   =>	"application/xcap-diff+xml",
            "xdm"			   =>	"application/vnd.syncml.dm+xml",
            "xdp"			   =>	"application/vnd.adobe.xdp+xml",
            "xdssc"			=>	"application/dssc+xml",
            "xdw"			   =>	"application/vnd.fujixerox.docuworks",
            "xenc"			=>	"application/xenc+xml",
            "xer"			   =>	"application/patch-ops-error+xml",
            "xfdf"			=>	"application/vnd.adobe.xfdf",
            "xfdl"			=>	"application/vnd.xfdl",
            "xhtml"			=>	"application/xhtml+xml",
            "xif"			   =>	"image/vnd.xiff",
            "xlam"			=>	"application/vnd.ms-excel.addin.macroenabled.12",
            "xls"			   =>	"application/vnd.ms-excel",
            "xlsb"			=>	"application/vnd.ms-excel.sheet.binary.macroenabled.12",
            "xlsm"			=>	"application/vnd.ms-excel.sheet.macroenabled.12",
            "xlsx"			=>	"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "xltm"			=>	"application/vnd.ms-excel.template.macroenabled.12",
            "xltx"			=>	"application/vnd.openxmlformats-officedocument.spreadsheetml.template",
            "xml"			   =>	"application/xml",
            "xo"			   =>	"application/vnd.olpc-sugar",
            "xop"			   =>	"application/xop+xml",
            "xpi"			   =>	"application/x-xpinstall",
            "xpm"			   =>	"image/x-xpixmap",
            "xpr"			   =>	"application/vnd.is-xpr",
            "xps"			   =>	"application/vnd.ms-xpsdocument",
            "xpw"			   =>	"application/vnd.intercon.formnet",
            "xslt"			=>	"application/xslt+xml",
            "xsm"			   =>	"application/vnd.syncml+xml",
            "xspf"			=>	"application/xspf+xml",
            "xul"			   =>	"application/vnd.mozilla.xul+xml",
            "xwd"			   =>	"image/x-xwindowdump",
            "xyz"			   =>	"chemical/x-xyz",
            "yaml"			=>	"text/yaml",
            "yang"			=>	"application/yang",
            "yin"			   =>	"application/yin+xml",
            "zaz"			   =>	"application/vnd.zzazz.deck+xml",
            "zip"			   =>	"application/zip",
            "zir"			   =>	"application/vnd.zul",
            "zmm"			   =>	"application/vnd.handheld-entertainment+xml"
         );

         $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

         if (array_key_exists($extension, $mime_type)) {
            return $mime_type[$extension];
         } else {
            "";
         }
      }
   }

}

?>
