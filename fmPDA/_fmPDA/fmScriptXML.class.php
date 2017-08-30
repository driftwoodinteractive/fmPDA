<?php
// *********************************************************************************************************************************
//
// FMScriptXML.class.php
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

require_once 'fmXMLParser.class.php';        // XML Parser to return FMS XML into an fmResult

// *********************************************************************************************************************************
class fmScriptXML extends fmScript
{
   function __construct($fm, $layout, $scriptName, $scriptParameters = '')
   {
      parent::__construct($fm, $layout, $scriptName, $scriptParameters);
   }

   // *********************************************************************************************************************************
   function execute()
   {
      fmLogger('*** Executing Script through XML interface ***');

      $header = array('Content-Type: application/x-www-form-urlencoded; charset=utf-8',
                      'X-FMI-PE-ExtendedPrivilege: IrG6U+Rx0F5bLIQCUb9gOw==',
                      'Authorization: Basic ' . base64_encode(utf8_decode($this->fm->username. ':' . utf8_decode($this->fm->password)))
                      );

      $options = array();
      $options[CURLOPT_HTTPHEADER] = $header;
//    $options['logSentHeaders'] = true;

      $url = $this->fm->host .'/fmi/xml/fmresultset.xml?-db=' . rawurlencode($this->fm->database).
                                                      '&-lay='. rawurlencode($this->layout).
                                                      '&-script='. rawurlencode($this->script).
                                                      '&-script.param='. rawurlencode($this->params).
                                                      '&-findany';

      $result = $this->fm->curl($url, METHOD_GET, '', $options);

      if ($this->fm->curlInfo['http_code'] == HTTP_UNAUTHORIZED) {
         $result = $this->fm->newError('Invalid user account and/or password', FM_ERROR_COMMUNICATION);
      }
      else if ($this->fm->curlErrNum != 0) {
         $result = $this->fm->newError($this->fm->curlErrMsg, $this->fm->curlErrNum);
      }
      else if ($this->fm->curlErrMsg != '') {
         $result = $this->fm->newError($this->fm->curlErrMsg, $this->fm->curlErrNum);
      }
      else {
         $xmlParser = new fmXMLParser();
         $result = $xmlParser->parse($this->fm, $this->layout, $result);                     // Parse into a fmResult
      }

      return $result;
   }

}

?>
