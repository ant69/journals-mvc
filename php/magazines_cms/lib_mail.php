<?php
/******************************************************************************/
/* БИБЛИОТЕКА РАБОТЫ С ЭЛЕКТРОННОЙ ПОЧТОЙ
/******************************************************************************/
//require_once('config.php');
// отсылка писем со всеми реквизитами и в правильной кодировке
// оригинал кода - http://webew.ru/articles/297.webew
function send_mime_mail($name_from, // имя отправителя
                        $email_from, // email отправителя
                        $name_to, // имя получателя
                        $email_to, // email получателя
                        $data_charset, // кодировка переданных данных
                        $send_charset, // кодировка письма
                        $subject, // тема письма
                        $body, // текст письма
 						$html = FALSE // письмо в виде html или обычного текста
                        ) {
  $to = mime_header_encode($name_to, $data_charset, $send_charset)
                 . ' <' . $email_to . '>';
  $subject = mime_header_encode($subject, $data_charset, $send_charset);
  $from =  mime_header_encode($name_from, $data_charset, $send_charset)
                     .' <' . $email_from . '>';
//  if($data_charset != $send_charset) { $body = iconv($data_charset, $send_charset, $body); }
  $body = replace_custom_symbols($body);

  $body = convert_cyr_string($body,"w","k");

  $type = ($html) ? 'html' : 'plain';

  $headers = "From: $from\r\n";
  $headers .= "Content-type: text/$type; charset=$send_charset\r\n";

  return mail($to, $subject, $body, $headers);
}
// Masterhost code example: http://masterhost.ru/support/doc/windows/smtp-auth/
function send_smtp_mail($name_to, // имя получателя
                        $email_to, // email получателя
                        $subject, // тема письма
                        $message, // текст письма
 						$name_from, // имя отправителя
                        $email_from = false, // email отправителя
                        $pass_from = false,
                        $html = false // письмо в виде html или обычного текста
                       ){

	global $mhSmtpMail_Server, $mhSmtpMail_Port, $mhSmtpMail_DefaultUsername, $mhSmtpMail_DefaultPassword;

	$mhSmtpMail_Username = $email_from ? $email_from : $mhSmtpMail_DefaultUsername;
    $mhSmtpMail_Password = $pass_from ? $pass_from : $mhSmtpMail_DefaultPassword;

	$to = $name_to . ' <' . $email_to . '>';
	$from =  $name_from . ' <' . $mhSmtpMail_Username . '>';
	$message = replace_custom_symbols($message);

	$type = ($html) ? 'html' : 'plain';

	// Заголовки сообщения, в них определяется кодировка сообщения, поля From, To и т.д.
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/$type; charset=windows-1251\r\n";
	$headers .= "To: $name_to <$email_to>\r\n";
	$headers .= "From: $from";

	$mhSmtpMail_localhost  = "localhost";
	$mhSmtpMail_newline    = "\r\n";
	$mhSmtpMail_timeout    = "30";

	$smtpConnect = fsockopen($mhSmtpMail_Server, $mhSmtpMail_Port, $errno, $errstr, $mhSmtpMail_timeout);
	$smtpResponse = fgets($smtpConnect, 515);

	if(empty($smtpConnect))
	  {
	    $output = "Failed to connect: $smtpResponse";
	    return $output;
	  }
	else
	  {
	    $logArray['connection'] = "Connected: $smtpResponse";
	  }

	fputs($smtpConnect, "HELO $mhSmtpMail_localhost" . $mhSmtpMail_newline);
	$smtpResponse = fgets($smtpConnect, 515);
	$logArray['heloresponse'] = "$smtpResponse";

	fputs($smtpConnect,"AUTH LOGIN" . $mhSmtpMail_newline);
	$smtpResponse = fgets($smtpConnect, 515);
	$logArray['authrequest'] = "$smtpResponse";

	fputs($smtpConnect, base64_encode($mhSmtpMail_Username) . $mhSmtpMail_newline);
	$smtpResponse = fgets($smtpConnect, 515);
	$logArray['authmhSmtpMail_username'] = "$smtpResponse";

	fputs($smtpConnect, base64_encode($mhSmtpMail_Password) . $mhSmtpMail_newline);
	$smtpResponse = fgets($smtpConnect, 515);
	$logArray['authmhSmtpMail_password'] = "$smtpResponse";

	fputs($smtpConnect, "MAIL FROM: $mhSmtpMail_Username" . $mhSmtpMail_newline);
	$smtpResponse = fgets($smtpConnect, 515);
	$logArray['mailmhSmtpMail_fromresponse'] = "$smtpResponse";

	fputs($smtpConnect, "RCPT TO: $email_to" . $mhSmtpMail_newline);
	$smtpResponse = fgets($smtpConnect, 515);
	$logArray['mailtoresponse'] = "$smtpResponse";

	fputs($smtpConnect, "DATA" . $mhSmtpMail_newline);
	$smtpResponse = fgets($smtpConnect, 515);
	$logArray['data1response'] = "$smtpResponse";

	fputs($smtpConnect, "Subject: $subject\r\n$headers\r\n\r\n$message\r\n.\r\n");

	$smtpResponse = fgets($smtpConnect, 515);
	$logArray['data2response'] = "$smtpResponse";

	fputs($smtpConnect,"QUIT" . $mhSmtpMail_newline);
	$smtpResponse = fgets($smtpConnect, 515);
	$logArray['quitresponse'] = "$smtpResponse";


//  return mail($to, $subject, $body, $headers);
}

/******************************************************************************/

function mime_header_encode($str, $data_charset, $send_charset) {
  $str = replace_custom_symbols($str);
  if($data_charset != $send_charset) {
    $str = iconv($data_charset, $send_charset, $str);
  }
  return '=?' . $send_charset . '?B?' . base64_encode($str) . '?=';
}

/******************************************************************************/

function replace_custom_symbols($user_txt) {
  $txt = str_replace('«', '"', $user_txt);
  $txt = str_replace('»', '"', $txt);
  $txt = str_replace('№', 'N', $txt);
  $txt = str_replace('—', '-', $txt);
  return $txt;
}

/******************************************************************************/

/******************************************************************************/


?>