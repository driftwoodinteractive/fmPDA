<?php

//               <div class="navigation-header2"><i class="fas fa-lock fa-fw"></i> Metadata</div>

// *********************************************************************************************************************************
function GetNavigationMenu($numLevelsToRoot = 0, $dataAPIVersion = 1, $adminAPIVersion = 2)
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
               <div class="navigation-header2"><a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#fmpda-constructor">Constructor</a></div>

               <div class="navigation-header2"><i class="fas fa-info-circle"></i> Metadata</div>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#listdatabases"><span class="indent-1">List Databases</span></a>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#listlayouts"><span class="indent-1">List Layouts</span></a>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#listscripts"><span class="indent-1">List Scripts</span></a>

               <div class="navigation-header2"><i class="fas fa-cloud-download-alt fa-fw"></i> Get</div>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiGetRecordID"><span class="indent-1">Get Record By ID</span></a>

               <div class="navigation-header2"><i class="fas fa-search fa-fw"></i> Find</div>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiFindAll"><span class="indent-1">Find All</span></a>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiFindAny"><span class="indent-1">Find Any</span></a>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiFindNonCompound"><span class="indent-1">Find (Non compound)</span></a>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiFindCompound"><span class="indent-1">Find (Compound)</span></a>

               <div class="navigation-header2"><i class="fas fa-edit fa-fw"></i> Editing</div>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiAdd"><span class="indent-1">Add Record</span></a>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiCreateCommit"><span class="indent-1">Create & Commit</span></a>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiEdit"><span class="indent-1">Edit Record</span></a>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiEditCommit"><span class="indent-1">Edit & Commit</span></a>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiDuplicate"><span class="indent-1">Duplicate Record</span></a>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiDelete"><span class="indent-1">Delete Record</span></a>

               <div class="navigation-header2"><i class="fas fa-file fa-fw"></i> Containers</div>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiGetContainer"><span class="indent-1">Get Container Data</span></a>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiGetContainerURL"><span class="indent-1">Get Container Data URL</span></a>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiUploadFile"><span class="indent-1">Upload Container</span></a>

               <div class="navigation-header2"><i class="fas fa-code fa-fw"></i> Scripting</div>
               <a href="'. $path .'test-fmPDA/index.php?v='. $dataAPIVersion .'#apiScript"><span class="indent-1">Run a Script</span></a>

               <div class="navigation-header2"><i class="fas fa-calendar-alt fa-fw"></i> Calendar</div>
               <a href="'. $path .'test-fmPDA/examples/calendar/calendar.php?v='. $dataAPIVersion .'?v='. $dataAPIVersion .'"><span class="indent-1">Full Calendar Demo</span></span></a>
            </div>



            <button class="dropdown-btn" id="fmDataAPI">
               <div class="navigation-header1">fmDataAPI<i class="fas fa-caret-down"></i></div>
            </button>
            <div class="dropdown-container">

               <div class="navigation-header2"><a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#dataapi-optional-parameters">Optional parameters to most calls</a></div>
               <div class="navigation-header2"><a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#dataapi-constructor">Constructor</a></div>

               <div class="navigation-header2"><i class="fas fa-info-circle"></i> Metadata</div>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiProductInfo"><span class="indent-1">Product Info</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiListDatabases"><span class="indent-1">List Databases</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiListLayouts"><span class="indent-1">List Layouts</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiListScripts"><span class="indent-1">List Scripts</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiLayoutMetadata"><span class="indent-1">Layout Metadata</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiValueList"><span class="indent-1">Get Value List</span></a>

               <div class="navigation-header2"><i class="fas fa-lock fa-fw"></i> Authentication</div>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiLogin"><span class="indent-1">Login</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiLogout"><span class="indent-1">Logout</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiValidateSession"><span class="indent-1">Validate Session</span></a>

               <div class="navigation-header2"><i class="fas fa-cloud-download-alt fa-fw"></i> Get</div>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiGetRecord"><span class="indent-1">Get Record By ID</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiGetRecord"><span class="indent-1">Get Record By ID with Ext. Auth.</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiGetRecords"><span class="indent-1">Get Records</span></a>

               <div class="navigation-header2"><i class="fas fa-search fa-fw"></i> Find</div>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiFindRecords"><span class="indent-1">Find (Non compound)</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiFindRecords"><span class="indent-1">Find (Compound)</span></a>

               <div class="navigation-header2"><i class="fas fa-edit fa-fw"></i> Editing</div>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiAddRecord"><span class="indent-1">Add Record</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiEditRecord"><span class="indent-1">Edit Record</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiDeleteRecord"><span class="indent-1">Delete Record</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiDuplicateRecord"><span class="indent-1">Duplicate Record</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiSetGlobals"><span class="indent-1">Set Global Fields</span></a>

               <div class="navigation-header2"><i class="fas fa-file fa-fw"></i> Containers</div>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiGetContainer"><span class="indent-1">Get Container</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiUploadContainer"><span class="indent-1">Upload Container</span></a>

               <div class="navigation-header2"><i class="fas fa-code fa-fw"></i> Scripting</div>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiExecuteScript"><span class="indent-1">Execute a Script (v18+)</span></a>
               <a href="'. $path .'test-fmDataAPI/index.php?v='. $dataAPIVersion .'#apiPerformScript"><span class="indent-1">PerformScript</span></a>

               <div class="navigation-header2"><i class="fas fa-calendar-alt fa-fw"></i> Calendar</div>
               <a href="'. $path .'test-fmDataAPI/examples/calendar/calendar.php?v='. $dataAPIVersion .'"><span class="indent-1">Full Calendar Demo</span></a>
            </div>


            <button class="dropdown-btn" id="fmAdminAPI">
               <div class="navigation-header1">fmAdminAPI<i class="fas fa-caret-down"></i></div>
            </button>
            <div class="dropdown-container">
               <div class="navigation-header2"><a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#adminapi-constructor">Constructor</a></div>

               <div class="navigation-header2"><i class="fas fa-lock fa-fw"></i> Authentication</div>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiLogin"><span class="indent-1">Login</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiLogout"><span class="indent-1">Logout</span></a>

               <div class="navigation-header2"><i class="fas fa-server fa-fw"></i> Server</div>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiGetServerInfo"><span class="indent-1">Get Server Info</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiGetServerStatus"><span class="indent-1">Get Server Status</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiSetServerStatus"><span class="indent-1">Set Server Status</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiGetLog"><span class="indent-1">Get Server Log</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiGetDataAPIConfiguration"><span class="indent-1">Get Data API Configuration</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiSetDataAPIConfiguration"><span class="indent-1">Set Data API Configuration</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiGetGeneralConfiguration"><span class="indent-1">Get General Configuration</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiSetGeneralConfiguration"><span class="indent-1">Set General Configuration</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiGetPHPConfiguration"><span class="indent-1">Get PHP Configuration</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiSetPHPConfiguration"><span class="indent-1">Set PHP Configuration</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiGetSecurityConfiguration"><span class="indent-1">Get Security Configuration</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiSetSecurityConfiguration"><span class="indent-1">Set Security Configuration</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiGetWebDirectConfiguration"><span class="indent-1">Get Web Direct Configuration</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiSetWebDirectConfiguration"><span class="indent-1">Set Web Direct Configuration</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiGetWPEConfiguration"><span class="indent-1">Get WPE Configuration</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiSetWPEConfiguration"><span class="indent-1">Set WPE Configuration</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiGetXDBCConfiguration"><span class="indent-1">Get xDBC Configuration</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiSetXDBCConfiguration"><span class="indent-1">Set xDBC Configuration</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiGetXMLConfiguration"><span class="indent-1">Get XML Configuration</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiSetXMLConfiguration"><span class="indent-1">Set XML Configuration</span></a>


               <div class="navigation-header2"><i class="fas fa-server fa-fw"></i> Databases</div>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiListDatabases"><span class="indent-1">List Databases</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiPerformOnDatabases"><span class="indent-1">Perform on All Databases</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiPerformOnDatabase"><span class="indent-1">Perform on a Database</span></a>


               <div class="navigation-header2"><i class="fas fa-server fa-fw"></i> Clients</div>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiListClients"><span class="indent-1">List Clients</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiDisconnectClient"><span class="indent-1">Disconnect Client</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiSendMessageToClient"><span class="indent-1">Send Message to Client</span></a>


               <div class="navigation-header2"><i class="fas fa-clock fa-fw"></i> Schedules</div>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiListSchedules"><span class="indent-1">List Schedules</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiCreateSchedule"><span class="indent-1">Create Schedule</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiDeleteSchedule"><span class="indent-1">Delete Schedule</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiDuplicateSchedule"><span class="indent-1">Duplicate Schedule</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiEnableSchedule"><span class="indent-1">Enable Schedule</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiDisableSchedule"><span class="indent-1">Disable Schedule</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiRunSchedule"><span class="indent-1">Run Schedule</span></a>

               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiGetSchedule"><span class="indent-1">Get Schedule</span></a>
               <a href="'. $path .'test-fmAdminAPI/index.php?v='. $adminAPIVersion .'#apiSetSchedule"><span class="indent-1">Set Schedule</span></a>
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
