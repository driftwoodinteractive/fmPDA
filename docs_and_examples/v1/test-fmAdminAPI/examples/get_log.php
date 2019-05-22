<?php
// *********************************************************************************************************************************
//
// get_log.php
//
// This example retrieves a FileMaker log through fmAdminAPI::apiGetLog().
//
// *********************************************************************************************************************************
//
// Copyright (c) 2017 - 2019 Mark DeNyse
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

require_once 'startup.inc.php';

$response = array();

/*
 * $url is where you previously stored the get_fms_log.php file on your FMS server.
 * Remeber, it needs to be on the same server as FileMaker Server so it has access
 * to the log file directory on the hard drive.
*/
$url = '';

/*
 * $type is the type of log you want to retrieve:
 * 'access', 'event', 'fmdapi', 'stderr', 'stdout', 'topcallstats', 'wpedebug', or 'wpe'
*/
$type = array_key_exists('type', $_GET) ? $_GET['type'] : 'event';


/*
 * $format is what format you want the log returned:
 * 'html-table', 'html or ''raw'
*/
$format = array_key_exists('format', $_GET) ? $_GET['format'] : 'html-table';


$fm = new fmAdminAPI(FM_HOST, FM_USERNAME, FM_PASSWORD);

$apiResult = $fm->apiGetLog($type, $format, $url);

if (! $fm->getIsError($apiResult)) {
   $response = $fm->getResponse($apiResult);
}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}

?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8" />
   <style>
      /* These classes are set in the returned html table from apiGetLog(). Define these how you want to customize the table. */
      .fm-log-table { border-collapse: collapse; border: 1px solid #dddddd; }
      .fm-log-table-row { }
      .fm-log-table-row:nth-child(even) {background-color: #f2f2f2;}
      .fm-log-table-cell { border-bottom: 1px solid #dddddd; padding-left: 5px; padding-right: 5px; }
   </style>
</head>
<body>
   <?php echo fmGetLog(); ?>
   <?php if (array_key_exists('log', $response)) { echo $response['log']; } ?>
</body>
</html>
