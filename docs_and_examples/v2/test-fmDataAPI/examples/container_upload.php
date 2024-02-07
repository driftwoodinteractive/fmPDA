<?php
// *********************************************************************************************************************************
//
// container_upload.php
//
// This example uses fmDataAPI::apiUploadContainer() to upload a file to a container field.
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

$recordID = '';
$photoURL = '';
$photoHW = '';
$photoName = '';

$fm = new fmDataAPI(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

// First, add a record
$fields = array();
$fields['Name'] = 'Project '. rand();

$apiResult = $fm->apiCreateRecord('Web_Project', $fields);
if (! $fm->getIsError($apiResult)) {
   $response = $fm->getResponse($apiResult);
//   fmLogger($response);
   $recordID = $response[FM_RECORD_ID];


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
//    $file['mimeType'] = $fm->get_mime_content_type('sample_files/sample.png');


   $apiResult = $fm->apiUploadContainer('Web_Project', $recordID, 'Photo', FM_FIELD_REPETITION_1, $file);
   if (! $fm->getIsError($apiResult)) {
      $response = $fm->getResponse($apiResult);
//      fmLogger($response);

      // Lastly, retrieve the container field and display it
      $apiResult = $fm->apiGetRecord('Web_Project', $recordID);
      if (! $fm->getIsError($apiResult)) {
         $responseData = $fm->getResponseData($apiResult);

         $photoURL = $responseData[0][FM_FIELD_DATA]['Photo'];
         $photoHW = $responseData[0][FM_FIELD_DATA]['c_PhotoHWHTML'];
         $photoName = $responseData[0][FM_FIELD_DATA]['PhotoName'];

         fmLogger('Project Name = '. $responseData[0][FM_FIELD_DATA]['Name']);
         fmLogger('Note that each time you retrieve the record, the URL changes:');
         fmLogger($photoURL);
      //   fmLogger($apiResult);
      }
      else {
         $errorInfo = $fm->getMessageInfo($apiResult);
         fmLogger('Found error(s) from apiGetRecord():');
         fmLogger($errorInfo);
      }
   }
   else {
      $errorInfo = $fm->getMessageInfo($apiResult);
      fmLogger('Found error(s) from apiUploadContainer():');
      fmLogger($errorInfo);
   }
}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('Found error(s) from apiCreateRecord():');
   fmLogger($errorInfo);
}

if ($photoURL != '') {
   echo '<img src="'. $photoURL .'" '. $photoHW .' alt="'. $photoName .'" /><br>';
   echo $photoName .'<br><br>';
}

echo fmGetLog();

?>
