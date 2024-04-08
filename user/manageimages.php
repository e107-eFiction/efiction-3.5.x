<?php
// ----------------------------------------------------------------------
// eFiction 3.2
// Copyright (c) 2007 by Tammy Keefer
// Valid HTML 4.01 Transitional
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

if(!defined("_CHARSET")) exit( );

// $upfile = isset($_FILES['upfile']) ? $_FILES['upfile'] : false;
include("includes/upload_class.php");
function buildImageList( ) {
		$dir = opendir(STORIESPATH."/".USERUID."/images/");
		while($file = readdir($dir)) {
			if(!in_array($file, array(".", "..", "/", "index.php", "imagelist.js"))) {
				$path =  STORIESPATH . "/" . USERUID . "/images/".$file;
				$image_files[] = "[\"" . STORIESPATH . "/" . USERUID . "/images/$file\", \"" . STORIESPATH . "/" . USERUID . "/images/$file\"]";

				$image4_files[] = '{ "title": "'. $file. '", "value": "'.$path.'" }';
		 
			}
		}
		if(isset($image_files)) {
			$handle = fopen(STORIESPATH."/".USERUID."/images/imagelist.js", 'w');
			$text = "var tinyMCEImageList = new Array(\n";
			$text .= implode(", \n", $image_files);
			$text .= ");\n\n";
			fwrite($handle, $text);

			$text = "var tinyMCE4ImageList = [\n";
			$text .= implode(", \n", $image4_files);
			$text .= "];\n\n";
			fwrite($handle, $text);

			fclose($handle);
		}
}

class multi_upfiles extends file_upload {
	
	var $number_of_files = 0;
	var $names_array;
	var $tmp_names_array;
	var $error_array;
	var $wrong_extensions = 0;
	var $bad_filenames = array( );
	
