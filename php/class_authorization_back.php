<?php
/*
***  Класс authorization создан на основе класса auth, разработанного Giorgos Tsiledakis
*** Этот класс предназначен для авторизации пользователей с использованием
*** базы данных MySQL и специальных функций, обеспечивающих обработку сессий.
*** Издательская фирма Сентябрь
*** 2011-2018
 *  Последняя редакция - 01.09.2018 г.
*/

class authorization_class {

	var $ErrorMsg = "";
	var $SuccessMsg = "";
	var $ShowPage = false;
    var $EnblRemember = true;
	/*
	**** @функция: authentication_class; Конструктор класса
	**** @include: класс для организации доступа к MySQL: database_class.php
	*/
	function authorization_class($uDB){

		$this->tbl = "auth_users"; 				// Название таблицы MySQL-базы, в которой хранятся данные о пользователях
		$this->tblID = "Id"; 					// Имя поля ID в таблице MySQL
		$this->tblUserName = "Login"; 			// Имя поля Username в таблице MySQL
		$this->tblUserEmail = "Email"; 			// Имя поля Useremail в таблице MySQL
		$this->tblUserPassHash = "PassHash";	// Имя поля Userpassword в таблице MySQL
		$this->tblSessionID = "SessionId"; 		// Имя поля для хранения ID сессии в таблице MySQL
		$this->tblSessionHash = "SessionHash";	// Имя поля для хранения текущего хэша сессии в таблице MySQL
		$this->tblLastLog = "LastLog"; 			// Имя поля Time в таблице MySQL

		$this->СookieLogin="AuthId";     		// Имя cookie для хранения логина
		$this->СookieHash="AuthHash"; 			// Имя cookie для хранения хешированного пароля

		$this->CookieExpDays = "30"; 			// "Срок хранения" (в днях) cookies

		$this->Remote = md5($_SERVER['REMOTE_ADDR']);
		$this->UserAgent = md5($_SERVER['HTTP_USER_AGENT']);

		$this->errorNoCookies="Через куки авторизоваться не удалось!";
		$this->errorNoLogin="Логин не указан или введен с ошибками";
//		$this->errorInvalid="НЕПРАВИЛЬНОЕ ИМЯ ПОЛЬЗОВАТЕЛЯ ИЛИ ПАРОЛЬ!";
//		$this->errorDelay="ВАШ АККАУНТ СЛИШКОМ ДОЛГО БЫЛ НЕАКТИВНЫМ <br> ИЛИ ВЫ ИСПОЛЬЗОВАЛИ СВОЙ ЛОГИН БОЛЕЕ ОДНОГО РАЗА!<br> ЭТА СЕССИЯ БОЛЬШЕ НЕ АКТИВНА!";

		$this->DB = $uDB;

		// Независимо от прочих условий устанавливаем эти две переменных,
		// обеспечивающие передачу SID
		@ini_alter ('session.use_cookies','1');
		@ini_alter ('session.use_trans_sid','1');

		$this->UserId = $this->check_session();
		//if ($this->ErrorMsg) {session_start();}
	}


