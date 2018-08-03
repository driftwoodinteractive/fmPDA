<?php
// *********************************************************************************************************************************
//
// container_upload.php
//
// This example uses newUploadContainerCommand() - adding a new record and then uploading a file to the record.
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

$fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

$addCommand = $fm->newAddCommand('Web_Project');
$addCommand->setField('Name', 'Project '. rand());
$addCommand->setField('Date_Start', date('m/d/Y'));
$addCommand->setField('Date_End', date('m/d/Y'));
$addCommand->setField('ColorIndex', 999);

$result = $addCommand->execute();

if (! fmGetIsError($result)) {
   $recordID = $result->getRecordId();

   $file = array();
   // Now upload the file to the container field by passing the path to the file:
   $file['path'] = 'sample_files/sample.png';
//    $file['path'] = 'sample_files/sample.pdf';
//    $file['path'] = 'sample_files/sample.xlsx';
//    $file['path'] = 'sample_files/sample.docx';
//    $file['path'] = 'sample_files/sample.pptx';


   // Or, if you already have the contents of the file:
//    $file['contents'] = file_get_contents('sample_files/sample.png');
//    $file['name']     = 'sample.png';
//    $file['mimeType'] = mime_content_type('sample_files/sample.png');


   $uploadCommand = $fm->newUploadContainerCommand('Web_Project', $recordID, 'Photo', FM_FIELD_REPETITION_1, $file);
   $result = $uploadCommand->execute();

   if (! fmGetIsError($result)) {
      fmLogger('Successful upload! modID='. $result->getModificationId());

      $photoURL = $result->getFieldUnencoded('Photo');                           // Just get the URL and off you go!
      $photoHW = $result->getFieldUnencoded('c_PhotoHWHTML');
      $photoName = $result->getFieldUnencoded('PhotoName');
   }
   else {
      fmLogger('Error = '. $result->getCode() .' Message = '. $result->getMessage());
   }
}
else {
   fmLogger('Error = '. $result->getCode() .' Message = '. $result->getMessage());
}

if ($photoURL != '') {
   echo '<img src="'. $photoURL .'" '. $photoHW .' alt="'. $photoName .'" /><br>';
   echo $photoName .'<br><br>';
}

echo fmGetLog();

?>
