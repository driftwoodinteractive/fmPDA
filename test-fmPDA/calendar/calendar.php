<?php
   require_once 'startup.inc.php';

   $startingDate = date('Y-m-d');      // Use a $_GET parameter to pass in a different starting date
?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8" />
      <title>fmPDA &#9829;</title>

      <link href="../../css/normalize.css" rel="stylesheet" />
      <link href="../../css/styles.css" rel="stylesheet" />

      <link href='js/lib/cupertino/jquery-ui.min.css' rel='stylesheet' />
      <link href='css/fullcalendar.css' rel='stylesheet' />
      <link href='css/fullcalendar.print.css' rel='stylesheet' media='print' />

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
      <h1 id="header">
         fmPDA <span style="color: #ff0000;">&#9829;</span><br>
         Calendar demo from my 2014 DevCon talk
      </h1>

      <h2>
      <div style="text-align: center;">
         <span class="link"><a href="../../index.php">Introduction</a></span>
         <span class="link"><a href="../../versionhistory.php">Version History</a></span>
         <span class="link"><a href="../../examples.php">Examples</a></span>
         <span class="link"><a href="https://driftwoodinteractive.com/fmpda">Download</a></span>
      </div>
      </h2>

      <div id='loading' style='display:none'><img src="img/ajax-loader.gif" height="11" width="43"></div>

      <div id='calendar'></div>

      <?php echo fmGetLog(); ?>

      <div id="footer">
         <a href="http://www.driftwoodinteractive.com"><img src="../../img/di.png" height="32" width="128" alt="Driftwood Interactive" style="vertical-align:text-bottom"></a>
      </div>

   </body>
</html>
