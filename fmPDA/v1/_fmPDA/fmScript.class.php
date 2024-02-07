<?php
// *********************************************************************************************************************************
//
// fmScript.class.php
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
class fmScript extends fmCommand
{
   public $script;
   public $params;

   function __construct($fm, $layout, $scriptName, $scriptParameters = '', $layoutResponse = '')
   {
      parent::__construct($fm, $layout);

      $this->script = $scriptName;
      $this->params = $scriptParameters;
      $this->resultLayout = $layoutResponse;
   }

   // Execute a script. We do this by doing a apiGetRecords() call for the first record on the specified layout.
   // For this to work, *YOU MUST* have at least one record in this table or the script *WILL NOT EXECUTE*.
   // For efficiency, you may want to create a table with just one record and no fields.
   function execute()
   {
      $apiResult = $this->fm->apiPerformScript($this->layout, $this->script, $this->params, $this->resultLayout);

      if ($this->fm->getTranslateResult()) {
         if (! fmGetIsError($apiResult)) {

            $responseData = $this->fm->getResponseData($apiResult);

            if ($this->resultLayout != '') {
               $this->layout = $this->resultLayout;
            }

            $result = $this->fm->newResult($this->layout, array_key_exists(0, $responseData) ? $responseData : array());
         }
         else {
            $result = $apiResult;
         }
      }
      else {
         $result = $apiResult;
      }

      return $result;
   }

}

?>
