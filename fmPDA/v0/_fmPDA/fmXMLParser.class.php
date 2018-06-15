<?php
// *********************************************************************************************************************************
//
// fmXMLParser.class.php
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

// *********************************************************************************************************************************
class fmXMLParser
{
   function __construct()
   {
   }

   // *********************************************************************************************************************************
   function parseFields($data, $includeRecordID = false)
   {
      $fields = array();

      if ($includeRecordID) {
         $fields[FM_RECORD_ID] = $data['@attributes']['record-id'];
      }

      foreach ($data['field'] as $field) {
         $fieldName = $field['@attributes']['name'];
         if (! is_array($field['data'])) {
            $fields[$fieldName] = $field['data'];
         }
         else {                                                                     // Repeating field
            foreach ($field['data'] as $repetition => $repeatingFieldValue) {
               if (! is_array($repeatingFieldValue) || (count($repeatingFieldValue) == 0)) { // XML may create an empty array for 'no value'. Strange, but true.
                  $fields[$fieldName .'('. ($repetition + 1) .')'] = '';
               }
               else {
                  $fields[$fieldName .'('. ($repetition + 1) .')'] = $repeatingFieldValue;
               }
            }
         }
      }

      return $fields;
   }

   // *********************************************************************************************************************************
   function parseOneRecord($data, $record)
   {
      $fmData = array();
      $fmData[FM_RECORD_ID] = $record['@attributes']['record-id'];               // Record ID
      $fmData[FM_MOD_ID] = $record['@attributes']['mod-id'];                     // Modification ID
      $fmData[FM_FIELD_DATA] = $this->parseFields($record);                      // Main fields on the layout

      $relatedSets = array();                                                    // Look for any portals
      if (array_key_exists('relatedset', $record)) {
         foreach ($record['relatedset'] as $relatedset) {
            $relatedsetName = $relatedset['@attributes']['table'];
            if (array_key_exists('record', $relatedset)) {
               $relatedSet = array();
               foreach ($relatedset['record'] as $relatedRecord) {
                  $relatedSet[] = $this->parseFields($relatedRecord, true/*includeRecordID*/);
               }
               $relatedSets[$relatedsetName] = $relatedSet;
            }
         }
         $fmData[FM_PORTAL_DATA] = $relatedSets;
      }

      $data[] = $fmData;

      return $data;
   }

   // *********************************************************************************************************************************
   function parse($fm, $layout, $rawXML)
   {
      $parsedXML = simplexml_load_string($rawXML);                                     // Convert XML into PHP array
      $json = json_encode($parsedXML);
      $xml = json_decode($json, true);

      if (($xml == '') ||                                                              // If it's no well formed, punt
          ! array_key_exists('error', $xml) ||
          ! array_key_exists('@attributes', $xml['error']) ||
          ! array_key_exists('code', $xml['error']['@attributes'])) {
         $result = $fm->newError('Bad XML', -1);
      }

      else if ($xml['error']['@attributes']['code'] != 0) {                            // Some sort of error?
         $result = $fm->newError('', $xml['error']['@attributes']['code']);
      }

      else {                                                                           // Looks good, let's get the data
         $data = array();

         $fetchSize = $xml['resultset']['@attributes']['fetch-size'];

         if (($fetchSize > 0) && array_key_exists('resultset', $xml) && array_key_exists('record', $xml['resultset'])) {
            $resultSet = $xml['resultset'];

            if ($fetchSize == 1) {
               $data = $this->parseOneRecord($data, $resultSet['record']);
            }
            else {
               foreach ($resultSet['record'] as $record) {
                  $data = $this->parseOneRecord($data, $record);
               }
            }
         }

         if ($fm->getTranslateResult()) {
            $result = $fm->newResult($layout, $data);
         }
         else {
            $result = $parsedXML;
         }
      }

      return $result;
   }
}

?>
