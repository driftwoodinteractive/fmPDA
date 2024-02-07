<?php
// *********************************************************************************************************************************
//
// container_data.php
//
// This test gets the contents of a container field and displays the contents, downloads, or inline's the file in the browser.
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

$options = array();
$options['action'] = array_key_exists('action', $_GET) ? $_GET['action'] : 'get';      // inline  download   get
$options['fileNameField'] = 'PhotoName';     // The value stored in the PhotoName field becomes the file name of the downloaded file.

$fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

$recordID = 8;

$photoURL = '';
$photoHW = '';
$photoName = '';

$result = $fm->getRecordById('Web_Project', $recordID);
if (! fmGetIsError($result)) {

   $url = $result->getFieldUnencoded('Photo');

   $result = $fm->getContainerData($url, $options);

   // If action is 'download' or 'inline' don't echo any thing else
   if ($options['action'] == 'get') {

      fmLogger($fm->file);

      echo fmGetLog();
   }
}
else {
   fmLogger('Error = '. $result->getCode() .' Message = '. $result->getMessage());
   echo fmGetLog();
}

?>
