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

This block for eFiction 3.0 displays a one question poll.  It is set up so that only members 
can vote and they can only vote once.  Visitors and members will see the current tally of 
votes while members who haven't voted will see the question with a form to cast their vote.  
The only thing to set in the admin panel is the question and the options.  Enter the options 
one per line in the text box.  Because of tinyMCE, <p> tags will get stripped from the options.  
All other allowable tags from your site's config settings will be honored.  You'll have to 
use <br> tags instead if you want a multi-line option.  Sorry. :( 

The bar graphic is 12 pixels high by 1 pixel wide.  There's a folder with a few alternatives 
included.  If you want to change the color of the bar for a single skin include the following 
in the skin's variables.php file.

$blocks['poll']['bar'] = $skindir."/images/BARIMAGE.JPG";

Replacing BARIMAGE.JPG with the name of your image.  If you want to style the bar images (adding
a 1px solid border looks nice) use the .poll class.  The poll question is inside a <div> with the 
id of poll_question. So you can style that with #poll_question.  There are a number of alternative 
bars in the bars folder.

You can only have one poll active at a time.  When you close a poll the votes for that poll are 
deleted and the total results stored with the poll question. I did it this way to keep the 
database from growing.  The votes table will then be emptied for use in the next poll.  If 
no poll is going on the block will display "There is no poll currently active."  

