<?php
// *********************************************************************************************************************************
//
// get_container.php
//
// This example uses fmDataAPI::apiGetRecord() to retrieve a record and display the contents of a container field.
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

$recordID = '';
$photoURL = '';
$photoHW = '';
$photoName = '';

// Set token to something invalid if you want to force fmPDA to request a new token on it's first call.
// This is mainly useful for debugging - it will slow down performance if you always force it to get a new token.
// The main reason for passing a token here is you want to store the token some place other than the default location
// in the session. In that case you should pass in a valid token and set storeTokenInSession => false. If you do this,
// you'll be responsible for storage of the token which you can retrieve with $fm->getToken().
$options = array(
                  'version'               => DATA_API_VERSION,
                  'storeTokenInSession'   => true,                  // This defaults to true - here for debugging only
                  'token'                 => ''  //'BAD-ROBOT'
               );

$fm = new fmDataAPI(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD, $options);

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
//    $file['mimeType'] = mime_content_type('sample_files/sample.png');


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


?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8" />
 		<meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>fmPDA &#9829;</title>
      <link href="../../css/normalize.css" rel="stylesheet" />
      <link href="../../css/styles.css" rel="stylesheet" />
      <link href="../../css/fontawesome-all.min.css" rel="stylesheet" />
      <link href="../../css/prism.css" rel="stylesheet" />
      <script src="../../js/prism.js"></script>
   </head>
   <body>

      <!-- Always on top: Position Fixed-->
      <header>
         fmPDA <span class="fmPDA-heart"><i class="fas fa-heart"></i></span>
         <span style="float: right;">Version <strong><?php echo DATA_API_VERSION; ?></strong> (FileMaker Server 17+)</span>
      </header>



      <!-- Fixed size after header-->
      <div class="content">

         <!-- Always on top. Fixed position, fixed width, relative to content width-->
         <div class="navigation-left">
            <div class="navigation-header1"><a href="../../index.php">Home</a></div>
            <br>

            <?php echo GetNavigationMenu(1); ?>
         </div>



          <!-- Scrollable div with main content -->
         <div class="main">

            <div class="main-header">
               fmDataAPI::apiUploadContainer()
            </div>


            <?php
               if ($photoURL != '') {
                  echo '<img src="'. $photoURL .'" '. $photoHW .' alt="'. $photoName .'" /><br>';
                  echo $photoName .'<br><br>';
               }
            ?>


            <!-- Display the debug log -->
            <?php echo fmGetLog(); ?>

         </div>

      </div>



      <!-- Always at the end of the page -->
      <footer>
         <a href="http://www.driftwoodinteractive.com"><img src="../../img/di.png" height="32" width="128" alt="Driftwood Interactive" style="vertical-align:text-bottom"></a><br>
         Copyright &copy; <?php echo date('Y'); ?> Mark DeNyse Released Under the MIT License.
      </footer>

		<script src="../../js/main.js"></script>
      <script>
         javascript:ExpandDropDown("fmDataAPI");
      </script>

   </body>
</html>