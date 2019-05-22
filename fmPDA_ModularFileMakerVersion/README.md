--------------------------------------------------------------------------------
--------------------------------------------------------------------------------

fmPDA
-----
Version 1

fmPDA provides scripts to use the Data API and Admin API without needing to know the
underlying constructs of how to make each call. The curl module wraps around Insert From URL
to handle all the error checking, automatic retries, etc. You can use this on any host, not just
FileMaker's APIs.


Version 2 will be released shortly - v1 works fine with Data API in FMS 17 and later.


--------------------------------------------------------------------------------
--------------------------------------------------------------------------------

REQUIREMENTS
------------
FileMaker 17 or later


INSTALLATION
------------
1. Import the following custom functions into your solution:
    fmPDA.jsonGet()   - This is a wrapper for the JSONGetElement but returns "" instead
                        of a JSON error string on error. We added the fmPDA. prefix to
                        avoid any namespace collisions with other custom functions.
    #()               - Part of the incredibly useful #Parameters module
    #Assign()         - Part of the incredibly useful #Parameters module
2. Copy/Import the fmPDA.v1 folder to your solution.
3. That's it - no layouts, tables, or TO's to add!


USAGE
-----
Check out the Examples script folder in this file. You'll find examples for all Data API calls
and a good chunk of Admin API calls as well. The simplest example is Data API: Test GetRecord
to retrieve one record by recordID. The accompanying 'demo' database can be used with all examples.
Use the Scripts menu to run any of the examples.




LICENSE
-------
fmPDA is released under the 'MIT' license. If you use this in your project, I'd
enjoy hearing about it. Please let me know if you have questions, find bugs, or
have suggestions for improvements. I'll do my best to respond to all queries.


Mark DeNyse
DriftwoodInteractive.com
github.com/driftwoodinteractive
info@driftwoodinteractive.com


--------------------------------------------------------------------------------
--------------------------------------------------------------------------------
Copyright (c) 2017 - 2019 Mark DeNyse

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
