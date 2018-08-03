<?php
// *********************************************************************************************************************************
//
// get_file.php
//
// This example uses fmCURL::getFile() to get/download/inline a file.
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

require_once 'startup.inc.php';

$url = 'https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png';

$options = array();
$options['action'] = array_key_exists('action', $_GET) ? $_GET['action'] : 'get';      // inline  download   get

$curl = new fmCURL();

$result = $curl->getFile($url, $options);

// If action is 'download' or 'inline' don't echo any thing else
if ($options['action'] == 'get') {

   fmLogger($result);
   fmLogger($curl->file);

   echo fmGetLog();
}

?>
