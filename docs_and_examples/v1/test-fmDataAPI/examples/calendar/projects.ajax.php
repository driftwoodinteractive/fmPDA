<?php


// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// This example shows what the code would look like after it's been 'rewritten' to use fmDataAPI in place of the old API
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
$query[FM_PROJECT_START_DATE_FIELD] = $fmStartDate .FILEMAKER_FIND_RANGE. $fmEndDate;
$requests[] = $query;

$query = array();
$query[FM_PROJECT_END_DATE_FIELD] = $fmStartDate .FILEMAKER_FIND_RANGE. $fmEndDate;
$requests[] = $query;

$query = array();
$query[FM_PROJECT_START_DATE_FIELD] = FILEMAKER_FIND_LT. $fmStartDate;
$query[FM_PROJECT_END_DATE_FIELD] = FILEMAKER_FIND_GT. $fmEndDate;
$requests[] = $query;

$data[FM_QUERY] = $requests;

$apiResult = $fm->apiFindRecords(FM_PROJECT_LAYOUT, $data);



// *********************************************************************************************************************************
// Process the result from FileMaker
//
if (! $fm->getIsError($apiResult)) {

   $responseData = $fm->getResponseData($apiResult);

   // Add a special event showing how many records were returned in the found set - for debugging.
   $event = array(
      'title'           => count($responseData) .' Projects',
      'start'           => $startDate,
      'backgroundColor' => '#000000'
   );
   $events[] = $event;



   // Process any records
   $eventIndex = 0;
   foreach ($responseData as $record) {
      $eventIndex++;
      $oneDayEvent = ($record[FM_FIELD_DATA][FM_PROJECT_START_DATE_FIELD] == $record[FM_FIELD_DATA][FM_PROJECT_END_DATE_FIELD]) ? true : false;
      $event = array(
         'id'              => $record[FM_FIELD_DATA][FM_PROJECT_ID_FIELD],
         'title'           => $record[FM_FIELD_DATA][FM_PROJECT_NAME_FIELD],
         'start'           => fmTofullCalendarDate($record[FM_FIELD_DATA][FM_PROJECT_START_DATE_FIELD]),
         'backgroundColor' => GetEventColor($record[FM_FIELD_DATA][FM_PROJECT_COLOR_INDEX_FIELD]),
         'borderColor'     => GetEventColor($record[FM_FIELD_DATA][FM_PROJECT_COLOR_INDEX_FIELD]),
      );

      if (! $oneDayEvent) {
         $event['title'] .= ' ('. fmTofullCalendarDate($record[FM_FIELD_DATA][FM_PROJECT_START_DATE_FIELD]) .' '. fmTofullCalendarDate($record[FM_FIELD_DATA][FM_PROJECT_END_DATE_FIELD]) .')';
         $event['end'] = fmTofullCalendarDate($record[FM_FIELD_DATA][FM_PROJECT_END_DATE_FIELD], true);
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

// *********************************************************************************************************************************
// Return our events (if any)
//
echo json_encode($events);

?>
