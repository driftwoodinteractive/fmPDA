<?php
// *********************************************************************************************************************************
//
// fmFind.class.php
//
// *********************************************************************************************************************************
//
// Copyright (c) 2017 - 2018 Mark DeNyse
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
class fmFind extends fmCommand
{
   public $offset;
   public $range;
   public $sort;

   function __construct($fm, $layout)
   {
      parent::__construct($fm, $layout);

      $this->offset = 0;
      $this->range = 0;
      $this->sort = array();
   }

   function execute()
   {
      $data = array();
      if ($this->offset != 0) {
         $data[] = 'offset='. $this->offset;
      }
      if ($this->range != 0) {
         $data[] = 'range='. $this->range;
      }
      if (count($this->sort) > 0) {
         $sort = array();
         foreach ($this->sort as $sortItem) {
            $sort[] = array('fieldName' => rawurlencode($sortItem['fieldName']), 'sortOrder' => $sortItem['sortOrder']);
         }
         $data[] = 'sort='. json_encode($sort);
      }
      $query = implode('&', $data);

      $apiResult = $this->fm->apiGetRecords($this->layout, $query);

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

   function setRelatedSetsFilters($relatedsetsfilter, $relatedsetsmax = null)
   {
      fmLogger(__METHOD__ .'(): is not supported by the Data API.');
   }

   function getRelatedSetsFilters()
   {
      fmLogger(__METHOD__ .'(): is not supported by the Data API.');
   }

}

?>
