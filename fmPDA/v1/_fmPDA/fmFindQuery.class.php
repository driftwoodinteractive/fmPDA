<?php
// *********************************************************************************************************************************
//
// fmFindQuery.class.php
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

require_once 'fmFind.class.php';

// *********************************************************************************************************************************
class fmFindQuery extends fmFind
{
   public $requests;                             // Non-compound find will only have one of these

   function __construct($fm, $layout)
   {
      parent::__construct($fm, $layout);

      $this->clearFindCriteria();
   }

   function getAPIParams()
   {
      $params = parent::getAPIParams();

      if (count($this->requests) > 0) {
         $requests = array();
         foreach ($this->requests as $findRequest) {
            $query = array();
            $request = $findRequest->getRequest();
            $fields = array();
            foreach ($request as $requestParts) {
               $fields[$requestParts['fieldName']] = $requestParts['value'];
            }
            if ($findRequest->omit) {
               $fields['omit'] = 'true';
            }
            $requests[] = $fields;
         }
         $params['query'] = $requests;
      }

      return $params;
   }

   function execute()
   {
      $apiResult = $this->fm->apiFindRecords($this->layout, '', $this->getAPIParams());

      if ($this->fm->getTranslateResult()) {
         if (fmGetIsError($apiResult)) {
            $result = $apiResult;
         }
         else {
            $responseData = $this->fm->getResponseData($apiResult);
            $result = $this->fm->newResult($this->layout, array_key_exists(0, $responseData) ? $responseData : array());
         }
      }
      else {
         $result = $apiResult;
      }

      return $result;
   }

   function add($precedence, $findRequest)
   {
      $request = new fmFindRequest($this->fm, $this->layout);
      $request->request = $findRequest->getRequest();
      $request->omit = $findRequest->getOmit();

      $this->requests[$precedence - 1] = $request;
   }

   function setOmit($value)
   {
      $request = $this->requests[0];
      $request->setOmit($value);

      $this->add(1, $request);
  }

   function addFindCriterion($fieldName, $value)
   {
      if (($fieldName != '') && ($value != '')) {
         $request = $this->requests[0];

         $request->addFindCriterion($fieldName, $value);

         $this->requests[0] = $request;
      }
   }

   function clearFindCriteria()
   {
      $this->requests = array();

      $this->requests[0] = new fmFindRequest($this->fm, $this->layout);
   }

}

?>
