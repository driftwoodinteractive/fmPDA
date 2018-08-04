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

require_once 'nav.inc.php';

// Let the caller decide which version of the API to use. We hard code the version we know works.
// The caller can also pass 'Latest' as a demonstration that these examples also work on the latest version.
define('SUPPORTED_API_VERSION', 1);

define('DATA_API_VERSION', array_key_exists('v', $_GET) ? $_GET['v'] : SUPPORTED_API_VERSION);

define('HIGHEST_VERSION',  (DATA_API_VERSION == 'Latest') ? SUPPORTED_API_VERSION : DATA_API_VERSION);

?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8" />
      <title>fmPDA &#9829;</title>
      <link href="../css/normalize.css" rel="stylesheet" />
      <link href="../css/fontawesome-all.min.css" rel="stylesheet" />
      <link href="../css/prism.css" rel="stylesheet" />
      <script src="../js/prism.js"></script>
      <link href="../css/styles.css" rel="stylesheet" />
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
            <?php echo GetNavigationMenu(0, DATA_API_VERSION); ?>
         </div>

          <!-- Scrollable div with main content -->
         <div class="main">
            <h4>Installation</h4>
            <ul>
               <li>Remove FileMaker's API For PHP class files (<code>FileMaker.php</code> and the adjacent <code>FileMaker</code> directory) from your project.</li>
               <li>Copy the <code>fmPDA/v<?php echo HIGHEST_VERSION; ?></code> directory into your project, typically in the same directory where FileMaker's old API resided.</li>
               <li>Wherever you do this:</li>
                     <pre><code class="language-php">require_once 'PATH-TO-FILEMAKER-CLASS-FILES/FileMaker.php';</code></pre>
                     replace with:<br>
                     <pre><code class="language-php">require_once 'PATH-TO-FMPDA-CLASS-FILES/fmPDA/v<?php echo HIGHEST_VERSION; ?>/fmPDA.php';</code></pre>
            </ul>
            <br>

            <h4>fmPDA v<?php echo HIGHEST_VERSION; ?> Overview</h4>
            <ul>
               <li>
                  <strong>fmCURL</strong>
                  <ul>
                     <code>fmCURL</code> is a wrapper for CURL calls. The <code>curl()</code> method sets the typical parameters and optionally encode/decodes JSON data. <code>fmCURL</code> is independent of the FM API; it can be used to communicate with virtually any host (Google, Swipe, etc.). The <code>fmAPI</code> class (see below) uses <code>fmCURL</code> to communicate with FileMaker's API.<br>
                     <pre><code class="language-php">
                        $curl = new fmCURL();<br>
                        $curlResult = $curl->curl('https://www.example.com');
                     </code></pre>
                     Additionally, <code>fmCURL</code> instantiates a global <code>fmLogger</code> object to log various messages these classes generate. You can use this for your own purposes as well. See any of the example files on how it's used.<br>
                  </ul>
               </li>
               <br>
               <br>
               <li>
                  <strong>fmAPI</strong>
                  <ul>
                     <code>fmAPI</code> encapsulates the interactions with FileMaker's API. <code>fmAdminAPI</code> and <code>fmDataAPI</code> extend this class. You won't typically instantiate this class directly.<br>
                     <br>
                     <code>fmAPI</code> takes care of managing the authentication token the API requires in all calls. Without your code needing to know, <code>fmAPI</code> will request a new token whenever the current token is invalid. By default, the token is stored in a session variable so calls across multiple PHP pages will reuse the same token. You can disable session variable storage, but you'll be responsible for managing the storage of the token.<br>
                  </ul>
               </li>
               <br>
               <br>
               <li>
                  <strong>fmAdminAPI</strong>
                  <ul>
                     <code>fmAdminAPI</code> encapsulates the interactions with FileMaker's Admin Console API. Use this to communicate with FileMaker Server's Admin Console to get the server status, schedules, configuration, etc.<br>
                     <pre><code class="language-php">
                        $fm = new fmAdminAPI($host, $userName, $password);<br>
                        $apiResult = $fm->apiGetServerStatus();
                     </code></pre>
                  </ul>
               </li>
               <br>
               <br>
               <li>
                  <strong>fmDataAPI</strong>
                  <ul>
                     <code>fmDataAPI</code> encapsulates interactions with FileMaker's Data API. The class provides methods for directly interacting with the Data API (Get, Find, Create, Edit, Delete, Upload Container, Set Globals, Scripts, etc.)<br>
                     <pre><code class="language-php">
                        $fm = new fmDataAPI($database, $host, $userName, $password);<br>
                        $apiResult = $fm->apiGetRecord($layout, $recordID);
                     </code></pre>
                     <h3 class="caution"><i class="fas fa-exclamation-triangle"></i> Caution</h3>
                     <ul>
                        <li>OAuth support is included but has not been tested.</li>
                        <li>The Data API replaces the name of the Table Occurrence in portals with the layout object name (if one exists). If you name your portals on the dedicated Web layouts (you do have those, right?) you've been using with the old API, you'll need to change your code (ugh) or remove the object names.</li>
                        <li>The Data API translates FM line separators from a line feed (\n) in the old API is now a carriage return (\r). If your code looks for line feeds, look for carriage returns now.</li>
                     </ul>
                  </ul>
               </li>
               <br>
               <br>
               <li>
                  <strong>fmPDA</strong>
                  <ul>
                     fmPDA provides <strong>method & data structure compatibility</strong> with FileMaker's 'old' API For PHP.<br>
                     <pre><code class="language-php">
                        $fm = new fmPDA($database, $host, $userName, $password); /* fmPDA instead of FileMaker */<br>
                        $findAllCommand = $fm->newFindAllCommand($layout);
                        $findAllCommand->addSortRule($fieldName, 1, FILEMAKER_SORT_DESCEND);
                        $result = $findAllCommand->execute();
                     </code></pre>
                     <h3 class="caution"><i class="fas fa-exclamation-triangle"></i> Remember, wherever you did this:</h3>
                     <pre><code class="language-php">$fm = new FileMaker(...);</code></pre>
                     replace it with:<br>
                     <pre><code class="language-php">$fm = new fmPDA(...);</code></pre>
                     <br>
                     Within the limits described below, your existing code should function as is, with the exception that it's using FileMaker's Data API instead of the XML interface. Not everything is supported, so you may have to make some changes to your code.<br>
                     <br>
                     fmPDA can also return the 'raw' data from the Data API; if you want to use <code>fmPDA</code> to create the structures for passing to the Data API but want to process the data on your own, set the '<code>translateResult</code>' element to false in the $options array you pass to the <code>fmPDA</code> constructor. Alternatively, you can override <code>fmPDA::newResult()</code> to return the result in whatever form you wish.<br>

                     <h3><span style="color: #63A63D;"><i class="fas fa-smile fa-lg"></i></span> What is supported</h3>
                     <ul>
                        <li>Get Record By ID</li>
                        <li>Find All</li>
                        <li>Find Any</li>
                        <li>Find (Non compound & Compound)</li>
                        <li>Add Record</li>
                        <li>Create Record & Commit</li>
                        <li>Edit Record</li>
                        <li>Get Record, Edit & Commit</li>
                        <li>Delete Record</li>
                        <li>Get Container Data</li>
                        <li>Get Container Data URL</li>
                        <li>Script execution</li>
                        <li>Duplicate record (The duplicate.php example file shows how to do this with a simple FM script)</li>
                        <li>Uploading a container (this is an extension thanks to the Data API); use <code>fmPDA::newUploadContainerCommand(...)</code></li>
                     </ul>

                     <h3><span style="color: #A40800;"><i class="fas fa-frown fa-lg"></i></span> What isn't supported</h3>
                     <ul>
                        <li>List scripts</li>
                        <li>List databases - in v1 or later, use <code>fmAdminAPI::apiListDatabases()</code></li>
                        <li>List layouts</li>
                        <li>Get layout metadata (some of it is available through an undocumented API)</li>
                        <li>Validation</li>
                        <li>Value Lists</li>
                        <li>Using <code>Commit()</code> to commit data on portals.</li>
                        <li>was made</li>
                     </ul>

                     <h3 class="caution"><i class="fas fa-exclamation-triangle"></i> Caution</h3>
                     <ul>
                        <li><code>fmRecord::getFieldAsTimestamp() (FileMaker_Record::getFieldAsTimestamp() in the old API)</code> can't automatically determine the field type as the Data API doesn't return field metadata. There is now a new third parameter (<code>$fieldType</code>) to tell the method how to convert the field data. See <code>FMRecord.class.php</code> for details.</li>
                     </ul>
                     <br>

                     <h3>A change you'll likely have to make to your code</h3>
                     The biggest change is replacing any calls to <code>FileMaker::isError($result)</code> to use the function <code>fmGetIsError($result)</code> as the FileMaker class no longer exists.
                     <pre><code class="language-php">
                        if (FileMaker::isError($result)) {
                           /* Oops. Let's handle the error... */
                        }
                     </code></pre>
                     Change it to:
                     <pre><code class="language-php">
                        if (fmGetIsError($result)) {
                           /* Oops. Let's handle the error... */
                        }
                     </code></pre>

                     <br>
                     If you really don't want to do this (::sigh::), you can change <code>fmPDA.conf.php</code> and modify the following line:<br>
                     <pre><code class="language-php">define('DEFINE_FILEMAKER_CLASS', false);</code></pre>
                     to:<br>
                     <pre><code class="language-php">define('DEFINE_FILEMAKER_CLASS', true);</code></pre>
                     This will create a 'glue' FileMaker class that fmPDA inherits from, and you can continue to use <code>FileMaker::isError()</code>. Even so, it's recommended that you should switch to <code>fmGetIsError()</code> in the future to reduce/eradicate your dependence on a class called FileMaker. You'll likely run into conflicts if you do this and keep FileMaker's old classes in your include tree. You Have Been Warned.<br>
                     <br>
                  </ul>
               </li>
            </ul>

            <h4>License</h4>
            fmPDA is released under the 'MIT' license. If you use this in your project I'd enjoy hearing about it. If you have questions, find bugs, or have suggestions for improvements, I'll do my best to respond to all queries.<br>
            <br>

         </div>

      </div>


      <!-- Always at the end of the page -->
      <footer>
         <a href="http://www.driftwoodinteractive.com"><img src="../img/di.png" height="32" width="128" alt="Driftwood Interactive" style="vertical-align:text-bottom"></a><br>
         Copyright &copy; <?php echo date('Y'); ?> Mark DeNyse Released Under the MIT License.
      </footer>

		<script src="../js/main.js"></script>

   </body>
</html>
