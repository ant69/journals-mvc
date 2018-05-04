<?php

################################################################################

class ClientValidation_class
{
	function ClientValidation_class($DM, $ClientData=array()) {
		$this->uDB = @$DM->uDB;
		$this->ClientData = @$ClientData;
		$this->AuthUserTable = 'auth_users';
		//print_r($DM->UserDBSettings);
	}

	############################################################################

	function Validate($UserId=false) {

		$this->HasErrors = 0;
		// Капча
		if (isset($this->ClientData['Captcha'])) {
			if ($this->ClientData['Captcha']!=$_SESSION['CAPTCHAString']) {
				//$this->HasErrors = 0;
				$this->HasErrors = 1;
				$this->errCaptcha = 'Пожалуйста, повторите ввод защитного кода!';
			}
		}

		// Фамилия
		if (isset($this->ClientData['F'])) {
			if ($Check = $this->CheckTextField($this->ClientData['F'], 1, 100)) {$this->HasErrors = 1;}
			switch ($Check) {
				case 1:  {	$this->errF = 'Вы не заполнили поле «Фамилия»';	break;	}
				case 2:  {	$this->errF = 'Длина поля «Фамилия» не может превышать 100 символов';	break;	}
			}
		}
		// Имя
		if (isset($this->ClientData['I'])) {
			if ($Check = $this->CheckTextField($this->ClientData['I'], 1, 100)) {$this->HasErrors = 1;}
			switch ($Check) {
				case 1:  {	$this->errI = 'Вы не заполнили поле «Имя»';	break;	}
				case 2:  {	$this->errI = 'Длина поля «Имя» не может превышать 100 символов';	break;	}
			}
		}
		// Отчество
		if (isset($this->ClientData['O'])) {
			if ($Check = $this->CheckTextField($this->ClientData['O'], 1, 100)) {$this->HasErrors = 1;}
			switch ($Check)
			{
				case 1:  {	$this->errO = 'Вы не заполнили поле «Отчество»';	break;	}
				case 2:  {	$this->errO = 'Длина поля «Отчество» не может превышать 100 символов';	break;	}
			}
		}
		// Логин
		if (isset($this->ClientData['Login'])) {
			if ($Check = $this->CheckTextField($this->ClientData['Login'], 3, 20)) {$this->HasErrors = 1;}
			switch ($Check) {
				case 0: {
					if ($this->CheckLoginInDB($this->ClientData['Login'], $UserId)) {
						$this->HasErrors = 1;
						$this->errLogin = 'Выбранный Вами логин уже имеется в базе данных. ';
					}
	                break;
				}
				case 1:  {	$this->errLogin = 'Минимальная длина поля «Логин» — 3 символа';	break;	}
				case 2:  {	$this->errLogin = 'Длина поля «Логин» не может превышать 20 символов';	break;	}
			}
		}
		// Email
		if (isset($this->ClientData['Email'])) {
			if ($Check = $this->CheckTextField($this->ClientData['Email'], 1)) {$this->HasErrors = 1;}
			switch ($Check) {
				case 0: {
					if (!$this->ValidateEmail($this->ClientData['Email'])) {
						$this->HasErrors = 1;
						$this->errEmail = 'Адрес электронной почты указан с ошибками';
					} else if ($this->CheckEmailInDB($this->ClientData['Email'], $UserId)) {
						$this->HasErrors = 1;
						$this->errEmail = 'Указанный Вами адрес электронной почты уже имеется в базе данных. Скорее всего, вы ранее регистрировались на одном из наших сайтов.<br>
						Поскольку на сайте журнала невозможно зарегистрироваться повторно с указанием ранее использованного адреса электронной почты, Вы можете выбрать, что делать дальше, из двух вариантов.<br>
						1: (рекомендуемый способ) авторизуйтесь на сайте журнала с использованием уже имеющейся у вас регистрации. Если Вы забыли, какие у Вас логин и пароль, воспользуйтесь <a href="profile/restore">функцией восстановления авторизационных данных по адресу электронной почты</a>. <br>
						2: Введите в этой регистрационной форме другой свой адрес электронной почты (если, конечно, он у Вас есть).';

					}
	                break;
				}
				case 1:  {	$this->errEmail = 'Вы не заполнили поле «Email»';	break;	}
				case 2:  {	$this->errEmail = 'Длина поля «Email» не может превышать 255 символов';	break;	}
			}
		}
		// Пароль
		//////////////////////////////////////////
		if (!$UserId) {
			if (isset($this->ClientData['Pass']) && isset($this->ClientData['Pass2'])) {
				if ($this->ClientData['Pass'] != $this->ClientData['Pass2']) {
					$this->HasErrors = 1;
					$this->errPass = 'Введенные вами пароли не совпадают.';
				} else {
					if ($Check = $this->CheckTextField($this->ClientData['Pass'], 3, 200)) {$this->HasErrors = 1;}
					switch ($Check) {
						case 1:  {	$this->errPass = 'Минимальная длина пароля составляет 3 символа';	break;	}
						case 2:  {	$this->errPass = 'Длина пароля не может превышать 20 символов';	break;	}
					}
				}
			}
		}
		//////////////////////////////////////////
		// Регион
		if (isset($this->ClientData['RegionId'])) {
			if ($this->ClientData['RegionId'] == 0 || $this->ClientData['RegionId'] == '') {
				$this->HasErrors = 1;
				$this->errRegion = 'Вы не указали свой регион';
			}
		}
		///////////////////////////////////////////
		// Место работы
		if (isset($this->ClientData['WorkPlace'])) {
			if ($Check = $this->CheckTextField($this->ClientData['WorkPlace'], 1, 400)) {$this->HasErrors = 1;}
			switch ($Check) {
				case 1:  {	$this->errWorkPlace = 'Вы не заполнили поле «Место работы»';	break;	}
				case 2:  {	$this->errWorkPlace = 'Длина поля «Место работы» не может превышать 400 символов';	break;	}
			}
		}
		// Должность
		if (isset($this->ClientData['WorkPosition'])) {
			if ($Check = $this->CheckTextField($this->ClientData['WorkPosition'], 1, 250)) {$this->HasErrors = 1;}
			switch ($Check) {
				case 1:  {	$this->errWorkPosition = 'Вы не заполнили поле «Должность»';	break;	}
				case 2:  {	$this->errWorkPosition = 'Длина поля «Должность» не может превышать 250 символов';	break;	}
			}
		}

		return !$this->HasErrors;
	}

