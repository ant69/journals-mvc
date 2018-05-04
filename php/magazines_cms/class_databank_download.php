<?php

class databank_download_class
{
	var $DB;
	/***************************************************************************
	 Конструктор класса
	 Конструктору передается ссылка на экземпляр базы данных, созданный вызывающим скриптом
	*/
	function databank_download_class($DM) {
		$this->DM = @$DM;
		$this->DB = $DM->DB;
		$CurrentPath = (isset($_GET['path'])) ? explode('/',$_GET['path']) : array();

		$this->LinkType = in_array($CurrentPath[0], array('pics', 'files', 'databank'))
			? $CurrentPath[0]
			: 'page';
		$this->DM->current_page_path = $CurrentPath;
	}

	############################################################################
	function get_picture() {
		echo 'Картинка для сайта: <pre>'; print_r($this->DM->current_page_path); echo '</pre>';
	}

	############################################################################
	function get_file() {
		echo 'Файл для сайта: <pre>'; print_r($this->DM->current_page_path); echo '</pre>';
	}

	############################################################################
	function get_databank_file() {
		$CurrentPath = @$this->DM->current_page_path;
		$IssuesPath = $GLOBALS['DatabankIssuesPath'];
		$AuthorsPath = $GLOBALS['DatabankAuthorsPath'];
		$ArticlesPath = $GLOBALS['DatabankArticlesPath'];
		$MaterialsPath = $GLOBALS['DatabankMaterialsPath'];
		//echo 'Банк данных: <pre>'; print_r($CurrentPath); echo '</pre>';
		include_once "class_file_managment.php";
		$f = new file_managment_class;
        if (count($CurrentPath)>1) {
        	switch ($CurrentPath[1]) {
        		case 'covers': {
        			$Size = isset($CurrentPath[4]) ? "_$CurrentPath[4]" : '';

        			$SqlIssue = 'SELECT Id FROM '.$this->DM->tbl_issues.'
        						WHERE EditionId=? AND Year=? AND Number=?';
        			$IssueData = $this->DB->run($SqlIssue, array($this->DM->EditionId, $CurrentPath[2], (int)$CurrentPath[3]))->fetch();
        			if ($IssueData)
        			{
        				//echo "Id номера - ".$IssueData[0]['Id'];
        				$IssueId = $IssueData['Id'];
        				$FileName = $IssuesPath.$IssueId.$Size.'.jpg';
        				$f->file_force_download($FileName);
        			}
        			//echo 'Показываем обложку журнала '.$EditionId.' номера '.$CurrentPath[3].' за '.$CurrentPath[2].' год';

        			break;
        		}
        		case 'persons': {
        			$Id = $CurrentPath[2];
        			$Size = isset($CurrentPath[3]) ? "_".$CurrentPath[3] : '';
        			$EditionId = $this->DM->EditionId;
       				$FileName = $AuthorsPath.$Id.'_'.$EditionId.$Size.'.jpg';
       				$f->file_force_download($FileName);
        			//echo 'Показываем фотографию автора '.$FileName.' журнала '.$EditionId.' ';
        			break;
        		}
        		case 'articles': {
        			$Id = $CurrentPath[2];
        			if ($CurrentPath[3]=='files') {
        				$FileName = $ArticlesPath."$Id/".$CurrentPath[4].".".$CurrentPath[5];

	       			} else {
	        			$Ext = ".".$CurrentPath[3];
	       				$FileName = $ArticlesPath.$Id.$Ext;
	       			}
       				$f->file_force_download($FileName);
        			break;
        		}
        		case 'materials': {
        			$FId = $CurrentPath[2];
        			$SqlFile = 'SELECT NameInBank, OriginalFileName FROM '.$this->DM->tbl_files.' WHERE Id=?';
        			$FileData = $this->DB->run($SqlFile, array($FId));
        			if ($FileData) {
        				$FileExt = end(explode('.', $FileData['OriginalFileName']));
        				$NameInBank = $FileData['NameInBank'];
        				$FileName = $MaterialsPath."$FId.$FileExt";
        				$f->file_force_download($FileName, "$NameInBank.$FileExt");
        			}
        			break;
        		}
         		default: break;
        	}
        }
	}

} # end of class