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

Copyright 2006 by Tammy Keefer.

This block for eFiction 3.0 displays which of your members is currently online 
and how many guests are visiting the site as well. In addition to the standard 
{blockname_title} and {blockname_content}, this block also adds {guests} as 
the number of guests and {onlinemembers} as the hyperlinked list of members online.

VERY IMPORTANT NOTE:  For this block to work correctly it must be set to Active 
even if it is only displayed on the index page because the block not only displays 
who is online it also tracks that information.  If it is set to "Index Only" you 
will only get the information for people on your index page.  And it must be set
this way on EVERY skin.  Again, even if it doesn't display, it must still be
set to Active for the visitor information to be recorded.

There is no admin panel for this block.  As is, it displays the people who have 
been on the site(loaded a page) in the last minute.  If you want to increase 
that time, open online.php and go to line 13 and replace 60 with the number of 
seconds you wish.