<?php
// *********************************************************************************************************************************
//
// fmLogger.class.php
//
// Logging tool. Can store the logging data internally, send to the system log.
//
// *********************************************************************************************************************************
//
// Copyright (c) 2017 Mark DeNyse
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


// *********************************************************************************************************************************
// Add to our log. Create a logging object if one doesn't exist.
//
function fmLogger($data, $logLevel = null)
{
   if (is_null(fmLogger::$gLogger)) {
      $logger = fmLogger::newLogger();
   }

   fmLogger::$gLogger->log($data, $logLevel);
}

// *********************************************************************************************************************************
function fmGetLog($asHTML = true)
{
   if (! is_null(fmLogger::$gLogger)) {
      return fmLogger::$gLogger->getlog($asHTML);
   }
   else {
      return '';
   }
}


// *********************************************************************************************************************************
class fmLogger
{
   public static $gLogger = null;

   public $logging;
   public $logToSystem;
   public $log;


   // *********************************************************************************************************************************
   function __construct($allowLogging = true)
   {
      $this->logging = $allowLogging;
      $this->logToSystem = false;
      $this->log = '';

      if (is_null(fmLogger::$gLogger)) {                          // Remember the first logger created
         fmLogger::$gLogger = $this;
      }

      return;
   }

   // *********************************************************************************************************************************
   function __destruct()
   {
      if (fmLogger::$gLogger == $this) {
         fmLogger::$gLogger = null;
      }
   }

   // *********************************************************************************************************************************
   static public function newLogger($allowLogging = true)
   {
      if (! is_null(fmLogger::$gLogger)) {
         $logger = fmLogger::$gLogger;
      }
      else {
         $logger = new fmLogger($allowLogging);
      }

      return $logger;
   }

   // *********************************************************************************************************************************
   public function log($data, $logLevel = null)
   {
      if ($this->logging) {

         if (is_array($data) || is_object($data)) {
            $data = print_r($data, 1);
         }

         $this->log  .= date('Y-m-d') .' '. strftime('%H:%M:%S') .' '. $data ."\n";

         if ($this->logToSystem) {
            error_log($data, 0);
         }
      }

      return;
   }

   // *********************************************************************************************************************************
   public function getLog($asHTML = true)
   {
      $result = '';

      if ($this->logging) {
         if ($asHTML) {
            $result .= '<div id="fmLogger" style="margin-top: 10px; padding-top: 10px; padding-bottom: 10px; padding-left: 5px; padding-right: 5px; white-space: nowrap; font-family: monospace; font-weight: normal; font-size: 11px; line-height: 13px; border: 1px solid gray; background-color: #eeeeee; text-align: left;">';
            $result .= str_replace(array("\n", ' '), array('<br>', '&nbsp;'), $this->log );
            $result .= '</div>';
         }
         else {
            $result .= str_replace(array('<br>', '&nbsp;'), array("\n", ' '), $this->log );
         }
      }

      return $result;
   }

   // *********************************************************************************************************************************
   function setLogging($allowLogging = true)
   {
      $this->logging = $allowLogging;
   }

}

?>
