# efiction v3.5.8

- added notification module
- changed files: user/editbio.php and user/lostpassword.php 


# efiction v3.5.7

minimum requirements:  MySQL 5.6.3  or MariaDB 10.5.0 


### Database changes:

- all IP adress fields - from integer to 46 chars

### Other changes:

- tinymce4 settings fix
- INET6 support needed
- file uploading fixed for PHP 8.1
- national characters fix in search form 



# efiction v3.5.6

non official version of 3.5.x efiction software

## Only for testing purposes


### Database changes:

- all date fields from datestamp format to unix time format

### File's changes

- related date field type changes
- found issues fixed
- deleted bridges 

### Looking for missing modules
If you have source code of missing and used modules, share it and I can try to add and fix them


### Installation
No change

### Update
Classic as before, run update process that should change your date fields without lossing your data. It works only for update from 3.5.5, not sooner.



<code>
// ----------------------------------------------------------------------
// Copyright (c) 2007 by Tammy Keefer
// Based on eFiction 1.1
// Copyright (C) 2003 by Rebecca Smallwood.
// http://efiction.sourceforge.net/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------

see more in README.txt file
</code>
