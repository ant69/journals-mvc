<?php
/*
* database_class - ����� ��� ������ � ������ ������
*
* ����� ���������� ��� �������, ����������� ������������ ��������
* �� ������������ � ������� ����� ������, ��������� �������
* ���������� ������ ������� ������ ��� ��� �������������.
*
* ����� �������� ��������� ������ ���� ������� � ������������
* ��� ������� ������������ ����, ��� � ������������� � �������
* �������� ����������.
*
* ������ 20.06.2011
*
* ������� ������: 1.0. ��������� ��������� - 20.06.2011
*
* � ������������ ����� ����������, ��������� ������������, ����� ��������
*
* �������� ����������:
* $db_name - ��� ���� ������
* ...
*
* �������� �������:
* Connect()
* Disconnect()
* ExecSQL()
*/
class database_class
{	var	$lnk; # ���������� ��
	var $msg1="��� ���������� � MySQL-��������! ���������� ��������� ���� ��������
				��� ����������� ����� ���������� � ���� ����� ������� \"connect()\" ";
	var $msg2="���������� ��������� ��������� SQL-�������!";

	/***************************************************************************
	 ����������� ������
	 ������������ ���������� ��� ���������. ������ - ��������� ��.
	 ������ - ����� ����������� ������
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
//		echo "�� �������������������";
	}
	/******************************************************************************/

	/***************************************************************************
	��������� ���������� � �����:
	connect("mysqlhost","mysqluser","mysqlpasswd","name of mysql database")
	��� ������� ���������� ��������� ���������� $lnk ��� ���������� ��������� �� ������
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
	�������������� ���������� � �����.
	���� ��� �������� ���������� ������ ���� ������ �� �������� �������,
	������ ������� ��������� ����������.
	������� ���������� true, ���� ���������� �������������.
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
	�������� ���������� � �����
	*/
	function disconnect() # ��������� ���������� � ��
	{
        mysql_close($this->lnk);
	}
	/******************************************************************************/

	/***************************************************************************
	������� ��� ���������� ������������� sql-�������.
	������� �������������� �������������� ���������� � ��, ����� ����������� ������
	*/
	function exec_sql($sql) # ���������� ��������������� ������ � ������������ �������
	{
		$res = false;
		if ($this->reconnect())
		{			if (!$this->lnk)
			{
				$this->error_id=$this->msg1;
				$this->makeerror();
			}
			//�������� ����������� ���������� ����������� sql-�������
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
	������� ���������� ���������� ����� � ����������� �������
	*/
	function get_count($Sql) # ���������� ���������� �������, ��������������� ������� $sql
	{
		//echo $Sql."<br>";
		if ($this->reconnect())
		{			if ($MysqlRes = $this->exec_sql($Sql))
			{
				$Result = mysql_num_rows($MysqlRes);
				//echo "��������� ���������� ������� '$Sql' ���������: $Result <br>";
			}		}
		else { $Result = 0; }
		return $Result;
	}
	/******************************************************************************/

	/***************************************************************************
	������� ���������� �������� ���� $Field �� ������� $Table ��� ������ � $Id
	*/
	function get_value($Table, $Id, $Field) # ���������� �������� ������
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
	������� ���������� ��������� ������.
	������ ������ ������� - ���������� ����� ������.
	������ ������ ������� - ����� �����, ����������� �� ���� ��� ���������� sql.
	*/
	function get_simple_sql_result($sql) # ���������� ���������� �������, ��������������� ������� $sql
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
	������� ��������� ���������� ����� �������� $Value ���� $Field � ������� $Table
	��� ������, ������ ������� ����� $Id
	*/
	function update_record($Table, $Id, $Field, $Value)
	{	    //echo "UPDATE $Table SET $Table.$Field = \"$Value\" WHERE Id = $Id<br>";
	    $res = mysql_query("UPDATE $Table SET $Table.$Field = \"$Value\" WHERE Id = $Id", $this->lnk);
	}
	/******************************************************************************/

	/***************************************************************************
	������� ��������� ���������� ����� �������� $Value ���������� ���� $Field � ������� $Table
	��� ������, ������ ������� ����� $Id
	*/
	function update_text_record($Table, $Id, $Field, $Value)
	{
	    $sql = "UPDATE $Table SET $Table.$Field = '$Value' WHERE Id = $Id";
	    $res = mysql_query($sql);
	}
	/******************************************************************************/

	/***************************************************************************
	������� ��������� ����� ������ ������ � ���������� � ID
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
	������� ������� ������ � ��������� Id �� ������� $Table
	*/
	function delete_record($Table, $Id)
	{
		$sql = "DELETE FROM $Table WHERE Id = $Id";
	    $res = mysql_query($sql, $this->lnk);
	}
	/******************************************************************************/

	/***************************************************************************
	 ������� makeerror() ���������� ������ ��� � ������ ������������� ������;
	 ���� ��������� ������ � ��������������� error_id, ��� ������� ���������� ������������ ������������ ��������� $msg1 ��� $msg2,
	 � ��������� ������ ��� ���������� ����� ������ mysql � ���������;
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