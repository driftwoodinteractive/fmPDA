<?php
// *********************************************************************************************************************************
//
// find_non_compound.php
//
// This test does a 'simple' (non-compound) Find for project records with a start dat of 7/7/2017
//
// The second test is a find that will return nothing. This demonstrates an fmError object returned as the result.
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

$findCommand = $fm->newFindCommand('Web_Project');

//$findCommand->addFindCriterion('Name', 'Test');
//$findCommand->addFindCriterion('ColorIndex', 7);
$findCommand->addFindCriterion('Date_Start', '9/1/2017');
//$findCommand->setOmit(true);

$findCommand->addSortRule('Name', 1, FILEMAKER_SORT_DESCEND);
//$findCommand->setRange(1, 3);

$findCommand->setScript('Test', 'test-params');
$findCommand->setPreCommandScript('Precommand', 'precommand-params');
$findCommand->setPreSortScript('Presort', 'presort-params');

$result = $findCommand->execute();

if (fmGetIsValid($result)) {
   fmLogger($result->getFetchCount() .' record(s)');
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
fmLogger('Find that will not return any records (should generate an fmError object (401)');

$findCommand->setScript('', '');
$findCommand->setPreCommandScript('', '');
$findCommand->setPreSortScript('', '');

$findCommand->clearFindCriteria();
$findCommand->addFindCriterion('Name', 'BAD-ROBOT');           // Something it won't find

$result = $findCommand->execute();

if (fmGetIsValid($result)) {
   $records = $result->getRecords();
   foreach ($records as $record) {
      fmLogger($record->getField('Name'));
   }
   fmLogger($records);
//    fmLogger($result);
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
               fmPDA::newFindCommand()
            </div>
            <div class="main-header main-sub-header-1" style="text-align: left;">
               Non-Compound Find
               <pre><code class="language-php">

                  $fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

                  $findCommand = $fm->newFindCommand('Web_Project');

                  $findCommand->addFindCriterion('Date_Start', '9/1/2017');
                  $findCommand->addSortRule('Name', 1, FILEMAKER_SORT_DESCEND);

                  $findCommand->setScript('Test', 'test-params');
                  $findCommand->setPreCommandScript('Precommand', 'precommand-params');
                  $findCommand->setPreSortScript('Presort', 'presort-params');

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
