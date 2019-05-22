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
      </header>


      <!-- Fixed size after header-->
      <div class="content">

         <!-- Always on top. Fixed position, fixed width, relative to content width-->
         <div class="navigation-left">
            <?php echo GetNavigationMenu(1); ?>
         </div>

          <!-- Scrollable div with main content -->
         <div class="main">

            <div id="fmcurl-constructor" class="api-object">
               <div class="api-header">
                  fmCURL Constructor
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     __construct($options = array(), $curlOptions = array()

                        Constructor for fmCURL.

                        Parameters:
                           (array)   $options          Optional parameters
                           (array)   $curlOptions      Optional parameters passed to curl()

                        Returns:
                           The newly created object.

                        Example:
                           $curl = new fmCURL();
                  </code></pre>
               </div>
            </div>

            <div id="curl" class="api-object">
               <div class="api-header">
                  fmCURL::curl()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function curl($url, $method = METHOD_GET, $data = '', $options = array())

                        Make a call to curl()

                        Parameters:
                           (string)   $url         The URL
                           (string)   $method      'GET', 'PUT', 'POST', 'PATCH', 'DELETE', etc.
                           (string)   $data        Data to send
                           (array)    $options     Optional parameters

                        Returns:
                           The data sent back from the host. getErrorInfo() may be used to see if there was an error.

                        Example:
                           $curl = new fmCURL();
                           $curlResult = $curl->curl('https://www.example.com');
                  </code></pre>
               </div>

               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="curl">Run Example 1</button> Get the contents of example.com
                 <br>
                 <br>
                 <button type="button" class="btn btn-primary run_php_script" phpscript="curl_bar" output="curl">Run Example 2</button> Where is ...
               </div>
               <div id="output_curl" class="api-example-output"></div>
            </div>

            <div id="getfile" class="api-object">
               <div class="api-header">
                  fmCURL::getFile()
               </div>

               <div class="api-description">
                  <pre><code class="language-php">
                     function getFile($url, $options = array())

                        Get the contents of a file, optionally downloading or displaying it inline in the browser.

                        Parameters:
                           (string)  $url           The URL where the file is located
                           (boolean) $options       An array of options:
                                                      ['action']           One of 3 choices:
                                                                              get         - Return the file in $this->file (default)
                                                                              download    - Send headers download, echo $this->file
                                                                              inline      - Send headers for inline viewing, echo $this->file
                                                      ['fileName']         The filename to use in the Content-Disposition header if ['action'] == 'download'
                                                      ['compress']         If 'gzip', compress the file. Can always do it if ['action'] == 'get'.
                                                                           For ['action'] == 'download' or ['action'] == 'inline', only if caller
                                                                           supports gzip compression.
                                                                           Defaults to 'gzip' for ['action'] == 'download' or ['action'] == 'inline',
                                                      ['compressionLevel'] If ['compress'] =='gzip', then gzip with this compression level
                                                                           (only valid for 'action' = 'download' or 'inline')
                                                      ['createCookieJar']  If true, a default cookie jar will be create to store any cookies
                                                                           returned from the call. Defaults to true.
                                                      ['retryOn401Error']  If true, a second attempt will be made with the cookie jar returned
                                                                           from the first attempt. This is necessary for some hosts when 401
                                                                           is returned the first time (sometimes with FM Data API).
                                                                           Defaults to false.
                                                      ['getMimeType']      If true, the file will be written to the tmp directory so that
                                                                           fmCURL::get_mime_content_type() can be used to properly determine the mime type.
                                                                           Defaults to true.

                        Returns:
                           An JSON-decoded associative array of the API result. Typically:
                              ['response'] If successful:
                                              ['url']                      The same URL passed in
                                              ['mimeType']                 The computed mime type (when $options['getMimeType'] is true) otherwise
                                                                           whatever curl determines
                                              ['fileName']                 The file name passed in $options['fileName'] or retrieved from the url
                                              ['extension']                The file's extension (may be empty)
                                              ['size']                     The size of the file
                              ['messages'] Array of messages. This mimics what the FM Data API returns.

                           ALSO: the contents of the file will be stored in $this->file to avoid copying/memory consumption

                        Example:
                           $curl = new fmCURL();
                           $result = $curl->getFile($url);
                           if (! $curl->getIsError($result)) {
                              // file contents are in $curl->file *not* $result
                              ...
                           }
                  </code></pre>
               </div>
               <div class="api-example-output-header">
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_file.php?action=get" output="get_file">Run Example 1</button> Get the Google logo
                 <br>
                 <br>
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_file.php?action=download" output="get_file" target="_blank">Run Example 2</button> Download the Google logo
                 <br>
                 <br>
                 <button type="button" class="btn btn-primary run_php_script" phpscript="get_file.php?action=inline" output="get_file" target="_blank">Run Example 3</button> Download/inline the Google logo
               </div>
               <div id="output_get_file" class="api-example-output"></div>
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
         javascript:ExpandDropDown("fmCURL");
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
