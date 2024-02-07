<?php
// *********************************************************************************************************************************
//
// fmRecord.class.php
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

// *********************************************************************************************************************************
function jsonEscapeValue_arraywalk(&$value, $key)
{
   if (!is_array($value)) {
      $value = (string)$value;
   }
}

// *********************************************************************************************************************************
class fmRecord
{
   public $data;
   public $editedFields;
   public $layout;
   public $fm;

   function __construct($fm, $layout, $data = array())
   {
      $data[FM_FIELD_DATA] = array_key_exists(FM_FIELD_DATA, $data) ? $data[FM_FIELD_DATA] : array();
      $data[FM_PORTAL_DATA] = array_key_exists(FM_PORTAL_DATA, $data) ? $data[FM_PORTAL_DATA] : array();
      $data[FM_RECORD_ID] = array_key_exists(FM_RECORD_ID, $data) ? $data[FM_RECORD_ID] : '';
      $data[FM_MOD_ID] = array_key_exists(FM_MOD_ID, $data) ? $data[FM_MOD_ID] : '';

      $this->fm = $fm;
      $this->layout = $layout;
      $this->data = $data;

      $this->clearEditedFields();

      array_walk($this->data[FM_FIELD_DATA], 'jsonEscapeValue_arraywalk');

      foreach ($this->data[FM_PORTAL_DATA] as $relatedSet) {
         array_walk($relatedSet, 'jsonEscapeValue_arraywalk');
      }

      return;
   }

   public function clearEditedFields()
   {
      $this->editedFields = array();
      return;
   }

   function commit()
   {
      $recordID = $this->getRecordId();

      if ($recordID == '') {                                                     // New record?
         $addEditCommand = new fmAdd($this->fm, $this->layout, $this->data[FM_FIELD_DATA]);
      }
      else {                                                                     // Must be an edit
         $addEditCommand = new fmEdit($this->fm, $this->layout, $recordID, $this->editedFields);
      }

      $result = $addEditCommand->execute(false /* Don't return record */);

      // Old API just returns true from a commit but does update internal info
      if ($recordID == '') {                                                     // New record?
         $this->data[FM_RECORD_ID] = $addEditCommand->recordID;
         $this->data[FM_MOD_ID] = 1;
      }
      else {
         $this->data[FM_MOD_ID] = $this->data[FM_MOD_ID]++;
         $this->clearEditedFields();
      }

      return true;
   }

   function delete()
   {
      return $this->fm->apiDeleteRecord($this->layout, $this->getRecordId());
   }

   private function getFieldData($field, $repetition = 0, $decode = true)
   {
      $result = '';

      if (array_key_exists($field .'('. $repetition .')', $this->data[FM_FIELD_DATA])) {              // Try exact repetition
         $result =  $this->data[FM_FIELD_DATA][$field .'('. $repetition .')'];
      }

      else if (($repetition == 0) && array_key_exists($field .'(1)', $this->data[FM_FIELD_DATA])) {   // See if it's really rep 1
         $result =  $this->data[FM_FIELD_DATA][$field .'(1)'];
      }

      else if (array_key_exists($field, $this->data[FM_FIELD_DATA])) {                                // No repetition
         $result =  $this->data[FM_FIELD_DATA][$field];
      }

      else {
         fmLogger(__METHOD__ .'(): '. $field .' does not exist. Is it missing from your FileMaker layout?');
      }

      if (($result != '') && $decode) {
         $result = htmlspecialchars($result);
      }

      return $result;
   }

   function getLayout()
   {
      return new fmLayout($this->fm, $this->layout, $this->data);
   }

