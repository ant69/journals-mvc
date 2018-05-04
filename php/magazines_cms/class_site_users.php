<?php
/*
***  Класс k_user создан для хранения пользовательской информации
***  в случае успешной авторизации пользователя на сайте.
*** Издательская фирма Сентябрь
*** 2011
*/

class k_user_class
{
	/*
	**** @функция: k_user_class Конструктор класса
	*/
	function k_user_class($SiteDBSettings, $UserDBSettings)
	{

		$GLOBALS['lib_database'];
		include_once "class_authorization.php";

		$this->k_tbl="konkurs_users";
		$this->g_tbl="konkurs_groups";
		$this->g_tblID="Id";
		$this->g_tblGroupName="Name";

		$this->gm_tbl="konkurs_group_members";
		$this->gm_tblGroupID="GroupId";
		$this->gm_tblUserID="UserId";

		$this->se_tbl="konkurs_site_elements";
		$this->se_tblID="Id";
		$this->se_tblName="Name";

		$this->rights_tbl="konkurs_rights";
		$this->rights_tblGroupID="GroupId";
		$this->rights_tblSiteElementID="SiteElementId";
		$this->rights_tblRight="Permissions";

		$this->kDBSettings = $kDBSettings;
		$this->uDBSettings = $uDBSettings;

		$this->kDB = new db_class($kDBSettings);
		$this->uDB = new db_class($uDBSettings);

	//	global $auth;
		$this->auth = new a_class($uDBSettings);
	//    $this->db = new db_class();
	    $this->loadUserData();
	   /**/
	}

	function loadUserData()
	{
		global $konkurs_year;
		$this->Year = $konkurs_year;
		$this->Data=$this->auth->getUserData();
	    if (!$this->Data)
	    {
	    	$this->UserOK=false;
	    }
	    else
		{
			$this->UserOK=true;
		   	$UserId=$this->Data['Id'];
		   	$this->UserId=$UserId;

		   	$kDB=$this->getNewDB();
		   	$SQ="UPDATE ".$this->k_tbl." SET LastLog = now() WHERE uId='$UserId'";
			$kDB->speak($SQ);

			$sql="SELECT * FROM ".$this->k_tbl." WHERE (uId='".$UserId."') and Year='".$konkurs_year."'";
			$kDB->speak($sql);
			$User = $kDB->listen();

	        if (is_array($User)) {
	        	$this->isMember=true;
	        	$this->MemberStatus=$User['MemberStatus'];
				$this->Data=array_merge($this->Data, $User);
			}
			$this->isAdmin = $this->Data['GlobalAdmin'];
			$this->IsBlogger = $this->Data['IsBlogger'];
			$this->BlogShow = $this->Data['BlogShow'];
			//$this->Login = $this->Data['Login'];
		}

	}

	function getNewDB($DBSettings=false)
	{
		if ($DBSettings==false) {$DBSettings=@$this->kDBSettings;}
		return new db_class($DBSettings);
	}


	function get_user_data_by_id($uId, $Fields=false)
	{
		global $konkurs_year;
		if ($Fields) {
			$SelectFields=''; $Div='';
			foreach ($Fields as $Field)
			{
				 $SelectFields = $SelectFields.$Div.$Field; $Div=', ';
			}
		}
		else $SelectFields='*';
		      $uDB=@$this->uDB;
		      //echo   "SELECT $SelectFields FROM ".$this->auth->tbl.' WHERE Id='.$uId;
		      $uDB->reconnect();
		      $uDB->speak("SELECT $SelectFields FROM ".$this->auth->tbl.' WHERE Id='.$uId);
		//			$cur_author = $auth->getUserData();
		$cur_person = $uDB->listen();

		$kDB = @$this->kDB;
		$kDB->reconnect();
		$kDB->speak("SELECT * FROM konkurs_users WHERE (uId='".$uId."') and (Year='".$konkurs_year."')");
		$User = $kDB->listen();
		//		$cur_person = $cur_person + $User[0];
		if (is_array($User)) {
			$cur_person = array_merge($cur_person, $User);
		}
		return $cur_person;
	}


	/*
	**** @function: checkSiteElement($SiteElementName) (called by checkAdmin())
	**** checks if the page is belongs only to some user group. If not -> showPage();
	**** if yes -> gets the user's group number from the MySQL User Group Field;
	**** if the group is the same-> showPage() else -> it creates an error page;
	*/
	function checkSiteElement($SiteElementName){
		$Year=@$this->Year;
		$db=@$this->getNewDB();
		$db->reconnect();
		$ret=0;
		$UserId=$this->UserId;


		if ($this->isAdmin=="1") {
		$ret=0x7;
		}
		else {
			//Вначале определяем Id элемента сайта, права доступа к которому надо проверить
			$se_SQL="SELECT ".$this->se_tblID." as seID FROM ".$this->se_tbl." WHERE ".$this->se_tblName."='".$SiteElementName."'";
			//echo $se_SQL;

			$db->speak($se_SQL);
			$se_data=$db->listen();
			if ($db->rows>0)
			{
				//Если по имени элемента найдена одна и только одна запись,
				//извлекаем Id этого элемента сайта для определения прав доступа к нему
			    $seID=$se_data['seID'];
				$rights_SQL="SELECT ".$this->rights_tblGroupID." as groupID, ".$this->rights_tblRight." as Rights FROM ".$this->rights_tbl." WHERE ".$this->rights_tblSiteElementID."='".$seID."'";
				$rights_sqlID=$db->speak($rights_SQL);
				//Если на данный элемент сайта определены права доступа хотя бы для одной группы,
				//формируем массив, в котором каждая запись массива имеет ключ, совпадающий с id группы,
				//и значение, соответствующее правам на элемент сайта участников этой группы
				while ($rows = $db->listen())
				{
					$Rights[$rows['groupID']]=$rows['Rights'];
				}
				//Если есть группы с заданными правами доступа к элементу сайта,
				//проверяем, входит ли текущий пользователь в эти группы
				if (count($Rights)>0)
				{
					foreach ($Rights as $GroupId => $Right)
					{
			        	$gm_SQL="SELECT * FROM ".$this->gm_tbl;
			        	$gm_SQL.=" WHERE ".$this->gm_tblGroupID."='".$GroupId."' and ".$this->gm_tblUserID."='".$UserId."'";
			        	$gm_SQL.=" and Year=$Year";
			        	//echo $gm_SQL.'<br>GroupId='.$GroupId.'; Right='.$Right.'<br>';
			        	//if ($SiteElementName=='mnUsers') {echo $gm_SQL.'<br>GroupId='.$GroupId.'; Right='.$Right.'<br>';}
			            $db->speak($gm_SQL);
			//            $db->onscreen($gm_SQL);
			            while ($gm_rows=$db->listen())
			            {
		//Если пользователь входит в текущую группу, члены которой имеют права доступа к элементу сайта,
		//определяем права пользователя как результат логического ИЛИ с его текущими правами и правами группы
			            	if ($db->rows==1) { 	$ret |= intval($Right);}
			            }
					}
				}
			}
		}
		//echo "<br>$SiteElementName - $ret<br>";
		return $ret;
	}
}
?>
