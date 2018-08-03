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
// if (fmGetIsValid($result)) {
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
$fm = new fmPDA(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD, FM_PROJECT_LAYOUT);
$findCommand = $fm->newCompoundFindCommand(FM_HOLIDAY_LAYOUT);
$index = 1;

$findRequest = $fm->newFindRequest(FM_HOLIDAY_LAYOUT);
$findRequest->addFindCriterion(FM_HOLIDAY_START_DATE_FIELD, $fmStartDate .FILEMAKER_FIND_RANGE. $fmEndDate);
$findCommand->add($index, $findRequest);
$index++;

$findRequest = $fm->newFindRequest(FM_HOLIDAY_LAYOUT);
$findRequest->addFindCriterion(FM_HOLIDAY_END_DATE_FIELD, $fmStartDate .FILEMAKER_FIND_RANGE. $fmEndDate);
$findCommand->add($index, $findRequest);
$index++;

$findRequest = $fm->newFindRequest(FM_HOLIDAY_LAYOUT);
$findRequest->addFindCriterion(FM_HOLIDAY_START_DATE_FIELD, FILEMAKER_FIND_LT. $fmStartDate);
$findRequest->addFindCriterion(FM_HOLIDAY_END_DATE_FIELD, FILEMAKER_FIND_GT. $fmEndDate);
$findCommand->add($index, $findRequest);
$index++;

$result = $findCommand->execute();













// *********************************************************************************************************************************
// Process the result from FileMaker
//
if (fmGetIsValid($result)) {

   // Add a special event showing how many records were returned in the found set - for debugging.
   $event = array(
      'title'           => $result->getFoundSetCount() .' Holidays',
      'start'           => $startDate,
      'backgroundColor' => '#000000'
   );
   $events[] = $event;



   // Process any records
   $records = $result->getRecords();
   foreach($records as $record) {
      $oneDayEvent = ($record->getField(FM_HOLIDAY_START_DATE_FIELD) == $record->getField(FM_HOLIDAY_END_DATE_FIELD)) ? true : false;
      $event = array(
         'title'           => $record->getFieldUnencoded(FM_HOLIDAY_NAME_FIELD),
         'start'           => fmTofullCalendarDate($record->getField(FM_HOLIDAY_START_DATE_FIELD)),
         'borderColor'     => '#FF8000',
         'backgroundColor' => '#FF8000'
      );

      if (! $oneDayEvent) {
         $event['title'] .= ' ('. fmTofullCalendarDate($record->getField(FM_HOLIDAY_START_DATE_FIELD)) .' '. fmTofullCalendarDate($record->getField(FM_HOLIDAY_END_DATE_FIELD)) .')';
         $event['end'] = fmTofullCalendarDate($record->getField(FM_HOLIDAY_END_DATE_FIELD), true);
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

echo json_encode($events);

?>
