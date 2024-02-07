<?php
// *********************************************************************************************************************************
//
// fmDuplicate.class.php
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

require_once 'fmCommand.class.php';

// *********************************************************************************************************************************
class fmDuplicate extends fmCommand
{
   function __construct($fm, $layout, $recordID)
   {
      parent::__construct($fm, $layout);

      $this->recordID = $recordID;

      return;
   }

   function execute($returnRecord = true)
   {
      $apiResult = $this->fm->apiDuplicateRecord($this->layout, $this->recordID);

      if ($this->fm->getTranslateResult()) {
         if (! fmGetIsError($apiResult)) {

            $response = $this->fm->getResponse($apiResult);
            $this->recordID = (array_key_exists(FM_RECORD_ID, $response) ? $response[FM_RECORD_ID] : '');

            if ($returnRecord && ($this->recordID != '')) {
               $apiResult = $this->fm->getRecordById($this->layout, $this->recordID);  // Old api returns record added so grab it now
               if (! fmGetIsError($apiResult)) {
                  $result = $this->fm->newResult($this->layout, array(FM_DATA => array($apiResult->data)));   // Convert fmRecord into a fmResult
               }
               else {
                  $result = $apiResult;
               }
            }
            else {
               $result = true;
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
