<?php
// *********************************************************************************************************************************
//
// edit.php
//
// This example uses fmDataAPI::apiEditRecord() to get an existing record, update a field, and then re-get the record.
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

// To test what a bad login looks like, change FM_USERNAME or FM_PASSWORD to something 'bad'
$fm = new fmDataAPI(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD, $options);

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
               fmDataAPI::apiEditRecord()
            </div>


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