<?php
// *********************************************************************************************************************************
//
// delete.php
//
// This test creates a record in the database and then deletes it.
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

// First, add a record to the database, get the returned recordID, then delete it.
$addCommand = $fm->newAddCommand('PHP_Project');
$addCommand->setField('Name', 'Project '. rand());
$addCommand->setField('Date_Start', date('m/d/Y'));
$addCommand->setField('Date_End', date('m/d/Y'));
$addCommand->setField('ColorIndex', 999);

$result = $addCommand->execute();

if (fmGetIsValid($result)) {
   fmLogger('Project Name = '. $result->getField('Name'));
//    fmLogger($result->dumpRecord());
   $recordID = $result->getRecordId();
   if ($recordID != '') {
      $deleteCommand = $fm->newDeleteCommand('PHP_Project', $recordID);
      $result = $deleteCommand->execute();

      if (fmGetIsValid($result)) {
         fmLogger($result->dumpRecord());
      }
      else {
         fmLogger('Error = '. $result->getCode() .' Message = '. $result->getMessage());
      }
   }
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
         Add, then Delete Record
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
