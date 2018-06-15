<?php
// *********************************************************************************************************************************
//
// find_all.php
//
// This test does a Find All, sorting the records in descending order by project name.
// If you uncomment the setRange() call, it will restrict the found set to records 3-5 of the found set.
//
// The test also does a FindAll on a table with no records. This shows an fmResult is returned with no records.
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
                  'version'             => DATA_API_VERSION,
                  'storeTokenInSession' => true,
                  'token'               => ''  //'BAD-ROBOT'
               );

$fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD, $options);

$findAllCommand = $fm->newFindAllCommand('Web_Project');

$findAllCommand->addSortRule('Name', 1, FILEMAKER_SORT_DESCEND);

// Uncomment this line to restrict where and the number of records returned.
//$findAllCommand->setRange(2, 3);

// If you want to test the result layout, uncomment this line and you'l end up with no records!
//$findAllCommand->setResultLayout('Web_Empty');

$result = $findAllCommand->execute();

if (fmGetIsValid($result)) {
   fmLogger('getTableRecordCount: '. $result->getTableRecordCount() .' record(s)');
   fmLogger('getFoundSetCount: '. $result->getFoundSetCount() .' record(s)');
   fmLogger('getFetchCount: '. $result->getFetchCount() .' record(s)');

   fmLogger('');
   $record = $result->getFirstRecord();
   if (! is_null($record)) {
      fmLogger('First record: Project Name = '. $record->getField('Name'));
   }

   fmLogger('');
   $record = $result->getLastRecord();
   if (! is_null($record)) {
      fmLogger('Last record: Project Name = '. $record->getField('Name'));
   }

   fmLogger('');
   $records = $result->getRecords();
   foreach ($records as $record) {
      fmLogger('Project Name = '. $record->getField('Name'));
   }
}
else {
   fmLogger('Error = '. $result->getCode() .' Message = '. $result->getMessage());
}


fmLogger('');
fmLogger('');
fmLogger('');
fmLogger('************************************************************************************************************************************');
fmLogger('Find All on empty table');

$findAllCommand = $fm->newFindAllCommand('Web_Empty');

$result = $findAllCommand->execute();

if (fmGetIsValid($result)) {
   fmLogger('Find on empty table: '. $result->getFetchCount() .' record(s)');
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
               fmPDA::newFindAllCommand()
            </div>
            <div class="main-header main-sub-header-1" style="text-align: left;">
               Find All Records
               <pre><code class="language-php">
                  $fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

                  $findAllCommand = $fm->newFindAllCommand('Web_Project');
                  $findAllCommand->addSortRule('Name', 1, FILEMAKER_SORT_DESCEND);

                  $result = $findCommand->execute();
               </code></pre>
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
         javascript:ExpandDropDown("fmPDA");
      </script>

   </body>
</html>