	/*
	**** @функция: checkSession (вызывается конструктором класса)
	**** Эта функция вызывает функцию hasCookie(), чтобы проверить,
	**** установлена ли в true глобальная переменная $globalConfig['acceptNoCookies'];
	**** Если куки не были установлены и мы их не принимаем, то генерируется ошибка.
	**** В противном случае проверяется, активна ли сессия. Если нет, то вызывается
	**** функция checkPost() (которая проверяет, был ли странице отправлен запрос POST);
	**** Если сессия существует, то осуществляется проверка $_POST['action']==logout
	**** Если в запросе POST присутствует команда logout, вызывается функция makeLogout();
	**** Если же logout не запрашивается, вызывается функция checkTime();
	*/
	function check_session() {
        // Если отправлена форма авторизации,
		// проверяем корректность отправленных данных
		// и авторизуемся при успехе
		if (@$_POST['action'] == 'login') {
            return $this->check_login();
        }

        // Если идентификатор сессии передан через GET,
		// принудительно устанавливаем этот идентификатор сессии
        if (isset($_REQUEST[session_name()])) {
            session_id($_REQUEST[session_name()]);
        }
        // Если идентификатор сессии сохранен в куках,
		// пытаемся авторизоваться с помощью данных, хранящихся в куках
        elseif (isset($_COOKIE['PHPSESSID'])) {
            session_id($_COOKIE['PHPSESSID']);
        	//return $this->check_remember();
            if ($this->EnblRemember && isset($_COOKIE[$this->СookieLogin]) && isset($_COOKIE[$this->СookieHash])) {
                //echo "Куки установлены, отрабатываем авторизацию";
                @ini_alter ('session.use_trans_sid','0');
                $UserId = $this->validate_cookie();
                if ($UserId>0) {return $UserId;}
            } else {
                //echo 'Куки не установлены';
                $this->ErrorMsg = $this->errorNoCookies;
            }
        }

		session_start();
		//echo "сессия стартовала!<br>";
		if (@$_SESSION['UserID'] && @$_SESSION['SessionID'] && @$_SESSION['SessionHash']) {
			// Параметры сессии userID и sessionID установлены, т.е. пользователь авторизован
			return $this->validate_session();
		}
		else {
			$this->SuccessMsg = 'Cессия запустилась, текущий пользователь не авторизован';
			return false;
		}

	}

    /*
    **** @функция: checkLogin (called by checkSession())
    **** Проверяет, были ли посланы следующие переменные $_POST['userName'],  $_POST['userPass'] и $_POST['action']="login";
    **** Если нет -> устанавливается сообщение об ошибке;
    **** Если да -> проверяем авторизационные данные с данными из БД;
    **** if all ok -> showPage() else -> it creates an error page;
    */
    function check_login() {
        if ((@$_POST['action'] == 'login') && ($_POST['userName']>'') && ($_POST['userPass']>'')) {
            $UserLogin = @$_POST['userName'];
            $UserPass = @$_POST['userPass'];
            //return $this->validate_login($UserLogin, $UserPass);
            $Sql='SELECT '.$this->tblID.' as Id, '.$this->tblUserPassHash.' as PassHash FROM '.$this->tbl.' 
				WHERE ('.$this->tblUserName.'=? OR '.$this->tblUserEmail.'=?)';
            if ($Data = $this->DB->run($Sql, array($UserLogin, $UserLogin))->fetch()) {
                $UserId = $Data['Id'];
                //echo "Есть такой пользователь ($UserId)!";
                if (($UserId > 0) && password_verify($UserPass, $Data['PassHash'])) {
                    //Авторизация прошла успешно
                    $this->SuccessMsg = 'Пользователь авторизован через форму авторизации';
                    session_start();
                    $this->init_session($UserId);
                } else {
                    $UserId = 0;
                }

            } else {
                $this->ErrorMsg = $this->errorNoLogin;
                $UserId = 0;
            }
            return $UserId;
        }
        $this->ErrorMsg = $this->errorNoLogin;
        //$this->make_error_html();
        return false;
    }


	/*
	**** @функция: validate_session (вызывается функцией check_login())
	**** Эта функция проверяет, есть ли в БД данные о пользователе с переданным логином.
	**** Если нет, то генерируется сообщение об ошибке
	**** Если да, проверяется, корректно ли указан пароль. Если пароль неверный, генерируется ошибка.
	*/
	function validate_session() {
		$Sql='SELECT '.$this->tblID.' as Id FROM '.$this->tbl.' 
				WHERE '.$this->tblID.'=? AND '.$this->tblSessionID.'=? AND '.$this->tblSessionHash.'=?';
		if ($Data = $this->DB->run($Sql, array($_SESSION['UserID'], $_SESSION['SessionID'], $_SESSION['SessionHash']))->fetch()) {
			//echo "Есть такой пользователь ($UserId)!";
			if ($UserId = $Data['Id']) {
				//Авторизация прошла успешно
                $this->SuccessMsg = 'Пользователь авторизован через сессию';

				$this->init_session($UserId);
			}
		} else {
			$UserId = 0;
            $this->ErrorMsg = 'Сессия "не прошла"';
		}
		return $UserId;
	}

