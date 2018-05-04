<?php

include_once("kernel_site_pages.php");
include_once("class_site_menu.php");
//include_once("../../vendor/sinergi/browser-detector/src/Browser.php");

class frontend_class
{
	var $DB;
//	var $LibNews = 'class_news_editor.php';
//	var $LibNewsTags = 'class_news_tags_editor.php';
//	var $LibNewsSources = 'class_news_source_editor.php';
//	var $LibAdmins = 'class_administrators_editor.php';
//	var $LibIssues = 'class_issue_editor.php';
//	var $LibRubrics = 'class_rubricator_editor.php';
//	var $LibPublications = 'class_publication_editor.php';
//	var $LibSitePages = 'class_site_page.php';
//	var $LibSiteMenu = 'class_site_menu.php';
//	var $LibPersons = 'class_persons_editor.php';

	/***************************************************************************
	 Конструктор класса
	 Конструктору передается ссылка на экземпляр базы данных, созданный вызывающим скриптом
	*/

	function frontend_class($DM, $IsAdmin=0) {
		$this->DM = @$DM;
		$this->DB = $DM->DB;
		$this->IsAdmin = $IsAdmin;

		$this->LinkType = 'page';

		$this->set_page_parameters();

		$this->Page = new site_page_class($this->DM, $this->PageTranslitName, $this->PageParameters);
        $this->MainMenu = new site_menu_class(@$DM);
	    //echo $this->PageTranslitName.'<pre>'; print_r($this->PageParameters); echo '</pre>';
	}

	############################################################################

	function set_page_parameters() {
		if (isset($_GET['path'])) 	{ $CurrentPath=explode('/',$_GET['path']);	}
		else { $CurrentPath=array(); }
        $this->DM->current_page_path = $CurrentPath;

		$PathL = count($CurrentPath);
		$CurLevel = 0;

		if (($PathL == 0) || ($CurrentPath[0]=='')) {
			$CurPage = $this->DM->DefaultPage;
		} else {
			if ($CurrentPath[0] == 'admin') {
				header("Location:http://cp.september.ru");
				exit;
			}
			if (is_numeric($CurrentPath[0])) {
				$CurrentPath = array('0'=>'link', '1'=>$CurrentPath[0]);
				$PathL=2;
			}
			$i=0;
			foreach ($CurrentPath as $P) {
			    $curP = $this->DB->run('SELECT Id FROM '.$this->DM->tbl_site_pages.' WHERE TranslitName=? AND SiteId=?', array($P, $this->DM->SiteId))->fetchAll();
				if (count($curP)==1) {
					$CurPage=$P;
					$i++;
				} else break;
			}
			// Если в базе данных не обнаружилось страниц, имена которых упомянуты в адресной строке,
			//считаем, что показывать надо домашнюю страницу.
			if ($i==0) { $CurPage = $this->DM->DefaultPage;  }
			// В противном случае имя страницы определилось на предыдущем этапе
			else { $CurLevel = $i-1; }
		}

        $this->PageTranslitName = strtolower($CurPage);

       	// Номер страницы по умолчанию для тех страниц сайта, где требуется разбиение на страницы
       	$this->PageParameters['page_num'] = 1;

        // Для главной страницы и работы с дайджестом - своя механика
        if (strtolower($CurPage)=='digest') {
        	$this->PageTranslitName = 'home';
        }

        // Для страниц второго, третьего уровней реализуем более сложную процедуру
		if ($PathL>1) {
			switch ($this->PageTranslitName) {
				case 'archive': {
					$Year = $CurrentPath[$CurLevel+1]; $Num = (int)$CurrentPath[$CurLevel+2];
					if (($Year < 2100) and ($Year > 1900) and ($Num < 50) and ($Num >= 0)) {
						$IssueId = $this->DM->get_issue_id_by_yearnum($Year, $Num);
						//echo "<br>Id номера - ".$IssueId."<br>";
						if ($IssueId>0) {
							$this->PageParameters['Year'] = $Year;
							$this->PageParameters['Num'] = $Num;
							$this->PageParameters['Id'] = $IssueId;
							if ($CurrentPath[3]>'') {
								$this->PageParameters['article'] = $CurrentPath[$CurLevel+3];
								$this->PageTranslitName = 'article';
							} else {
								$this->PageTranslitName = 'issue';
							}
						}
					}
					break;
				}
				case 'persons': {
					if (strlen($CurrentPath[$CurLevel+1])>3) {
						$this->PageTranslitName = 'person';
						$this->PageParameters['author'] = $CurrentPath[$CurLevel+1];
					} else {
						$this->PageTranslitName = 'persons';
						$this->PageParameters['letter'] = $CurrentPath[$CurLevel+1];
					}
					break;
				}
				case 'rubrics': {
					if ($CurrentPath[$CurLevel+1]>'') {
						$this->PageTranslitName = 'rubric';
						$this->PageParameters['rubric'] = $CurrentPath[$CurLevel+1];
					} else {
						$this->PageTranslitName = 'rubrics';
					}
					break;
				}
				case 'posts': {
					if (is_numeric($CurrentPath[1])) {
						$this->PageTranslitName = 'post';
						$this->PageParameters['id'] = $CurrentPath[1];
					} else if (is_numeric($CurrentPath[2]) && $CurrentPath[1]=='page') {
						$this->PageTranslitName = 'posts';
						$this->PageParameters['page_num'] = $CurrentPath[2];
					}
					break;
				}
				case 'news': {
					if (is_numeric($CurrentPath[1])) {
						$this->PageTranslitName = 'news_item';
						$this->PageParameters['id'] = $CurrentPath[1];
					} else if (is_numeric($CurrentPath[2]) && $CurrentPath[1]=='page') {
						$this->PageTranslitName = 'news';
						$this->PageParameters['page_num'] = $CurrentPath[2];
					}
					break;
				}
				case 'articles': {
					if (is_numeric($CurrentPath[2]) && $CurrentPath[1]=='page') {
						$this->PageParameters['page_num'] = $CurrentPath[2];
					}
					break;
				}
				case 'home': {
					if (is_numeric($CurrentPath[1])) {
						$this->PageTranslitName = 'home';
						$this->PageParameters['page_num'] = $CurrentPath[1];
					}
					break;
				}
				case 'link': {
					if (is_numeric($CurrentPath[1])) {
						$this->PageTranslitName = 'link';
						$this->PageParameters['link_id'] = abs((int)$CurrentPath[1]);
					}
					break;
				}
				default: break;
			}
		}
	}


} # end of class

?>