<?php
// *********************************************************************************************************************************
//
// perform_on_database.php
//
// This example uses fmAdminAPI::apiPerformOnDatabase() to open/close/pause/resume a database on the server.
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

// This is for TESTING. Be careful you don't operate on the wrong database!!!!
$databaseID = 1;

$status = $_GET['status'];             // OPENED, PAUSED, RESUMED, CLOSED

$options = array();
if (array_key_exists('password', $_GET)) {
   $options['password'] = $_GET['password'];
}

if (array_key_exists('messageText', $_GET)) {
   $options['messageText'] = $_GET['messageText'];
}

if (array_key_exists('force', $_GET)) {
   $options['force'] = $_GET['force'];
}

$fm = new fmAdminAPI(FM_HOST, FM_USERNAME, FM_PASSWORD);

$apiResult = $fm->apiPerformOnDatabase($databaseID, $status, $options);
if (! $fm->getIsError($apiResult)) {
   $response = $fm->getResponse($apiResult);
   fmLogger($response);
}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}

echo fmGetLog();

?>