	/*
	**** @функция: validate_session (вызывается функцией check_login())
	**** Эта функция проверяет, есть ли в БД данные о пользователе с переданным логином.
	**** Если нет, то генерируется сообщение об ошибке
	**** Если да, проверяется, корректно ли указан пароль. Если пароль неверный, генерируется ошибка.
	*/
	function validate_cookie() {
		$Sql='SELECT '.$this->tblID.' as Id FROM '.$this->tbl.'
				WHERE '.$this->tblID.'=? AND '.$this->tblSessionHash.'=?';
		if ($Data = $this->DB->run($Sql, array($_COOKIE[$this->СookieLogin], $_COOKIE[$this->СookieHash]))->fetch()) {
			//echo "Есть такой пользователь ($UserId)!";
			if ($UserId = $Data['Id']) {
				//Авторизация прошла успешно
                $this->SuccessMsg = 'Пользователь авторизован через куки';
                session_start();
				$this->init_session($UserId);
			}
		} else {
			$UserId = 0;
            $this->ErrorMsg = $this->errorNoCookies;
		}
		return $UserId;
	}

    /*
    **** @функция: validate_login (вызывается функцией check_login())
    **** Эта функция проверяет, есть ли в БД данные о пользователе с переданным логином.
    **** Если нет, то генерируется сообщение об ошибке
    **** Если да, проверяется, корректно ли указан пароль. Если пароль неверный, генерируется ошибка.
    */
	function init_session($UserId) {
        $SessionHash = md5($this->Remote . time() . $this->UserAgent);
        $SqlUpdate = 'UPDATE ' . $this->tbl . ' SET '.$this->tblLastLog . '=now(), ' . $this->tblSessionID . '="' . session_id().'", '.$this->tblSessionHash.'="'.$SessionHash.'" WHERE ' . $this->tblID . '=?';
        $this->DB->run($SqlUpdate, array($UserId));
        $_SESSION['SessionID'] = session_id();
        $_SESSION['UserID'] = $UserId;
        $_SESSION['SessionHash'] = $SessionHash;
        if ($this->EnblRemember) {
            //	echo 'Устанавливаем куки (CookieExpDays = '.$this->CookieExpDays.') <br>';
            setcookie($this->СookieLogin, $UserId,
                time()+(60*60*24*$this->CookieExpDays), '/');
            setcookie($this->СookieHash, $SessionHash,
                time()+(60*60*24*$this->CookieExpDays),'/');
            setcookie(session_name(), session_id(),
                time()+(60*60*24*$this->CookieExpDays), '/');
        }

        $this->ShowPage = true;
    }


    /*
    **** @function: makeLogout(called by checkSession())
    **** sets MySQL Time Field=0 and SessionID Field='';
    **** closes the session and goes to logout page, if some $_POST['action']="logout" was sent;
    */
    function make_logout() {
        $this->DB->run('UPDATE '.$this->tbl.' SET '.$this->tblLastLog.' = 0, '.$this->tblSessionID.'="", '.$this->tblSessionHash.'="" WHERE '.$this->tblID.'=?',
            array($_SESSION['UserID']));
        $this->UserId = 0;
        if ($this->EnblRemember && isset($_COOKIE[$this->СookieLogin]) && isset($_COOKIE[$this->СookieHash])) {
            setcookie($this->СookieLogin,"",time()-3600, '/');
            setcookie($this->СookieHash,"",time()-3600, '/');
            setcookie(session_name(),"",time()-3600, '/');
        }
        session_start();
        session_destroy();
        $this->ShowPage = false;
        //header ("Location: ".$_SERVER['HTTP_REFERER']);
    }


	/*
	**** @function: getUser
	**** Эта функция возвращает массив со значением полей записи авторизованного пользователя;
	*/
	function get_user_data() {
		if ($this->ShowPage) {
			return $this->DB->run('SELECT * FROM '.$this->tbl.' WHERE '.$this->tblID.'=?', array($this->UserId))->fetch();
		} else {
			return false;
		}
	}


} #end of class
?>
