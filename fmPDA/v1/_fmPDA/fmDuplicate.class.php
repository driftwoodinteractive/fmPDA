<?php
// *********************************************************************************************************************************
//
// fmDuplicate.class.php
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

require_once 'fmCommand.class.php';

// *********************************************************************************************************************************
class fmDuplicate extends fmCommand
{
   function __construct($fm, $layout, $recordID, $duplicateScript)
   {
      parent::__construct($fm, $layout);

      if ($duplicateScript == '') {
         fmLogger(__METHOD__ .'(): REQUIRES a script to be passed to do the actual Duplicate Record script step.');
      }

      $this->recordID = $recordID;
      $this->script = $duplicateScript;

      return;
   }

   function execute()
   {
      $apiResult = $this->fm->apiGetRecord($this->layout, $this->recordID, '', $this->getAPIParams());

      if ($this->fm->getTranslateResult()) {
         if (! fmGetIsError($apiResult)) {

            // The Data API enforces returning the record you requested regardless of what your script does,
            // so your Duplicate script should return the recordID of the duplicated record. Then we do another
            // getRecordByID to return the duplicated record data.
            $response = $this->fm->getResponse($apiResult);
            $apiResult = $this->fm->apiGetRecord($this->layout, $response['scriptResult']);

            if ($this->fm->getTranslateResult()) {
               if (! fmGetIsError($apiResult)) {
                  $responseData = $this->fm->getResponseData($apiResult);
                  $result = new fmRecord($this->fm, $this->layout, array_key_exists(0, $responseData) ? $responseData[0] : array());
               }
               else {
                  $result = $apiResult;
               }
            }
            else {
               $result = $apiResult;
            }
         }
         else {
            $result = $apiResult;
         }
      }
      else {
         $result = $apiResult;
      }

      return $result;
   }
}

?>
