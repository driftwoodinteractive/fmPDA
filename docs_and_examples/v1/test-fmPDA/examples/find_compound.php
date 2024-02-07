<?php
// *********************************************************************************************************************************
//
// find_compound.php
//
// This test does a compound Find for project records. It omits any with 'Test' in the name or includes records with a ColorIndex of 5.
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

$findCommand = $fm->newCompoundFindCommand('Web_Project');
$findCommand->addSortRule('Name', 1, FILEMAKER_SORT_DESCEND);

$findRequest = $fm->newFindRequest('Web_Project');

$findRequest->addFindCriterion('Name', 'Test');
$findRequest->setOmit(true);
$findCommand->add(1, $findRequest);

$findRequest->clearFindCriteria();
// $findRequest->addFindCriterion('Date_Start', '9/1/2017');
$findRequest->addFindCriterion('ColorIndex', '5');
$findCommand->add(2, $findRequest);

$findCommand->setRange(0, 200);

$result = $findCommand->execute();

if (! fmGetIsError($result)) {
   fmLogger($result->getFetchCount() .' record(s)');
   $records = $result->getRecords();
   foreach ($records as $record) {
      fmLogger('Project Name = '. $record->getField('Name') .' ColordIndex = '. $record->getField('ColorIndex'));
   }
}
else {
   fmLogger('Error = '. $result->getCode() .' Message = '. $result->getMessage());
}

echo fmGetLog();

?>

