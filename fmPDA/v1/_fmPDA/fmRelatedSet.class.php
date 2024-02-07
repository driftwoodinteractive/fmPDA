<?php
// *********************************************************************************************************************************
//
// fmRelatedSet.class.php
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

class fmRelatedSet
{
   public $relatedSetName;
   public $data;
   public $layout;
   public $fm;

   function __construct($fm, $layout, $relatedSetName, $data = array())
   {
      $this->fm = $fm;
      $this->layout = $layout;
      $this->relatedSetName = $relatedSetName;
      $this->data = $data;
   }

   private function getFieldNames()
   {
      $fields = array();

      if ((count($this->data) > 0)) {
         $allfields = array_keys($this->data[0]);

         $fields = array();
         foreach ($allfields as $field) {
            if (substr($field, -1, 1) == ')') {                                  // If it's a repeating field, remove (nnn)
               $pieces = explode('(', $field);
               $field = $pieces[0];
            }
            $fields[$field] = null;                                              // Using the field as a key will overwrite for repeating fields
         }
      }
      return $fields;
   }

   function getField($fieldName)
   {
      if (array_key_exists($field, $this->data)) {
         return new fmField($this->fm, $this->layout, $fieldName);
      }
      else {
         return $this->fm->newError('Field is missing', FM_ERROR_FIELD_IS_MISSING);
      }
   }

   function getFields()
   {
      $fields = $this->getFieldNames();

      foreach ($fields as $fieldName => $field) {
         $fields[$fieldName] = new fmField($this->fm, $this->layout, $fieldName);      // Using the field as a key will overwrite for repeating fields
      }

      return $fields;
   }

   function getName()
   {
      return $this->relatedSetName;
   }

   function listFields()
   {
      return array_keys($this->getFieldNames());
   }

}

?>
