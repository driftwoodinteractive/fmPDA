<?php

// *********************************************************************************************************************************
function GetNavigationMenu($numLevelsToRoot = 0, $apiVersion = 1)
{
   $path = '';
   for ($i = 1; $i <= $numLevelsToRoot; $i++) {
      $path .= '../';
   }

   $html = '
            <br>
            <div class="navigation-header1"><a href="'. $path .'../index.php">Home</a></div>
            <br>

            <button class="dropdown-btn" id="fmPDA">
               <div class="navigation-header1">fmPDA<i class="fas fa-caret-down"></i></div>
            </button>
            <div class="dropdown-container">
               <div class="navigation-header2"><a href="'. $path .'test-fmPDA/index.php?v='. $apiVersion .'#fmpda-constructor">Constructor</a></div>

               <div class="navigation-header2"><i class="fas fa-cloud-download-alt fa-fw"></i> Get</div>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiGetRecordID"><span class="indent-1">Get Record By ID</span></a>

               <div class="navigation-header2"><i class="fas fa-search fa-fw"></i> Find</div>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiFindAll"><span class="indent-1">Find All</span></a>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiFindAny"><span class="indent-1">Find Any</span></a>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiFindNonCompound"><span class="indent-1">Find (Non compound)</span></a>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiFindCompound"><span class="indent-1">Find (Compound)</span></a>

               <div class="navigation-header2"><i class="fas fa-edit fa-fw"></i> Editing</div>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiAdd"><span class="indent-1">Add Record</span></a>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiCreateCommit"><span class="indent-1">Create & Commit</span></a>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiEdit"><span class="indent-1">Edit Record</span></a>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiEditCommit"><span class="indent-1">Edit & Commit</span></a>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiDuplicate"><span class="indent-1">Duplicate Record</span></a>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiDelete"><span class="indent-1">Delete Record</span></a>

               <div class="navigation-header2"><i class="fas fa-file fa-fw"></i> Containers</div>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiGetContainer"><span class="indent-1">Get Container Data</span></a>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiGetContainerURL"><span class="indent-1">Get Container Data URL</span></a>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiUploadFile"><span class="indent-1">Upload Container</span></a>

               <div class="navigation-header2"><i class="fas fa-code fa-fw"></i> Scripting</div>
               <a href="'. $path .'test-fmpda/index.php?v='. $apiVersion .'#apiScript"><span class="indent-1">Run a Script</span></a>

               <div class="navigation-header2"><i class="fas fa-calendar-alt fa-fw"></i> Calendar</div>
               <a href="'. $path .'test-fmpda/examples/calendar/calendar.php?v='. $apiVersion .'?v='. $apiVersion .'"><span class="indent-1">Full Calendar Demo</span></span></a>
            </div>



            <button class="dropdown-btn" id="fmDataAPI">
               <div class="navigation-header1">fmDataAPI<i class="fas fa-caret-down"></i></div>
            </button>
            <div class="dropdown-container">

               <div class="navigation-header2"><a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#dataapi-optional-parameters">Optional parameters to most calls</a></div>
               <div class="navigation-header2"><a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#dataapi-constructor">Constructor</a></div>

               <div class="navigation-header2"><i class="fas fa-lock fa-fw"></i> Authentication</div>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#apiLogin"><span class="indent-1">Login</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#apiLogout"><span class="indent-1">Logout</span></a>

               <div class="navigation-header2"><i class="fas fa-cloud-download-alt fa-fw"></i> Get</div>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#apiGetRecord"><span class="indent-1">Get Record By ID</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#apiGetRecord"><span class="indent-1">Get Record By ID with Ext. Auth.</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#apiGetRecords"><span class="indent-1">Get Records</span></a>

               <div class="navigation-header2"><i class="fas fa-search fa-fw"></i> Find</div>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#apiFindRecords"><span class="indent-1">Find (Non compound)</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#apiFindRecords"><span class="indent-1">Find (Compound)</span></a>

               <div class="navigation-header2"><i class="fas fa-edit fa-fw"></i> Editing</div>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#apiAddRecord"><span class="indent-1">Add Record</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#apiEditRecord"><span class="indent-1">Edit Record</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#apiDeleteRecord"><span class="indent-1">Delete Record</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#apiSetGlobals"><span class="indent-1">Set Global Fields</span></a>

               <div class="navigation-header2"><i class="fas fa-file fa-fw"></i> Containers</div>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#apiGetContainer"><span class="indent-1">Get Container</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#apiUploadContainer"><span class="indent-1">Upload Container</span></a>

               <div class="navigation-header2"><i class="fas fa-code fa-fw"></i> Scripting</div>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $apiVersion .'#apiScript"><span class="indent-1">Run a Script</span></a>

               <div class="navigation-header2"><i class="fas fa-calendar-alt fa-fw"></i> Calendar</div>
               <a href="'. $path .'test-fmDataAPI/examples/calendar/calendar.php?v='. $apiVersion .'"><span class="indent-1">Full Calendar Demo</span></a>
            </div>


            <button class="dropdown-btn" id="fmAdminAPI">
               <div class="navigation-header1">fmAdminAPI<i class="fas fa-caret-down"></i></div>
            </button>
            <div class="dropdown-container">
               <div class="navigation-header2"><a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#adminapi-constructor">Constructor</a></div>

               <div class="navigation-header2"><i class="fas fa-lock fa-fw"></i> Authentication</div>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiLogin"><span class="indent-1">Login</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiLogout"><span class="indent-1">Logout</span></a>

               <div class="navigation-header2"><i class="fas fa-server fa-fw"></i> Server</div>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiGetServerInfo"><span class="indent-1">Get Server Info</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiGetServerStatus"><span class="indent-1">Get Server Status</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiSetServerStatus"><span class="indent-1">Set Server Status</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiGetPHPConfig"><span class="indent-1">Get PHP Configuration</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiSetPHPConfig"><span class="indent-1">Set PHP Configuration</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiGetXMLConfig"><span class="indent-1">Get XML Configuration</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiSetXMLConfig"><span class="indent-1">Set XML Configuration</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiGetGeneralConfig"><span class="indent-1">Get General Configuration</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiSetGeneralConfig"><span class="indent-1">Set General Configuration</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiGetSecurityConfig"><span class="indent-1">Get Security Configuration</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiSetSecurityConfig"><span class="indent-1">Set Security Configuration</span></a>

               <div class="navigation-header2"><i class="fas fa-server fa-fw"></i> Databases</div>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiListDatabases"><span class="indent-1">List Databases</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiOpenDatabase"><span class="indent-1">Open Database</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiCloseDatabase"><span class="indent-1">Close Database</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiPauseDatabase"><span class="indent-1">Pause Database</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiResumeDatabase"><span class="indent-1">Resume Database</span></a>


               <div class="navigation-header2"><i class="fas fa-server fa-fw"></i> Clients</div>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiDisconnectClient"><span class="indent-1">Disconnect Client</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiSendMessageToClient"><span class="indent-1">Send Message to Client</span></a>

               <div class="navigation-header2"><i class="fas fa-clock fa-fw"></i> Schedules</div>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiListSchedules"><span class="indent-1">List Schedules</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiCreateSchedule"><span class="indent-1">Create Schedule</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiDeleteSchedule"><span class="indent-1">Delete Schedule</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiDuplicateSchedule"><span class="indent-1">Duplicate Schedule</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiEnableSchedule"><span class="indent-1">Enable Schedule</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiDisableSchedule"><span class="indent-1">Disable Schedule</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiRunSchedule"><span class="indent-1">Run Schedule</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiGetSchedule"><span class="indent-1">Get Schedule</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $apiVersion .'#apiSetSchedule"><span class="indent-1">Set Schedule</span></a>
            </div>


            <button class="dropdown-btn" id="fmCURL">
               <div class="navigation-header1">fmCURL<i class="fas fa-caret-down"></i></div>
            </button>
            <div class="dropdown-container">
               <div class="navigation-header2"><a href="'. $path .'test-fmCURL/index.php#fmcurl-constructor">Constructor</a></div>
               <div class="navigation-header2"><i class="fas fa-cloud-download-alt fa-fw"></i> Get</div>
               <a href="'. $path .'test-fmCURL/index.php#curl"><span class="indent-1">Get data </span></a>
               <a href="'. $path .'test-fmCURL/index.php#getfile"><span class="indent-1">Get/Download a file</span></a>
            </div>


            <br>
            <div class="navigation-header1"><a href="https://driftwoodinteractive.com/fmpda" target="_blank">Download</a></div>
            <div class="navigation-header1"><a href="'. $path .'../versionhistory.php">Version History</a></div>
            <div class="navigation-header1"><a href="'. $path .'../license.php">License</a></div>
   ';

   return $html;
}

?>