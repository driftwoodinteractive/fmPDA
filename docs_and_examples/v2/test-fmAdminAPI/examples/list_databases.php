<?php
// *********************************************************************************************************************************
//
// list_databases.php
//
// This example uses fmAdminAPI::apiListDatabases() to list the databases and currently connected clients
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

$fm = new fmAdminAPI(FM_HOST, FM_USERNAME, FM_PASSWORD);

$apiResult = $fm->apiListDatabases();
if (! $fm->getIsError($apiResult)) {
   $response = $fm->getResponse($apiResult);
//    fmLogger($response);
   fmLogger($response['openDBCount'] .' Open Database(s)');
   fmLogger($response['totalDBCount'] .' Database(s):');
   $databases = $response['databases'];
   foreach ($databases as $database) {
      fmLogger($database['filename'] .' '. $database['status'] .' # Clients: '. $database['clients'] .' ['. implode(' ', $database['enabledExtPrivileges']) .'] Size: '. number_format($database['size'], 0, '.', ','));
   }
}
else {
   $errorInfo = $fm->getMessageInfo($apiResult);
   fmLogger('Found error(s):');
   fmLogger($errorInfo);
}

echo fmGetLog();

?>
