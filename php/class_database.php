<?php
/*
* database_class - класс для работы с базами данных
*
* Класс определяет ряд методов, позволяющих осуществлять операции
* по манипуляциям с текущей базой данных, параметры которой
* передаются новому объекту класса при его инициализации.
*
* Класс является составной частью ядра системы и используется
* как другими библиотеками ядра, так и подключаемыми к системе
* модулями расширения.
*
* Создан 20.06.2011
*
* Текущая версия: 1.0. Последние изменения - 20.06.2011
*
* © Издательская фирма «Сентябрь», Александр Наровлянский, Павел Антошкин
*
* Перечень параметров:
* $db_name - имя базы данных
* ...
*
* Перечень методов:
* Connect()
* Disconnect()
* ExecSQL()
*/
class database_class
{	var	$lnk; # дескриптор БД
	var $msg1="Нет соединения с MySQL-сервером! Пожалуйста проверьте файл настроек
				или используйте перед обращением к базе вызов функции \"connect()\" ";
	var $msg2="Пожалуйста проверьте синтаксис SQL-запроса!";

	/***************************************************************************
	 Конструктор класса
	 Конструктору передаются два параметра. Первый - параметры БД.
	 Второй - режим отображения ошибок
	*/
	function database_class($DBSettings=false, $SilentMode=true)
	{
		$this->Active = false;
		$this->SilentMode = $SilentMode;
		if (is_array($DBSettings))
		{
			if ($this->connect($DBSettings['server'],
							$DBSettings['login'],
							$DBSettings['pass'],
							$DBSettings['db']))
			{
				$this->Active = true;
				$this->server	= $DBSettings['server'];
				$this->login 	= $DBSettings['login'];
				$this->pass 	= $DBSettings['pass'];
				$this->db 		= $DBSettings['db'];
			}
		}
		if (!$SilentMode and !$this->Active)
		{
			$this->printerror=true;
		}
//		echo "БД проинициализирована";
	}
	/******************************************************************************/

	/***************************************************************************
	Установка соединения с базой:
	connect("mysqlhost","mysqluser","mysqlpasswd","name of mysql database")
	Эта функция возвращает указатель соединения $lnk или генерирует сообщение об ошибке
	*/
	function connect($host=false, $user=false, $pass=false, $dbname=false)
	{
		$lnk=@mysql_connect($host, $user, $pass);

		mysql_query ("set character_set_client='cp1251'");
		mysql_query ("set character_set_results='cp1251'");
		mysql_query ("set collation_connection='cp1251_general_ci'");

		if (!$lnk)
		{
			$this->makeerror();
			return false;
		}
		$db=@mysql_select_db($dbname, $lnk);
		if (!$db)
		{
			$this->makeerror();
			return false;
			exit;
		}
		$this->lnk=$lnk;
		return $this->lnk;
	}
	/******************************************************************************/

	/***************************************************************************
	Восстановление соединения с базой.
	Если при создании экземпляра класса база данных не возникло проблем,
	данная функция обновляет соединение.
	Функция возвращает true, если соединение восстановлено.
	*/
	function reconnect ()
	{
		if ($this->Active)
		{
			if (!$this->connect($this->server, $this->login, $this->pass, $this->db))
			{				$this->Active = false;			}
		}
		return $this->Active;
	}
	/******************************************************************************/

	/***************************************************************************
	Закрытие соединения с базой
	*/
	function disconnect() # разрывает соединение с БД
	{
        mysql_close($this->lnk);
	}
	/******************************************************************************/

	/***************************************************************************
	Функция для выполнения произвольного sql-запроса.
	Вначале осуществляется восстановление соединения с БД, затем выполняется запрос
	*/
	function exec_sql($sql) # возвращает ассоциированный массив с результатами запроса
	{
		$res = false;
		if ($this->reconnect())
		{			if (!$this->lnk)
			{
				$this->error_id=$this->msg1;
				$this->makeerror();
			}
			//Удаление результатов выполнения предыдущего sql-запроса
			if ($this->sql_id)
			{
				@mysql_free_result($this->sql_id);
			}
			$res = mysql_query($sql, $this->lnk);
			$this->sql_id = $res;
		}
		return $res;
	}
	/******************************************************************************/

