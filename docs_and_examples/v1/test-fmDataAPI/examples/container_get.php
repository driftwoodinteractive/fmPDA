<?php
// *********************************************************************************************************************************
//
// get_container.php
//
// This example uses fmDataAPI::apiGetContainer() to retrieve the contents of a container field.
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

$options = array();
$options['action'] = array_key_exists('action', $_GET) ? $_GET['action'] : 'get';      // inline  download   get
$options['fileNameField'] = 'PhotoName';     // The value stored in the PhotoName field becomes the file name of the downloaded file.

$fm = new fmDataAPI(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

$apiResult = $fm->apiGetContainer('Web_Project', 114, 'Photo', FM_FIELD_REPETITION_1, $options);
if (! $fm->getIsError($apiResult)) {

   // If action is 'download' or 'inline' don't echo any thing else
   if ($options['action'] == 'get') {

      $response = $fm->getResponse($apiResult);
      fmLogger($response);
      fmLogger($fm->file);

      echo fmGetLog();
   }
}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}

?>
