<?php
// *********************************************************************************************************************************
//
// fmCommand.class.php
//
// *********************************************************************************************************************************
//
// Copyright (c) 2017 Mark DeNyse
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
class fmCommand
{
   public $recordID;
   public $script;
   public $preScript;
   public $postScript;
   public $layout;
   public $fm;

   function __construct($fm, $layout)
   {
      $this->fm = $fm;
      $this->layout = $layout;
   }

   function execute()
   {
      fmLogger(__METHOD__ .'(): must be overridden.');
   }

   function getRecordId($recordId)
   {
      return $this->recordID;
   }

   function setResultLayout($layout)
   {
      fmLogger(__METHOD__ .'(): is not supported by the Data API.');
   }

   function setScript($scriptName, $scriptParameters = null)
   {
      fmLogger(__METHOD__ .'(): is not supported by the Data API.');
   }

   function setPreCommandScript($scriptName, $scriptParameters = null)
   {
      fmLogger(__METHOD__ .'(): is not supported by the Data API.');
   }

   function setPreSortScript($scriptName, $scriptParameters = null)
   {
      fmLogger(__METHOD__ .'(): is not supported by the Data API.');
   }

   function setRecordId($recordId)
   {
      $this->recordID = $recordId;
   }

   function validate($fieldName = null)
   {
      fmLogger(__METHOD__ .'(): is not supported by the Data API.');
   }

   // *********************************************************************************************************************************
   // Return the value as a string as json_encode will leave integers unquoted and the Data API does not like this.
   // If you don't do this you'll get:
   // {"errorMessage":"Value in field failed calculation test of validation entry option","errorCode":"507"}
   //
   function jsonEscapeValue($value)
   {
      return (string)$value;
   }

}

?>
