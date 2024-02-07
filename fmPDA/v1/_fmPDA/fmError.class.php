<?php
// *********************************************************************************************************************************
//
// fmError.class.php
//
// *********************************************************************************************************************************
//
// Copyright (c) 2017 - 2024 Mark DeNyse
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

class fmError
{
   public $code;
   public $message;
   public $_fm;

   function __construct($fm, $message = null, $code = null)
   {
      $this->_fm = $fm;
      $this->code = $code;
      $this->message = $message;

      fmLogger('&#9785; '. __METHOD__ .'(): Code = '. $code .' Message = '. $message);

      return;
   }

   function getCode()
   {
      return $this->code;
   }

   function getMessage()
   {
      if (($this->message === null) && ($this->getCode() !== null)) {
         return $this->getErrorString();
      }
      return $this->message;
   }

   function getErrorString()
   {
      // FileMaker's language specific error messages are not included here.
      // If you really need this, it's not hard to implement this yourself.
      return '';
   }

   function isValidationError()
   {
      return false;
   }

}

?>
