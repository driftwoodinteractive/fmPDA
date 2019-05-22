<?php
// *********************************************************************************************************************************
//
// edit_schedule.php
//
// This example uses fmAdminAPI::apiListSchedules(), then fmAdminAPI::apiSetSchedule to set email notification for all schedules.
//
// *********************************************************************************************************************************
//
// Copyright (c) 2017 - 2019 Mark DeNyse
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

require_once 'startup.inc.php';

$emailAddress = 'info@driftwoodinteractive.com';

$fm = new fmAdminAPI(FM_HOST, FM_USERNAME, FM_PASSWORD);

$apiResult = $fm->apiListSchedules();
if (! $fm->getIsError($apiResult)) {
   $response = $fm->getResponse($apiResult);

   foreach ($response['schedules'] as $schedule) {
// fmLogger($schedule);
// fmLogger($schedule['id'] .': sendEmail='. $schedule['sendEmail']);


      if (strtolower($schedule['taskType']) == 'backup') {
//       if (1 || ! $schedule['sendEmail']) {                                      // If it's not set, set it now

         $data = array();
         $data['taskType'] = 1;                                                    // These are required...
         $data['name'] = $schedule['name'];
         $data['freqType'] = $schedule['freqType'];

         // 2018-06-13 15:28:00
         //Year-Month-Day-Hour-Minute-Second" 24-hour format
         $data['startDate'] = str_replace(array(' ', ':'), array('-', '-'), $schedule['startDate']);

         if ($schedule['freqType'] == 3/* Weekly */) {
            $data['daysOfTheWeek'] = $schedule['daysOfTheWeek'];                    // Required for weekly
         }

         $data['target'] = $schedule['backupType']['target'];
         $data['backupTarget'] = $schedule['backupType']['backupTarget'];

         $data['sendEmail'] = true;                                               // Now to our optional stuff
         $data['emailAddresses'] = $emailAddress;

         $apiResult = $fm->apiSetSchedule($schedule['id'], $data);

         if (! $fm->getIsError($apiResult)) {
            $response = $fm->getResponse($apiResult);
            fmLogger('Enabled email notification for schedule '. $schedule['id']);
//             fmLogger($response);
         }
         else {
            $errorInfo = $fm->getMessageInfo($apiResult);
            fmLogger('Found error(s):');
            fmLogger($errorInfo);
         }
      }
   }
}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}

echo fmGetLog();

?>
