<?php
// *********************************************************************************************************************************
//
// fmUpload.class.php
//
// This class isn't really necessary - you can use this if you're more comfortable with the older API.
// For performance reasons it's probably best if you call apiUploadContainer() if you're passing the file contents.
// If you're passing the path there should be no performance penalty.
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
class fmUpload extends fmCommand
{
   public $fieldName;
   public $fieldRepetition;
   public $file;

   function __construct($fm, $layout, $recordID, $fieldName, $fieldRepetition, $file)
   {
      parent::__construct($fm, $layout);

      $this->recordID = $recordID;
      $this->fieldName = $fieldName;
      $this->fieldRepetition = $fieldRepetition;
      $this->file = $file;

      return;
   }

   // We mirror what the fmEdit command does by (optionally, but ON by default) returning the record we just 'edited'.
   function execute($returnRecord = true)
   {
      $apiResult = $this->fm->apiUploadContainer($this->layout, $this->recordID, $this->fieldName, $this->fieldRepetition, $this->file);

      if ($this->fm->getTranslateResult()) {
         if (fmGetIsError($apiResult)) {
            $result = $apiResult;
         }
         else if ($returnRecord) {
            $record = $this->fm->getRecordById($this->layout, $this->recordID);
            $result = $this->fm->newResult($this->layout, array(FM_DATA => array($record->data)));   // Convert fmRecord into a fmResult
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

}


?>
