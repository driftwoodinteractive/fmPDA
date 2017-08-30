<?php
// *********************************************************************************************************************************
//
// fmFindQuery.class.php
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

require_once 'fmFind.class.php';

// *********************************************************************************************************************************
class fmFindQuery extends fmFind
{
   public $compoundRequests;                             // Non-compound find will only have one of these
   public $omit;

   function __construct($fm, $layout)
   {
      parent::__construct($fm, $layout);

      $this->clearFindCriteria();
   }

   function execute()
   {
      $data = array();
      if (count($this->compoundRequests) > 0) {
         $requests = array();
         foreach ($this->compoundRequests as $request) {
            $query = array();
            foreach ($request as $requestParts) {
               $query[$requestParts['fieldName']] = $this->jsonEscapeValue($requestParts['value']);
            }
            if ($this->omit || (array_key_exists('omit', $requestParts) && $requestParts['omit'])) {
               $query['omit'] = 'true';
            }
            $requests[] = $query;
         }
         $data['query'] = $requests;
      }
      if ($this->offset != 0) {
         $data['offset'] = (string)$this->offset;
      }
      if ($this->range != 0) {
         $data['range'] = (string)$this->range;
      }
      if (count($this->sort) > 0) {
         $sort = array();
         foreach ($this->sort as $sortItem) {
            $sort[] = array('fieldName' => $sortItem['fieldName'], 'sortOrder' => $sortItem['sortOrder']);
         }
         $data['sort'] = $sort;
      }

      $apiResult = $this->fm->apiFindRecords($this->layout, $data);

      if ($this->fm->getTranslateResult()) {
         if (fmGetIsError($apiResult)) {
            $result = $apiResult;
         }
         else {
            $result = $this->fm->newResult($this->layout, array_key_exists(0, $apiResult[FM_DATA]) ? $apiResult[FM_DATA] : array());
         }
      }
      else {
         $result = $apiResult;
      }

      return $result;
   }

   function add($precedence, $findRequest)
   {
      $fields = array();

      $requests = $findRequest->getRequest();
      foreach ($requests as $request) {
         $fields[] = array('fieldName' => $request['fieldName'], 'value' => $request['value'], 'omit' => $findRequest->getOmit());
      }

      $this->compoundRequests[$precedence - 1] = $fields;
   }

   function setOmit($value)
   {
      $this->omit = $value;
   }

   function addFindCriterion($fieldName, $value)
   {
      $this->compoundRequests[0][] = array('fieldName' => $fieldName, 'value' => $value);
   }

   function clearFindCriteria()
   {
      $this->compoundRequests = array();
      $this->compoundRequests[] = array();

      $this->omit = false;
   }

}

?>
