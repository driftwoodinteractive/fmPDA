<?php
// *********************************************************************************************************************************
//
// edit.php
//
// This example uses fmDataAPI::apiEditRecord() to get an existing record, update a field, and then re-get the record.
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

$fm = new fmDataAPI(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

$recordID = 6;

$apiResult = $fm->apiGetRecord('Web_Project', $recordID);
if (! $fm->getIsError($apiResult)) {
   $responseData = $fm->getResponseData($apiResult);
   fmLogger('Current ColorIndex = '. $responseData[0][FM_FIELD_DATA]['ColorIndex']);
//   fmLogger($responseData);


   $fields = array();
   $fields['ColorIndex'] = $responseData[0][FM_FIELD_DATA]['ColorIndex'] + 10;   // Add 10 to the existing value

   $apiResult = $fm->apiEditRecord('Web_Project', $recordID, $fields);
   if (! $fm->getIsError($apiResult)) {

      $response = $fm->getResponse($apiResult);
      fmLogger('Modification ID = '. $response[FM_MOD_ID]);
//         fmLogger($responseData);

      $apiResult = $fm->apiGetRecord('Web_Project', $recordID);
      if (! $fm->getIsError($apiResult)) {
         $responseData = $fm->getResponseData($apiResult);
         fmLogger('New ColorIndex = '. $responseData[0][FM_FIELD_DATA]['ColorIndex']);
      }
   }
   else {
      $errorInfo = $fm->getMessageInfo($apiResult);
      fmLogger('Found error(s):');
      fmLogger($errorInfo);
   }
}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}

echo fmGetLog();

?>
