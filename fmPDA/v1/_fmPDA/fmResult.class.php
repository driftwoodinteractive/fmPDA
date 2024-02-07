<?php
// *********************************************************************************************************************************
//
// fmResult.class.php
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

class fmResult
{
   public $data;
   public $layout;
   public $fm;

   function __construct($fm, $layout, $data = array())
   {
      $this->fm = $fm;
      $this->layout = $layout;
      $this->data = $data;
}

   function getLayout()
   {
      $result = null;

      $record = $this->getFirstRecord();
      if (! is_null($record)) {
         $result = new fmLayout($this->fm, $this->layout, $record->data);
      }
      else {
         $result = new fmLayout($this->fm, $this->layout);                    // No records in result, no data to pass except layout name
      }

      return $result;
   }

   function getRecords()
   {
      $records = array();
      foreach($this->data as $data) {
         $records[] = new fmRecord($this->fm, $this->layout, $data);
      }

      return $records;
   }

   function getFields()
   {
      $fields = array();

      if ((count($this->data) > 0)) {
         $allfields = array_keys($this->data[0][FM_FIELD_DATA]);

         $fields = array();
         foreach ($allfields as $field) {
            if (substr($field, -1, 1) == ')') {                            // If it's a repeating field, remove (nnn)
               $pieces = explode('(', $field);
               $field = $pieces[0];
            }
            $fields[$field] = $field;                                      // Using the field as a key will overwrite for repeating fields
         }
         $fields = array_keys($fields);                                    // Now we'll have a unique list of field names
      }
      return $fields;
   }

   function getRelatedSets()
   {
      $relatedSets = array();

      $recordData = $this->data[0];
      if (! is_null($recordData) && array_key_exists(FM_PORTAL_DATA, $recordData)) {
         $relatedSets = array_keys($recordData[FM_PORTAL_DATA]);
      }

      return $relatedSets;
   }

   function getTableRecordCount()
   {
      fmLogger(__METHOD__ .'(): is not supported by the Data API. Returning getFetchCount('. $this->getFetchCount() .')');
      return $this->getFetchCount();
   }

   function getFoundSetCount()
   {
      fmLogger(__METHOD__ .'(): is not supported by the Data API. Returning getFetchCount('. $this->getFetchCount() .')');
      return $this->getFetchCount();
   }

   function getFetchCount()
   {
      return count($this->data);
   }

   function getFirstRecord()
   {
      if (count($this->data) > 0) {
         return new fmRecord($this->fm, $this->layout, $this->data[0]);
      }
      else {
         return null;                                                         // This is what the old API returns
      }
   }

   function getLastRecord()
   {
      if (count($this->data) > 0) {
         return new fmRecord($this->fm, $this->layout, $this->data[count($this->data) - 1]);
      }
      else {
         return null;                                                         // This is what the old API returns
      }
   }

}

?>