	/***************************************************************************
	Функция возвращает количество строк в результатах запроса
	*/
	function get_count($Sql) # возвращает количество записей, удовлетворяющих запросу $sql
	{
		//echo $Sql."<br>";
		if ($this->reconnect())
		{			if ($MysqlRes = $this->exec_sql($Sql))
			{
				$Result = mysql_num_rows($MysqlRes);
				//echo "Результат выполнения запроса '$Sql' следующий: $Result <br>";
			}		}
		else { $Result = 0; }
		return $Result;
	}
	/******************************************************************************/

	/***************************************************************************
	Функция возвращает значение поля $Field из таблицы $Table для записи с $Id
	*/
	function get_value($Table, $Id, $Field) # возвращает значение записи
	{
		$sql = "SELECT $Field FROM $Table WHERE Id = $Id";
		if ($this->get_count($sql)>0)
		{			$ret = mysql_result(mysql_query($sql), 0, $Field);
		}
		else {$ret = '';}
		//echo $sql;
//		print_r(mysql_query("SELECT $Field FROM $Table WHERE Id = $Id;"));
//		echo $foo = mysql_result(mysql_query("SELECT $Field FROM $Table WHERE Id = $Id;"), 0, $Field);
//		echo $Field;
		return $ret;
	}
	/******************************************************************************/

	/***************************************************************************
	Функция возвращает двумерный массив.
	Первый индекс массива - порядковый номер строки.
	Второй индекс массива - имена полей, извлекаемых из базы при выполнении sql.
	*/
	function get_simple_sql_result($sql) # возвращает количество записей, удовлетворяющих запросу $sql
	{
   		$this->reconnect();
   		//echo '<br>'.$sql.'<br>';
	    $Res = mysql_query($sql, $this->lnk);
	    while ($Row = mysql_fetch_assoc ( $Res ))
	    {	    	$Foo[] = $Row;
	    }
		return $Foo;
	}
	/******************************************************************************/

	/***************************************************************************
	Функция позволяет установить новое значение $Value поля $Field в таблице $Table
	для записи, индекс которой равен $Id
	*/
	function update_record($Table, $Id, $Field, $Value)
	{	    //echo "UPDATE $Table SET $Table.$Field = \"$Value\" WHERE Id = $Id<br>";
	    $res = mysql_query("UPDATE $Table SET $Table.$Field = \"$Value\" WHERE Id = $Id", $this->lnk);
	}
	/******************************************************************************/

	/***************************************************************************
	Функция позволяет установить новое значение $Value текстового поля $Field в таблице $Table
	для записи, индекс которой равен $Id
	*/
	function update_text_record($Table, $Id, $Field, $Value)
	{
	    $sql = "UPDATE $Table SET $Table.$Field = '$Value' WHERE Id = $Id";
	    $res = mysql_query($sql);
	}
	/******************************************************************************/

	/***************************************************************************
	Функция добавляет новую пустую запись и возвращает её ID
	*/
	function create_new_record($Table)
	{
		$sql = "INSERT INTO $Table (Id) VALUES (NULL)";
	    $res = mysql_query($sql, $this->lnk);
		$sql = "SELECT * FROM $Table ORDER BY Id DESC";
	    $res = mysql_query($sql, $this->lnk);
	    return mysql_result($res, 0, 'Id');
	}
	/******************************************************************************/

	/***************************************************************************
	Функция удаляет запись с указанным Id из таблицы $Table
	*/
	function delete_record($Table, $Id)
	{
		$sql = "DELETE FROM $Table WHERE Id = $Id";
	    $res = mysql_query($sql, $this->lnk);
	}
	/******************************************************************************/

	/***************************************************************************
	 Функция makeerror() вызывается всякий раз в случае возникновения ошибок;
	 Если возникает ошибка с идентификатором error_id, эта функция возвращает определенное пользвателем сообщение $msg1 или $msg2,
	 В противном случае она возвращает номер ошибки mysql и сообщение;
	*/
	function makeerror()
	{
		$result = false;
		if (!$this->error_id)
		{
			if (mysql_errno())
			{
				$result=mysql_errno() . ": " . mysql_error();
				$this->errors=$result;
			}
		}
		else
		{
			$result=$this->error_id;
			$this->errors=$result;
		}
		return $result;
	}
}


?>