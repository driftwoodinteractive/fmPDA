<?php
/*
 * *********************************************************************************************************************************
 *
 * get_fms_log.php
 *
 * https://<server>/<path-to>/get_fms_log.php
 *
 * Retrieve a FileMaker log. This uses fmAdminAPI::apiGetServerStatus() to authenticate - if the call is successful
 * then the caller is considered to be legitimate enough to retrieve a log file from the server as well.
 *
 * The log data is not routed through the Admin API, so should the Admin API be metered in the future you won't incur
 * any cost to using this method.
 *
 * Parameters:
 *    JSON payload in body. Request is made via POST:
 *       (string) host              Host name for server
 *       (string) username          Admin console username
 *       (string) password          Admin console password
 *       (string) token             The authorization token returned from a previous call to the Admin API.
 *                                  This is optional, and if the token is not valid, a new token will be returned.
 *       (string) version           The version of the Admin API to make when calling fmAdminAPI::apiGetServerStatus()
 *                                  This is optional and defaults to 1.
 *       (string) type              The type of log to return
 *                                     'access'
 *                                     'event'
 *                                     'fmdapi'
 *                                     'stderr'
 *                                     'stdout'
 *                                     'topcallstats'
 *                                     'wpedebug'
 *                                     'wpe'
 *       (string) format            The format of the log data:
 *                                     'raw'            The contents of the file as read from disk (default)
 *                                     'html'           htmlspecialchars() encoded and new lines into <br> tags
 *                                     'html-table'     HTML table, htmlspecialchars() for the data
 *
 *
 * Returns:
 *    JSON encoded structure:
 *       (integer) code              An error code. 0 if successful
 *       (string)  errorMessage      Any error message
 *       (string)  token             The authentication token that was used to communicate with the Admin API
 *       (blob)    log               The log file in whatever form specified by format in the request payload
 *       (text)    fmlog             Any output from fmLogger() calls this script generates. This allows the caller
 *                                   to display the output in the browser if they wish.
 *
 * *********************************************************************************************************************************
 *
 * Copyright (c) 2017 - 2018 Mark DeNyse
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * *********************************************************************************************************************************
 */

// Path the fmPDA's fmDataAdminAPI class:
require_once '../../../../fmPDA/v1/fmAdminAPI.class.php';


$result = array();
$result[FM_RESULT]        = 0;
$result[FM_ERROR_MESSAGE] = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

   $body = file_get_contents('php://input');                   // Get the payload from the body

   if ($body != '') {

      $params = json_decode($body, true);                      // Transmorgify the JSON into a PHP array

      if (json_last_error() == JSON_ERROR_NONE) {

         $result = GetFileMakerLog($result, $params);          // Authenticate, get the log, and format it

      }
      else {
         $result[FM_RESULT]        = '-3';
         $result[FM_ERROR_MESSAGE] = 'Invalid JSON in payload';
      }
   }
   else {
      $result[FM_RESULT]        = '-2';
      $result[FM_ERROR_MESSAGE] = 'Parameters are missing';
   }
}
else {
   $result[FM_RESULT]        = '-1';
   $result[FM_ERROR_MESSAGE] = 'Request type must be POST';
}


SendResult($result);                                           // Send the data back to our caller



/*
 * *********************************************************************************************************************************
 * Authenticate
 */
function Authenticate($result, $params = array())
{
   $defaultParams = array('host'     => '',
                          'username' => '',
                          'password' => '',
                          'token'    => '',
                          'version'  => 1
                    );
   $params = array_merge($defaultParams, $params);

   $options = array();
   $options['version'] = $params['version'];
   $options['token'] = $params['token'];

   $fm = new fmAdminAPI($params['host'], $params['username'], $params['password'], $options);

   $apiResult = $fm->apiGetServerStatus();                                    // Get the server status which validates we're authenticated

   if (! $fm->getIsError($apiResult)) {                                       // Were we able to authenticate with un/pw or token?
      $result['token'] = $fm->getToken();                                     // Return the valid token
  }
   else {
      $messageInfo = $fm->getFirstMessage($apiResult);
      $result[FM_RESULT]        = $messageInfo[FM_RESULT];
      $result[FM_ERROR_MESSAGE] = $messageInfo[FM_ERROR_MESSAGE];
   }

   $result['fmlog'] = fmGetLog();

   return $result;
}

