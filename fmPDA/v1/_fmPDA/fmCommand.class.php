<?php
// *********************************************************************************************************************************
//
// fmCommand.class.php
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
class fmCommand
{
   public $layout;
   public $fm;
   public $recordID;
   public $offset;
   public $range;
   public $sort;
   public $script;
   public $scriptParams;
   public $preScript;
   public $preScriptParams;
   public $preSortScript;
   public $preSortScriptParams;
   public $resultLayout;

   function __construct($fm, $layout)
   {
      $this->fm = $fm;
      $this->layout = $layout;
      $this->recordID = '';
      $this->offset = 0;
      $this->range = 0;
      $this->sort = array();
      $this->script = '';
      $this->scriptParams = '';
      $this->preScript = '';
      $this->preScriptParams = '';
      $this->preSortScript = '';
      $this->preSortScriptParams = '';
      $this->resultLayout = '';
   }

   function getAPIParams()
   {
      $params =
            array(
               'limit'                  => $this->range,
               'offset'                 => $this->offset,
               'sort'                   => $this->sort,
               'script'                 => $this->script,
               'scriptParams'           => $this->scriptParams,
               'scriptPrerequest'       => $this->preScript,
               'scriptPrerequestParams' => $this->preScriptParams,
               'scriptPresort'          => $this->preSortScript,
               'scriptPresortParams'    => $this->preSortScriptParams,
               'layoutResponse'         => $this->resultLayout
            );

      return $params;
   }

  function execute()
   {
      fmLogger(__METHOD__ .'(): must be overridden.');
   }

   function getRecordId($recordId)
   {
      return $this->recordID;
   }

   function addSortRule($fieldname, $precedence, $order = 'ascend')
   {
      $this->sort[$precedence] = array('fieldName' => $fieldname, 'precedence' => $precedence, 'sortOrder' => $order);

      return;
   }

   function clearSortRules()
   {
      $this->sort = array();

      return;
   }

   function setRange($skip = 0, $range = 0)
   {
      $this->offset = $skip + 1;                // + 1 since Old PHP API wants to SKIP $skip records. Data API means to START at what is $skip
      $this->range = $range;

      return;
   }

   function getRange()
   {
      return array('skip' => $this->offset - 1, 'max' => $this->range);
   }

   function setResultLayout($layout)
   {
      $this->resultLayout = $layout;
   }

   function setScript($scriptName, $scriptParameters = null)
   {
      $this->script = $scriptName;
      $this->scriptParams = $scriptParameters;
   }

   function setPreCommandScript($scriptName, $scriptParameters = null)
   {
      $this->preScript = $scriptName;
      $this->preScriptParams = $scriptParameters;
   }

   function setPreSortScript($scriptName, $scriptParameters = null)
   {
      $this->preSortScript = $scriptName;
      $this->preSortScriptParams = $scriptParameters;
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
   // {"code":"507","message":"Value in field failed calculation test of validation entry option"}
   //
   function jsonEscapeValue($value)
   {
      return (string)$value;
   }

}

?>
