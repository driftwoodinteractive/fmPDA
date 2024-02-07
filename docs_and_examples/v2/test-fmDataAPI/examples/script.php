<?php
// *********************************************************************************************************************************
//
// script.php
//
// This example uses fmDataAPI::apiPerformScript().
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

require_once 'startup.inc.php';

$script = 'Test';
// $script = 'New Script';
$params = 'some';     // 'all' or 'some'

$fm = new fmDataAPI(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

$apiResult = $fm->apiPerformScript('Web_Global', $script, $params, 'Web_Project');

if (! $fm->getIsError($apiResult)) {

   $response = $fm->getResponse($apiResult);
//fmLogger($response);

//     [scriptResult.prerequest] => Prerequest Result
//     [scriptError.prerequest] => 0
//     [scriptResult.presort] => Presort Result
//     [scriptError.presort] => 0
//     [scriptResult] => This is my script result!
//     [scriptError] => 0

   if (array_key_exists('scriptResult', $response)) {
      fmLogger('scriptResult='. $response['scriptResult']);
   }
   if (array_key_exists('scriptError', $response)) {
      fmLogger('scriptError='. $response['scriptError']);
   }

   $responseData = $fm->getResponseData($apiResult);
// fmLogger($responseData);
   foreach ($responseData as $record) {
      fmLogger('Project Name = '. $record[FM_FIELD_DATA]['Name']);
   }
}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('Found error(s) from apiPerformScript():');
   fmLogger($errorInfo);
}

echo fmGetLog();

?>
