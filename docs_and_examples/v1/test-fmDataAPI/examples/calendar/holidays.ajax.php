<?php

// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Changes made to use fmPDA:
// changed:
// $fm = new FileMaker(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);
// to:
// $fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD, FM_PROJECT_LAYOUT);
//
// if (! FileMaker::isError($result)) {
// to:
// if (! fmGetIsError($result)) {
// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

require_once 'startup.inc.php';

// *********************************************************************************************************************************
// Get our date range
$startDate     = $_GET['start'];
$endDate       = $_GET['end'];

$fmStartDate   = date('m/d/Y', strtotime($startDate));         // Convert passed YYYY-MM-DD into FileMaker MM/DD/YYYY
$fmEndDate     = date('m/d/Y', strtotime($endDate));           // Convert passed YYYY-MM-DD into FileMaker MM/DD/YYYY



// *********************************************************************************************************************************
// $events is what we will JSON encode and return to our caller
$events = array();



// *********************************************************************************************************************************
// Create a compound find and execute it in FileMaker
//
$fm = new fmDataAPI(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);


$data = array();
$requests = array();

$query = array();
$query[FM_HOLIDAY_START_DATE_FIELD] = $fmStartDate .FILEMAKER_FIND_RANGE. $fmEndDate;
$requests[] = $query;

$query = array();
$query[FM_HOLIDAY_END_DATE_FIELD] = $fmStartDate .FILEMAKER_FIND_RANGE. $fmEndDate;
$requests[] = $query;

$query = array();
$query[FM_HOLIDAY_START_DATE_FIELD] = FILEMAKER_FIND_LT. $fmStartDate;
$query[FM_HOLIDAY_END_DATE_FIELD] = FILEMAKER_FIND_GT. $fmEndDate;
$requests[] = $query;

$data[FM_QUERY] = $requests;

$apiResult = $fm->apiFindRecords(FM_HOLIDAY_LAYOUT, $data);










// *********************************************************************************************************************************
// Process the result from FileMaker
//
if (! fmGetIsError($apiResult)) {

   $responseData = $fm->getResponseData($apiResult);

   // Add a special event showing how many records were returned in the found set - for debugging.
   $event = array(
      'title'           => count($responseData) .' Holidays',
      'start'           => $startDate,
      'backgroundColor' => '#000000'
   );
   $events[] = $event;

   // Process any records
   foreach ($responseData as $record) {
      $oneDayEvent = ($record[FM_FIELD_DATA][FM_HOLIDAY_START_DATE_FIELD] == $record[FM_FIELD_DATA][FM_HOLIDAY_END_DATE_FIELD]) ? true : false;
      $event = array(
         'title'           => $record[FM_FIELD_DATA][FM_HOLIDAY_NAME_FIELD],
         'start'           => fmTofullCalendarDate($record[FM_FIELD_DATA][FM_HOLIDAY_START_DATE_FIELD]),
         'borderColor'     => '#FF8000',
         'backgroundColor' => '#FF8000'
      );

      if (! $oneDayEvent) {
         $event['title'] .= ' ('. fmTofullCalendarDate($record[FM_FIELD_DATA][FM_HOLIDAY_START_DATE_FIELD]) .' '. fmTofullCalendarDate($record[FM_FIELD_DATA][FM_HOLIDAY_END_DATE_FIELD]) .')';
         $event['end'] = fmTofullCalendarDate($record[FM_FIELD_DATA][FM_HOLIDAY_END_DATE_FIELD], true);
      }

      $events[] = $event;
   }
}
else {
   $errorInfo = $fm->getFirstMessage($apiResult);
   if ($errorInfo[FM_CODE] != FM_ERROR_NO_RECORDS) {             // Report any error that isn't 401 'no records match the request'
      $event = array(
         'title'           => 'Error '. $errorInfo[FM_CODE] ."\n". $errorInfo[FM_MESSAGE] .' for '. $startDate .' to '. $endDate,  // Error info
         'start'           => $startDate,
         'backgroundColor' => '#000000'
      );
      $events[] = $event;
   }
}

echo json_encode($events);

?>
