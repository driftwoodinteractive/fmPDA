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

$fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

$findAllCommand = $fm->newFindAllCommand('Web_Project');

$findAllCommand->addSortRule('Name', 1, FILEMAKER_SORT_DESCEND);

// Uncomment this line to restrict where and the number of records returned.
// $findAllCommand->setRange(0, 200);

// If you want to test the result layout, uncomment this line and you'l end up with no records!
//$findAllCommand->setResultLayout('Web_Empty');

$result = $findAllCommand->execute();

if (! fmGetIsError($result)) {
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

if (! fmGetIsError($result)) {
   fmLogger('Find on empty table: '. $result->getFetchCount() .' record(s)');
}
else {
   fmLogger('Error = '. $result->getCode() .' Message = '. $result->getMessage());
}

echo fmGetLog();

?>
