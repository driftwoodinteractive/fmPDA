<?php
// *********************************************************************************************************************************
//
// get_record.php
//
// This example uses fmDataAPI::apiGetRecord().
//    - Retrieves one record one one record that doesn't exist to demonstrate error handling.
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

$output = '';

$fm = new fmDataAPI(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);


$output .= GetTheRecord($fm, 1);
$output .= GetTheRecord($fm, 55555);            // Will fail - record doesn't exist


function GetTheRecord($fm, $recordID, $showRecordData = false)
{
   $output = '';

   $params =
         array(
//            'limit'                  => '10',
//            'offset'                 => '3',
//            'sort'                   => array(array('fieldName' => 'Field1'), array('fieldName' => 'Field2', 'sortOrder' => 'descend')),
//             'script'                 => 'MoofScript',
//             'scriptParams'           => 'MoofScriptParam',
//             'scriptPrerequest'       => 'PrerequestScript',
//             'scriptPrerequestParams' => 'PrerequestScriptParam',
//             'scriptPresort'          => 'PresortScript',
//             'scriptPresortParams'    => 'PresortScriptParam',
//             'layoutResponse'         => 'ResponseLayout',
//             'portals'                => array('portal1', 'portal10'),
//            'portalLimits'           => array(array('name' => 'portal1', 'limit' => '7'), array('name' => 'portal10', 'limit' => '99')),
//            'portalOffsets'          => array(array('name' => 'portal1', 'offset' => '2'), array('name' => 'portal10', 'offset' => '33'))
         );

   $apiResult = $fm->apiGetRecord('Web_Project', $recordID, '', $params);
   if (! $fm->getIsError($apiResult)) {
      $responseData = $fm->getResponseData($apiResult);

      if ($showRecordData) {
         $output .= print_r($responseData, true);;
      }
      else {
         $output .= '<strong>Record ID '. $recordID .' Name:</strong><br>'. $responseData[0][FM_FIELD_DATA]['Name'] .'<br><br>';
      }
   }
   else {
      $errorInfo = $fm->getMessageInfo($apiResult);
      $output .= '<strong>Record ID '. $recordID .' (Error):</strong><br>code='. $errorInfo[0][FM_CODE] .' message='. $errorInfo[0][FM_MESSAGE] .'<br><br>';
   }

   return $output;
}


echo $output;

echo fmGetLog();

?>
