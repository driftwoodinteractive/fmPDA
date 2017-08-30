<?php
// *********************************************************************************************************************************
//
// container_data_url.php
//
// This test gets a record and displays the image and name. The Data API returns a fully qualified URL you can put directly
// into an <img tag. It's unclear how long this URL is valid for, as each time you request the record you're given a new URL.
// It's likely you won't be able to cache these URLs for too long.
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

$fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD, 'PHP_Project');

$photoURL = '';
$photoHW = '';
$photoName = '';

$result = $fm->getRecordById('PHP_Project', 8);
if (fmGetIsValid($result)) {

// $photoURL = $fm->getContainerDataURL($result->getFieldUnencoded('Photo')); // No need to do this
   $photoURL = $result->getFieldUnencoded('Photo');                           // Just get the URL and off you go!

   $photoHW = $result->getFieldUnencoded('c_PhotoHWHTML');
   $photoName = $result->getFieldUnencoded('PhotoName');

   fmLogger('Note that each time you retrieve the record, the URL changes:');
   fmLogger($photoURL);
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
      <title>fmPDA &#9829;</title>
      <link href="../css/normalize.css" rel="stylesheet" />
      <link href="../css/styles.css" rel="stylesheet" />
   </head>
   <body>

      <h1 id="header">
         fmPDA <span style="color: #ff0000;">&#9829;</span><br>
         No more getContainerDataURL() !
      </h1>

      <h2>
      <div style="text-align: center;">
         <span class="link"><a href="../index.php">Introduction</a></span>
         <span class="link"><a href="../versionhistory.php">Version History</a></span>
         <span class="link"><a href="../examples.php">Examples</a></span>
         <span class="link"><a href="https://driftwoodinteractive.com/fmpda">Download</a></span>
      </div>
      </h2>

      <?php
         if ($photoURL != '') {
            echo '<img src="'. $photoURL .'" '. $photoHW .' alt="'. $photoName .'" /><br>';
            echo $photoName .'<br><br>';
         }
      ?>

      <?php echo fmGetLog(); ?>

      <div id="footer">
         <a href="http://www.driftwoodinteractive.com"><img src="../img/di.png" height="32" width="128" alt="Driftwood Interactive" style="vertical-align:text-bottom"></a>
      </div>
   </body>
</html>
