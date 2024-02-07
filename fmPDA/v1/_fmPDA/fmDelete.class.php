<?php
// *********************************************************************************************************************************
//
// fmDelete.class.php
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
class fmDelete extends fmCommand
{
   public $recordID;

   function __construct($fm, $layout, $recordID)
   {
      parent::__construct($fm, $layout);

      $this->recordID = $recordID;

      return;
   }

   function execute()
   {
      $record = $this->fm->getRecordById($this->layout, $this->recordID);        // Old api returns record added so grab it now

      if ($this->fm->getTranslateResult()) {
         if (fmGetIsValid($record)) {
            $apiResult = $this->fm->apiDeleteRecord($this->layout, $this->recordID, $this->getAPIParams());

            if (fmGetIsError($apiResult)) {
               $result = $apiResult;
            }
            else {
               $result = $this->fm->newResult($this->layout, array($record->data));   // Convert fmRecord into a fmResult
            }
         }
         else {
            $result = $this->fm->newError('Record is missing', FM_ERROR_RECORD_IS_MISSING);
         }
      }
      else {
         $result = $record;
      }

      return $result;
   }
}

?>
