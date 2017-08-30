<?php
// *********************************************************************************************************************************
//
// examples.php
//
// *********************************************************************************************************************************
//
// Copyright (c) 2017 Mark DeNyse
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
?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8" />
      <title>fmPDA &#9829; Examples</title>
      <link href="css/normalize.css" rel="stylesheet" />
      <link href="css/styles.css" rel="stylesheet" />
   </head>
   <body>

      <h1 id="header">
         fmPDA <span style="color: #ff0000;">&#9829;</span><br>
         A replacement class for the FileMaker API For PHP using the FileMaker Data API (REST)<br>
      </h1>

      <h2>
      <div style="text-align: center;">
         <span class="link"><a href="index.php">Introduction</a></span>
         <span class="link"><a href="versionhistory.php">Version History</a></span>
         <span class="link">Examples</span>
         <span class="link"><a href="https://driftwoodinteractive.com/fmpda">Download</a></span>
      </div>
      </h2>

      <h3>
         fmCURL
      </h3>
      <ul>
         <li style="list-style-type: none; font-size: 1.25em;">&#8595; Get</li>
         <ul>
            <li><a href="test-fmCURL/curl.php">Get contents of www.example.com</a></li>
         </ul>
      </ul>

      <h3>
         fmDataAPI
      </h3>
      <ul>
         <li style="list-style-type: none; font-size: 1.25em;">&#128269; Find</li>
         <ul>
            <li><a href="test-fmDataAPI/get_record.php">Get Record By ID</a></li>
         </ul>
      </ul>

      <h3>
         fmPDA
      </h3>
      <ul>
         <li style="list-style-type: none; font-size: 1.25em;">&#128269; Find</li>
         <ul>
            <li><a href="test-fmpda/get_record_by_id.php">Get Record By ID</a></li>
            <li><a href="test-fmpda/find_all.php">Find All</a></li>
            <li><a href="test-fmpda/find_any.php">Find Any</a></li>
            <li><a href="test-fmpda/find_non_compound.php">Find (Non compound)</a></li>
            <li><a href="test-fmpda/find_compound.php">Find (Compound)</a></li>
         </ul>
         <br>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#9998; Editing</li>
         <ul>
            <li><a href="test-fmpda/add.php">&#10010; Add Record</a></li>
            <li><a href="test-fmpda/create_commit.php">&#10010; Create Record & Commit</a></li>
            <li><a href="test-fmpda/edit.php">&#9998; Edit Record</a></li>
            <li><a href="test-fmpda/edit_commit.php">&#9998; Get Record, Edit & Commit</a></li>
            <li><a href="test-fmpda/delete.php">&#128465; Delete Record</a></li>
         </ul>
         <br>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#9968; Containers</li>
         <ul>
            <li><a href="test-fmpda/container_data.php">Get Container Data</a></li>
            <li><a href="test-fmpda/container_data_url.php">Get Container Data URL</a></li>
         </ul>
         <br>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#128220; Scripting</li>
         <ul>
            <li><a href="test-fmpda/script.php">Script execution - emulated with the old XML interface. When FMI supoprts this directly, fmPDA will be updated to use the Data API.</a></li>
         </ul>
         <br>
         <br>

         <li style="list-style-type: none; font-size: 1.25em;">&#128197; Calendar Example</li>
         <ul>
            <li><a href="test-fmpda/calendar/calendar.php">From my 2014 DevCon talk demonstrating Full Calendar & CWP (with AJAX)</a></li>
         </ul>
         <br>
      </ul>

      <div id="footer">
         <a href="http://www.driftwoodinteractive.com"><img src="img/di.png" height="32" width="128" alt="Driftwood Interactive" style="vertical-align:text-bottom"></a><br>
         <br>
         Copyright &copy; <?php echo date('Y'); ?> Mark DeNyse Released Under the MIT License.
      </div>
   </body>
</html>
