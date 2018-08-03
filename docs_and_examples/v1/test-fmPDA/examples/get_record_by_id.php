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
//
$options = array(
   'authentication'        => array(                                           // Set list of external databases we want to connect to
                                    'sources' => array(
                                          array(
                                             'database'  => 'externaldb',      // Do NOT include .fmpNN extension
                                             'username'  => 'externaluser',
                                             'password'  => 'externalpass'
                                          )
                                       )
                                    )
);

$fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD, $options);

$output = '';
$output .= GetTheRecord($fm, 1);
$output .= GetTheRecord($fm, 55555);            // Will fail - record doesn't exist


function GetTheRecord($fm, $recordID, $showRecordData = false)
{
   $output = '';

   $result = $fm->getRecordById('Web_Project', $recordID);
   if (! fmGetIsError($result)) {
      if ($showRecordData) {
         $output .= $result->dumpRecord();
      }
      else {
         $output .= '<strong>Record ID '. $recordID .' Name:</strong><br>'. $result->getFieldUnencoded('Name') .'<br><br>';
      }
   }
   else if (is_null($result)) {
      $output .= 'Record not found.';
   }
   else {
      $output .= '<strong>Record ID '. $recordID .' (Error):</strong><br>code='. $result->getCode() .' message='. $result->getMessage() .'<br><br>';
  }

   return $output;
}

echo fmGetLog();

?>