	############################################################################
	// Функция проверки текстового поля. Возвращает одно из трех значений
	// 0 - поле валидно
	// 1 - длина введенного текста меньше допустимого минимума
	// 2 - длина введенного текста больше допустимого максимума
	function CheckTextField($Text, $MinLength=1, $MaxLength=255) {
		$CurLength = strlen($Text);
		$Ret = 0;
		if ($CurLength < $MinLength){ $Ret = 1; }
		if ($CurLength > $MaxLength){ $Ret = 2; }
		return $Ret;
	}

	############################################################################

	function ValidateEmail($Email) {
		$RegExp="/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i";
		if ( !preg_match($RegExp, $Email) ) {
		     return false;
		}
		return true;
	}


	############################################################################

	function CheckEmailInDB($Email, $UserId) {
		$SqlCheck='SELECT Email FROM '.$this->AuthUserTable.' WHERE Email=?';
		$Params = array($Email);
		if ($UserId) {$SqlCheck .= ' AND Id <> ?'; $Params[] = $UserId;}
		return $this->uDB->run($SqlCheck, $Params)->fetchColumn();
	}

	############################################################################

	function CheckLoginInDB($Login, $UserId) {
		$SqlCheck= 'SELECT Login FROM '.$this->AuthUserTable.' WHERE Login=?';
		$Params = array($Login);
		if ($UserId) {$SqlCheck .= ' AND Id <> ?'; $Params[] = $UserId;}
        return $this->uDB->run($SqlCheck, $Params)->fetchColumn();
	}


} # end of class

################################################################################


?>