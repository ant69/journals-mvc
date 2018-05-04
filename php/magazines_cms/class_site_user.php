<?php
/*
*** Класс site_user создан для получения пользовательской информации
*** в случае успешной авторизации пользователя на сайте.
***
*** Для инициализации класса используются две переменные, содержащие информацию
*** о параметрах подключения к двум базам данных: текущей БД и глобальной БД пользователей.
***
*** При создании экземпляра этого класса происходит следующее:
*** 1. В конструкторе класса инициализируются переменные, содержащие сведения
***    о таблицах и именах полей баз данных.
*** 2. Создаются экземпляры классов баз данных для доступа к обоим БД
*** 3. Создается экземпляр класса авторизации
*** 4. В случае, если текущий пользователь авторизован, загружаются его данные
***    из обоих БД.
***
*** Издательская фирма Сентябрь
*** 2011
*/

class site_user_class
{
	/*
	**** @функция: site_user_class Конструктор класса
	В файле config.php должны быть заданы глобальные переменные,
	определяющие пути к классам $GLOBALS['lib_database'] и $GLOBALS['lib_authorization']
	*/
	function site_user_class($DB)
	{
		// Имя 'расширительной' таблицы в БД сайта
		// с информацией о пользователях текущего сайта
		$this->u_tbl="users";

		$this->DB = $DB;

		// Создание в переменных класса двух подклассов для организации взаимодействия
		// с БД текущего сайта и глобальной БД пользователей
		//$this->sDB = new database_class($SiteDBSettings);
		//$this->uDB = new database_class($UserDBSettings);

		// Создание авторизационного класса
		$this->Auth = new authorization_class($DB);
		// Загрузка пользовательских данных в переменные класса в случае,
		// если текущий посетитель сайта авторизован
	    $this->load_user_data();
	   /**/
	}

	function load_user_data() {
		$this->Data=$this->Auth->get_user_data();
		//print_r($this->Data);
	    if (!$this->Data) {
			// Пользователь НЕ авторизован, извлекать из БД нечего
	    	$this->UserOK=false;
	    } else {
			$this->UserOK=true;

		   	// Уникальный идентификатор текущего пользователя в глобальной БД пользователей
		   	$UserId=$this->Data['Id'];
		   	$this->UserId=$UserId;

			$this->isAdmin = $this->Data['GlobalAdmin'];
			$this->IsEditor = $this->is_member_of_group(1);
			$this->SessionId = $_SESSION['SessionID'];
			$this->IP = $_SERVER['REMOTE_ADDR'];		}

	}

	function cross_auth() {
		 if (@$_POST['action']=='login') {
		    $Sql = 'SELECT HidedAuthorizationUrl FROM sites WHERE HidedAuthorizationUrl>""';
			if ($Data = $this->uDB->run($Sql)->fetch()) {
			    $JScript = "";
			    foreach ($Data as $CrossSite) {
				    $JScript .= '<script language="javascript" type="text/javascript" src="'.$CrossSite['HidedAuthorizationUrl'].'?login='.$_POST['userName'].'&pass='.$_POST['userPass'].'"></script>';
			    }
			}
			echo $JScript;
		 }
	}


	function is_member_of_group($GroupId) {
	    $Sql = 'SELECT Id FROM group_members WHERE UserId=? AND GroupId=?)';
		return $this->DB->run($Sql, array($this->UserId, $GroupId))->fetchColumn();
	}

}
?>
