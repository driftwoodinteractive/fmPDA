<?php
// *********************************************************************************************************************************
//
// fmLayout.class.php
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

class fmLayout
{
   public $data;
   public $layout;
   public $fm;

   // *********************************************************************************************************************************
   function __construct($fm, $layout, $data = array())
   {
      $this->fm = $fm;
      $this->layout = $layout;
      $this->data = $data;

      return;
   }

   // *********************************************************************************************************************************
   function getName()
   {
      return $this->layout;
   }

   // *********************************************************************************************************************************
   function getDatabase()
   {
      return $this->fm->database;
   }

   // *********************************************************************************************************************************
   function listFields()
   {
      return array_keys($this->getFieldNames());
   }

   // *********************************************************************************************************************************
   function getField($fieldName)
   {
      if (array_key_exists($field, $this->data[FM_FIELD_DATA])) {
         return new fmField($this->fm, $this->layout, $fieldName);
      }
      else {
         return $this->fm->newError('Field is missing', FM_ERROR_FIELD_IS_MISSING);
      }
   }

   // *********************************************************************************************************************************
   private function getFieldNames()
   {
      $fields = array();

      if ((count($this->data) > 0)) {
         $allFields = array_keys($this->data[FM_FIELD_DATA]);

         $fields = array();
         foreach ($allFields as $field) {
            if (substr($field, -1, 1) == ')') {                                  // If it's a repeating field, remove (nnn)
               $pieces = explode('(', $field);
               $field = $pieces[0];
            }
            $fields[$field] = null;                                              // Using the field as a key will overwrite for repeating fields
         }
      }

      return $fields;
   }

   // *********************************************************************************************************************************
   function getFields()
   {
      $fields = $this->getFieldNames();

      foreach ($fields as $field => $value) {
         $fields[$field] = new fmField($this->fm, $this->layout, $field);     // Using the field as a key will overwrite for repeating fields
      }

      return $fields;
   }

   // *********************************************************************************************************************************
   function listRelatedSets()
   {
      $relatedSets = array();

      if (array_key_exists(FM_PORTAL_DATA, $this->data)) {
         $relatedSets = array_keys($this->data[FM_PORTAL_DATA]);
      }

      return $relatedSets;
   }

   // *********************************************************************************************************************************
   function getRelatedSet($relatedSet)
   {
      if (array_key_exists(FM_PORTAL_DATA, $this->data) && array_key_exists($relatedSet, $this->data[FM_PORTAL_DATA])) {
         $result = new fmRelatedSet($this->fm, $this->layout, $relatedSet, $this->data[FM_PORTAL_DATA][$relatedSet]);
      }
      else {
         $result = $this->fm->newError('RelatedSet Not Found');
      }

      return $result;
   }

   // *********************************************************************************************************************************
   function getRelatedSets()
   {
      $result = array();

      foreach ($this->data[FM_PORTAL_DATA] as $relatedSet -> $data) {
         $result[$relatedSet] = new fmRelatedSet($this->fm, $this->layout, $relatedSet, $data);
      }

      return $result;
   }

}

?>
