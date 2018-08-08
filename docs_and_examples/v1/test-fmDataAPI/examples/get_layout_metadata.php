<?php
// *********************************************************************************************************************************
//
// get_layout_metadata.php
//
// This example uses an UNDOCUMENTED API to get layout metadata. USE AT YOUR OWN RISK.
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


// This is *UNDOCUMENTED* - USE AT YOUR OWN RISK.
// Gleaned from /Library/FileMaker Server/Web Publishing/publishing-engine/node-wip/routes/api.js
define('PATH_METADATA', PATH_DATA_API_BASE .'/' .'%%%DATABASE%%%' . '/layouts/%%%LAYOUTNAME%%%/metadata');

fmLogger('This example uses an <span style="color: #ff0000;">UNDOCUMENTED API</span> to get layout metadata. USE AT YOUR OWN RISK.');

$fm = new fmDataAPI(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

$apiResult = $fm->fmAPI($fm->getAPIPath(PATH_METADATA, 'Web_Project'), METHOD_GET);
if (! $fm->getIsError($apiResult)) {
   fmLogger($fm->getResponse($apiResult), true);
}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('code='. $errorInfo[0][FM_CODE] .' message='. $errorInfo[0][FM_MESSAGE]);
}

echo fmGetLog();

?>
