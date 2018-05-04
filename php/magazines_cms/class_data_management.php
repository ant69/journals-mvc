<?php
 	// Модуль класса для работы с базами данных
	//include_once($GLOBALS['lib_database']);
    include_once($GLOBALS['lib_db_pdo']);

################################################################################

class data_management_class {
	#function data_management_class($GLOBALS) # конструктор
	function data_management_class() {
		$this->EditionId = $GLOBALS['EditionId'];

		$this->DBSettings = $GLOBALS['DBSettings'];
		$this->uDBSettings = $GLOBALS['uDBSettings'];
		$this->UserDBSettings = $GLOBALS['UserDBSettings'];
		$this->MainDBSettings = $GLOBALS['MainDBSettings'];
		$this->SubscribeDBSettings = $GLOBALS['SubscribeDBSettings'];

		$this->TemplateDir = $GLOBALS['TemplateDir'];
		$this->CurrentTemplateDir = $GLOBALS['CurrentTemplateDir'];

		$this->HeaderImages = $GLOBALS['HeaderImages'];
		$this->FooterImages = $GLOBALS['FooterImages'];
		$this->MenuImages = $GLOBALS['MenuImages'];

		$this->CoversDir = $GLOBALS['CoversDir'];
		$this->PersonsDir = $GLOBALS['PersonsDir'];
		$this->ArticlesDir = $GLOBALS['ArticlesDir'];
		$this->MaterialsDir = $GLOBALS['MaterialsDir'];
		$this->FilesDir = $GLOBALS['FilesDir'];
		$this->IncludePagesDir = $GLOBALS['IncludePagesDir'];

		$this->DefaultPage = $GLOBALS['DefaultPage'];
		$this->Header = $GLOBALS['Header'];
		$this->Footer = $GLOBALS['Footer'];

		$this->Covers = $GLOBALS['Covers'];
		$this->CoverBorderColor = $GLOBALS['CoverBorderColor'];

		$this->PersonPhoto = $GLOBALS['PersonPhoto'];
		$this->PersonPhotoBorderColor = $GLOBALS['PersonPhotoBorderColor'];

        $this->Base = "http://".$_SERVER['HTTP_HOST'];

		$this->DB = new db_pdo($this->DBSettings);
		$this->uDB = new db_pdo($this->uDBSettings);
        //$this->UserDB = New database_class($this->UserDBSettings);
		//$this->MainDB = New database_class($this->MainDBSettings);
		//$this->SubscribeDB = New database_class($this->SubscribeDBSettings);

		$this->tbl_editions_sites = $GLOBALS['tbl_editions_sites'];
		$this->tbl_site_pages = $GLOBALS['tbl_site_pages'];
		$this->tbl_site_menu = $GLOBALS['tbl_site_menu'];
		$this->tbl_issues = $GLOBALS['tbl_issues'];
		$this->tbl_files = $GLOBALS['tbl_files'];

		$this->init_settings();
	}


	############################################################################

	function init_settings() {
        if ($Settings = $this->DB->run("SELECT * FROM {$this->tbl_editions_sites} WHERE EditionId=?", array($this->EditionId))->fetchAll())	{
			foreach ($Settings as $Param) {
				$this->SiteName = $Param['SiteName'];
				$this->SiteId = $Param['Id'];
				$this->Keywords = $Param['KeywordsDefault'];
				$this->Description = $Param['DescriptionDefault'];
				$this->SiteCounters = $Param['Counters'];
			}
			return true;
		}
		return false;
	}


	############################################################################

	function get_issues() {
		$Sql = "SELECT Id, CONCAT(Title, ', ',Num,'-', Year) as Title FROM '.$this->tbl_issues.' ORDER BY CrossNum";
		return $this->DB->run($Sql)->fetchAll();
	}

	############################################################################

	function get_issue($IssueId) {
		return $this->DB->run('SELECT * FROM '.$this->tbl_issues.' WHERE Id=?', array($IssueId))->fetch();
	}
	############################################################################

/*	function get_issue_id_by_publication($PublicationId) {
		$Sql = "SELECT IssueId FROM publications WHERE Id=$PublicationId";
		if ($Publication = $db->get_simple_sql_result($Sql)) {$Ret = $Publication[0]['IssueId'];	}
		else {$Ret = false;}
		return $Ret;
	}*/

	############################################################################

	function get_issue_id_by_yearnum($Year, $Num) {
		$Sql = 'SELECT Id FROM '.$this->tbl_issues.' WHERE Number=? and Year=? and EditionId=?';
		if ($IssueData=$this->DB->run($Sql, array($Num, $Year, $this->EditionId))->fetch())	{
			return $IssueData['Id'];
		}
		return false;
	}
	############################################################################

	function get_rubrics($IssueId=0)
	{
		$db = New database_class($this->DBSettings);
		$Sql = "SELECT Id, Title FROM rubrics ORDER BY Title";
		return $db->get_simple_sql_result($Sql);
	}

	############################################################################

	function get_persons()
	{
		$db = New database_class($this->DBSettings);
		$Sql = "SELECT F, I, O, Id FROM persons ORDER BY F, I, O";
		return $db->get_simple_sql_result($Sql);
	}

	############################################################################

	function get_authors_by_publication($PublicationId)
	{
		$db = New database_class($this->DBSettings);
		$Sql = "SELECT a.PersonId, p.F, p.I, p.O FROM authors a INNER JOIN persons p ON a.PersonId=p.Id WHERE PublicationId=$PublicationId";
		//echo $Sql;
		return $db->get_simple_sql_result($Sql);
	}

	############################################################################

	function get_files()
	{
		$db = New database_class($this->DBSettings);
		$Sql = "SELECT * FROM files ORDER BY Title";
		return $db->get_simple_sql_result($Sql);
	}

	############################################################################

	function get_files_by_publication($PublicationId)
	{
		$db = New database_class($this->DBSettings);
		$Sql = "SELECT * FROM files WHERE PublicationId=$PublicationId ORDER BY Rank";
		//echo $Sql;
		return $db->get_simple_sql_result($Sql);
	}

	############################################################################

	function get_main_menu_items()
	{
		/*$db = @$this->MainDB;
		$SiteId = $this->SiteId;
		$Sql = "SELECT * FROM ".$this->tbl_site_menu." WHERE SiteId=$SiteId AND IsShow=1 ORDER BY Rank";
		return $db->get_simple_sql_result($Sql);*/
		return $this->DB->run('SELECT * FROM '.$this->tbl_site_menu.' WHERE SiteId=? AND IsShow=1 ORDER BY Rank', array($this->SiteId))->fetchAll();
	}

	############################################################################

	function get_html_blocks()
	{
		/*$db = New database_class($this->DBSettings);
		$Sql = "SELECT * FROM html_blocks ORDER BY Title";
		return $db->get_simple_sql_result($Sql);*/
		return $this->DB->run('SELECT * FROM html_blocks ORDER BY Title')->fetchAll();
	}
	############################################################################

	function get_templates()
	{
		$db = New database_class($this->DBSettings);
		$Sql = "SELECT * FROM page_templates ORDER BY Title";
		return $db->get_simple_sql_result($Sql);
	}


} # end of class      $this->DB->get_simple_sql_result("SELECT Id, Title FROM rubrics ORDER BY Rank")

################################################################################


?>