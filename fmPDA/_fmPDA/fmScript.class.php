<?php
// *********************************************************************************************************************************
//
// fmScript.class.php
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

require_once 'fmCommand.class.php';

// *********************************************************************************************************************************
class fmScript extends fmCommand
{
   public $script;
   public $params;

   function __construct($fm, $layout, $scriptName, $scriptParameters = '')
   {
      parent::__construct($fm, $layout);

      $this->script = $scriptName;
      $this->params = $scriptParameters;
   }

   function execute()
   {
      fmLogger('Executing Script not supported in Data API (yet!).');

      if ($this->fm->getTranslateResult()) {
         // For now - when FMI supports script execution we'll flush this out to work.
         return $this->fm->newError('Request not supported', 21);    // Not currently supported by Data API (for now)
      }
      else {
         return array(FM_ERROR_CODE => 22, FM_ERROR_MESSAGE => 'Request not supported', FM_RESULT => 'OK');
      }
   }

}

?>
