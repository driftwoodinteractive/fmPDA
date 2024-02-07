<?php
// *********************************************************************************************************************************
//
// versionhistory.php
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
?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8" />
      <title>fmPDA &#9829;</title>
      <link href="css/normalize.css" rel="stylesheet" />
      <link href="css/styles.css" rel="stylesheet" />
      <link href="css/fontawesome-all.min.css" rel="stylesheet" />

      <style>
         table { width: 100%; }
         table { border-collapse: collapse; }
         caption { font-weight: bold; font-size: 1.1em; }
         table, caption, th, td { border: 1px solid black; }
         th { text-align: left; }
         th, td { padding: 5px; vertical-align: top; }
      </style>
   </head>
   <body>

      <!-- Always on top: Position Fixed-->
      <header>
         fmPDA <span class="fmPDA-heart"><i class="fas fa-heart"></i></span>
      </header>



      <!-- Fixed size after header-->
      <div class="content">

         <!-- Always on top. Fixed position, fixed width, relative to content width-->
         <div class="navigation-left">
            <div class="navigation-header1"><a href="index.php">Home</a></div>
            <br>

            <button class="dropdown-btn">
               <div class="navigation-header1">Versions<i class="fas fa-caret-down"></i></div>
            </button>
            <div class="dropdown-container">
               <a href="v1/index.php?v=1"><span class="indent-1">v1</span></a>
               <a href="v2/index.php?v=2"><span class="indent-1">v2</span></a>
               <a href="v2/index.php?v=Latest"><span class="indent-1">vLatest</span></a>
            </div>
            <br>

            <div class="navigation-header1"><a href="https://driftwoodinteractive.com/fmpda" target="_blank">Download</a></div>
            <br>
            <div class="navigation-header1"><a href="versionhistory.php">Version History</a></div>
            <br>
            <div class="navigation-header1"><a href="license.php">License</a></div>

         </div>



          <!-- Scrollable div with main content -->
         <div class="main">

            <div class="main-header">
               Version History
            </div>

            <table>
               <tr>
                  <th style="width: 15%;">Version</th><th style="width: 15%;">Date</th><th style="width: 70%;">Notes</th>
               </tr>
               <tr>
                  <td>1.0</td><td>2017 August 28</td><td>Initial release.</td>
               </tr>
               <tr>
                  <td>1.1</td><td>2018 June 13</td><td>Support for FileMaker Server 17 Data API v1, added AdminAPI v1</td>
               </tr>
               <tr>
                  <td>2.0</td><td>2019 May 22</td><td>Support for FileMaker Server 18 Data API v1, Admin API v2.</td>
               </tr>
               <tr>
                  <td>2.1</td><td>2020 September 15</td>
                  <td>
                     Support for FileMaker Server 19 Data API v2<br>
                     <ul>
                        Added additional methods for Data API v1 that are part of FileMaker Server 18:
                        <ul>
                        <li><code>Added fmDataAPI::ValidateSession()</code></li>
                        </ul>
                        <br>
                        Added additional methods for Admin API v2 that are part of FileMaker Server 18:
                        <ul>
                        <li><code>Added fmAdminAPI::GetODBCJDBCConfiguration()</code></li>
                        <li><code>Added fmAdminAPI::SetODBCJDBCConfiguration()</code></li>
                        <li><code>Added fmAdminAPI::GetDataAPIConfiguration()</code></li>
                        <li><code>Added fmAdminAPI::SetDataAPIConfiguration()</code></li>
                        <li><code>Added fmAdminAPI::GetWebDirectonfiguration()</code></li>
                        <li><code>Added fmAdminAPI::SetWebDirectonfiguration()</code></li>
                        <li><code>Added fmAdminAPI::GetWPEConfiguration()</code></li>
                        <li><code>Added fmAdminAPI::SetWPEonfiguration()</code></li>
                        </ul>
                     </ul>
                     Added <code>fmAPI::EncodeParameter()</code> method to properly handle encoding of parameters for GET and POST operations. <code>fmDataAPI::getAPIParams()</code> now calls this.<br>
                     <br>
                     Added $fieldValues (array of field name/values) parameter to <code>fmPDA::newAddCommand()</code> and <code>fmPDA::newEditCommand()</code> to match FileMaker API for PHP. Thanks to Darren LaPadula for finding this.<br>
                     <br>
                     Changed fmPDA Add/Edit/Duplicate/Upload <code>execute()</code> methods to return a result object (instead of a record object) to match FileMaker API for PHP. Thanks to Darren LaPadula for finding this.<br>
                  </td>
               </tr>
               <tr>
                  <td>2.2</td><td>2024 February 6</td>
                  <td>
                     Support for PHP 8.2<br>
                     <br>
                     Support for FileMaker Server 20+<br>
                     <br>
                     Added <code>fmDataAPI::apiGetValueList()</code> to retrieve a specific value list from a layout.<br>
                  </td>
               </tr>
            </table>

         </div>

      </div>



      <!-- Always at the end of the page -->
      <footer>
         <a href="http://www.driftwoodinteractive.com"><img src="img/di.png" height="32" width="128" alt="Driftwood Interactive" style="vertical-align:text-bottom"></a><br>
         Copyright &copy; <?php echo date('Y'); ?> Mark DeNyse Released Under the MIT License.
      </footer>

      <script>
         /* Loop through all dropdown buttons to toggle between hiding and showing its dropdown content - This allows the user to have multiple dropdowns without any conflict */
         var dropdown = document.getElementsByClassName("dropdown-btn");
         var i;

         for (i = 0; i < dropdown.length; i++) {
           dropdown[i].addEventListener("click", function() {
             this.classList.toggle("active");
             var dropdownContent = this.nextElementSibling;
             if (dropdownContent.style.display === "block") {
               dropdownContent.style.display = "none";
             } else {
               dropdownContent.style.display = "block";
             }
           });
         }
      </script>

   </body>
</html>
