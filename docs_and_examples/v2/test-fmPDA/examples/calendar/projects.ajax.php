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
// Create a compound find and execute it in fmPDA
//
$fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);

$findCommand = $fm->newCompoundFindCommand(FM_PROJECT_LAYOUT);
$index = 1;

$findRequest = $fm->newFindRequest(FM_PROJECT_LAYOUT);
$findRequest->addFindCriterion(FM_PROJECT_START_DATE_FIELD, $fmStartDate .FILEMAKER_FIND_RANGE. $fmEndDate);
$findCommand->add($index, $findRequest);
$index++;

$findRequest = $fm->newFindRequest(FM_PROJECT_LAYOUT);
$findRequest->addFindCriterion(FM_PROJECT_END_DATE_FIELD, $fmStartDate .FILEMAKER_FIND_RANGE. $fmEndDate);
$findCommand->add($index, $findRequest);
$index++;

$findRequest = $fm->newFindRequest(FM_PROJECT_LAYOUT);
$findRequest->addFindCriterion(FM_PROJECT_START_DATE_FIELD, FILEMAKER_FIND_LT. $fmStartDate);
$findRequest->addFindCriterion(FM_PROJECT_END_DATE_FIELD, FILEMAKER_FIND_GT. $fmEndDate);
$findCommand->add($index, $findRequest);
$index++;

$result = $findCommand->execute();











// *********************************************************************************************************************************
// Process the result from FileMaker
//
if (! fmGetIsError($result)) {

   // Add a special event showing how many records were returned in the found set - for debugging.
   $event = array(
      'title'           => $result->getFoundSetCount() .' Projects',
      'start'           => $startDate,
      'backgroundColor' => '#000000'
   );
   $events[] = $event;



   // Process any records
   $eventIndex = 0;
   $records = $result->getRecords();
   foreach($records as $record) {
      $eventIndex++;
      $oneDayEvent = ($record->getField(FM_PROJECT_START_DATE_FIELD) == $record->getField(FM_PROJECT_END_DATE_FIELD)) ? true : false;
      $event = array(
         'id'              => $record->getField(FM_PROJECT_ID_FIELD),
         'title'           => $record->getFieldUnencoded(FM_PROJECT_NAME_FIELD),
         'start'           => fmTofullCalendarDate($record->getField(FM_PROJECT_START_DATE_FIELD)),
         'backgroundColor' => GetEventColor($record->getField(FM_PROJECT_COLOR_INDEX_FIELD)),
         'borderColor'     => GetEventColor($record->getField(FM_PROJECT_COLOR_INDEX_FIELD)),
      );

      if (! $oneDayEvent) {
         $event['title'] .= ' ('. fmTofullCalendarDate($record->getField(FM_PROJECT_START_DATE_FIELD)) .' '. fmTofullCalendarDate($record->getField(FM_PROJECT_END_DATE_FIELD)) .')';
         $event['end'] = fmTofullCalendarDate($record->getField(FM_PROJECT_END_DATE_FIELD), true);
      }

      $events[] = $event;
   }
}
else {
   if ($result->getCode() != 401) {                // Report any error that isn't 401 'no records match the request'
      $event = array(
         'title'           => 'Error '. $result->getCode() ."\n". $result->getMessage() .' for '. $startDate .' to '. $endDate,  // Error info
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
