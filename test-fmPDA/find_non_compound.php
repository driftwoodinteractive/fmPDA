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

$findCommand = $fm->newFindCommand('PHP_Project');

//$findCommand->addFindCriterion('Name', 'Test');
//$findCommand->addFindCriterion('ColorIndex', 7);
$findCommand->addFindCriterion('Date_Start', '7/7/2017');
//$findCommand->setOmit(true);

$findCommand->addSortRule('Name', 1, FILEMAKER_SORT_DESCEND);
//$findCommand->setRange(1, 3);

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

$findCommand->clearFindCriteria();
$findCommand->addFindCriterion('Name', 'BAD-ROBOT');           // Something it won't find

$result = $findCommand->execute();

if (fmGetIsValid($result)) {
   $records = $result->getRecords();
   foreach ($records as $record) {
      fmLogger($record->getField('Name'));
   }
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
      <link href="../css/normalize.css" rel="stylesheet" />
      <link href="../css/styles.css" rel="stylesheet" />
   </head>
   <body>

      <h1 id="header">
         fmPDA <span style="color: #ff0000;">&#9829;</span><br>
         Simple (Non-Compound) Find
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

      <div id="footer">
         <a href="http://www.driftwoodinteractive.com"><img src="../img/di.png" height="32" width="128" alt="Driftwood Interactive" style="vertical-align:text-bottom"></a>
      </div>
   </body>
</html>
