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

		// Таблица для хранения данных пользователя
	    $this->table_users = "auth_users"; 				// Название таблицы MySQL-базы, в которой хранятся данные о пользователях
		$this->fldID = "Id"; 					        // Имя поля ID в таблице        
        $this->fldUserName = "Login"; 			        // Имя поля для хранения логина
        $this->fldUserEmail = "Email"; 			        // Имя поля для хранения адреса электронной почты
        $this->fldPasswordHash = "PassHash";           // Имя поля для хранения хэша пароля (функция password_hash)

        $this->table_sessions = "auth_users_sessions";	// Название таблицы MySQL-базы, в которой хранятся данные о сессиях
        $this->fldSId = "Id";	                        // Имя поля для хранения id записи в таблице сессий
        $this->fldUserId = "UserId";	                // Имя поля для хранения id пользователя из таблицы пользователей
        $this->fldSessionID = "SessionId"; 		        // Имя поля для хранения ID сессии
        $this->fldSessionHash = "SessionHash";	        // Имя поля для хранения текущего хэша сессии
        $this->fldCreateTime = "CreateSessionTime";	    // Имя поля хранения даты успешной авторизации через форму авторизации
        $this->fldCreateIp = "CreateSessionIp";	        // Имя поля хранения ip-адреса, с которого прошла успешная авторизация
        $this->fldRemoteHost = "RemoteHost";	        // Имя поля хранения имени домена, на котором произошла авторизация
        $this->fldHttpReferrer = "HttpReferrer";	    // Имя поля хранения url страницы, на которой произошла авторизация
        $this->fldAuthMethod = "AuthMethod";	        // Имя поля для хранения метода авторизации (через форму авторизации - 1, через кросс-авторизацию - 2)
        $this->fldUserAgent = "UserAgent";  	        // Имя поля хранения информации о UserAgent устройства, на котором произошла авторизация
        $this->fldLastLogTime = "LastSessionTime";	    // Имя поля хранения времени актуализации сессии (время последнего визита)
        $this->fldLastIp = "LastViewIp";	            // Имя поля хранения ip-адреса во время последнего визита
        $this->fldCounter = "ViewsCounter";             // Имя поля хранения информации о количестве переходов в рамках сессии


        $this->СookieLogin="AuthId";     		        // Имя cookie для хранения логина
		$this->СookieHash="AuthHash"; 			        // Имя cookie для хранения хешированного пароля

		$this->CookieExpDays = "30"; 			        // "Срок хранения" (в днях) cookies

		$this->Remote = md5($_SERVER['REMOTE_ADDR']);
		$this->UserAgent = md5($_SERVER['HTTP_USER_AGENT']);

		$this->errorNoCookies = "Через куки авторизоваться не удалось!";
		$this->errorNoLogin = "Логин не указан или введен с ошибками";
        $this->errorPassword = 'Пользователь ввел неправильный пароль.';
        $this->errorSessionIncorrect = 'Cессия запустилась, но в ней отсутствуют сессионные переменные';
        $this->errorSessionFails = 'Cессия запустилась, однако текущий пользователь не авторизован';

        $this->successLogin = 'Пользователь авторизован через форму авторизации';
        $this->successCookies = 'Пользователь авторизован через куки';
        $this->successSession = 'Пользователь авторизован через сессию';

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

		if ($_SESSION['UserID'] && $_SESSION['SessionID'] && $_SESSION['SessionHash']) {
			// Параметры сессии userID и sessionID установлены, т.е. пользователь авторизован
            //echo 'Обрабатываем сессию';
			return $this->validate_session();
		}
		else {
		    //echo 'Сессия некорректна';
			$this->ErrorMsg = $this->errorSessionIncorrect;
			return false;
		}

	}

    /*
    **** @функция: checkLogin (called by checkSession())
    **** Проверяет, были ли посланы следующие переменные $_POST['userName'],  $_POST['userPass'] и $_POST['action']="login";
    **** Если нет -> устанавливается сообщение об ошибке;
    **** Если да -> проверяем авторизационные данные, сравнивая их с данными из БД;
    **** if all ok -> showPage() else -> it creates an error page;
    */
    function check_login() {
        if ((@$_POST['action'] == 'login') && ($_POST['userName']>'') && ($_POST['userPass']>'')) {
            $UserLogin = @$_POST['userName'];
            $UserPass = @$_POST['userPass'];
            $Sql='
                SELECT '.$this->fldID.' as Id, '.$this->fldPasswordHash.' as PassHash 
                FROM '.$this->table_users.' 
				WHERE '.$this->fldUserName.'=? 
				    OR '.$this->fldUserEmail.'=?';
            if ($Data = $this->DB->run($Sql, array($UserLogin, $UserLogin))->fetch()) {
                $UserId = $Data['Id'];
                //echo "Есть такой пользователь ($UserId)!";
                if (($UserId > 0) && password_verify($UserPass, $Data['PassHash'])) {
                    //Авторизация прошла успешно
                    $this->SuccessMsg = $this->successLogin;
                    session_start();
                    $this->init_session($UserId, 1);
                } else {
                    $this->ErrorMsg = $this->errorPassword;
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
    **** @функция: validate_session
    **** Проверяет, содержит ли сессия данные для авторизации пользователя;
    **** Если нет -> устанавливается сообщение об ошибке;
    **** Если да -> проверяем авторизационные данные, сравнивая их с данными из БД;
    **** if all ok -> showPage() else -> it creates an error page;
    */
	function validate_session() {
		$Sql='
		    SELECT u.'.$this->fldID.' as Id FROM '.$this->table_users.' u, '.$this->table_sessions.' s 
			WHERE u.'.$this->fldID.'=s.'.$this->fldUserId.' 
			    AND u.'.$this->fldID.'=? 
			    AND s.'.$this->fldSessionID.'=? 
			    AND s.'.$this->fldSessionHash.'=?';
		if ($Data = $this->DB->run($Sql, array($_SESSION['UserID'], $_SESSION['SessionID'], $_SESSION['SessionHash']))->fetch()) {
			//echo "Есть такой пользователь ($UserId)!";
			if ($UserId = $Data['Id']) {
				//Авторизация прошла успешно
                $this->SuccessMsg = $this->successSession;

				$this->init_session($UserId);
			}
		} else {
			$UserId = 0;
            $this->ErrorMsg = $this->errorSessionFails;
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
		$Sql='
		    SELECT u.'.$this->fldID.' as Id 
		    FROM '.$this->table_users.' u, '.$this->table_sessions.' s 
			WHERE u.'.$this->fldID.'=? 
			    AND s.'.$this->fldSessionHash.'=?
			    AND s.'.$this->fldSessionID.'=?
			    AND s.'.$this->fldUserId.'=u.'.$this->fldID.''
        ;
		if ($Data = $this->DB->run($Sql, array($_COOKIE[$this->СookieLogin], $_COOKIE[$this->СookieHash], session_id()))->fetch()) {
			//echo "Есть такой пользователь ($UserId)!";
			if ($UserId = $Data['Id']) {
				//Авторизация прошла успешно
                $this->SuccessMsg = $this->successCookies;
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
    **** @функция: init_session (вызывается функциями check_login(), check_session, check_cookies)
    **** Эта функция инициализирует сессию и сохраняет в БД сведения о текущей сессии
    **** Функции передаются два параметра:
    **** $UserId - идентификатор пользователя в таблице tbl_users
    **** $Method - метод авторизации. Если эта переменная не передана, по умолчанию считается, что Method=0
    **** 0 - Авторизация осуществляется через Cookie или Session
    **** 1 - Пользователь авторизовался через форму авторизации
    **** 2 - Пользователь авторизовался с помощью ссылки (кросс-авторизация) (Не реализовано)
    **** 3 - Пользователь авторизовался с помощью ссылки восстановления пароля (Не реализовано)
    **** Эта функция ничего не возвращает, но устанавливает свойство объекта класса ShowPage в true.
    */
	function init_session($UserId, $Method=0) {
        $SessionId = session_id();
        $CurrentIp = $_SERVER['REMOTE_ADDR'];
        $Now = date('Y-m-d H:i:s', time());
        $SessionHash = md5($CurrentIp . $Now);

        $CheckSqlSession = 'SELECT Id FROM '.$this->table_sessions.' WHERE '.$this->fldUserId.'=? AND '.$this->fldSessionID.'=?';
        if ($sId = $this->DB->run($CheckSqlSession, array($UserId, $SessionId))->fetchColumn()) {
            // Сессия была создана раньше, поэтому сейчас мы только обновляем данные о последнем заходе
            $SqlUpdate = '
                UPDATE ' . $this->table_sessions . ' 
                SET '.$this->fldLastLogTime . '=?,
                    ' . $this->fldSessionHash . '=?,
                    ' . $this->fldLastIp . '=?,
                    ' . $this->fldCounter . '=' . $this->fldCounter . ' + 1
                WHERE ' . $this->fldSId . '=?';
            $this->DB->run($SqlUpdate, array($Now, $SessionHash, $CurrentIp, $sId));
        } else {
            // В рамках текущей сессии пользователь авторизовался впервые
            $SessionHash = md5($this->Remote . time() . $this->UserAgent);
            $SqlInsert = '
                INSERT INTO ' . $this->table_sessions . ' 
                ('.$this->fldUserId.', '.$this->fldSessionID.', '.$this->fldSessionHash.', 
                 '.$this->fldCreateTime.', '.$this->fldCreateIp.', '.$this->fldRemoteHost.', 
                 '.$this->fldHttpReferrer.', '.$this->fldAuthMethod.',  '.$this->fldUserAgent.',  
                 '.$this->fldLastLogTime.',  '.$this->fldLastIp.')
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $this->DB->run($SqlInsert,
                array($UserId, $SessionId, $SessionHash, $Now, $CurrentIp, parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST),
                    $_SERVER['HTTP_REFERER'], $Method, $_SERVER['HTTP_USER_AGENT'], $Now, $CurrentIp));
        }

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
        $this->DB->run('UPDATE '.$this->table_sessions.' SET ' .$this->fldSessionHash.'="" 
            WHERE '.$this->fldUserId.'=? && '.$this->fldSessionID.'=?',
            array($_SESSION['UserID'], $_SESSION['SessionID']));
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
			return $this->DB->run('SELECT * FROM '.$this->table_users.' WHERE '.$this->fldID.'=?', array($this->UserId))->fetch();
		} else {
			return false;
		}
	}


} #end of class
?>
