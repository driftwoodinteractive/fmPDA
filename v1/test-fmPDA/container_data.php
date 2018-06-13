<?php
// *********************************************************************************************************************************
//
// container_data.php
//
// This test gets a record and displays the image and name. Note that the need for the old-style 'Container Bridge' file is
// no longer necessary. The Data API returns a fully qualified URL you can put directly into an <img tag. It's unclear how long
// this URL is valid for, as each time you request the record you're given a new URL.
// It's likely you won't be able to cache these URLs for too long.
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

$fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

$photoURL = '';
$photoHW = '';
$photoName = '';

$result = $fm->getRecordById('Web_Project', 1);
if (fmGetIsValid($result)) {
// $photoURL = $fm->getContainerData($result->getFieldUnencoded('Photo'));    // No need to do this and then use a 'Container Bridge' PHP script
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
         <span style="float: right;">Version <strong>1</strong> (FileMaker Server 17+)</span>
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
               fmPDA::getRecordById()
            </div>
            <div class="main-header main-sub-header-1" style="text-align: left;">
               No more getContainerData() !
               <pre><code class="language-php">
                  $fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);<br>

                  $result = $fm->getRecordById('Web_Project', 1);
                  if (fmGetIsValid($result)) {
                     $photoURL = $result->getFieldUnencoded('Photo');
                  }
               </code></pre>
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
         javascript:ExpandDropDown("fmPDA");
      </script>

   </body>
</html>
