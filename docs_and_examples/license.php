<?php
// *********************************************************************************************************************************
//
// license.php
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
?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8" />
      <title>fmPDA &#9829;</title>
      <link href="css/normalize.css" rel="stylesheet" />
      <link href="css/styles.css" rel="stylesheet" />
      <link href="css/fontawesome-all.min.css" rel="stylesheet" />
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
               <a href="v0/index.php"><span class="indent-1">v0</span></a>
               <a href="v1/index.php?v=1"><span class="indent-1">v1</span></a>
               <a href="v1/index.php?v=Latest"><span class="indent-1">vLatest</span></a>
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
               License
            </div>

            <pre>
            License
            -------
            fmPDA is released under the 'MIT' license. If you use this in your project, I'd
            enjoy hearing about it. Please let me know if you have questions, find bugs, or
            have suggestions for improvements. I'll do my best to respond to all queries.


            Mark DeNyse
            Driftwood Interactive
            fmpda@driftwoodinteractive.com


            --------------------------------------------------------------------------------
            --------------------------------------------------------------------------------
            Copyright (c) 2017 - 2019 Mark DeNyse

            Permission is hereby granted, free of charge, to any person obtaining a copy of
            this software and associated documentation files (the "Software"), to deal in
            the Software without restriction, including without limitation the rights to
            use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
            the Software, and to permit persons to whom the Software is furnished to do so,
            subject to the following conditions:

            The above copyright notice and this permission notice shall be included in all
            copies or substantial portions of the Software.

            THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
            IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
            FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
            COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
            IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
            CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

            --------------------------------------------------------------------------------
            --------------------------------------------------------------------------------
            </pre>

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
