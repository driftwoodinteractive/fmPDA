<?php
// *********************************************************************************************************************************
//
// list_databases.php
//
// This example uses fmAdminAPI::apiListDatabases() to list the databases and currently connected clients
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

$fm = new fmAdminAPI(FM_HOST, FM_USERNAME, FM_PASSWORD);

$apiResult = $fm->apiListDatabases();
if (! $fm->getIsError($apiResult)) {
   $response = $fm->getResponse($apiResult);

//    fmLogger($response);

   fmLogger($response['fmproCount']    .' Pro Users');
   fmLogger($response['fmgoCount']     .' Go Users');
   fmLogger($response['fmwebdCount']   .' Web Direct Users');
   fmLogger($response['fmdapiCount']   .' Data API Users');
   fmLogger($response['fmmiscCount']   .' Team Users');
   fmLogger($response['fmproCount']    .' Misc Users');
   fmLogger($response['fmdapiCount']   .' Data API Outbound');
   fmLogger($response['openDBCount']   .' Open Databases');
   fmLogger($response['time']          .' Server Time');
   fmLogger('');

   fmLogger($response['totalDBCount'] .' Database(s):');
   $files = $response['files']['files'];
   foreach ($files as $file) {
      fmLogger($file['filename'] .' '. $file['status'] .' # Clients: '. $file['clients'] .' ['. implode(' ', $file['enabledExtPrivileges']) .'] Size: '. number_format($file['size'], 0, '.', ','));
   }


   $clients = $response['clients']['clients'];
   fmLogger('');
   fmLogger(count($clients) .' Client(s):');
   foreach ($clients as $client) {

      $files = '';
      foreach ($client['guestFiles'] as $guestFile) {
         $files .= $guestFile['filename'] .' ';
      }

      fmLogger($client['id'] .' '. $client['status'] .' '. $client['userName'] .' '. $client['appVersion'] .' '. $client['appType'] .' '. $client['computerName'] .' '. $client['connectDuration'] .' '. $client['ipaddress'] .' '. $client['operatingSystem'] . (($client['concurrent'] == 1) ? ' Concurrent' : '') . (($client['teamLicensed'] == 1) ? ' Team' : '') .' ['. trim($files) .']');
   }

}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}

echo fmGetLog();

?>
