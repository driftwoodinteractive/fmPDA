<?php
// *********************************************************************************************************************************
//
// index.php
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

require_once '../nav.inc.php';

define('DATA_API_VERSION', 1);
define('ADMIN_API_VERSION', 2);

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
         <span style="float: right;">Version <strong><?php echo DATA_API_VERSION; ?></strong> (FileMaker Server 18+)</span>
      </header>


      <!-- Fixed size after header-->
      <div class="content">

         <!-- Always on top. Fixed position, fixed width, relative to content width-->
         <div class="navigation-left">
            <?php echo GetNavigationMenu(1, 2, ADMIN_API_VERSION); ?>
         </div>

          <!-- Scrollable div with main content -->
         <div class="main">

            <div id="fmpda-constructor" class="api-object">
               <div class="api-header">
                  fmPDA Constructor
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     __construct($database, $host, $username, $password, $options = array())

                        Constructor for fmPDA.

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

                                                           ['sources'] => array(                      External database authentication
                                                                            array(
                                                                              'database'  => '',      // do NOT include .fmpNN
                                                                              'username'  => '',
                                                                              'password'  => ''
                                                                            )
                                                                          )

                        Returns:
                           The newly created object.

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                  </code></pre>
               </div>
            </div>

            <div id="listdatabases" class="api-object">
               <div class="api-header">
                  fmPDA::listDatabases($userName = '', $password = '')
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function listDatabases()

                        Retrieve a list of hosted FileMaker databases that the given credentails can see.

                        Parameters:
                           (string)  $username         The user name of the account to authenticate with
                                                       If empty, the username passed to the constructor will be used.
                           (string)  $password         The password of the account to authenticate with
                                                       If empty, the password passed to the constructor will be used.

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] A list of databases
                              ['messages'] Array of code/message pairs

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $apiResult = $fm->listDatabases()
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" api="data" phpscript="list_databases">Run Example</button> Return a list of databases
               </div>
               <div id="output_list_databases" class="api-example-output"></div>
            </div>

            <div id="listlayouts" class="api-object">
               <div class="api-header">
                  fmPDA::listLayouts()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function listLayouts()

                        Retrieves a list of layouts for the database

                        Parameters:
                           None

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] A list of layouts in the database
                              ['messages'] Array of code/message pairs

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $apiResult = $fm->listLayouts()
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" api="data" phpscript="list_layouts">Run Example</button> Return a list of layouts in the database
               </div>
               <div id="output_list_layouts" class="api-example-output"></div>
            </div>

            <div id="apiListScripts" class="api-object">
               <div class="api-header">
                  fmPDA::listScripts()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function listScripts()

                        Retrieves a list of scripts for the database

                        Parameters:
                           None

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] A list of scripts in the database
                              ['messages'] Array of code/message pairs

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $apiResult = $fm->listScripts()
                           if (! $fm->getIsError($apiResult)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" api="data" phpscript="list_scripts">Run Example</button> Return a list of scripts in the database
               </div>
               <div id="output_list_scripts" class="api-example-output"></div>
            </div>

            <div id="apiGetRecordID" class="api-object">
               <div class="api-header">
                  fmPDA::getRecordById()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function getRecordById($layout, $recordID = '')

                        Get the record specified by $recordID. If you omit the recordID, the first record in the table is returned.

                        Parameters:
                           (string)  $layout           The name of the layout
                           (integer) $recordID         The recordID of the record to retrieve

                        Returns:
                           An fmRecord or fmError object

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $result = $fm->getRecordById('Web_Project', 1);
                           if (! $fm->getIsError($result)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_record_by_id">Run Example</button> Get 2 records by ID, second one will fail because it doesn't exist
               </div>
               <div id="output_get_record_by_id" class="api-example-output"></div>
            </div>

            <div id="apiFindAll" class="api-object">
               <div class="api-header">
                  fmPDA::newFindAllCommand()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function newFindAllCommand($layoutName)

                        Create a new find all object.

                        Parameters:
                           (string)  $layoutName           The name of the layout

                        Returns:
                           A fmFind object

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $findAllCommand = $fm->newFindAllCommand($layoutName);
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="find_all">Run Example</button> Find all records
               </div>
               <div id="output_find_all" class="api-example-output"></div>
            </div>

            <div id="apiFindAny" class="api-object">
               <div class="api-header">
                  fmPDA::newFindAnyCommand()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function newFindAnyCommand($layoutName)

                        Create a new find any object.

                        Parameters:
                           (string)  $layoutName           The name of the layout

                        Returns:
                           A fmFind object

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $findAnyCommand = $fm->newFindAnyCommand($layoutName);
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="find_any">Run Example</button> Find the first record
               </div>
               <div id="output_find_any" class="api-example-output"></div>
            </div>

            <div id="apiFindNonCompound" class="api-object">
               <div class="api-header">
                  fmPDA::newFindCommand()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function newFindCommand($layoutName)

                        Create a new find object.

                        Parameters:
                           (string)  $layoutName           The name of the layout

                        Returns:
                           A fmFindQuery object

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $findCommand = $fm->newFindCommand($layoutName);
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="find_non_compound">Run Example</button> Execute a non-compound find
               </div>
               <div id="output_find_non_compound" class="api-example-output"></div>
            </div>

            <div id="apiFindCompound" class="api-object">
               <div class="api-header">
                  fmPDA::newCompoundFindCommand()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function newCompoundFindCommand($layoutName)

                        Create a new compound find object.

                        Parameters:
                           (string)  $layoutName           The name of the layout

                        Returns:
                           A fmFindQuery object

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $findCommand = $fm->newCompoundFindCommand($layoutName);
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="find_compound">Run Example</button> Execute a compound find
               </div>
               <div id="output_find_compound" class="api-example-output"></div>
            </div>

            <div id="apiAdd" class="api-object">
               <div class="api-header">
                  fmPDA::createRecord()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function createRecord($layout, $fieldValues = array())

                        Create a record object. The records Commit() method will send the data to the server.

                        Parameters:
                           (string)  $layout           The name of the layout
                           (array)   $fieldValues      An array of field name/value pairs

                        Returns:
                           The newly created record object.

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $record = $fm->createRecord('Web_Project', $fields);
                           $result = $record->commit();
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="add">Run Example</button> Create a record
               </div>
               <div id="output_add" class="api-example-output"></div>
            </div>

            <div id="apiCreateCommit" class="api-object">
               <div class="api-header">
                  fmPDA::newAddCommand()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function newAddCommand($layoutName)

                        Create a new add command object.

                        Parameters:
                           (string)  $layoutName           The name of the layout

                        Returns:
                           A fmRecord object

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $addCommand = $fm->newAddCommand('Web_Project');
                           $addCommand->setField('Name', 'Test');
                           $result = $addCommand->execute();
                  </code></pre>
               </div>
            </div>

            <div id="apiEdit" class="api-object">
               <div class="api-header">
                  fmPDA::newEditCommand()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function newEditCommand($layoutName, $recordID)

                        Create a new edit record object.

                        Parameters:
                           (string)  $layoutName           The name of the layout
                           (integer) $recordID             The recordID of the record to edit

                        Returns:
                           A fmEdit object

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $findAnyCommand = $fm->newEditCommand($layoutName, $recordID);
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="edit">Run Example</button> Edit a record
               </div>
               <div id="output_edit" class="api-example-output"></div>
            </div>

            <div id="apiEditCommit" class="api-object">
               <div class="api-header">
                  fmPDA::getRecordById() and fmRecord::commit()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     Use fmPDA::getRecordById() and fmRecord::commit() to get the record and then commit changes.
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="edit_commit">Run Example</button> Get a record and commit changes
               </div>
               <div id="output_edit_commit" class="api-example-output"></div>
            </div>

            <div id="apiDuplicate" class="api-object">
               <div class="api-header">
                  fmPDA::newDuplicateCommand()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function newDuplicateCommand($layoutName, $recordID)

                        Create a new duplicate record object.

                        Parameters:
                           (string)  $layoutName           The name of the layout
                           (integer) $recordID             The recordID of the record to duplicate

                        Returns:
                           A fmDuplicate object

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $newDuplicateCommand = $fm->newDuplicateCommand($layoutName, $recordID, $duplicateScript);
                           $result = $newDuplicateCommand->execute();
                           if (! $fm->getIsError($result)) {
                              ...
                           }
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="duplicate">Run Example</button> Duplicate a record
               </div>
               <div id="output_duplicate" class="api-example-output"></div>
            </div>

            <div id="apiDelete" class="api-object">
               <div class="api-header">
                  fmPDA::newDeleteCommand()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function newDeleteCommand($layoutName, $recordID)

                        Create a new delete record object.

                        Parameters:
                           (string)  $layoutName           The name of the layout
                           (integer) $recordID             The recordID of the record to delete

                        Returns:
                           A fmDelete object

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $findAnyCommand = $fm->newDeleteCommand($layoutName, $recordID);
                           $result = $findAnyCommand->execute();
                           if (! $fm->getIsError($result)) {
                              ...
                           }

                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="delete">Run Example</button> Delete a record
               </div>
               <div id="output_delete" class="api-example-output"></div>
            </div>

            <div id="apiGetContainer" class="api-object">
               <div class="api-header">
                  fmPDA::getContainerData()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function getContainerData($containerURL, $options)

                        Returns the contents of the container field. Given the changes with the Data API, you can bypass this entirely if you are
                        simply wanting to display the container with an &lt;img tag. Just take the url returned in the field and feed that into the
                        &lt;img tag.

                        Note that the $options parameter is new to fmPDA - it does not exist in the FileMaker API for PHP.

                        Parameters:
                           (string)  $containerURL      The URL to the container
                           (array)   $options           The options array as defined by fmCURL::getFile(), additionally:
                                                         ['fileNameField'] The field name where the file name is stored on the record
                                                                           This lets the caller specify the downloaded filename
                                                                           if [action'] = 'download'

                        Returns:
                           The contents of the container field

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $url = $fm->getContainerData($containerURL);
                  </code></pre>
               </div>

               <div class="api-example-output-header">

                 <button type="button" class="btn btn-primary run_php_script" phpscript="container_data.php?action=get" output="container_get">Run Example 1</button> Use fmPDA::getContainerData() to display the container field contents.
                 <br>
                 <br>
                 <button type="button" class="btn btn-primary run_php_script" phpscript="container_data.php?action=download" output="container_get" target="_blank">Run Example 2</button> Use fmPDA::getContainerData() to download the container field contents.
                 <br>
                 <br>
                 <button type="button" class="btn btn-primary run_php_script" phpscript="container_data.php?action=inline" output="container_get" target="_blank">Run Example 3</button> Use fmPDA::getContainerData() to download/inline the container field contents.
               </div>
               <div id="output_container_get" class="api-example-output"></div>
            </div>

            <div id="apiGetContainerURL" class="api-object">
               <div class="api-header">
                  fmPDA::getContainerDataURL()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function getContainerDataURL($graphicURL)

                        This is not needed anymore - just use the URL return in the field data directly. Your existing code will continue to work
                        but it would be best for performance if you modify your code to simply use the URL returned from getFieldUnencoded() and
                        place it in the &lt;img tag directly.

                        Parameters:
                           (string)  $layout           The name of the layout
                           (array)   $fieldValues      An array of field name/value pairs

                        Returns:
                           The same URL passed in

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $url = $fm->getContainerDataURL($graphicURL);
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="container_data_url">Run Example</button> Get the URL to the container field and display the container in an &lt;img&gt; tag
               </div>
               <div id="output_container_data_url" class="api-example-output"></div>
            </div>

            <div id="apiUploadFile" class="api-object">
               <div class="api-header">
                  fmPDA::newUploadContainerCommand()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function newUploadContainerCommand($layout, $recordID, $fieldName, $fieldRepetition, $file)

                        Upload a file to a container field.
                        This method does not exist in FileMakers API For PHP but is provided as the Data API now directly supports this.

                        Parameters:
                           (string)  $layout               The name of the layout
                           (integer) $recordID             The recordID of the record to edit
                           (string)  $fieldName            The field name where the file will be stored
                           (integer) $fieldRepetition      The field repetition number
                           (string)  $file                 An array of information about the file to be uploaded. You specify ['path'] or ['contents']:
                                                               $file['path']       The path to the file to upload (use this or ['contents'])
                                                               $file['contents']   The file contents (use this or ['path'])
                                                               $file['name']       The file name (required if you use ['contents'] otherwise
                                                                                   it will be determined)
                                                               $file['mimeType']   The MIME type

                        Returns:
                           A fmUpload object

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $file = array();
                           $file['path'] = 'sample_files/sample.png';
                           $uploadContainerCommand = $fm->newUploadContainerCommand($layout, $recordID, $fieldName, $fieldRepetition, $file);
                           $result = $performScriptCommand->execute();
                           if (! $fm->getIsError($result)) {
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
                  fmPDA::newPerformScriptCommand()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function newPerformScriptCommand($layout, $scriptName, $params = '')

                        Execute a script.

                        Parameters:
                           (string)  $layoutName           The name of the layout
                           (string)  $scriptName           The name of the FileMaker script to execute
                           (string)  $params               The script parameter

                        Returns:
                           A fmScript object

                        Example:
                           $fm = new fmPDA($database, $host, $username, $password);
                           $performScriptCommand = $fm->newPerformScriptCommand($layout, $scriptName, $params);
                           $result = $performScriptCommand->execute();
                           if (! $fm->getIsError($result)) {
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
         javascript:ExpandDropDown("fmPDA");
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

            theURL += ((theURL.indexOf("?") == -1) ? "?" : "&") + "v=<?php echo DATA_API_VERSION; ?>";

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