/*
 * *********************************************************************************************************************************
 * GetFileMakerLog
 */
function GetFileMakerLog($result, $params = array())
{
   $logPaths = array();

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

      $logType = strtolower($params['type']);
      $logPath = array_key_exists($logType, $logPaths) ? $logPaths[$logType] : '';

      if ($logPath != '') {
         $result['log'] = FormatLog(file_get_contents($logPath), $params);      // Get the log and format how the caller wants
      }
      else {
         $result[FM_RESULT]        = '-5';
         $result[FM_ERROR_MESSAGE] = 'Invalid log type';
      }
   }
   else {
      $result[FM_RESULT]        = '-4';
      $result[FM_ERROR_MESSAGE] = 'Unknown host type (cloud?)';
   }

   return $result;
}

/*
 * *********************************************************************************************************************************
 * FormatLog
 */
function FormatLog($log, $params)
{
   if (array_key_exists('format', $params)) {

      switch ($params['format']) {

         case 'html-table': {

            // The class names are defined so that the caller can substitue their own CSS styling
            // for the table by defining these CSS styles:
            $tableClass = 'fm-log-table';
            $rowClass   = 'fm-log-table-row';
            $cellClass  = 'fm-log-table-cell';

            $log = '<table class="'. $tableClass .'">'.
                   '<tr class="'. $rowClass .'">'.
                   '<td class="'. $cellClass .'">'.

                   str_replace(array("\t",
                                     "\r",
                                     "\n"),
                               array('</td><td class="'. $cellClass .'">',
                                     '</td></tr><tr class="'. $rowClass .'"><td class="'. $cellClass .'">',
                                     '</td></tr><tr class="'. $rowClass .'"><td class="'. $cellClass .'">'),
                               htmlspecialchars($log)).

                   '</td>'.
                   '</tr>'.
                   '</table>';
            break;
         }

         case 'html': {
            $log = nl2br(htmlspecialchars($log));
            break;
         }

         case 'raw':
         default: {
            break;
         }
      }

   }

   return $log;
}


/*
 * *********************************************************************************************************************************
 * SendResult
 *
 * Send the header and data to the client. The data will be compressed if the client can accept it and we're told to.
 * We don't compress if there isn't any data, since (a) that would be silly and (b) gzencode() would return a 20-byte blob of data.
 *
 */
function SendResult($data, $compress = true)
{
   $compressionLevel = 9;

   $data = json_encode($data);

   $dataLength = strlen($data);
   $compressedDataLength = 0;

   $acceptableEncoding = array_key_exists('HTTP_ACCEPT_ENCODING', $_SERVER) ? strtolower($_SERVER['HTTP_ACCEPT_ENCODING']) : '';
	$clientAcceptsGZIP = (strpos($acceptableEncoding, 'gzip') !== false) ? true : false;

   if ($compress && $clientAcceptsGZIP && ($dataLength > 0)) {
      $compressedData = gzencode($data, $compressionLevel);
      $compressedDataLength = strlen($compressedData);

      if ($dataLength <= $compressedDataLength) {
         $compressedDataLength = 0;                                                              // Send as uncompressed
      }
   }

   header('Expires: 0');
   header('Pragma: no-cache');
   header('Cache-control: no-store, no-cache, private');
   header('Content-type: application/json');

   if ($compressedDataLength > 0) {
      header('Content-Encoding: gzip');
      header('Content-length: '. $compressedDataLength);
      echo $compressedData;
   }
   else {
      header('Content-length: '. $dataLength);
      echo $data;
   }

   return;
}

?>
