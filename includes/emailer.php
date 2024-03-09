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



function sendemail($to_name,$to_email,$from_name,$from_email,$subject,$message,$type="plain",$cc="",$bcc="") {
                 
	global $language, $smtp_host, $smtp_username, $smtp_password, $siteemail;     
   
	// Check for hackers and spammers and bad input
	if(!isset($_SERVER['HTTP_USER_AGENT'])) return false;
	$badStrings = array("Content-Type:", "MIME-Version:", "Content-Transfer-Encoding:", "bcc:", "cc:");
	$checks = array($to_name, $to_email, $from_name, $from_email, $subject);
	foreach($checks as $check) {
		foreach($badStrings as $bad){
			if(strpos($check, $bad) !== false) return false; // Spammer
		}
	}
	unset($bad, $badStrings, $checks, $check);
	if (!((bool) preg_match('/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD', $to_email))) return false; // Bad e-mail
	if (!((bool) preg_match('/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD', $from_email))) {
		echo "bad email";
		return false; // Bad e-mail
	}
	if (empty($_SERVER['HTTP_USER_AGENT']) || !$_SERVER['REQUEST_METHOD'] == "POST") return false; // Spammer
	$subject = descript(strip_tags($subject));
	$message = descript($message);
	// End paranoia
 
	// Try to determine the right $type setting
 	if(strpos($message, "<br>") || strpos($message, "</p>") || strpos($message, "<br />") || strpos($message, "<br>") || strpos($message, "<a href")) $type = "html";
	
	require_once(_BASEDIR."includes/PHPMailerAutoload.php");
	$mail = new PHPMailer;
	if(file_exists(_BASEDIR."languages/mailer/phpmailer.lang-".$language.".php"))
		$mail->SetLanguage($language, _BASEDIR."languages/mailer/");
	else 
		$mail->SetLanguage("en", _BASEDIR."languages/mailer/");

	if(!$smtp_host) {
		$mail->IsMail( );
	}
	else { 
		$mail->IsSMTP( );
		$mail->Host = $smtp_host;
		$mail->SMTPAuth = true;
		$mail->Username = $smtp_username;
		$mail->Password = $smtp_password;
	}
	$mail->CharSet = _CHARSET;
	$mail->From = $siteemail;
	$mail->FromName = $from_name;
	$mail->AddAddress($to_email, $to_name);
	$mail->AddReplyTo($from_email, $from_name);
	if($cc) {
		$cc = explode(", ", $cc);
		foreach ($cc as $ccaddress) {
			$mail->AddCC($ccaddress);
		}
	}
	if ($bcc) {
		$bcc = explode(", ", $bcc);
		foreach ($bcc as $bccaddress) {
			$mail->AddBCC($bccaddress);
		}
	}
	if ($type == "plain") {
		$mail->IsHTML(false);
	} else {
		$mail->IsHTML(true);
	}
	
	$mail->Subject = $subject;
	$mail->Body = $message;
	if(!$mail->Send()) {
		$mail->ErrorInfo;
		$mail->ClearAllRecipients();
		$mail->ClearReplyTos();
		return $mail->ErrorInfo;
	} else {
		$mail->ClearAllRecipients(); 
		$mail->ClearReplyTos();
		return true;
	}

}
?>