<?php
// *********************************************************************************************************************************
//
// get_schedule.php
//
// This example uses fmAdminAPI::apiListSchedules() to list the schedules on the server.
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

require_once 'startup.inc.php';

$fm = new fmAdminAPI(FM_HOST, FM_ADMIN_USERNAME, FM_ADMIN_PASSWORD);

$apiResult = $fm->apiListSchedules();
if (! $fm->getIsError($apiResult)) {
   $response = $fm->getResponse($apiResult);

   foreach ($response['schedules'] as $schedule) {
      $scheduleID = $schedule[0]['id'];

      $apiResult = $fm->apiGetSchedule($scheduleID);
      if (! $fm->getIsError($apiResult)) {
         $response = $fm->getResponse($apiResult);
         fmLogger($response);
      }
      else {
         $errorInfo = $fm->getMessageInfo($apiResult);
         fmLogger('Found error(s):');
         fmLogger($errorInfo);
      }

   /*
      fmLogger($response['totalDBCount'] .' Database(s):');
      $files = $response['files']['files'];
      foreach ($files as $file) {
         fmLogger($file['filename'] .' '. $file['status']);
      }

      $clients = $response['clients']['clients'];
      fmLogger('');
      fmLogger(count($clients) .' Client(s):');
      foreach ($clients as $client) {
         fmLogger($client['userName'] .' '. $client['appVersion'] .' '. $client['appType']);
      }
   */
   }
}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}

echo fmGetLog();

?>
