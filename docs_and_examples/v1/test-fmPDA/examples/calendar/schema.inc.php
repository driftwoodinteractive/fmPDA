<?php

// Scripts
define('FM_PROJECT_CLICK_SCRIPT',      'Web_ProjectClick');


// Layouts
define('FM_PROJECT_LAYOUT',            'Web_Project');
define('FM_PHASE_LAYOUT',              'Web_Phase');
define('FM_HOLIDAY_LAYOUT',            'Web_Holiday');


// Table Occurences
define('FM_PHASE_PROJECT',             'Phase_Project');


// Project table
define('FM_PROJECT_ID_FIELD',          '___kp_ProjectID');
define('FM_PROJECT_NAME_FIELD',        'Name');
define('FM_PROJECT_START_DATE_FIELD',  'Date_Start');
define('FM_PROJECT_END_DATE_FIELD',    'Date_End');
define('FM_PROJECT_COLOR_INDEX_FIELD', 'ColorIndex');


// Phase table
define('FM_PHASE_ID_FIELD',            '___kp_PhaseID');
define('FM_PHASE_DESCRIPTION_FIELD',   'Description');
define('FM_PHASE_START_DATE_FIELD',    'Date_Start');
define('FM_PHASE_END_DATE_FIELD',      'Date_End');
define('FM_PHASE_COLOR_INDEX_FIELD',   'ColorIndex');


// Holiday table
define('FM_HOLIDAY_ID_FIELD',          '___kp_HolidayID');
define('FM_HOLIDAY_NAME_FIELD',        'Name');
define('FM_HOLIDAY_START_DATE_FIELD',  'Date_Start');
define('FM_HOLIDAY_END_DATE_FIELD',    'Date_End');



function fmTofullCalendarDate($fmDate, $addExtraDay = false)
{
   return date('Y-m-d', strtotime($fmDate) + ($addExtraDay ? 86400 : 0));     // 86400 = 1 day in seconds
}


?>
