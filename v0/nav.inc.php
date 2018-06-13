<?php

// *********************************************************************************************************************************
function GetNavigationMenu($numLevelsToRoot = 0)
{
   $path = '';
   for ($i = 1; $i <= $numLevelsToRoot; $i++) {
      $path .= '../';
   }

   $html = '
            <button class="dropdown-btn" id="fmPDA">
               <div class="navigation-header1">fmPDA<i class="fas fa-caret-down"></i></div>
            </button>
            <div class="dropdown-container">
               <div class="navigation-header2"><i class="fas fa-cloud-download-alt fa-fw"></i> Get</div>
               <a href="'. $path .'test-fmpda/get_record_by_id.php"><span class="indent-1">Get Record By ID</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-search fa-fw"></i> Find</div>
               <a href="'. $path .'test-fmpda/find_all.php"><span class="indent-1">Find All</span></a>
               <a href="'. $path .'test-fmpda/find_any.php"><span class="indent-1">Find Any</span></a>
               <a href="'. $path .'test-fmpda/find_non_compound.php"><span class="indent-1">Find (Non compound)</span></a>
               <a href="'. $path .'test-fmpda/find_compound.php"><span class="indent-1">Find (Compound)</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-edit fa-fw"></i> Editing</div>
               <a href="'. $path .'test-fmpda/add.php"><span class="indent-1">Add Record</span></a>
               <a href="'. $path .'test-fmpda/create_commit.php"><span class="indent-1">Create & Commit</span></a>
               <a href="'. $path .'test-fmpda/edit.php"><span class="indent-1">Edit Record</span></a>
               <a href="'. $path .'test-fmpda/edit_commit.php"><span class="indent-1">Edit & Commit</span></a>
               <a href="'. $path .'test-fmpda/delete.php"><span class="indent-1">Delete Record</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-file fa-fw"></i> Containers</div>
               <a href="'. $path .'test-fmpda/container_data.php"><span class="indent-1">Get Container Data</span></a>
               <a href="'. $path .'test-fmpda/container_data_url.php"><span class="indent-1">Get Container Data URL</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-code fa-fw"></i> Scripting</div>
               <a href="'. $path .'test-fmpda/script.php"><span class="indent-1">Run a Script</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-calendar-alt fa-fw"></i> Calendar</div>
               <a href="'. $path .'test-fmpda/calendar/calendar.php"><span class="indent-1">Calendar Demo</span></span></a>
               <br>
            </div>
            <br>


            <button class="dropdown-btn" id="fmDataAPI">
               <div class="navigation-header1">fmDataAPI<i class="fas fa-caret-down"></i></div>
            </button>
            <div class="dropdown-container">
               <div class="navigation-header2"><i class="fas fa-cloud-download-alt fa-fw"></i> Get</div>
               <a href="'. $path .'test-fmDataAPI/get_record.php"><span class="indent-1">Get Record By ID</span></a>
               <br>
            </div>
            <br>


             <button class="dropdown-btn" id="fmCURL">
               <div class="navigation-header1">fmCURL<i class="fas fa-caret-down"></i></div>
            </button>
            <div class="dropdown-container">
               <div class="navigation-header2"><i class="fas fa-cloud-download-alt fa-fw"></i> Get</div>
               <a href="'. $path .'test-fmCURL/curl.php"><span class="indent-1">Get contents of example.com</span></a>
            </div>
            <br>


           <div class="navigation-header1"><a href="https://driftwoodinteractive.com/fmpda" target="_blank">Download</a></div>
            <br>
            <div class="navigation-header1"><a href="'. $path .'../versionhistory.php">Version History</a></div>
            <br>
            <div class="navigation-header1"><a href="'. $path .'../license.php">License</a></div>
   ';

   return $html;
}

?>