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

      if (array_key_exists(FM_DATA, $this->data)) {
         $data = $this->data[FM_DATA];

         foreach($data as $dataRecord) {
            $records[] = new fmRecord($this->fm, $this->layout, $dataRecord);
         }
      }

      return $records;
   }

   function getFields()
   {
      $fields = array();

      if (array_key_exists(FM_DATA, $this->data)) {
         $data = $this->data[FM_DATA];

         if ((count($data) > 0)) {
            $allfields = array_keys($data[0][FM_FIELD_DATA]);

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
      }

      return $fields;
   }

   function getRelatedSets()
   {
      $relatedSets = array();

      if (array_key_exists(FM_DATA, $this->data)) {
         $data = $this->data[FM_DATA];

         $recordData = $data[0];
         if (! is_null($recordData) && array_key_exists(FM_PORTAL_DATA, $recordData)) {
            $relatedSets = array_keys($recordData[FM_PORTAL_DATA]);
         }
      }

      return $relatedSets;
   }

   function getTableRecordCount()
   {
      $totalRecordCount = 0;

      if (array_key_exists(FM_DATA_INFO, $this->data)) {
         $dataInfo = $this->data[FM_DATA_INFO];

         if (array_key_exists('totalRecordCount', $dataInfo)) {
            $totalRecordCount = $dataInfo['totalRecordCount'];
         }
      }

      return $totalRecordCount;
   }

   function getFoundSetCount()
   {
      $foundCount = 0;

      if (array_key_exists(FM_DATA_INFO, $this->data)) {
         $dataInfo = $this->data[FM_DATA_INFO];

         if (array_key_exists('foundCount', $dataInfo)) {
            $foundCount = $dataInfo['foundCount'];
         }
      }

      return $foundCount;
   }

   function getFetchCount()
   {
      $returnedCount = 0;

      if (array_key_exists(FM_DATA_INFO, $this->data)) {
         $dataInfo = $this->data[FM_DATA_INFO];

         if (array_key_exists('returnedCount', $dataInfo)) {
            $returnedCount = $dataInfo['returnedCount'];
         }
      }

      return $returnedCount;
   }

   function getFirstRecord()
   {
      if (array_key_exists(FM_DATA, $this->data)) {
         $data = $this->data[FM_DATA];

         if (count($data) > 0) {
            return new fmRecord($this->fm, $this->layout, $data[0]);
         }
         else {
            return null;                                                         // This is what the old API returns
         }
      }
      else {
         return null;                                                           // This is what the old API returns
      }
   }

   function getLastRecord()
   {
      if (array_key_exists(FM_DATA, $this->data)) {
         $data = $this->data[FM_DATA];

         if (count($data) > 0) {
            return new fmRecord($this->fm, $this->layout, $data[count($data) - 1]);
         }
         else {
            return null;                                                         // This is what the old API returns
         }
      }
      else {
         return null;                                                           // This is what the old API returns
      }
   }

}

?>
