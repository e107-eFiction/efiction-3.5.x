// ----------------------------------------------------------------------
// Copyright (c) 2007 by Tammy Keefer
// Also Like Module developed for eFiction 3.0
// // http://efiction.hugosnebula.com/
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

This little module will let you add the following variables to your storyindex.tpl

{theend} -

Displays "The End." or "The End...Maybe." depending on whether or not the story is marked
complete.

{authorcount} -

Displays the number of other stories the author has written.

{favof} - 

Displays: This story is a favorite of X members. 

Where X is the number of members. 

{alsolike} - 
Dispays: Members who liked TITLE also liked X other stories.  

Where TITLE is the title of the story (as a link to the story) and X is the number of
stories linked to a browse page listing the stories in question.

The stories are the other favorite stories from members who also listed this story 
in their favorites.

The browse panel for this module displays the stories in the standard browse page.

To install this module:

1. Upload the entire alsolike folder to the the modules folder within your 
eFiction installation.

2. Go to http://www.yoursite.com/modules/storyend/install.php where 
www.yoursite.com is your eFiction site's address.

3. Goto the main default_tpls folder.  Open up viewstory.tpl and/or storyindex.tpl and add 
{storyend} where you want this text to appear.

4. Do the same for any skins with their own viewstory.tpl and/or storyindex.tpl

To uninsall this module:

1. Go to http://www.yoursite.com/modules/storyend/uninstall.php where 
www.yoursite.com is your eFiction site's address.