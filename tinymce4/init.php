<?php

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
		skin: 'lightgray',
		min_height: 200,
		plugins: [
		    'autolink lists link image charmap paste preview hr anchor pagebreak',
		    'searchreplace wordcount visualblocks visualchars code fullscreen',
		    'insertdatetime media nonbreaking save table contextmenu directionality',
		    'emoticons template textcolor colorpicker textpattern imagetools toc textcolor table'
		],
		paste_word_valid_elements: 'b,strong,i,em,h1,h2,u,p,ol,ul,li,a[href],span,color,font-size,font-color,font-family,mark,table,tr,td',
		paste_retain_style_properties : 'all',
		paste_strip_class_attributes: 'none',
		toolbar1: 'undo redo | insert styleselect | bold italic underline strikethrough | link image | alignleft aligncenter alignright alignjustify code pastetext',
		toolbar2: 'preview | bullist numlist | forecolor backcolor emoticons | fontselect |  fontsizeselect wordcount', 
		image_advtab: true,
		templates: [
		    { title: 'Test template 1', content: 'Test 1' },
		    { title: 'Test template 2', content: 'Test 2' }
		],
		content_css: [
		    '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
		    '//www.tinymce.com/css/codepen.min.css'
		],";
		if(USERUID) 
			echo "		external_image_list_url : '".STORIESPATH."/".USERUID."/images/imagelist.js',";
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
