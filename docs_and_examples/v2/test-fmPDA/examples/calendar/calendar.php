<?php
   require_once 'startup.inc.php';

   $startingDate = date('Y-m-d');      // Use a $_GET parameter to pass in a different starting date
?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8" />
      <title>fmPDA &#9829;</title>

      <link href="../../../../css/normalize.css" rel="stylesheet" />
      <link href="../../../../css/fontawesome-all.min.css" rel="stylesheet" />

      <link href='js/lib/cupertino/jquery-ui.min.css' rel='stylesheet' />
      <link href='css/fullcalendar.css' rel='stylesheet' />
      <link href='css/fullcalendar.print.css' rel='stylesheet' media='print' />

      <link href="../../../../css/styles.css" rel="stylesheet" />

      <script src='js/lib/moment.min.js'></script>
      <script src='js/lib/jquery.min.js'></script>
      <script src='js/fullcalendar.min.js'></script>
      <script>

         $(document).ready(function() {

            $('#calendar').fullCalendar({
               theme: true,
               header: {
                  left: 'prev,next today',
                  center: 'title',
                  right: 'month,agendaWeek,agendaDay'
               },
               defaultDate: '<?php echo $startingDate; ?>',
               editable: false,
               weekMode: 'variable',

               eventSources: [
                  { url: 'projects.ajax.php' },
                  { url: 'holidays.ajax.php' }
               ],

               eventClick: function(event, jsEvent, view) {
                  window.location.href = "<?php echo 'fmp://'. FM_HOST .'/'. FM_DATABASE .'.fmp12?script='. FM_PROJECT_CLICK_SCRIPT .'&param='; ?>"+ event.id;
               },

               loading: function(bool) {
                  $('#loading').toggle(bool);
               }
            });
         });

      </script>
      <style>
         #calendar {
            width: 900px;
            margin: 10px auto;
         }

         #loading {
            position: absolute; top: 20x; right: 30px;
         }

      </style>
   </head>

   <body>
      <!-- Always on top: Position Fixed-->
      <header>
         fmPDA <span class="fmPDA-heart"><i class="fas fa-heart"></i></span>
         <span style="float: right;">Version <strong>1</strong> (FileMaker Server 17+)</span>
      </header>



      <!-- Fixed size after header-->
      <div class="content">

         <!-- Always on top. Fixed position, fixed width, relative to content width-->
         <div class="navigation-left">
            <?php echo GetNavigationMenu(3, 1, 2); ?>
         </div>



          <!-- Scrollable div with main content -->
         <div class="main">

            <div class="main-header">
               Calendar demo from my 2014 DevCon talk
            </div>


            <div id='loading' style='display:none'><img src="img/ajax-loader.gif" height="11" width="43"></div>
            <div id='calendar'></div>


            <!-- Display the debug log -->
            <?php echo fmGetLog(); ?>

         </div>

      </div>


      <!-- Always at the end of the page -->
      <footer>
         <a href="http://www.driftwoodinteractive.com"><img src="../../../../img/di.png" height="32" width="128" alt="Driftwood Interactive" style="vertical-align:text-bottom"></a><br>
         Copyright &copy; <?php echo date('Y'); ?> Mark DeNyse Released Under the MIT License.
      </footer>

		<script src="../../../../js/main.js"></script>
      <script>
         javascript:ExpandDropDown("fmPDA");
      </script>

   </body>
</html>
