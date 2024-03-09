<?php

echo "
<script language=\"javascript\" type=\"text/javascript\"><!--";
		$tinymessage = dbquery("SELECT message_text FROM " . TABLEPREFIX . "fanfiction_messages WHERE message_name = 'tinyMCE' LIMIT 1");
		list($tinysettings) = dbrow($tinymessage);
		if (!empty($tinysettings) && $current != "adminarea")
		{
			echo $tinysettings;
		}
		else
		{
			echo "
	tinyMCE.init({ 
		theme: 'advanced',
		height: '250',
		language: '$language',
		convert_urls: 'false',
		mode: 'textareas',
		extended_valid_elements: 'a[name|href|target|title]',
		plugins: 'advhr,advimage,advlink,searchreplace,contextmenu,preview,fullscreen,paste" . ($current == "adminarea" ? ",codeprotect" : "") . "',
		theme_advanced_buttons1_add: 'fontsizeselect',
		theme_advanced_buttons2_add: 'separator,pasteword,pastetext',
		theme_advanced_buttons3_add_before: 'tablecontrols,separator',
		theme_advanced_buttons3_add: 'advhr',
		theme_advanced_toolbar_align: 'center',
		theme_advanced_statusbar_location: 'bottom',
		theme_advanced_path: 'false',
		editor_deselector: 'mceNoEditor',
";
			if (USERUID)
				echo "		external_image_list_url : '" . STORIESPATH . "/" . USERUID . "/images/imagelist.js',";
			echo "
		theme_advanced_resizing: true," . ($current == "adminarea" ? "\n\t\tentity_encoding: 'raw'" : "\n\t\tinvalid_elements: 'script,object,applet,iframe'") . "
   });
";
		}
		echo "
var tinyMCEmode = true;
	function toogleEditorMode(id) {
		var elm = document.getElementById(id);

		if (tinyMCE.getInstanceById(id) == null)
			tinyMCE.execCommand('mceAddControl', false, id);
		else
			tinyMCE.execCommand('mceRemoveControl', false, id);
	}
";
		echo " --></script>";