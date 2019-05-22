<?php
// *********************************************************************************************************************************
//
// startup.inc.php
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

date_default_timezone_set('America/New_York');

// For DEBUGGING - Don't Even. Think. About. Doing. This. In. A. Production. Environment. Really.
ini_set('error_reporting', -1);
ini_set('display_startup_errors', 'on');
ini_set('display_errors', 'on');
ini_set('log_errors', 'on');


// If you *really* need to include FileMaker's API For PHP include it *before* fmPDA

// Include fmPDA - this replaces where you'd normally include FileMaker.php
require_once '../../../../fmPDA/v1/fmPDA.php';

require_once '../../host.inc.php';


// Set which version of the Data API we use by default. If not passed as a GET parameter, use the Latest version.
define('DATA_API_VERSION',              array_key_exists('v', $_GET) ? $_GET['v'] : FM_VERSION_LATEST);

?>
