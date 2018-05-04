<?php
	
	if ($_SERVER['SERVER_NAME'] == 'doy'  OR $_SERVER['SERVER_NAME'] == 'praktika')
	{
		//Каталог изображений для новостей 
		$GLOBALS['news_pictures_url'] = "http://pics/news/";
	}else{
	# ХОСТИНГ
		//Каталог изображений для новостей 
		$GLOBALS['news_pictures_url'] = "http://files.direktor.ru/news/";
	}
	
	$GLOBALS['mhSmtpMail_Server']     		= "ssl://smtp.yandex.ru";	// Укажите адрес SMTP-сервера
	$GLOBALS['mhSmtpMail_Port']       		= "465";					// Порт SMTP-сервера, как правило 25
	$GLOBALS['mhSmtpMail_DefaultUsername'] = "support@direktor.ru";	// Имя почтового ящика (пользователя)
	$GLOBALS['mhSmtpMail_DefaultPassword'] = "tech6podderzhka";		// и пароль к нему.
?>