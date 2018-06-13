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
               <a href="'. $path .'test-fmpda/get_record_by_id.php?v='. DATA_API_VERSION .'"><span class="indent-1">Get Record By ID</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-search fa-fw"></i> Find</div>
               <a href="'. $path .'test-fmpda/find_all.php?v='. DATA_API_VERSION .'"><span class="indent-1">Find All</span></a>
               <a href="'. $path .'test-fmpda/find_any.php?v='. DATA_API_VERSION .'"><span class="indent-1">Find Any</span></a>
               <a href="'. $path .'test-fmpda/find_non_compound.php?v='. DATA_API_VERSION .'"><span class="indent-1">Find (Non compound)</span></a>
               <a href="'. $path .'test-fmpda/find_compound.php?v='. DATA_API_VERSION .'"><span class="indent-1">Find (Compound)</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-edit fa-fw"></i> Editing</div>
               <a href="'. $path .'test-fmpda/add.php?v='. DATA_API_VERSION .'"><span class="indent-1">Add Record</span></a>
               <a href="'. $path .'test-fmpda/create_commit.php?v='. DATA_API_VERSION .'"><span class="indent-1">Create & Commit</span></a>
               <a href="'. $path .'test-fmpda/edit.php?v='. DATA_API_VERSION .'"><span class="indent-1">Edit Record</span></a>
               <a href="'. $path .'test-fmpda/edit_commit.php?v='. DATA_API_VERSION .'"><span class="indent-1">Edit & Commit</span></a>
               <a href="'. $path .'test-fmpda/duplicate.php?v='. DATA_API_VERSION .'"><span class="indent-1">Duplicate Record</span></a>
               <a href="'. $path .'test-fmpda/delete.php?v='. DATA_API_VERSION .'"><span class="indent-1">Delete Record</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-file fa-fw"></i> Containers</div>
               <a href="'. $path .'test-fmpda/container_data.php?v='. DATA_API_VERSION .'"><span class="indent-1">Get Container Data</span></a>
               <a href="'. $path .'test-fmpda/container_data_url.php?v='. DATA_API_VERSION .'"><span class="indent-1">Get Container Data URL</span></a>
               <a href="'. $path .'test-fmpda/container_upload.php?v='. DATA_API_VERSION .'"><span class="indent-1">Upload Container</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-code fa-fw"></i> Scripting</div>
               <a href="'. $path .'test-fmpda/script.php?v='. DATA_API_VERSION .'"><span class="indent-1">Run a Script</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-calendar-alt fa-fw"></i> Calendar</div>
               <a href="'. $path .'test-fmpda/calendar/calendar.php?v='. DATA_API_VERSION .'"><span class="indent-1">Calendar Demo</span></span></a>
               <br>
            </div>
            <br>



            <button class="dropdown-btn" id="fmDataAPI">
               <div class="navigation-header1">fmDataAPI<i class="fas fa-caret-down"></i></div>
            </button>
            <div class="dropdown-container">
               <div class="navigation-header2"><i class="fas fa-lock fa-fw"></i> Authentication</div>
               <a href="'. $path .'test-fmDataAPI/login.php?v='. DATA_API_VERSION .'"><span class="indent-1">Login</span></a>
               <a href="'. $path .'test-fmDataAPI/logout.php?v='. DATA_API_VERSION .'"><span class="indent-1">Logout</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-cloud-download-alt fa-fw"></i> Get</div>
               <a href="'. $path .'test-fmDataAPI/get_record.php?v='. DATA_API_VERSION .'"><span class="indent-1">Get Record By ID</span></a>
               <a href="'. $path .'test-fmDataAPI/get_record_external.php?v='. DATA_API_VERSION .'"><span class="indent-1">Get Record By ID with Ext. Auth.</span></a>
               <a href="'. $path .'test-fmDataAPI/get_records.php?v='. DATA_API_VERSION .'"><span class="indent-1">Get Records</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-search fa-fw"></i> Find</div>
               <a href="'. $path .'test-fmDataAPI/find_non_compound.php?v='. DATA_API_VERSION .'"><span class="indent-1">Find (Non compound)</span></a>
               <a href="'. $path .'test-fmDataAPI/find_compound.php?v='. DATA_API_VERSION .'"><span class="indent-1">Find (Compound)</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-edit fa-fw"></i> Editing</div>
               <a href="'. $path .'test-fmDataAPI/add.php?v='. DATA_API_VERSION .'"><span class="indent-1">Add Record</span></a>
               <a href="'. $path .'test-fmDataAPI/edit.php?v='. DATA_API_VERSION .'"><span class="indent-1">Edit Record</span></a>
               <a href="'. $path .'test-fmDataAPI/delete.php?v='. DATA_API_VERSION .'"><span class="indent-1">Delete Record</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-file fa-fw"></i> Containers</div>
               <a href="'. $path .'test-fmDataAPI/container_get.php?v='. DATA_API_VERSION .'"><span class="indent-1">Get Container</span></a>
               <a href="'. $path .'test-fmDataAPI/container_upload.php?v='. DATA_API_VERSION .'"><span class="indent-1">Upload Container</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-code fa-fw"></i> Scripting</div>
               <a href="'. $path .'test-fmDataAPI/script.php?v='. DATA_API_VERSION .'"><span class="indent-1">Run a Script</span></a>
               <br>

               <div class="navigation-header2"><i class="fas fa-calendar-alt fa-fw"></i> Calendar</div>
               <a href="'. $path .'test-fmDataAPI/calendar/calendar.php?v='. DATA_API_VERSION .'"><span class="indent-1">Full Calendar Demo</span></a>
               <br>
            </div>
            <br>


            <button class="dropdown-btn" id="fmAdminAPI">
               <div class="navigation-header1">fmAdminAPI<i class="fas fa-caret-down"></i></div>
            </button>
            <div class="dropdown-container">
               <div class="navigation-header2"><i class="fas fa-lock fa-fw"></i> Authentication</div>
               <a href="'. $path .'test-fmAdminAPI/login.php?v='. DATA_API_VERSION .'"><span class="indent-1">Login</span></a>
               <a href="'. $path .'test-fmAdminAPI/logout.php?v='. DATA_API_VERSION .'"><span class="indent-1">Logout</span></a>
            <br>

            <div class="navigation-header2"><i class="fas fa-server fa-fw"></i> Server</div>
            <a href="'. $path .'test-fmAdminAPI/get_server_status.php?v='. DATA_API_VERSION .'"><span class="indent-1">Get Server Status</span></a>
            <a href="'. $path .'test-fmAdminAPI/list_databases.php?v='. DATA_API_VERSION .'"><span class="indent-1">List Databases</span></a>
            <a href="'. $path .'test-fmAdminAPI/get_php_configuration.php?v='. DATA_API_VERSION .'"><span class="indent-1">Get PHP Configuration</span></a>
            <a href="'. $path .'test-fmAdminAPI/get_xml_configuration.php?v='. DATA_API_VERSION .'"><span class="indent-1">Get XML Configuration</span></a>
            <a href="'. $path .'test-fmAdminAPI/get_server_general_configuration.php?v='. DATA_API_VERSION .'"><span class="indent-1">Get General Configuration</span></a>
            <a href="'. $path .'test-fmAdminAPI/get_server_security_configuration.php?v='. DATA_API_VERSION .'"><span class="indent-1">Get Security Configuration</span></a>
            <br>

            <div class="navigation-header2"><i class="fas fa-clock fa-fw"></i> Schedules</div>
            <a href="'. $path .'test-fmAdminAPI/list_schedules.php?v='. DATA_API_VERSION .'"><span class="indent-1">List Schedules</span></a>
            <a href="'. $path .'test-fmAdminAPI/get_schedule.php?v='. DATA_API_VERSION .'"><span class="indent-1">Get Schedule</span></a>
            <br>
            </div>
            <br>



            <button class="dropdown-btn" id="fmCURL">
               <div class="navigation-header1">fmCURL<i class="fas fa-caret-down"></i></div>
            </button>
            <div class="dropdown-container">
               <div class="navigation-header2"><i class="fas fa-cloud-download-alt fa-fw"></i> Get</div>
               <a href="'. $path .'test-fmCURL/curl.php?v='. DATA_API_VERSION .'"><span class="indent-1">Get contents of example.com</span></a>
               <a href="'. $path .'test-fmCURL/curl_bar.php?v='. DATA_API_VERSION .'"><span class="indent-1">Where is ...</span></a>
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