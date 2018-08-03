<?php
// *********************************************************************************************************************************
//
// get_container_url.php
//
// This example uses fmDataAPI::apiGetRecord() to retrieve a record and display the contents of a container field. We let the
// browser actually download the file through the use of the <img tag.
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

$photoURL = '';
$photoHW = '';
$photoName = '';

$fm = new fmDataAPI(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

$apiResult = $fm->apiGetRecord('Web_Project', 1);
if (! $fm->getIsError($apiResult)) {
   $responseData = $fm->getResponseData($apiResult);

   $photoURL = $responseData[0][FM_FIELD_DATA]['Photo'];                           // Just get the URL and off you go!
   $photoHW = $responseData[0][FM_FIELD_DATA]['c_PhotoHWHTML'];
   $photoName = $responseData[0][FM_FIELD_DATA]['PhotoName'];

   fmLogger('Project Name = '. $responseData[0][FM_FIELD_DATA]['Name']);
   fmLogger('Note that each time you retrieve the record, the URL changes:');
   fmLogger($photoURL);
//   fmLogger($apiResult);
}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}

if ($photoURL != '') {
   echo '<img src="'. $photoURL .'" '. $photoHW .' alt="'. $photoName .'" /><br>';
   echo $photoName .'<br><br>';
}

echo fmGetLog();

?>
