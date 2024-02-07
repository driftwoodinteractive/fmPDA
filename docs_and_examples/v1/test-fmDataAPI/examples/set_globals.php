<?php
// *********************************************************************************************************************************
//
// set_globals.php
//
// This example uses fmDataAPI::apiSetGlobalFields(). Globals stick around for the duration of your session (until you log out
// or the session token expires).
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

$fields = array();
$fields['Project::gGlobal'] = 5;
//$fields['Project::SomeOtherGlobal'] = 'bla';


$fm = new fmDataAPI(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

$apiResult = $fm->apiSetGlobalFields('Web_Project', $fields);

$errorInfo = $fm->getMessages($apiResult);

// Show the response for the set
fmLogger('Response from apiSetGlobalFields():');
fmLogger($errorInfo);


// Now get the value back to see if it really stuck. To verify it's really sticking for the session, run this
// script once, then comment out the call to apiSetGlobalFields() above but leave in the apiGetRecord() call.
// You'll see the value remain until you log out of the session or the token expires.
$apiResult = $fm->apiGetRecord('Web_Project', 1);
if (! $fm->getIsError($apiResult)) {
   $responseData = $fm->getResponseData($apiResult);
      fmLogger('Project::gGlobal = '. $responseData[0][FM_FIELD_DATA]['gGlobal']);
}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}

echo fmGetLog();

?>
