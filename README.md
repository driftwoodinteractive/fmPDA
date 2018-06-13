                                   fmPDA
                            FileMaker Php Data Api

A replacement class for the FileMaker API For PHP using the FileMaker Data API

                                 Mark DeNyse
                        fmpda@driftwoodinteractive.com


Your Problem:
-------------
You have Custom Web Publishing (CWP) code written using FileMaker's
API for PHP. FileMaker has made it clear the new Data API is the way to go, and
the XML interface (which FileMaker's API for PHP uses) will likely be deprecated
in the future. Your code will break. Game Over, Dude.


So, what do you do?
-------------------
- Rewrite your code to use the new Data API. Not 'hard', but it'll take time to
  rewrite/debug. In the end, your code may be a little faster. Yay, you.

- Use a library that someone wrote to solve the same problem. Less time
  consuming, especially if the code could replicate FileMaker's API for PHP.


Wait, what?
-----------
fmPDA provides method & data structure compatibility with FileMaker's API For
PHP. So, only minor changes should be needed to your code.




fmPDA v1
--------

fmCURL
------
fmCURL is a wrapper for CURL calls. The curl() method sets the typical
parameters and optionally encode/decodes JSON data. fmCURL is independent of
the FM API; it can be used to communicate with virtualy any host (such as
Google, Swipe, etc.). The fmAPI class (see below) uses fmCURL to communicate
with FileMaker's API.

Example:
$curl = new CURL();
$curlResult = $curl->curl('https://www.example.com');

Additionally, fmCURL instantiates a global fmLogger object to log various
messages these classes generate. You can use this for your own purposes as well.
See any of the example files on how it's used.



fmAPI
-----
fmAPI encapsulates the interactions with FileMaker's API. fmAdminAPI and
fmDataAPI extend this class. You won't typically instantiate this class
directly.

fmAPI takes care of managing the authentication token the API requires in all
calls. It will request a new token whenever the current token is invalid without
your code needing to know. By default, the token is stored in a session variable
so calls across multiple PHP pages will reuse the same token. You can disable
session variable storage, but you'll be responsible for managing the storage of
the token.



fmAdminAPI
----------
fmAdminAPI encapsulates the interactions with FileMaker's Admin Console API. Use
this to communicate with FileMaker Server's Admin Console to get the server
status, schedules, configuration, etc.

Example:
$fm = new fmAdminAPI($host, $userName, $password);
$apiResult = $fm->apiGetServerStatus();



fmDataAPI
---------
fmDataAPI encapsulates interactions with FileMaker's Data API. The class
provides methods for directly interacting with the Data API (Get, Find, Create,
Edit, Delete, Upload Container, Set Globals, Scripts, etc.)

Example:
$fm = new fmDataAPI($database, $host, $userName, $password);
$apiResult = $fm->apiGetRecord($layout, $recordID);


Caution
-------
- OAuth support is included but has not been tested.

- The Data API replaces the name of the Table Occurrence in portals with the
  layout object name (if one exists). If you name your portals on the dedicated
  Web layouts (you do have those, right?) you've been using with the old API,
  you'll need to change your code (ugh) or remove the object names.

- The Data API translates FM line separators from a line feed (\n) in the old
  API is now a carriage return (\r). If your code looks for line feeds, look for
  carriage returns now.



fmPDA
-----
fmPDA provides method & data structure compatibility with FileMaker's 'old' API
For PHP.

Example:
$fm = new fmPDA($database, $host, $userName, $password);
$findAllCommand = $fm->newFindAllCommand($layout);
$findAllCommand->addSortRule($fieldName, 1, FILEMAKER_SORT_DESCEND);
$result = $findAllCommand->execute();


Remember, wherever you did this:

$fm = new FileMaker(...);

replace it with:

$fm = new fmPDA(...);


Within the limits described below, your existing code should function as is,
with the exception that it's using FileMaker's Data API instead of the XML
interface. Not everything is supported, so you may have to make some changes to
your code.

fmPDA can also return the 'raw' data from the Data API; if you want to use fmPDA
to create the structures for passing to the Data API but want to process the
data on your own, set the 'translateResult' element to false in the $options
array you pass to the fmPDA constructor. Alternatively, you can override
fmPDA::newResult() to return the result in whatever form you wish.


What is supported
-----------------
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
- Duplicate record (The duplicate.php example file shows how to do this with a
  simple FM script)


What isn't supported
--------------------
- List scripts
- List databases - in v1 or later, use fmAdminAPI::apiListDatabases()
- List layouts
- Get layout metadata
- Validation
- Value Lists
- Using Commit() to commit data on portals.
- getTableRecordCount() and getFoundSetCount() - fmPDA will create a fmLogger()
  message and return getFetchCount(). One suggestion has been made to create an
  unstored calculation field in your table to return these values and place them
  on your layout.


Caution
-------
- getFieldAsTimestamp() can't automatically determine the field type as the Data
  API doesn't return field metadata. There is now a new third parameter
  ($fieldType) to tell the method how to convert the field data. See
  FMRecord.class.php for details.

- getContainerData() and getContainerDataURL() now return the full URL - no need
  for the 'ContainerBridge' file! See container_data.php or
  container_data_url.php for an example.




A change you'll likely have to make to your code
------------------------------------------------
The biggest change is replacing any calls to FileMaker::isError($result) to use
the function fmGetIsError($result) as the FileMaker class no longer exists.

```
if (FileMaker::isError($result)) {
   /* Oops. Let's handle the error... */
}
```
Change it to:

```
if (fmGetIsError($result)) {
   /* Oops. Let's handle the error... */
}
```


If you really don't want to do this (::sigh::), you can change fmPDA.conf.php
and modify the following line:

define('DEFINE_FILEMAKER_CLASS', false);

to:

define('DEFINE_FILEMAKER_CLASS', true);

This will create a 'glue' FileMaker class that fmPDA inherits from, and you can
continue to use FileMaker::isError(). Even so, it's recommended that you should
switch to fmGetIsError() in the future to reduce/eradicate your dependence on a
class called FileMaker. You'll run into conflicts if you do this and keep
FileMaker's old classes in your include tree. You Have Been Warned.




PHP Compatibility
-----------------
fmPDA has been tested with PHP versions 5.2.17 through 7.1.6.



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
Copyright (c) 2017 - 2018 Mark DeNyse

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
