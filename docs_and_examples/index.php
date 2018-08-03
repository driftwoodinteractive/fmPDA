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
?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8" />
      <title>fmPDA &#9829;</title>
      <link href="css/normalize.css" rel="stylesheet" />
      <link href="css/styles.css" rel="stylesheet" />
      <link href="css/fontawesome-all.min.css" rel="stylesheet" />
      <link href="css/prism.css" rel="stylesheet" />
      <script src="js/prism.js"></script>
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
               fmPDA (<u>F</u>ileMaker <u>P</u>HP <u>D</u>ata <u>A</u>PI)<br>
            </div>

            <h4>What is fmPDA <span class="fmPDA-heart"><i class="fas fa-heart"></i></span> ?</h4>
            fmPDA <span class="fmPDA-heart"><i class="fas fa-heart"></i></span> is a set of PHP classes for FileMaker's Data and Admin API. The special sauce, the fmPDA class: a replacement for FileMaker's API For PHP FileMaker class, using the Data API. fmPDA provides <strong>method & data structure compatibility</strong> with FileMaker's API For PHP classes.
            <p>
            But wait, there's more! A set of Modular FileMaker Scripts to let you access the Data API and Admin API right from your FileMaker solution. Perfect for your standalone solution or iOS SDK app.
            </p>
            <br>


            <h4><span style="color: #8E3C18;"><i class="fas fa-poo fa-lg"></i></span> You've got CWP Code.</h4>
            You have Custom Web Publishing (CWP) code written using FileMaker's API for PHP. FileMaker has made it clear the new Data API is the way to go, and the XML interface (which FileMaker's API for PHP uses) will likely be deprecated and removed in the future. Your code will break. Game over, Player One.<br>
            <h4><span style="color: #CA9008;"><i class="fas fa-question-circle fa-lg"></i></span> OK, Dallas, We've Had A Problem Here. What Do We Do Now?</h4>
            <ul>
               <li>Rewrite your code to use the new Data API. Not 'hard', but it'll take time to rewrite/debug. In the end, your code may be a little faster. Yay, you.</li>
               <li>Use a library that someone wrote to solve the same problem. Less time consuming, especially if the code could <i><strong>replicate FileMaker's API for PHP<strong></i>.</li>
            </ul>
            <h4><span style="color: #63A63D;"><i class="fas fa-smile fa-lg"></i></span> Wait, What?</h4>
            fmPDA provides <strong>method & data structure compatibility with FileMaker's API For PHP</strong>. Only minor changes should be needed to your code and it should take you less than an hour.<br>
            <br>


            <h4>Choose the version based on your FileMaker Server version:</h4>
            <a href="v0/">Version '0' (FileMaker Server 16) - this was the initial trial release and is not available on FileMaker Server 17 or later.</a><br>
            <br>
            <a href="v1/index.php?v=1">Version 1 (FileMaker Server 17 and later) - Version 1 was released with FileMaker Server 17</a><br>
            <br>
            <a href="v1/index.php?v=Latest">Version Latest - The latest supported version</a><br>
            <br>


            <h4>PHP Compatibility</h4>
            fmPDA has been tested with PHP versions 5.2.17 through 7.1.6.<br>
            <br>


            <h4>License</h4>
            fmPDA is released under the 'MIT' license. If you use this in your project I'd enjoy hearing about it. If you have questions, find bugs, or have suggestions for improvements, I'll do my best to respond to all queries.<br>
            <br>


            Mark DeNyse<br>
            Driftwood Interactive<br>
            fmpda@driftwoodinteractive.com<br>

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
