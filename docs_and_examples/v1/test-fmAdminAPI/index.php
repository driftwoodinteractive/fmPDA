<?php
// *********************************************************************************************************************************
//
// index.php
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

require_once '../nav.inc.php';

// Let the caller decide which version of the API to use. We hard code the version we know works.
// The caller can also pass 'Latest' as a demonstration that these examples also work on the latest version.
define('SUPPORTED_API_VERSION', 1);

define('API_VERSION', array_key_exists('v', $_GET) ? $_GET['v'] : SUPPORTED_API_VERSION);

?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8" />
      <title>fmPDA &#9829;</title>
      <link href="../../css/normalize.css" rel="stylesheet" />
      <link href="../../css/fontawesome-all.min.css" rel="stylesheet" />
      <link href="../../css/prism.css" rel="stylesheet" />
      <script src="../../js/prism.js"></script>
		<script type='text/javascript' src='../../js/jquery-3.3.1.js'></script>
		<script type='text/javascript' src='../../js/bootstrap/js/bootstrap.min.js'></script>
      <link href="../../js/bootstrap/css/bootstrap.css" rel="stylesheet" />
      <link href="../../css/styles.css" rel="stylesheet" />
   </head>
   <body>

      <!-- Always on top: Position Fixed-->
      <header>
         fmPDA <span class="fmPDA-heart"><i class="fas fa-heart"></i></span>
         <span style="float: right;">Version <strong><?php echo API_VERSION; ?></strong> (FileMaker Server 17+)</span>
      </header>


      <!-- Fixed size after header-->
      <div class="content">

         <!-- Always on top. Fixed position, fixed width, relative to content width-->
         <div class="navigation-left">
            <?php echo GetNavigationMenu(1, API_VERSION); ?>
         </div>

          <!-- Scrollable div with main content -->
         <div class="main">

            <div id="adminapi-constructor" class="api-object">
               <div class="api-header">
                  fmAdminAPI Constructor
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                      function __construct($host, $username, $password, $options = array())

                         Constructor for fmAdminAPI.

                         Parameters:
                            (string)  $host             The host name typically in the format of https://HOSTNAME
                            (string)  $username         The user name of the account to authenticate with
                            (string)  $password         The password of the account to authenticate with
                            (array)   $options          Optional parameters
                                                            ['version'] Version of the API to use (1, 2, etc. or 'Latest')
                                                            ['cloud'] Set to true if you're using FileMaker Cloud

                         Returns:
                            The newly created object.

                         Example:
                            $fm = new fmAdminAPI($host, $username, $password);
                  </code></pre>
               </div>
            </div>

            <div id="apiLogin" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiLogin()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiLogin()

                        Create a new session on the server. Authentication parameters were previously passed to the  __construct method.
                        Normally you will not call this method as the other apiNNNNNN() methods take care of logging in when appropriate.
                        By default, the authentication token is stored within this class and reused for all further calls. If the server
                        replies that the token is not longer valid (FM_ERROR_INSUFFICIENT_PRIVILEGES - 9), this class will automatically login
                        again to get a new token.

                        The server expires a timer after approximately 15 minutes, but the timer is reset each time you make a call to
                        the server. You should not assume it is always 15 minutes; a later version of FileMaker Server may change this
                        time. Let this class handle the expiration in a graceful manner.

                        Parameters:
                           None

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] If the call succeeds, the ['token'] element contains the authentication token.
                              ['messages'] Array of code/message pairs

                        Example:
                           $fm = new fmAdminAPI($database, $host, $username, $password);
                           $apiResult = $fm->apiLogin();
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="login">Run Example</button> Create a new session
               </div>
               <div id="output_login" class="api-example-output"></div>
            </div>
            <div id="apiLogout" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiLogout()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiLogout()

                        Logs out of the current session and clears the username, password, and authentication token.

                        Normally you will not call this method so that you can keep re-using the authentication token for future calls.
                        Only logout if you know you are completely done with the session. This will also clear the stored username/password.

                        Parameters:
                           None

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiLogout();
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="logout">Run Example</button> Log out of the session
               </div>
               <div id="output_logout" class="api-example-output"></div>
            </div>

            <div id="apiGetServerInfo" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiGetServerInfo()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiGetServerInfo()

                        Get information about the server. You do not need to authenticate to get this information.

                        Parameters:
                           None

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['data']   An array of data
                              ['result'] 0 if successful else an error code

                        Example:
                           $fm = new fmAdminAPI($host);
                           $apiResult = $fm->apiGetServerInfo();
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }

                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_server_info">Run Example</button> Get information about the server
               </div>
               <div id="output_get_server_info" class="api-example-output"></div>
            </div>
            <div id="apiGetServerStatus" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiGetServerStatus()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiGetServerStatus()

                        Get the status of the server

                        Parameters:
                           (array) $data An array of options to set in the general configuration.

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ['running'] 1 if the server is running

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiGetServerStatus();
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                   </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_server_status">Run Example</button> Get the server status
               </div>
               <div id="output_get_server_status" class="api-example-output"></div>
            </div>
            <div id="apiSetServerStatus" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiSetServerStatus()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiSetServerStatus($data)

                        Set the status of the server

                        Parameters:
                           (array) $data An array of options to set. Currently 'running' is the only option.

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ['running'] 1 if the server is running

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiSetServerStatus($data);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>

            <div id="apiGetLog" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiGetLog($url, $type, $format)
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiGetLog($type, $format, $url = '')

                        Get a FileMaker Server log.

                        This is an 'extension' to the Admin API.
                        To make this work, you must be running the fmPDA class files on your FMS server *or* install
                        the 'get_fms_log.php' file on your FMS server. Both this method andget_fms_log.php will authenticate
                        through the Admin API by sending a apiGetServerStatus() request.

                        Parameters:
                        (string) type           The type of log to return
                                                      'access'
                                                      'event'
                                                      'fmdapi'
                                                      'stderr'
                                                      'stdout'
                                                      'topcallstats'
                                                      'wpedebug'
                                                      'wpe'
                        (string) format         The format of the log data:
                                                      'raw'         The contents of the file as read from disk (default)
                                                      'html'        Convert \r and \n to HTML &lt;br&gt; tags
                                                      'html-table'  HTML table
                        (string) url            If blank, fmPDA must reside on the same server as FileMaker server
                                                so that it has access to the log files directory. If fmPDA is on a separate server
                                                you will need to install get_fms_log.php resides on your FMS server and then pass
                                                the URL to that location here.

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result']         0 if successful else an error code
                              ['message']        The error message
                              ['log']            The log file, in whatever form specified by type in the request payload

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiGetLog($url, $type, $format);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_log.php?type=event&format=html-table" output="get_log" >Run Example</button> Get Server 'Event' Log
               </div>
               <div id="output_get_log" class="api-example-output"></div>
            </div>

            <div id="apiGetPHPConfig" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiGetPHPConfiguration()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiGetPHPConfiguration()

                        Get the PHP configuration for the server.

                        Parameters:
                           None

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ['enabled'] 1 if PHP is enabled, 0 otherwise
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiGetPHPConfiguration();
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_php_configuration">Run Example</button> Get the PHP configuration
               </div>
               <div id="output_get_php_configuration" class="api-example-output"></div>
            </div>
            <div id="apiSetPHPConfig" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiSetPHPConfiguration()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiSetPHPConfiguration($data)

                        Set the PHP configuration on the server.

                        Parameters:
                           (array) $data An array of options to set in the PHP configuration.

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $option = array();
                           $option[ ... ] = '';
                           $apiResult = $fm->apiSetPHPConfiguration($data);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>

            <div id="apiGetXMLConfig" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiGetXMLConfiguration()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiGetXMLConfiguration()

                        Get the XML configuration for the server.

                        Parameters:
                           None

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ['enabled'] 1 if XML is enabled, 0 otherwise

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiGetXMLConfiguration();
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_xml_configuration">Run Example</button> Get the XML configuration
               </div>
               <div id="output_get_xml_configuration" class="api-example-output"></div>
            </div>
            <div id="apiSetXMLConfig" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiSetXMLConfiguration()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiSetXMLConfiguration($data)

                        Get the XML configuration for the server.

                        Parameters:
                           (array) $data An array of options to set in the XML configuration. 'enabled' is currently the only option.

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $option = array();
                           $option['enabled'] = 'true';
                           $apiResult = $fm->apiSetXMLConfiguration($data);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>

            <div id="apiGetGeneralConfig" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiGetServerGeneralConfiguration()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiGetServerGeneralConfiguration()

                        Get the server's general configuration

                        Parameters:
                           None

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiGetServerGeneralConfiguration();
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_server_general_configuration">Run Example</button> Get the Server's General Configuration
               </div>
               <div id="output_get_server_general_configuration" class="api-example-output"></div>
            </div>
            <div id="apiSetGeneralConfig" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiSetServerGeneralConfiguration()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiSetServerGeneralConfiguration($data)

                        Get the server's security configuration

                        Parameters:
                           (array) $data An array of options to set in the general configuration.

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiSetServerGeneralConfiguration($data);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>

            <div id="apiGetSecurityConfig" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiGetServerSecurityConfiguration()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiGetServerSecurityConfiguration()

                        Get the server's security configuration

                        Parameters:
                           None

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiGetServerSecurityConfiguration();
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_server_security_configuration">Run Example</button> Get the Server's Security Configuration
               </div>
               <div id="output_get_server_security_configuration" class="api-example-output"></div>
            </div>
            <div id="apiSetSecurityConfig" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiSetServerSecurityConfiguration()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiSetServerSecurityConfiguration($data)

                        Get the server's security configuration

                        Parameters:
                           (array) $data An array of options to set in the general configuration.

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiSetServerSecurityConfiguration($data);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>

            <div id="apiListDatabases" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiListDatabases()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiListDatabases()

                        List the databases on the server

                        Parameters:
                           None

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiListDatabases();
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }

                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="list_databases">Run Example</button> List the databases
               </div>
               <div id="output_list_databases" class="api-example-output"></div>
            </div>
            <div id="apiOpenDatabase" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiOpenDatabase()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiOpenDatabase($databaseID, $password = '')

                        Open the database specified by $databaseID

                        Parameters:
                           (integer)  $databaseID    The database ID
                           (string)   $password      The encryption password for the database.

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiOpenDatabase($databaseID);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>
            <div id="apiCloseDatabase" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiCloseDatabase()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiCloseDatabase($databaseID, $message = '')

                        Close the database specified by $databaseID

                        Parameters:
                           (integer)  $databaseID    The database ID
                           (string)   $message       The message to display to users being disconnected

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiCloseDatabase($databaseID, 'Down for maintenance. Be back in an hour!');
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>
            <div id="apiPauseDatabase" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiPauseDatabase()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiPauseDatabase($databaseID)

                        Pause the database specified by $databaseID

                        Parameters:
                           (integer)  $databaseID    The database ID

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiPauseDatabase($databaseID);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>
            <div id="apiResumeDatabase" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiResumeDatabase()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiResumeDatabase($databaseID)

                        Resume a paused database specified by $databaseID

                        Parameters:
                           (integer)  $databaseID    The database ID

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiResumeDatabase($databaseID);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>

            <div id="apiDisconnectClient" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiDisconnectClient()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiDisconnectClient($clientID, $message = '', $graceTime = '')

                        Disconnect a client

                        Parameters:
                           (integer)   $clientID  The client ID
                           (string)    The message to send
                           (integer)   The number of seconds to wait before disconnecting (0-3600)

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiDisconnectClient($data);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>
            <div id="apiSendMessageToClient" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiSendMessageToClient()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiSendMessageToClient($clientID, $message)

                        Send a messge to a client

                        Parameters:
                           (integer)   $clientID  The client ID
                           (string)    The message to send

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiDisconnectClient($data);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>

            <div id="apiListSchedules" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiListSchedules()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiListSchedules()

                        Get a list of schedules.

                        Parameters:
                           None

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiListSchedules();
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="list_schedules">Run Example</button> Get a list of schedules
               </div>
               <div id="output_list_schedules" class="api-example-output"></div>
            </div>

            <div id="apiCreateSchedule" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiCreateSchedule()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiCreateSchedule($data)

                        Create a new schedule

                        Parameters:
                           (array) $data An array of options to set specifying the schedule.

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiCreateSchedule($data);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>
            <div id="apiDeleteSchedule" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiDeleteSchedule()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiDeleteSchedule($scheduleID)

                        Delete a schedule

                        Parameters:
                           (integer) $scheduleID   The schedule ID

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ['schedules'] The deleted schedule
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiDeleteSchedule($scheduleID);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>
            <div id="apiDuplicateSchedule" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiDuplicateSchedule()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiDuplicateSchedule($scheduleID)

                        Duplicate a schedule

                        Parameters:
                           (integer) $scheduleID   The schedule ID

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ['schedules'] The schedule that was duplicated
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiDuplicateSchedule($scheduleID);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>
            <div id="apiEnableSchedule" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiEnableSchedule()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiEnableSchedule($scheduleID)

                        Enable a schedule

                        Parameters:
                           (integer) $scheduleID   The schedule ID

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ['schedules'] The schedule that was enabled
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiEnableSchedule($scheduleID);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>
            <div id="apiDisableSchedule" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiDisableSchedule()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiDisableSchedule($scheduleID)

                        Disable a schedule

                        Parameters:
                           (integer) $scheduleID   The schedule ID

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ['schedules'] The schedule that was disabled
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiDisableSchedule($scheduleID);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>
            <div id="apiRunSchedule" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiRunSchedule()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiRunSchedule($scheduleID)

                        Run a schedule

                        Parameters:
                           (integer) $scheduleID   The schedule ID

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ['schedules'] The schedule that was executed
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiRunSchedule($scheduleID);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>
            <div id="apiGetSchedule" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiGetSchedule()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiGetSchedule($scheduleID)

                        Get a schedule

                        Parameters:
                           (integer) $scheduleID   The schedule ID

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ['schedules'] The schedule
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiGetSchedule($scheduleID);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_schedule">Run Example</button> Get a schedule
               </div>
               <div id="output_get_schedule" class="api-example-output"></div>
            </div>
            <div id="apiSetSchedule" class="api-object">
               <div class="api-header">
                  fmAdminAPI::apiSetSchedule()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiSetSchedule($scheduleID, $data)

                        Set a schedule

                        Parameters:
                           (integer)   $scheduleID   The schedule ID
                           (array)     $data         An array of options to set specifying the schedule.

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['result'] 0 if successful else an error code
                              ['schedules'] The schedule
                              ...

                        Example:
                           $fm = new fmAdminAPI($host, $username, $password);
                           $apiResult = $fm->apiSetSchedule($scheduleID, $data);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>
            </div>

         </div>

      </div>


      <!-- Always at the end of the page -->
      <footer>
         <a href="http://www.driftwoodinteractive.com"><img src="../../img/di.png" height="32" width="128" alt="Driftwood Interactive" style="vertical-align:text-bottom"></a><br>
         Copyright &copy; <?php echo date('Y'); ?> Mark DeNyse Released Under the MIT License.
      </footer>

		<script src="../../js/main.js"></script>
      <script>
         javascript:ExpandDropDown("fmAdminAPI");
      </script>

      <script>
         $(".run_php_script").click(function() {
            target = $(this).attr("target");
            theID = $(this).attr("phpscript");
            outputID = $(this).attr("output");
            if (outputID == null) {
               outputID = theID;
            }

            theURL = "examples/"+ theID;
            if (theID.indexOf(".php") == -1) {
               theURL += ".php";
            }
            questionMark = "?";
            if (theID.indexOf(questionMark) == -1) {
               theURL += "?";
            }
            theURL += "&v=<?php echo API_VERSION; ?>";

            if (target == "_blank") {
               window.open(theURL);
            }
            else {
               $.ajax({
                    url: theURL,
                    dataType: 'html',
                    success: function(data, textStatus, jqXHR) {
                        if (data != null) {
                           $("#output_" + outputID).append(data);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) { alert("error!");
                        $("#output_" + outputID).html(textStatus +" "+ errorThrown);
                    }
               });
            }
         });
      </script>

   </body>
</html>
