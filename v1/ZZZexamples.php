<?php
// *********************************************************************************************************************************
//
// examples.php
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


// Let the caller decide which version of the API to use. We hard code the version we know works.
// The caller can also pass 'Latest' as a demonstration that these examples also work on the latest version.
define('SUPPORTED_API_VERSION', 1);

define('DATA_API_VERSION', array_key_exists('v', $_GET) ? $_GET['v'] : SUPPORTED_API_VERSION);


?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8" />
      <title>fmPDA &#9829; Examples</title>
      <link href="../css/normalize.css" rel="stylesheet" />
      <link href="../css/styles.css" rel="stylesheet" />
   </head>
   <body>

      <h1 id="header">
         <span>fmPDA <span style="color: #ff0000;">&#9829;</span><br>
         A replacement class for the FileMaker API For PHP using the FileMaker Data API (REST)<br>
      </h1>

      <h2>
      <div style="text-align: center;">
         <span class="link"><a href="../index.php">Home</a></span>
         <span class="link"><a href="index.php">Version 1</a></span>
         <span class="link">Examples</span>
         <span class="link"><a href="https://driftwoodinteractive.com/fmpda">Download</a></span>
      </div>
      </h2>

      <h3 style="text-align: center;">This version (1) is for FileMaker Server 17 (and beyond) of the Data and Admin APIs.</h3>

      <h3>
         fmCURL
      </h3>
      <ul>
         <li style="list-style-type: none; font-size: 1.25em;">&#8595;&nbsp;&nbsp;Get</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmCURL/curl.php">Get contents of www.example.com</a></li>
         </ul>
      </ul>

      <h3>
         fmAdminAPI
      </h3>
      <ul>
         <li style="list-style-type: none; font-size: 1.25em;">&#128274;&nbsp;&nbsp;Authentication</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmAdminAPI/login.php?v=<?php echo DATA_API_VERSION; ?>">Login</a></li>
            <li><a href="test-fmAdminAPI/logout.php?v=<?php echo DATA_API_VERSION; ?>">Logout</a></li>
         </ul>
         <br>
         <li style="list-style-type: none; font-size: 1.25em;">&#9729;&nbsp;&nbsp;Database Server</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmAdminAPI/list_databases.php?v=<?php echo DATA_API_VERSION; ?>">List Databases</a></li>
            <li><a href="test-fmAdminAPI/get_server_status.php?v=<?php echo DATA_API_VERSION; ?>">Get Server Status</a></li>
         </ul>
         <br>
         <li style="list-style-type: none; font-size: 1.25em;">&#128337;&nbsp;&nbsp;Schedules</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmAdminAPI/list_schedules.php?v=<?php echo DATA_API_VERSION; ?>">List Schedules</a></li>
         </ul>
         <br>
      </ul>

      <h3>
         fmDataAPI
      </h3>
      <ul>
         <li style="list-style-type: none; font-size: 1.25em;">&#128274;&nbsp;&nbsp;Authentication</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmDataAPI/login.php?v=<?php echo DATA_API_VERSION; ?>">Login</a></li>
            <li><a href="test-fmDataAPI/logout.php?v=<?php echo DATA_API_VERSION; ?>">Logout</a></li>
         </ul>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#8595;&nbsp;&nbsp;Get</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmDataAPI/get_record.php?v=<?php echo DATA_API_VERSION; ?>">Get Record By ID</a></li>
            <li><a href="test-fmDataAPI/get_record_external.php?v=<?php echo DATA_API_VERSION; ?>">Get Record By ID with External Authentication</a></li>
            <li><a href="test-fmDataAPI/get_records.php?v=<?php echo DATA_API_VERSION; ?>">Get Records</a></li>
         </ul>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#128269;&nbsp;&nbsp;Find</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmDataAPI/find_non_compound.php?v=<?php echo DATA_API_VERSION; ?>">Find (Non compound)</a></li>
            <li><a href="test-fmDataAPI/find_compound.php?v=<?php echo DATA_API_VERSION; ?>">Find (Compound)</a></li>
         </ul>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#9998;&nbsp;&nbsp;Editing</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmDataAPI/add.php?v=<?php echo DATA_API_VERSION; ?>">&#10010; Add Record</a></li>
            <li><a href="test-fmDataAPI/edit.php?v=<?php echo DATA_API_VERSION; ?>">&#9998; Edit Record</a></li>
            <li><a href="test-fmDataAPI/delete.php?v=<?php echo DATA_API_VERSION; ?>">&#128465; Delete Record</a></li>
         </ul>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#128444;&nbsp;&nbsp;Containers</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmDataAPI/container_get.php?v=<?php echo DATA_API_VERSION; ?>">Get Container</a></li>
            <li><a href="test-fmDataAPI/container_upload.php?v=<?php echo DATA_API_VERSION; ?>">Upload Container</a></li>
         </ul>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#128220;&nbsp;&nbsp;Scripting</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmDataAPI/script.php?v=<?php echo DATA_API_VERSION; ?>">Run a Script</a></li>
         </ul>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#128197;&nbsp;&nbsp;Calendar Example</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmDataAPI/calendar/calendar.php?v=<?php echo DATA_API_VERSION; ?>">From my 2014 DevCon talk demonstrating Full Calendar & CWP (with AJAX)</a></li>
         </ul>
         <br>
      </ul>

      <h3>
         fmPDA
      </h3>
      <ul>
         <li style="list-style-type: none; font-size: 1.25em;">&#8595;&nbsp;&nbsp;Get</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmpda/get_record_by_id.php?v=<?php echo DATA_API_VERSION; ?>">Get Record By ID</a></li>
         </ul>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#128269;&nbsp;&nbsp;Find</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmpda/find_all.php?v=<?php echo DATA_API_VERSION; ?>">Find All</a></li>
            <li><a href="test-fmpda/find_any.php?v=<?php echo DATA_API_VERSION; ?>">Find Any</a></li>
            <li><a href="test-fmpda/find_non_compound.php?v=<?php echo DATA_API_VERSION; ?>">Find (Non compound)</a></li>
            <li><a href="test-fmpda/find_compound.php?v=<?php echo DATA_API_VERSION; ?>">Find (Compound)</a></li>
         </ul>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#9998;&nbsp;&nbsp;Editing</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmpda/add.php?v=<?php echo DATA_API_VERSION; ?>">&#10010; Add Record</a></li>
            <li><a href="test-fmpda/create_commit.php?v=<?php echo DATA_API_VERSION; ?>">&#10010; Create Record & Commit</a></li>
            <li><a href="test-fmpda/edit.php?v=<?php echo DATA_API_VERSION; ?>">&#9998; Edit Record</a></li>
            <li><a href="test-fmpda/edit_commit.php?v=<?php echo DATA_API_VERSION; ?>">&#9998; Get Record, Edit & Commit</a></li>
            <li><a href="test-fmpda/duplicate.php?v=<?php echo DATA_API_VERSION; ?>">&#9998; Duplicate Record (requires you adding an FM Script which has a single 'Duplicate Record' script step)</a></li>
            <li><a href="test-fmpda/delete.php?v=<?php echo DATA_API_VERSION; ?>">&#128465; Delete Record</a></li>
         </ul>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#128444;&nbsp;&nbsp;Containers</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmpda/container_data.php?v=<?php echo DATA_API_VERSION; ?>">Get Container Data</a></li>
            <li><a href="test-fmpda/container_data_url.php?v=<?php echo DATA_API_VERSION; ?>">Get Container Data URL</a></li>
            <li><a href="test-fmpda/container_upload.php?v=<?php echo DATA_API_VERSION; ?>">Upload Container</a></li>
        </ul>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#128220;&nbsp;&nbsp;Scripting</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmpda/script.php?v=<?php echo DATA_API_VERSION; ?>">Run a Script</a></li>
         </ul>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#128197;&nbsp;&nbsp;Calendar Example</li>
         <ul style="line-height: 1.5em;">
            <li><a href="test-fmpda/calendar/calendar.php?v=<?php echo DATA_API_VERSION; ?>">From my 2014 DevCon talk demonstrating Full Calendar & CWP (with AJAX)</a></li>
         </ul>
         <br>
      </ul>

      <div id="footer">
         <a href="http://www.driftwoodinteractive.com"><img src="../img/di.png" height="32" width="128" alt="Driftwood Interactive" style="vertical-align:text-bottom"></a><br>
         <br>
         Copyright &copy; <?php echo date('Y'); ?> Mark DeNyse Released Under the MIT License.
      </div>
   </body>
</html>
