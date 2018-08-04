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

?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8" />
      <title>fmPDA &#9829;</title>
      <link href="../css/normalize.css" rel="stylesheet" />
      <link href="../css/styles.css" rel="stylesheet" />
      <link href="../css/fontawesome-all.min.css" rel="stylesheet" />
      <link href="../css/prism.css" rel="stylesheet" />
      <script src="../js/prism.js"></script>
   </head>
   <body>

      <!-- Always on top: Position Fixed-->
      <header>
         fmPDA <span class="fmPDA-heart"><i class="fas fa-heart"></i></span>
         <span style="float: right;">Version <strong>0</strong> (FileMaker Server 16)</span>
      </header>


      <!-- Fixed size after header-->
      <div class="content">

         <!-- Always on top. Fixed position, fixed width, relative to content width-->
         <div class="navigation-left">
            <div class="navigation-header1"><a href="../index.php">Home</a></div>
            <br>

            <?php echo GetNavigationMenu(); ?>
         </div>

          <!-- Scrollable div with main content -->
         <div class="main">
            <h4>Installation</h4>
            <ul>
               <li>Remove FileMaker's API For PHP class files (<code>FileMaker.php</code> and the adjacent <code>FileMaker</code> directory) from your project.</li>
               <li>Copy the <code>fmPDA/v0</code> directory into your project, typically in the same directory where FileMaker's old API resided.</li>
               <li>Wherever you do this:</li>
               <pre><code class="language-php">require_once 'PATH-TO-FILEMAKER-CLASS-FILES/FileMaker.php';</code></pre>
               replace with:<br>
               <pre><code class="language-php">require_once 'PATH-TO-FMPDA-CLASS-FILES/fmPDA/v0/fmPDA.php';</code></pre>
            </ul>
            <br>

            <h4>fmPDA v0 Overview</h4>
            <ul>
               <li>
                  <strong>fmCURL</strong>
                  <ul>
                     <code>fmCURL</code> is a wrapper for CURL calls. The <code>curl()</code> method sets the typical parameters and optionally encode/decodes JSON data. <code>fmCURL</code> is independent of the FM API; it can be used to communicate with virtually any host (Google, Swipe, etc.). The <code>fmDataAPI</code> class (see below) uses <code>fmCURL</code> to communicate with FileMaker's API.<br>
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
                  <strong>fmDataAPI</strong>
                  <ul>
                     <code>fmDataAPI</code> encapsulates interactions with FileMaker's Data API. The class provides methods for directly interacting with the Data API (Get, Find, Create, Edit, Delete, Set Globals, Scripts, etc.)<br>
                     <pre><code class="language-php">
                        $fm = new fmDataAPI($database, $host, $userName, $password);<br>
                        $apiResult = $fm->apiGetRecord($layout, $recordID);
                     </code></pre>
                     <h3 class="caution"><i class="fas fa-exclamation-triangle"></i> Caution</h3>
                     <ul>
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
                        <li>Script execution - emulated with the old XML interface (v1 now supports script calls). This is only for direct script calls: pre-script, pre-command, and pre-sort are not be emulated.</li>
                     </ul>

                     <h3><span style="color: #A40800;"><i class="fas fa-frown fa-lg"></i></span> What isn't supported</h3>
                     <ul>
                        <li>Duplicate record</li>
                        <li>Pre-script, pre-command, pre-sort script execution</li>
                        <li>Setting the Result layout</li>
                        <li>List scripts</li>
                        <li>List databases</li>
                        <li>List layouts</li>
                        <li>Get layout metadata (some of it is available through an undocumented API)</li>
                        <li>Validation</li>
                        <li>Value Lists</li>
                        <li>Using <code>Commit()</code> to commit data on portals.</li>
                        <li><code>getTableRecordCount()</code> and <code>getFoundSetCount()</code> - fmPDA will create a <code>fmLogger()</code> message and return <code>getFetchCount()</code>. One suggestion as a workaround was made to create an unstored calculation field in your table to return these values and place them on your layout.</li>
                     </ul>

                     <h3>Changes you'll likely have to make to your code</h3>
                     The biggest change is replacing all calls to FileMaker::isError() to use the function fmGetIsError() as the FileMaker class no longer exists. If this is a major hassle, you can change conf.fmPDA.php and modify the following line:<br>
                     <pre><code class="language-php">define('DEFINE_FILEMAKER_CLASS', false);</code></pre>
                     to:<br>
                     <pre><code class="language-php">define('DEFINE_FILEMAKER_CLASS', true);</code></pre>
                     This will create a 'glue' FileMaker class that fmPDA inherits from, and you can continue to use <code>FileMaker::isError()</code>. Even so, it's recommended that you should switch to <code>fmGetIsError()</code> in the future to reduce/eradicate your dependence on a class called FileMaker. You'll likely run into conflicts if you do this and keep FileMaker's old classes in your include tree, so beware.<br>
                     <br>


                     <h3>Things to look out for with the new Data API</h3>
                     <ul>
                        <li>Do not name a field called omit; that name is used in a find query to omit records.</li>
                        <li>Do not name a field called deleteRelated; that name is used when editing a record to delete a related record.</li>
                        <li><code>fmRecord::getFieldAsTimestamp() (FileMaker_Record::getFieldAsTimestamp() in the old API)</code> can't automatically determine the field type as the Data API doesn't return field metadata. There is now a new third parameter (<code>$fieldType</code>) to tell the method how to convert the field data. See <code>FMRecord.class.php</code> for details.</li>
                     </ul>
                     <br>
                  </ul>
               </li>


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
