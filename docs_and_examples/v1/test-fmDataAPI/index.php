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

define('DATA_API_VERSION', array_key_exists('v', $_GET) ? $_GET['v'] : SUPPORTED_API_VERSION);

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
      <link href="../../css/styles.css?zzz=<?php echo rand(); ?>" rel="stylesheet" />
   </head>
   <body>

      <!-- Always on top: Position Fixed-->
      <header>
         fmPDA <span class="fmPDA-heart"><i class="fas fa-heart"></i></span>
         <span style="float: right;">Version <strong><?php echo DATA_API_VERSION; ?></strong> (FileMaker Server 17+)</span>
      </header>


      <!-- Fixed size after header-->
      <div class="content">

         <!-- Always on top. Fixed position, fixed width, relative to content width-->
         <div class="navigation-left">
            <?php echo GetNavigationMenu(1, DATA_API_VERSION); ?>
         </div>

          <!-- Scrollable div with main content -->
         <div class="main">

            <div id="dataapi-optional-parameters" class="api-object">
               <div class="api-header">
                  Optional parameters to most API methods.
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     The $options parameter to most apiNNNNNN() methods is an array of abstracted options passed to the Data API.
                     Not every call supports all these options. See the documentation for each call to determine what options are allowed.

                     $options = array(
                        'limit'                  => &lt;integer>,
                        'offset'                 => &lt;integer>,
                        'sort'                   => array(array('fieldName' => &lt;string>) [, 'fieldName' => &lt;string>)] [, 'sortOrder' => &lt;string>)]), // sortOder should be 'ascend' or 'descend'
                        'script'                 => &lt;string>,
                        'scriptParams'           => &lt;string>,
                        'scriptPrerequest'       => &lt;string>,
                        'scriptPrerequestParams' => &lt;string>,
                        'scriptPresort'          => &lt;string>,
                        'scriptPresortParams'    => &lt;string>,
                        'layoutResponse'         => &lt;string>,
                        'portals'                => array(&lt;string> [, &lt;string>, ...]),

                        'portalLimits'           => array(
                                                          array('name' => &lt;string>, 'limit' => (integer)),
                                                          ...
                                                         ),

                        'portalOffsets'          => array(
                                                          array('name' => '&lt;string>', 'offset' => (integer)),
                                                          ...
                                                         ),

                        'query'                 => array(
                                                           array('&lt;fieldname>' => &lt;string> [, '&lt;fieldname>' => &lt;string>, ...] ['omit' => 'true']),
                                                           ...
                                                         )
                     );
                  </code></pre>
               </div>
            </div>

            <div id="dataapi-constructor" class="api-object">
               <div class="api-header">
                  fmDataAPI Constructor
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     __construct($database, $host, $username, $password, $options = array())

                        Constructor for the fmDataAPI class.

                        Parameters:
                           (string)  $database         The name of the database (do NOT include the .fmpNN extension)
                           (string)  $host             The host name typically in the format of https://HOSTNAME
                           (string)  $username         The user name of the account to authenticate with
                           (string)  $password         The password of the account to authenticate with
                           (array)   $options          Optional parameters
                                                         ['version'] Version of the API to use (1, 2, etc. or 'Latest')

                                                         ['authentication'] set to 'oauth' for oauth authentication
                                                         ['oauthID'] oauthID
                                                         ['oauthIdentifier'] oauth identifier

                                                         ['sources'] => array(
                                                                          array(
                                                                            'database'  => '',      // do NOT include .fmpNN
                                                                            'username'  => '',
                                                                            'password'  => ''
                                                                          )
                                                                        )

                         Returns:
                            The newly created object.

                         Example:
                            $fm = new fmDataAPI($database, $host, $username, $password);
                  </code></pre>
               </div>
            </div>


            <div id="apiLogin" class="api-object">
               <div class="api-header">
                  fmDataAPI::apiLogin()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiLogin()

                        Create a new session on the server. Authentication parameters were previously passed to the  __construct method.
                        Normally you will not call this method as the other apiNNNNNN() methods take care of logging in when appropriate.
                        By default, the authentication token is stored within this class and reused for all further calls. If the server
                        replies that the token is not longer valid (FM_ERROR_INVALID_TOKEN - 952), this class will automatically login
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
                           $fm = new fmDataAPI($database, $host, $username, $password);
                           $apiResult = $fm->apiLogin();
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="login">Run Example</button> Log in and create a new session
               </div>
               <div id="output_login" class="api-example-output"></div>
            </div>

            <div id="apiLogout" class="api-object">
               <div class="api-header">
                  fmDataAPI::apiLogout()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiLogout()

                        Logs out of the current session and clears the username, password, and authentication token.
                        Any global variables previously set will be restored to their previous values by the server.

                        Normally you will not call this method so that you can keep re-using the authentication token for future calls.
                        Only logout if you know you are completely done with the session. This will also clear the stored username/password.

                        Parameters:
                           None

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] Typically this will be empty
                              ['messages'] Array of code/message pairs

                        Example:
                           $fm = new fmDataAPI($database, $host, $username, $password);
                           $apiResult = $fm->apiLogout();
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="logout">Run Example</button> Log out and destroy the session
               </div>
               <div id="output_logout" class="api-example-output"></div>
            </div>

            <div id="apiGetRecord" class="api-object">
               <div class="api-header">
                  fmDataAPI::apiGetRecord()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiGetRecord($layout, $recordID, $params = '', $options = array())

                        Get the record specified by $recordID.

                        Parameters:
                           (string)  $layout           The name of the layout
                           (integer) $recordID         The recordID of the record to retrieve
                           (string)  $params           The raw GET parameters to modify the call. Typically done with $options [optional]
                           (array)   $options          Additional API parameters  call [optional]

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] The record data
                              ['messages'] Array of code/message pairs

                        Example:
                           $fm = new fmDataAPI($database, $host, $username, $password);
                           $apiResult = $fm->apiGetRecord('Web_Project', 1);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                           $apiResult = $fm->apiGetRecord('Web_Project', 55555);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_record">Run Example 1 </button> Get 2 records by ID, second one will fail because it doesn't exist
                 <br>
                 <br>
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_record_external" output="get_record">Run Example 2</button> Get 1 record by ID using external authentication
               </div>
               <div id="output_get_record" class="api-example-output"></div>
            </div>

            <div id="apiGetRecords" class="api-object">
               <div class="api-header">
                  fmDataAPI::apiGetRecords()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiGetRecords($layout, $params = '', $options = array())

                        Get a series of records. By default the first 100 records will be returned.

                        Parameters:
                           (string)  $layout           The name of the layout
                           (string)  $params           The raw GET parameters (ie: '&_limit=50&_offset=10') to modify the call. Typically done with $options [optional]
                           (array)   $options          Additional API parameters(ie: array('limit' => 50, 'offset' => 10) to modify the call [optional]

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] The record data
                              ['messages'] Array of code/message pairs

                        Example:
                           $fm = new fmDataAPI($database, $host, $username, $password);
                           $apiResult = $fm->apiGetRecords('Web_Project', '', array('limit' => 50, 'offset' => 10));
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_records">Run Example</button> Get 50 records starting at the 10th record
               </div>
               <div id="output_get_records" class="api-example-output"></div>
            </div>

            <div id="apiFindRecords" class="api-object">
               <div class="api-header">
                  fmDataAPI::apiFindRecords()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiFindRecords($layout, $data, $options = array())

                        Find record(s) with a query which can be a non-compound or a compound find.

                        Parameters:
                           (string)  $layout           The name of the layout
                           (array)   $data             An array of query parameters in the format the Data API expects.
                                                       Currently this is the same format as $options['query'].
                           (array)   $options          Additional API parameters. Valid options:
                                                         ['limit']
                                                         ['offset']
                                                         ['sort']
                                                         ['script']
                                                         ['scriptParams']
                                                         ['scriptPrerequest']
                                                         ['scriptPrerequestParams']
                                                         ['scriptPresort']
                                                         ['scriptPresortParams']
                                                         ['layoutResponse']
                                                         ['portals']
                                                         ['portalLimits']
                                                         ['portalOffsets']
                                                         ['query']

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] The record(s) data
                              ['messages'] Array of code/message pairs

                        Example:
                           $fm = new fmDataAPI($database, $host, $username, $password);
                           $data = array(
                                    'query'=> array(
                                                array('Name' => 'Test', 'ColorIndex' => '5', 'omit' => 'true'),
                                                array('ColorIndex' => '20')
                                              )
                                   );
                           $apiResult = $fm->apiFindRecords('Web_Project', $data);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="find_non_compound">Run Example 1 </button> Non compound find
                 <br>
                 <br>
                 <button type="button" class="btn btn-primary run_php_script" phpscript="find_compound" output="find_non_compound">Run Example 2</button> Compound find
               </div>
               <div id="output_find_non_compound" class="api-example-output"></div>
            </div>

            <div id="apiAddRecord" class="api-object">
               <div class="api-header">
                  fmDataAPI::apiCreateRecord()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiCreateRecord($layout, $fields, $options = array())

                        Create a new record.

                        Parameters:
                           (string)  $layout           The name of the layout
                           (array)   $fields           An array of field name/value pairs
                           (array)   $options          Additional API parameters. Valid options:
                                                         ['script']
                                                         ['scriptParams']
                                                         ['scriptPrerequest']
                                                         ['scriptPrerequestParams']
                                                         ['scriptPresort']
                                                         ['scriptPresortParams']

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] If the call succeeds:
                                           ['modId'] Modification ID (should be 0)
                                           ['recordId'] Record ID of the newly created record
                              ['response']
                              ['messages'] Array of code/message pairs

                        Example:
                           $fm = new fmDataAPI($database, $host, $username, $password);
                           $fields = array();
                           $fields['Name'] = 'Test';
                           $fields['ColorIndex'] = 999;
                           $apiResult = $fm->apiCreateRecord('Web_Project', $fields);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="add">Run Example</button> Add a record
               </div>
               <div id="output_add" class="api-example-output"></div>
            </div>

            <div id="apiEditRecord" class="api-object">
               <div class="api-header">
                  fmDataAPI::EditRecord()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiEditRecord($layout, $recordID, $data, $options = array())

                        Edit a record.

                        Parameters:
                           (string)  $layout           The name of the layout
                           (integer) $recordID         The recordID of the record to edit
                           (array)   $data             Array of field name/value pairs
                           (array)   $options          Additional API parameters. Valid options:
                                                         ['script']
                                                         ['scriptParams']
                                                         ['scriptPrerequest']
                                                         ['scriptPrerequestParams']
                                                         ['scriptPresort']
                                                         ['scriptPresortParams']

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] If the call succeeds:
                                           ['modId'] The new modification ID of the edited record
                              ['messages'] Array of code/message pairs

                        Example:
                           $fm = new fmDataAPI($database, $host, $username, $password);
                           $fields = array();
                           $fields['ColorIndex'] = 48;
                           $apiResult = $fm->apiEditRecord('Web_Project', 6, $fields);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="edit">Run Example</button> Edit a record
               </div>
               <div id="output_edit" class="api-example-output"></div>
            </div>

            <div id="apiDeleteRecord" class="api-object">
               <div class="api-header">
                  fmDataAPI::apiDeleteRecord()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiDeleteRecord($layout, $recordID, $options = array())

                        Delete a record.

                        Parameters:
                           (string)  $layout           The name of the layout
                           (integer) $recordID         The recordID of the record to delete
                           (array)   $options          Additional API parameters. Valid options:
                                                         ['script']
                                                         ['scriptParams']
                                                         ['scriptPrerequest']
                                                         ['scriptPrerequestParams']
                                                         ['scriptPresort']
                                                         ['scriptPresortParams']

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] Typically this will be empty
                              ['messages'] Array of code/message pairs

                        Example:
                           $fm = new fmDataAPI($database, $host, $username, $password);
                           $apiResult = $fm->apiDeleteRecord('Web_Project', 5);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="delete">Run Example</button> Delete a record
               </div>
               <div id="output_delete" class="api-example-output"></div>
            </div>

            <div id="apiSetGlobals" class="api-object">
               <div class="api-header">
                  fmDataAPI::apiSetGlobals()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiSetGlobalFields($layout, $data)

                        Set global variable(s). The values retain their value throughout the session until you logout or the token expires.

                        Parameters:
                           (string)  $layout           The name of the layout
                           (array)   $data             An array of field name/value pairs

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] Typically this will be empty
                              ['messages'] Array of code/message pairs

                        Example:
                           $fm = new fmDataAPI($database, $host, $username, $password);
                           $apiResult = $fm->apiSetGlobalFields('Web_Global', array('Project::gGlobal' => 5));
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="set_globals">Run Example</button> Set a global field
               </div>
               <div id="output_set_globals" class="api-example-output"></div>
            </div>

            <div id="apiGetContainer" class="api-object">
               <div class="api-header">
                  fmDataAPI::apiGetContainer() or fmDataAPI::GetRecord() to retrieve a URL or the container contents
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                        Example 1:
                           $fm = new fmDataAPI($database, $host, $username, $password);
                           $apiResult = $fm->apiGetRecord('Web_Project', 1);
                           if (! $fm->getIsError($apiResult)) {
                              $responseData = $fm->getResponseData($apiResult);

                              $photoURL = $responseData[0][FM_FIELD_DATA]['Photo'];
                              $photoHW = $responseData[0][FM_FIELD_DATA]['c_PhotoHWHTML'];
                              $photoName = $responseData[0][FM_FIELD_DATA]['PhotoName'];

                              echo '&lt;img src="'. $photoURL .'" '. $photoHW .' alt="'. $photoName .'" /><br>';
                           }

                        Examples 2, 3, & 4:
                           $options = array();
                           $options['action'] = 'get';              // get, download, or inline
                           $options['fileNameField'] = 'PhotoName'; // The value stored in the PhotoName field becomes the file name of the downloaded file.

                           $fm = new fmDataAPI(FM_DATABASE, FM_HOST, FM_USERNAME, FM_PASSWORD);
                           $apiResult = $fm->apiGetContainer('Web_Project', 114, 'Photo', FM_FIELD_REPETITION_1, $options);
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="container_get_url" output="container_get">Run Example 1</button> Use fmDataAPI::GetRecord() to retrieve a record and display the container in an &lt;img&gt; tag
                 <br>
                 <br>
                 <button type="button" class="btn btn-primary run_php_script" phpscript="container_get.php?action=get" output="container_get">Run Example 2</button> Use fmDataAPI::apiGetContainer() to display the container field contents.
                 <br>
                 <br>
                 <button type="button" class="btn btn-primary run_php_script" phpscript="container_get.php?action=download" output="container_get" target="_blank">Run Example 3</button> Use fmDataAPI::apiGetContainer() to download the container field contents.
                 <br>
                 <br>
                 <button type="button" class="btn btn-primary run_php_script" phpscript="container_get.php?action=inline" output="container_get" target="_blank">Run Example 4</button> Use fmDataAPI::apiGetContainer() to download/inline the container field contents.
               </div>
               <div id="output_container_get" class="api-example-output"></div>
            </div>

            <div id="apiUploadContainer" class="api-object">
               <div class="api-header">
                  fmDataAPI::apiUploadContainer()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiUploadContainer($layout, $recordID, $fieldName, $fieldRepetition, $file)

                        Upload a file to a container field.

                        Parameters:
                           (string)  $layout           The name of the layout
                           (integer) $recordID         The recordID of the record to store the file in
                           (string)  $fieldName        The field name where the file will be stored
                           (integer) $fieldRepetition  The field repetition number
                           (string)  $file             An array of information about the file to be uploaded. You specify ['path'] or ['contents']:
                                                          $file['path']       The path to the file to upload (use this or ['contents'])
                                                          $file['contents']   The file contents (use this or ['path'])
                                                          $file['name']       The file name (required if you use ['contents'] otherwise
                                                                              it will be determined)
                                                          $file['mimeType']   The MIME type

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] Typically this will be empty
                              ['messages'] Array of code/message pairs

                        Example:
                           $fm = new fmDataAPI($database, $host, $username, $password);
                           $file = array();
                           $file['path'] = 'sample_files/sample.png';
                           $apiResult = $fm->apiUploadContainer('Web_Project', 1, 'Photo', FM_FIELD_REPETITION_1, $file);
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="container_upload">Run Example</button> Upload a file to a container field
               </div>
               <div id="output_container_upload" class="api-example-output"></div>
            </div>

            <div id="apiScript" class="api-object">
               <div class="api-header">
                  fmDataAPI::apiPerformScript()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function apiPerformScript($layout, $scriptName, $params = '', $layoutResponse = '')

                        Execute a script. We do this by doing a apiGetRecords() call for the first record on the specified layout.
                        For this to work, *YOU MUST* have at least one record in this table or the script *WILL NOT EXECUTE*.
                        For efficiency, you may want to create a table with just one record and no fields.
                        Typically you will call this with an $layout set to a layout with nothing on it and then use $layoutResponse
                        to indicate where you expect the result from the script to have put the record(s).

                        Parameters:
                           (string)  $layout           The name of the layout
                           (string)  $scriptName       The name of the FileMaker script to execute
                           (string)  $params           The script parameter
                           (string)  $layoutResponse   The name of the layout that any found set will be returned from

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] Returns any record(s) in ['data'] if there is a found set.
                                           ['scriptError'] has any scripting error
                                           ['scriptResult'] is the value returned from the Exit Script[] script step
                              ['messages'] Array of code/message pairs

                        Example:
                           $fm = new fmDataAPI($database, $host, $username, $password);
                           $apiResult = $fm->apiPerformScript('Web_Global', 'Test', 'some', 'Web_Project');
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="script">Run Example</button> Run a script
               </div>
               <div id="output_script" class="api-example-output"></div>
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
         javascript:ExpandDropDown("fmDataAPI");
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
            theURL += "&v=<?php echo DATA_API_VERSION; ?>";

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