	function extra_text($msg_num) {
		switch ($this->language) {
			case "de":
			// add you translations here
			break;
			default:
			$extra_msg[1] = "<strong>".$this->the_file."</strong>";
			$extra_msg[2] = sprintf(_WRONGEXT, $this->wrong_extensions, $this->ext_string);
			$extra_msg[3] = "<strong>".$this->the_file."</strong> "._IMAGETOOBIG;
			$extra_msg[4] = _NOFILESSELECTED;
			$extra_msg[5] = _BADFILENAMES."<strong>".implode(",", $this->bad_filenames)."</strong>";
		}
		return $extra_msg[$msg_num];
	}
	// some error (HTTP)reporting, change the messages or remove options if you like.
	// this method checkes the number of files for upload
	// this example works with one or more files
	function count_files() {
		foreach ($this->names_array as $test) {
			if ($test != "") {
				$this->number_of_files++;
			}
		}
		if ($this->number_of_files > 0) {
			return true;
		} else {
			return false;
		} 
	}
	function get_img_size($file) {
		$img_size = getimagesize($file);
		$this->x_size = $img_size[0];
		$this->y_size = $img_size[1];
	}
	function upload_multi_files () {
		global $output, $imageheight, $imagewidth; 
		$this->message = array();
		if ($this->count_files()) {
			foreach ($this->names_array as $key => $value) { 
				if ($value != "") {
					$this->the_file = $value;
					$new_name = $this->set_file_name();
					$ext = $this->get_extension($new_name);
					$validImage = true;
					if($ext == ".jpg" || $ext == ".gif" || $ext == ".jpeg") {
						$this->get_img_size($this->tmp_names_array[$key]);
						if($this->x_size > $imageheight) {
							$this->message[] = $this->extra_text(3);
							$validImage = false;
						}
						else if($this->y_size > $imagewidth) {
							$this->message[] = $this->extra_text(3);
							$validImage = false;
						}
					}
					if($validImage) {
						if ($this->check_file_name($new_name)) {
							if ($this->validateExtension()) {
								$this->file_copy = $new_name;
								$this->the_temp_file = $this->tmp_names_array[$key];
								if (is_uploaded_file($this->the_temp_file)) {
									if($this->move_upload($this->the_temp_file, $this->file_copy)) {
										$this->message[] = $this->error_text($this->error_array[$key]);
										if ($this->rename_file) $this->message[] = $this->error_text(16);
										sleep(1); // wait a seconds to get an new timestamp (if rename is set)
									} else {
										$this->message[] = $this->extra_text(1);
										$this->message[] = $this->error_text($this->error_array[$key]);
									}
								}
							} else {
								$this->wrong_extensions++;
						}
						} else {
							$this->bad_filenames[] = $this->the_file;
						}
					}
				}
			}
			if (count($this->bad_filenames) > 0) $this->message[] = $this->extra_text(5);
			if ($this->wrong_extensions > 0) {
				$this->show_extensions();
				$this->message[] = $this->extra_text(2);
			}
		} else {
			$this->message[] = $this->extra_text(3);
		}
		$output .= write_message(is_array($this->message) ? implode("<br />", $this->message) : _ERROR);
	}
}

		
$output .= "<div id=\"pagetitle\">"._MANAGEIMAGES."</div>";
if($imageupload) {

	$max_size = 1024*250; // the max. size for uploading
	

	if(isset($_POST['submit'])) {
		$multi_upload = new multi_upfiles;
		$multi_upload->upload_dir = STORIESPATH."/".USERUID."/images/"; // "files" is the folder for the uploaded files (you have to create this folder)
		$multi_upload->extensions = array(".png", ".gif", ".jpg", ".jpeg"); // specify the allowed extensions here
		$multi_upload->message[] = $multi_upload->extra_text(4); // a different standard message for multiple files
		//$multi_upload->rename_file = true; // set to "true" if you want to rename all files with a timestamp value
		$multi_upload->do_filename_check = "n"; // check filename ...

		$multi_upload->tmp_names_array = $_FILES['upfile']['tmp_name'];
		$multi_upload->names_array = $_FILES['upfile']['name'];
		$multi_upload->error_array = $_FILES['upfile']['error'];
		$multi_upload->replace = "n"; 
		$multi_upload->upload_multi_files();
		buildImageList( );
	}
	if(isset($_GET['upload']) && !isset($_POST['submit'])) {
			$output .= "<form method=\"post\" action=\"user.php?action=manageimages&upload=upload\" ENCTYPE=\"multipart/form-data\">
					<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$max_size\">";
			for($x = 1; $x < 6; $x++) {
				$output .= "<div style='width: 300px; margin: 0 auto;'><label for='upfile$x'>"._IMAGE." $x:</label><input id=\"upfile$x\" type=\"file\" class=\"textbox\" name=\"upfile[]\"></div>";
			}
			$output .= "<div style='text-align: center;'><input name=\"submit\" type=\"submit\" class=\"button\" value=\"upload\"></div></form>";
	}
	else {
		if(isset($_GET['delete'])) {
			$folder = STORIESPATH."/".USERUID."/images";
			$validFile = false;
			$directory = opendir($folder);
			while($filename = readdir($directory)) {
				if($filename == $_GET['delete']) $validFile = true;
			}
			if($validFile) {
				$success = unlink($folder ."/". $_GET['delete']);
				if($success) $output .= write_message(_ACTIONSUCCESSFUL);
				else $output .= write_error(_ERROR);
			}
		}
		$folder1 = STORIESPATH."/".USERUID."";
		if(!file_exists($folder1)) {
			mkdir($folder1, 0755);
			chmod($folder1, 0777);
		}

		$folder = STORIESPATH."/".USERUID."/images";
		if (!file_exists($folder)) {
			mkdir($folder, 0755);
			chmod($folder, 0777);
		}

		$directory = opendir("$folder");
		$output .= "<div style=\"width: 90%; margin: 0 auto;\"><table cellpadding=\"3\" cellspacing=\"0\" width=\"100%\" class=\"tblborder\">
				<tr><th class='tblborder'>"._FILENAME."</th><th class='tblborder'>"._IMAGECODE."</th><th class='tblborder'>"._OPTIONS."</th></tr>";
		$count = 0;
		while($filename = readdir($directory)) {
			if($filename=="." || $filename==".." || $filename == "imagelist.js") continue;
			list($imght, $imgwth, $type, $attr) = getimagesize("$folder/$filename");
			$output .= "<tr><td class='tblborder'>$filename</td><td class='tblborder'>&#60;img src=\"$folder/$filename\"&#62;</td><td class='tblborder' style='text-align: center;'><a href=\"javascript:pop('$folder/$filename', $imgwth, $imght, 'yes')\">"._VIEW."</a> | <a href=\"user.php?action=manageimages&amp;delete=$filename\" onclick=\"return confirm('"._CONFIRMDELETE."')\">"._DELETE."</a></td></tr>";
			$count++;
		}
		if($count == 0) $output .= "<tr><td colspan='3' style='text-align: center;'>"._NORESULTS."</td></tr>";
		$output .= "</table></div>";
		$output .= write_message("<a href=\"user.php?action=manageimages&amp;upload=upload\">"._UPLOADIMAGE."</a>");

	}
}
else { 
	$output .= write_message(_NOTAUTHORIZED);
}
?>
