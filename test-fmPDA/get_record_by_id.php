<?php
// *********************************************************************************************************************************
//
// get_record_by_id.php
//
// This example uses getRecordById().
//    - Retrieves one record and dump the contents to the log.
//    - Retrieves a different record and displays the project name in the log.
//    - Attempts to retrieve a record which doesn't exist, return a null result to match the old API.
//
// *********************************************************************************************************************************
//
// Copyright (c) 2017 Mark DeNyse
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
                  'storeTokenInSession' => true,
                  'token' => ''  //'BAD-ROBOT'
               );

$fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD, 'PHP_Project', $options);


//
// Get a record by ID 1
//
$result = $fm->getRecordById('PHP_Project', 1);
// fmLogger($result);
if (fmGetIsValid($result)) {
   fmLogger($result->dumpRecord());
}
else if (is_null($result)) {
   fmLogger('Record not found.');
}
else {
   fmLogger('Error = '. $result->getCode() .' Message = '. $result->getMessage());
}


//
// Get a record by ID 5
//
fmLogger('');
fmLogger('');
fmLogger('');
fmLogger('************************************************************************************************************************************');
$result = $fm->getRecordById('PHP_Project', 5);
//fmLogger($result);
if (fmGetIsValid($result)) {
   fmLogger('Project Name = '. $result->getFieldUnencoded('Name'));
}
else if (is_null($result)) {
   fmLogger('Record not found.');
}
else {
   fmLogger('Error = '. $result->getCode() .' Message = '. $result->getMessage());
}


//
// Get a record by ID 55555 - this will fail as the record doesn't exist.
//
fmLogger('');
fmLogger('');
fmLogger('');
fmLogger('************************************************************************************************************************************');
$result = $fm->getRecordById('PHP_Project', 55555);
//fmLogger($result);
if (fmGetIsValid($result)) {
   fmLogger('Project Name = '. $result->getFieldUnencoded('Name'));
}
else if (is_null($result)) {                                   // Remember, the old API returns null when the record can't be found
   fmLogger('Record not found.');
}
else {
   fmLogger('Error = '. $result->getCode() .' Message = '. $result->getMessage());
}

?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8" />
      <title>FM PHP Data API Test: GetRecordByID</title>
      <link href="../css/normalize.css" rel="stylesheet" />
      <link href="../css/styles.css" rel="stylesheet" />
   </head>
   <body>

      <h1 id="header">
         fmPDA <span style="color: #ff0000;">&#9829;</span><br>
         getRecordById()
      </h1>

      <h2>
      <div style="text-align: center;">
         <span class="link"><a href="../index.php">Introduction</a></span>
         <span class="link"><a href="../versionhistory.php">Version History</a></span>
         <span class="link"><a href="../examples.php">Examples</a></span>
         <span class="link"><a href="https://driftwoodinteractive.com/fmpda">Download</a></span>
      </div>
      </h2>

      <?php echo fmGetLog(); ?>
      <br>

      <div id="footer">
         <a href="http://www.driftwoodinteractive.com"><img src="../img/di.png" height="32" width="128" alt="Driftwood Interactive" style="vertical-align:text-bottom"></a>
      </div>
   </body>
</html>
