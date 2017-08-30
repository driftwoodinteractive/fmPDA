                                   fmPDA
                            FileMaker Php Data Api

A replacement class for the FileMaker API For PHP using the FileMaker Data API

                                 Mark DeNyse
                        fmpda@driftwoodinteractive.com


At FM Devon 2017, there was a discusssion about how to move existing CWP code
using FileMaker's API For PHP to the new Data API (REST) interface. fmPDA solves
this issue by providing method-level compatibility to the existing API with
minimal code changes on your part. Under the hood, fmPDA uses the new Data API.
fmPDA has been tested with PHP versions 5.2.17 through 7.1.6.

fmPDA is divided into three classes:

fmCURL
------
fmCURL is a wrapper for curl() calls. You can use this class for any curl()
calls you need to make. This class is used by the fmDataAPI class to communicate
with FileMaker's Data API. Additionally, fmCURL instantiates a global fmLogger
object to log various messages the classes generate. You can use this for your
own purposes as well. See any of the example files on how it's used.


fmDataAPI
---------
fmDataAPI encapsulates the interactions with FileMaker's Data API. It takes care
of managing the authentication token the Data API uses. By default it stores the
token in a session variable so calls across multiple PHP pages will reuse the
same token. If the token ages out, fmDataAPI will ask the Data API for a new one
and update the session variable. You can disable session variable storage, but
you'll then be responsible for managing the storage of the token.

fmPDA
-----
fmPDA mirrors FileMaker's 'old' API For PHP. To use it, you'll remove:

include 'PATH-TO-FILEMAKER-CLASS-FILES/FileMaker.php';

and replace it with:

include 'PATH-TO-FMPDA-CLASS-FILES/fmPDA.php';

Within the limits described below, your existing code existing code should
function as is, with the exception that it's using FileMaker's Data API instead
of the XML interface. Not everything is supported, so you will probably have to
make some changes to your code.

fmPDA can also return the 'raw' data from the Data API; if you want to use fmPDA
to create the structures for passing to the Data API but want to process the
data on your own, set the 'translateResult' element to true in the $options
array you pass to the fmPDA constructor.


What is supported:
------------------
- Get Record By ID
- Find All
- Find Any
- Find (Non compound & Compound)
- Add Record
- Create Record & Commit
- Edit Record
- Get Record, Edit & Commit
- Delete Record
- Get Container Data
- Get Container Data URL
- Script execution
   Emulated with the old XML interface. When FMI supports this directly,
   fmPDA will be updated to use the Data API. This is only for direct script
   calls - pre-script, pre-command, and pre-sort can not be emulated.

What isn't supported:
---------------------
- Duplicate record
- Pre-script, pre-command, pre-sort script execution
- Setting the Result layout
- List scripts
- List databases
- List layouts
- Get layout metadata
- Validation
- Value Lists
- getTableRecordCount() and getFoundSetCount()
   fmPDA will create a fmLogger() message and return getFetchCount().
   One suggestion has been made to create an unstored calculation field in
   your table to return these values and place them on your layout.
- Using Commit() to commit data on portals.


Changes you'll likely have to make to your code:
------------------------------------------------
The biggest change is replacing all calls to FileMaker::isError() to use the
function fmGetIsError() as the FileMaker class no longer exists. If this is a
major hassle, you can change conf.fmPDA.php and modify the following line:

   define('DEFINE_FILEMAKER_CLASS', false);

to:

   define('DEFINE_FILEMAKER_CLASS', true);

This will create a 'glue' FileMaker class that fmPDA inherits from, and you can
continue to use FileMaker::isError(). You should switch to fmGetIsError() in the
future to reduce your dependence on the FileMaker class.



Things to look out for with the new Data API:
---------------------------------------------
Do not name a field called omi; that name is used in a find query to omit
records.

Do not name a field called deleteRelated; that name is used when editing a
record to delete a related record.

getFieldAsTimestamp() can't automatically determine the field type as the Data
API doesn't return field metadata. There is now a new third parameter
($fieldType) to tell the method how to convert the field data. See
Record.inc.php for details.

getContainerData() and getContainerDataURL() now return the full URL - no need
for the 'ContainerBridge' file! See container_data.php or container_data_url.php
for an example.

The Data API replaces the name of the Table Occurrence in portals with the
layout object name (if one exists). If you name your portals on the dedicated
CWP layouts (you do have those, right?) you've been using with the old API,
you'll need to change your code (ugh) or remove the object names (recommended).

The Data API translates FM line separators from a line feed (\n) in the old API
is now a carriage return (\r). If your code looks for line feeds, look for
carriage returns now.



License
-------
fmPDA is released under the 'MIT' license. If you use this in your project, I'd
enjoy hearing about it. Please let me know if you have questions, find bugs, or
have suggestions for improvements. I'll do my best to respond to all queries.


Mark DeNyse
Driftwood Interactive
fmpda@driftwoodinteractive.com


--------------------------------------------------------------------------------
--------------------------------------------------------------------------------
Copyright (c) 2017 Mark DeNyse

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

--------------------------------------------------------------------------------
--------------------------------------------------------------------------------
