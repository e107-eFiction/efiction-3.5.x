<?php
$image_list_path = STORIESPATH . "/" . USERUID . "/images/imagelist.js";
$image_list_exists = file_exists($image_list_path); 
 
if (USERUID && $image_list_exists)
{
echo "
<script src='".STORIESPATH . "/" . USERUID . "/images/imagelist.js"."'></script>";
} 
echo "
	<script language=\"javascript\" type=\"text/javascript\"><!--";
	$tinylanguage = $language;
	if (!file_exists(_BASEDIR . "tinymce/js/tinymce/langs/{$language}.js"))
	{
		$tinylanguage = "en";
	} 
	$tinymessage = dbquery("SELECT message_text FROM ".TABLEPREFIX."fanfiction_messages WHERE message_name = 'tinyMCE' LIMIT 1");
	list($tinysettings) = dbrow($tinymessage);
	if(!empty($tinysettings) && $current != "adminarea") {
		echo $tinysettings;
	}
	else {
		echo "
	tinymce.init({
  		selector: 'textarea:not(.mceNoEditor)',
  		menubar: false,
		language: '$tinylanguage',
  		theme: 'modern',
		invalid_styles: 'color,font-size,margin,line-height,font-family,margin-top,margin-bottom',
		plugins: 'wordcount emoticons fullscreen anchor code hr image link paste ',
		skin: 'lightgray',
		min_height: 200,
	    browser_spellcheck: true,
		relative_urls: false,
		remove_script_host: false,
    	convert_urls: true,
		paste_word_valid_elements: 'b,strong,i,em,h1,h2,u,p,ol,ul,li,a[href],span,color,font-size,font-color,font-family,mark,table,tr,td',
		toolbar1: 'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist | undo redo | fullscreen code | link unlink | emoticons image anchor hr ',	image_advtab: true,
     	paste_word_valid_elements: 'b,strong,i,em,p,span,u,strike,br',
    	paste_retain_style_properties: 'text-decoration,text-align',
	    menu: {
			file: { title: 'File', items: 'newdocument' },
			edit: { title: 'Edit', items: 'undo redo | cut copy paste pastetext | selectall' },
			insert: { title: 'Insert', items: 'link media | template hr' },
			view: { title: 'View', items: 'visualaid' },
			table: { title: 'Table', items: 'inserttable tableprops deletetable | cell row column' },
			tools: { title: 'Tools', items: '' }
    	},";
		if (USERUID && $image_list_exists)
			echo "	image_list: tinyMCE4ImageList ,";
		echo "
		theme_modern_resizing: true,".($current == "adminarea" ? "\n\t\tentity_encoding: 'raw'" : "\n\t\tinvalid_elements: 'script,object,applet,iframe'")."
   });
	
";
	}
	echo "
var tinyMCEmode = true;
	function toogleEditorMode(id) {
		var elm = document.getElementById(id);

		if (tinyMCE.get(id) == null)
		tinymce.EditorManager.execCommand('mceAddEditor',true, id);
		//tinyMCE.execCommand('mceAddControl', false, id);
		else
		tinymce.EditorManager.execCommand('mceRemoveEditor', true, id);
		//	tinyMCE.execCommand('mceRemoveControl', false, id);
	}
";
/*echo "
var tinyMCEmode = true;
	function toogleEditorMode(id) {
		var elm = document.getElementById(id);

		if (tinyMCE.get(id) == null)
			tinyMCE.execCommand('mceAddControl', false, id);
		else
			tinyMCE.execCommand('mceRemoveControl', false, id);
	}
";*/
echo " --></script>";
