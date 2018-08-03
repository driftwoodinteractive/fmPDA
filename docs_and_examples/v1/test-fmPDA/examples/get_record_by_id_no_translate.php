<?php
// *********************************************************************************************************************************
//
// get_record_by_id_no_translate.php
//
// This is the same as get_record_by_id_no_translate.php except fmPDA returns the Data API data structures.
//
// This example uses getRecordById().
//    - Retrieves one record and dump the contents to the log.
//    - Retrieves a different record and displays the project name in the log.
//    - Attempts to retrieve a record which doesn't exist, return a null result to match the old API.
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

// These are optional parameters to the fmPDA constructor.
$options = array(
                  'translateResult'     => false                // Do *not* return old-style objects - return DataAPI structure
               );

$fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD, $options);


//
// Get a record by ID 1
//
$result = $fm->getRecordById('Web_Project', 1);
if (! $fm->getIsError($result)) {
   fmLogger('Project Name = '. $result[FM_RESPONSE][FM_DATA]['0'][FM_FIELD_DATA]['Name']);
//   fmLogger($result);
}
else {
   $errorInfo = $fm->getMessageInfo($result);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}


//
// Get a record by ID 5
//
fmLogger('');
fmLogger('');
fmLogger('');
fmLogger('************************************************************************************************************************************');
$result = $fm->getRecordById('Web_Project', 5);
if (! $fm->getIsError($result)) {
   fmLogger('Project Name = '. $result[FM_RESPONSE][FM_DATA]['0'][FM_FIELD_DATA]['Name']);
}
else {
   $errorInfo = $fm->getMessageInfo($result);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}


//
// Get a record by ID 55555 - this will fail as the record doesn't exist.
//
fmLogger('');
fmLogger('');
fmLogger('');
fmLogger('************************************************************************************************************************************');
$result = $fm->getRecordById('Web_Project', 55555);
if (! $fm->getIsError($result)) {
   fmLogger('Project Name = '. $result[FM_RESPONSE][FM_DATA]['0'][FM_FIELD_DATA]['Name']);
}
else {
   $errorInfo = $fm->getMessageInfo($result);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}

echo fmGetLog();

?>
