<?php
// *********************************************************************************************************************************
//
// get_records.php
//
// This example uses fmDataAPI::apiGetRecord().
//    - Retrieves one record and dump the contents to the log.
//    - Retrieves a different record and displays the project name in the log.
//    - Attempts to retrieve a record which doesn't exist so you can see what the API returns.
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

require_once 'startup.inc.php';

$fm = new fmDataAPI(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

//
// Get the first (at most) 50 records, starting with the 10th record.
//
$apiResult = $fm->apiGetRecords('Web_Project', '', array('limit' => 50, 'offset' => 10));
if (! $fm->getIsError($apiResult)) {
   $responseData = $fm->getResponseData($apiResult);
   foreach ($responseData as $record) {
      fmLogger('Project Name = '. $record[FM_FIELD_DATA]['Name']);
   }
}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}


//
// Get a 3 records starting with the first record.
//
fmLogger('');
fmLogger('');
fmLogger('');
fmLogger('************************************************************************************************************************************');
$apiResult = $fm->apiGetRecords('Web_Project', '', array('limit' => 3, 'offset' => '1'));
if (! $fm->getIsError($apiResult)) {
   $responseData = $fm->getResponseData($apiResult);
   foreach ($responseData as $record) {
      fmLogger('Project Name = '. $record[FM_FIELD_DATA]['Name']);
   }
}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}


echo fmGetLog();

?>