   function getFields()
   {
      $fields = array();

      if ((count($this->data) > 0)) {
         $allfields = array_keys($this->data[FM_FIELD_DATA]);

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

   function getField($field, $repetition = 0)
   {
      return $this->getFieldData($field, $repetition, true/* Decode */);
   }

   function getFieldUnencoded($field, $repetition = 0)
   {
      return $this->getFieldData($field, $repetition, false/* Don't decode */);
   }

   // If the field is a date field, the timestamp is
   // for the field date at midnight. It the field is a time field,
   // the timestamp is for that time on January 1, 1970. Timestamp
   // (date and time) fields map directly to the UNIX timestamp. If the
   // specified field is not a date or time field, or if the timestamp
   // generated would be out of range, then this method returns a
   //
   // Note that $fieldType is a new parameter to this API - it does not exist in the old API.
   // The Data API does not return metadata so we can not determine the field type on our own.
   //
   function getFieldAsTimestamp($field, $repetition = 0, $fieldType = 'timestamp')
   {
      $fieldData = $this->getFieldData($field, $repetition, false/* Don't decode */);

      switch($fieldType) {
         case 'date': {
            $dateParts = explode('/', $fieldData);

            if (count($dateParts) != 3) {
               $result = $this->fm->newError('Failed to parse "'. $field .'" as a FileMaker date value.');
            }
            else {
               $result = mktime(0, 0, 0, $dateParts[0], $dateParts[1], $dateParts[2]);
               if ($result === false) {
                  $result = $this->fm->newError('Failed to convert "'. $field .'" to a UNIX timestamp.');
               }
            }
            break;
         }

         case 'time': {
            $timeParts = explode(':', $fieldData);
            if (count($timeParts) != 3) {
               $result = $this->fm->newError('Failed to parse "'. $field .'" ('. $fieldData .') as a FileMaker time value.');
            }
            else {
               $result = mktime($timeParts[0], $timeParts[1], $timeParts[2], 1, 1, 1970);
               if ($result === false) {
                  $result = $this->fm->newError('Failed to convert "'. $field .'" ('. $fieldData .')" to a UNIX timestamp.');
               }
            }
            break;
         }

         case 'timestamp': {
            $result = strtotime($fieldData);
            if ($result === false) {
               $result = $this->fm->newError('Failed to convert "'. $field .'" ('. $fieldData .')" to a UNIX timestamp.');
            }
            break;
         }

         default: {
            $result = $this->fm->newError('Only time, date, and timestamp fields can be converted to UNIX timestamps.');
            break;
         }
      }

      return $result;
   }

   function getRecordId()
   {
      return $this->data[FM_RECORD_ID];
   }

   function getModificationId()
   {
      return $this->data[FM_MOD_ID];
   }

   function getRelatedSets()
   {
      $relatedSets = array();

      if (count($this->data) > 0) {
         $recordData = $this->data[0];
         if (! is_null($recordData)) {
            $relatedSets = array_keys($recordData[FM_PORTAL_DATA]);
         }
      }

      return $relatedSets;
   }

   function getRelatedSet($relatedSet)
   {
      $result = array();

      if (array_key_exists($relatedSet, $this->data[FM_PORTAL_DATA])) {
         foreach($this->data[FM_PORTAL_DATA][$relatedSet] as $data) {
            $relatedRecord = array();
            $relatedRecord[FM_FIELD_DATA] = $data;                                  // Related data becomes the record data
            $relatedRecord[FM_PORTAL_DATA] = array();
            $relatedRecord[FM_RECORD_ID] = $data[FM_RECORD_ID];
            $relatedRecord[FM_MOD_ID] = '';
            $result[] = new fmRecord($this->fm, $this->layout, $relatedRecord);
         }
      }
      else {
         $result = $this->fm->newError('Related set "'. $relatedSet .'" not present.');
      }

      return $result;
   }

   function setField($field, $value, $repetition = 0)
   {
      if ($repetition == 0) {
         $fieldName = $field;
      }
      else {
         $fieldName = $field .'('. $repetition .')';
      }

      if (array_key_exists($fieldName, $this->data[FM_FIELD_DATA])) {
         $this->editedFields[$fieldName] = (string)$value;
         $this->data[FM_FIELD_DATA][$fieldName] = (string)$value;
      }
      else {
         fmLogger(__METHOD__ .'(): '. $field .' does not exist. Is it missing from your FileMaker layout?');
      }

      return $value;
   }

   // Note that $fieldType is a new parameter to this API - it does not exist in the old API.
   // The Data API does not return metadata so we can not determine the field type on our own.
   //
   function setFieldFromTimestamp($field, $timestamp, $repetition = 0, $fieldType = 'timestamp')
   {
      switch ($fieldType) {
         case 'date':      { $result = $this->setField($field, date('m/d/Y', $timestamp), $repetition);        break; }
         case 'time':      { $result = $this->setField($field, date('H:i:s', $timestamp), $repetition);        break; }
         case 'timestamp': { $result = $this->setField($field, date('m/d/Y H:i:s', $timestamp), $repetition);  break; }
         default:          { $result = $this->fm->newError('Only time, date, and timestamp fields can be set to the value of a timestamp.'); break; }
      }

      return $result;
   }

   // Handy debugging function to see all the fields and related data in a record. Typically sent to the fmLogger::log() method.
   function dumpRecord()
   {
      $data = 'Record ('. FM_RECORD_ID .' = '. $this->getRecordId() .', '. FM_MOD_ID .' = '. $this->getModificationId() .')'. '<br>';

      $data .= print_r($this->data[FM_FIELD_DATA], 1);

      foreach ($this->data[FM_PORTAL_DATA] as $relatedSetName => $relatedSetData) {
         $data .= '['. $relatedSetName .']' .'<br>';
         $data .= print_r($relatedSetData, 1);
      }

      return $data;
   }

}

?>
