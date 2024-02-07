<?php
// *********************************************************************************************************************************
//
// fmEdit.class.php
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

require_once 'fmAddEdit.class.php';

// *********************************************************************************************************************************
class fmEdit extends fmAddEdit
{
   public $modificationID;

   function __construct($fm, $layout, $recordID = '', $fieldValues = array())
   {
      parent::__construct($fm, $layout, $fieldValues);

      $this->setModificationId('');
      $this->setRecordId($recordID);

      return;
   }

   function execute($returnRecord = true)
   {
      $apiResult = $this->fm->apiEditRecord($this->layout, $this->recordID, $this->fields, $this->getAPIParams());

      if ($this->fm->getTranslateResult()) {
         if (fmGetIsError($apiResult)) {
            $result = $apiResult;
         }
         else if ($returnRecord) {
            $apiResult = $this->fm->getRecordById($this->layout, $this->recordID);
            if (! fmGetIsError($apiResult)) {
               $result = $this->fm->newResult($this->layout, array($apiResult->data));   // Convert fmRecord into a fmResult
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

      return $result;
   }

   function setModificationId($modificationID = '')
   {
      $this->modificationID = $modificationID;
   }

}

?>
