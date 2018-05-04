<?php

//include_once "class_form_simple.php";
//include_once "class_upload_and_resize_image.php";


function get_tag( $Tag, $Html, $Matches)
{
	if ((strlen($Tag)>0) and (strlen($Html)>0))
	{
		$OpenTagPos = strpos($Html, "<$Tag>");
		if ($OpenTagPos !== false)
		{
			$CloseTagPos = strpos($Html, "</$Tag>");
			if ($CloseTagPos !== false)
			{
				if ($CloseTagPos > $OpenTagPos)
				{
					$StartContentPos = $OpenTagPos + strlen($Tag)+2;
					$CloseContentPos = $CloseTagPos + strlen($Tag)+3;

					$Matches[] = substr($Html, $StartContentPos, $CloseTagPos - $StartContentPos);
					$Matches = get_tag($Tag, substr($Html, $CloseContentPos, strlen($Html) - $CloseContentPos), $Matches);
				}
			}
		}
	}
	return $Matches;
}

################################################################################

class site_page_class
{
	function site_page_class($DM, $PageTranslitName, $PageParams=array()) # конструктор
	{

		$this->DM = @$DM;
		$this->DB = $DM->DB;

		//echo '<pre>'; print_r($DM->current_page_path); echo '</pre>';
		$this->CurPage = $PageTranslitName;
		$this->SiteId = $DM->SiteId;
		//$this->ScriptOpenTag = '#SCRIPT#';
		//$this->ScriptCloseTag = '#/SCRIPT#';

		$this->tbl_site_pages = $GLOBALS['tbl_site_pages'];
		$this->tbl_site_map = $GLOBALS['tbl_site_map'];
		$this->tbl_pages_blocks = $GLOBALS['tbl_pages_blocks'];

		$this->tbl_block_templates = $GLOBALS['tbl_block_templates'];
		$this->tbl_block_templates_areas = $GLOBALS['tbl_block_templates_areas'];
		$this->tbl_blocks_content = $GLOBALS['tbl_blocks_content'];


        $this->PageParams = $PageParams;
		//echo '123<pre>';         print_r($this->PageParams); echo '</pre>';
		$this->get_page();

		//$this->Title = $this->DM->SiteName;
		//$this->Keywords = $this->DM->Keywords;
		//$this->Description = $this->DM->Description;

	}

	############################################################################

	function get_page()
	{
        $Page = $this->DB->run('SELECT * FROM '.$this->tbl_site_pages.' WHERE TranslitName=? AND SiteId=?', array($this->CurPage, $this->SiteId))->fetch();

		$this->PageId = $Page['Id'];
		$this->SiteId = $Page['SiteId'];
		$this->ShortName = $Page['ShortName'];
		$this->Title = $this->DM->SiteName.' :: '.$Page['Title'];
		$this->Description = $Page['Description'];
		$this->TranslitName = $Page['TranslitName'];
		$this->Keywords = $this->DM->Keywords.', '.$Page['Keywords'];
		$this->set_meta();
		$this->ScriptName = $Page['ScriptName'];
		$this->Level = $Page['Level'];
		$this->Rank = $Page['Rank'];
		$this->ParentId = $Page['ParentId'];
		$this->TemplateId = $Page['BlockTemplateId'];
		$this->IsShow = $Page['IsShow'];
	}

	############################################################################

	function set_meta()
	{
		//echo "TranslitName => ".$this->TranslitName;
		//$this->Description = $Page['Description'];
		//$this->Keywords = $this->DM->Keywords.', '.$Page['Keywords'];
		switch ($this->TranslitName)
		{
			case 'link':
			{
				include_once($GLOBALS["lib_short_link"]);
				$this->ShortLink = new short_link_class(@$this->DM, $this->PageParams['link_id']);
				$this->Title = $this->DM->SiteName.' :: Сервис коротких ссылок';
				break;
			}

			case 'person':
			{
				include_once($GLOBALS["lib_issue_viewer"]);
				$Issue = new issue_viewer_class(@$this->DM);
				$AuthorId = $Issue->get_author_id_by_translit($this->PageParams['author']);
				$Meta = $Issue->get_author_page_meta($AuthorId);
				$this->Description = $Meta['description'];
				$this->Keywords .= $Meta['keywords'];
				$this->Title = $this->DM->SiteName.' :: '.$Meta['title'];
				$Issue = NULL;
				break;
			}
			case 'article':
			{
				include_once($GLOBALS["lib_issue_viewer"]);
				$Issue = new issue_viewer_class(@$this->DM);
				//print_r($this->PageParams);
				$ArticleId = $Issue->get_article_id_by_translit($this->PageParams['Id'],
					$this->PageParams['article']);
				$Meta = $Issue->get_article_page_meta($ArticleId);
				$this->Description = $Meta['description'];
				$this->Keywords .= $Meta['keywords'];
				$this->Title = $this->DM->SiteName.' :: '.$Meta['title'];;
				$Issue = NULL;
				break;
			}
			case 'rubric':
			{
				include_once($GLOBALS["lib_issue_viewer"]);
				$Issue = new issue_viewer_class(@$this->DM);
				//print_r($this->PageParams);
				$RubricId = $Issue->get_rubric_id_by_translit($this->PageParams['rubric']);
				$Meta = $Issue->get_rubric_page_meta($RubricId);
				$this->Description = $Meta['description'];
				$this->Keywords .= $Meta['keywords'];
				$this->Title = $this->DM->SiteName.' :: '.$Meta['title'];;
				$Issue = NULL;
				break;
			}
			case 'issue':
			{
				include_once($GLOBALS["lib_issue_viewer"]);
				$Issue = new issue_viewer_class(@$this->DM);

				$Meta = $Issue->get_issue_page_meta($this->PageParams['Id']);
				$this->Description = $Meta['description'];
				$this->Keywords .= $Meta['keywords'];
				$this->Title = $this->DM->SiteName.' :: '.$Meta['title'];;
				$Issue = NULL;
				break;
			}
			case 'post':
			{
				include_once($GLOBALS["lib_posts_viewer"]);
				$Post = new posts_viewer_class(@$this->DM);

				$Meta = $Post->get_post_page_meta($this->PageParams['id']);
				$this->Description = $Meta['description'];
				$this->Keywords .= $Meta['keywords'];
				$this->Title = $this->DM->SiteName.' :: '.$Meta['title'];;
				$Issue = NULL;
				break;
			}
			case 'news_item':
			{
				include_once($GLOBALS["lib_news_viewer"]);
				$News = new news_viewer_class(@$this->DM);

				$Meta = $News->get_news_page_meta($this->PageParams['id']);
				$this->Description = $Meta['description'];
				$this->Keywords .= $Meta['keywords'];
				$this->Title = $this->DM->SiteName.' :: '.$Meta['title'];;
				$Issue = NULL;
				break;
			}
		}
	}

	############################################################################

	function get_element($PageId, $ParentId)
	{
		$Res = false;
		$Elements = $this->DB->run('SELECT * FROM '.$this->tbl_pages_blocks.'
						WHERE PageId=? AND ParentId=? AND IsShow=1
						ORDER By Rank', array($PageId, $ParentId));
		if (count($Elements)>0)	{
			$Res = '';
			while ($E = $Elements->fetch())	{
				$ElementParentId	= $E['Id'];
				$ElementType		= $E['ElementType'];
				$ElementId			= $E['ElementId'];
				switch ($ElementType) {
					case element_block: {
                        if ($BD=$this->DB->run('SELECT * FROM '.$this->tbl_block_templates.' WHERE Id=?',
                            array($ElementId) )->fetch()) {
                        	$BlockContent = htmlspecialchars_decode($BD['Code']);
							$SqlAreas = 'SELECT b.*, a.TranslitName as AreaName
									FROM '.$this->tbl_pages_blocks.' b
									LEFT JOIN '.$this->tbl_block_templates_areas.' a ON a.Id=b.ElementId
									WHERE b.PageId=? AND b.ParentId=?';
							$Areas = $this->DB->run($SqlAreas, array($PageId, $ElementParentId));
                            while ($A = $Areas->fetch()) {
                                $AreaId = $A['Id'];
                                $AreaName = $A['AreaName'];
                                $AreaContent = htmlspecialchars_decode($this->get_element($PageId, $AreaId));
                                $BlockContent = str_replace("<<".$AreaName.">>", $AreaContent, $BlockContent);
                            }
                        	$Res .= $BlockContent;
                        }
						break;
					}
					case element_html: {
                        $HTMLData = $this->DB->run('SELECT Content FROM '.$this->tbl_blocks_content.' WHERE Id=?',
                                                    array($ElementId));
                        while ($htmlData = $HTMLData->fetch()) {
	                        $Res .= htmlspecialchars_decode($htmlData['Content'], ENT_QUOTES);
	                    }
						break;
					}
					case element_script:
					{
                        $ScriptData = $this->DB->run('SELECT Content FROM '.$this->tbl_blocks_content.' WHERE Id=?',
                            array($ElementId));
                        while ($sData = $ScriptData->fetch()) {
                            $Res .= $this->get_script_running_result_html($sData['Content']);
                        }
						break;
					}
					default: break;
				}

			}
		}

		//$Res=count($Elements);

		return $Res;
	}
	############################################################################

	function get_script_running_result_html($ScriptCode)
	{
		$DB = @$this->DB;
 		$Ret = true;
 		//echo $ScriptCode;
 		$ScriptParts=explode('/',$ScriptCode);
 		$PageParams = $this->PageParams;
 		//print_r($PageParams); echo '<br>';
 		//print_r($ScriptParts); echo '<br>';
		if (count($ScriptParts)>0)
		{

			switch ($ScriptParts[0])
			{
				case 'ARTICLES':
				{
					include_once($GLOBALS["lib_issue_viewer"]);
					$Articles = new issue_viewer_class(@$this->DM);
					$Page = $this->PageParams['page_num'];
					$Ret = $Articles->show_open_articles($Page);
					break;
					/*
					// ToDo
					if ($ScriptParts[1] == 'TOP_POPULAR')
					{
						$Ret="<p class='main'>Самые популярные статьи</p>";
					}
					// ToDo
					if ($ScriptParts[1] == 'TOP_COMMENTED')
					{
						$Ret="<p class='main'>Самые комментируемые статьи</p>";
					}
					// ToDo
					if ($ScriptParts[1] == 'TOP_LAST')
					{
						$Ret="<p class='main'>Последние статьи</p>";
					}
					break;*/
				}
				case 'BREADCRUMBS':
				{
					// ToDo
					//$Ret="<p class='main'>Хлебные крошки</p>";
					$Ret = $this->get_breadcrumbs();

					break;
				}

				case 'ISSUES':
				{
					include_once($GLOBALS["lib_issue_viewer"]);
					$Issue = new issue_viewer_class(@$this->DM);
					if ($ScriptParts[1] == 'RUBRICS')
					{
						$Ret=$Issue->show_rubrics();
					}
					if ($ScriptParts[1] == 'RUBRIC')
					{
						$RubricTranslit = $this->PageParams['rubric'];
						$Ret=$Issue->get_rubric($RubricTranslit);
					}
					break;
				}


				case 'ISSUE':
				{
					include_once($GLOBALS["lib_issue_viewer"]);
					$Issue = new issue_viewer_class(@$this->DM);
					if ($ScriptParts[1] == 'BOOKCASE')
					{
						$Ret=$Issue->show_bookcase();
					}
					if ($ScriptParts[1] == 'ARCHIVE')
					{
						$Ret=$Issue->show_archive();
					}
					if ($ScriptParts[1] == 'ARCHIVE_BLOCK')
					{
						$IssueId = $this->PageParams['Id'];
						$Ret=$Issue->get_archive_block($IssueId);
					}
					if ($ScriptParts[1] == 'CURRENT_ISSUE_BLOCK')
					{
						$Ret=$Issue->show_current_issue_frame();
						//$Ret = '4321';
					}
					if ($ScriptParts[1] == 'CONTENT')
					{
						$IssueId = $this->PageParams['Id'];
						$Ret=$Issue->get_issue_content($IssueId);
						//$Ret = $PageParams['Year'];
					}
					if ($ScriptParts[1] == 'COVER')
					{
						$IssueId = $this->PageParams['Id'];
						$Ret=$Issue->get_issue_cover($IssueId);
						//$Ret = $PageParams['Year'];
					}
					if ($ScriptParts[1] == 'DETAILS')
					{
						$IssueId = $this->PageParams['Id'];
						$Ret=$Issue->get_issue_details($IssueId);
						//$Ret = $PageParams['Year'];
					}
					if ($ScriptParts[1] == 'ARTICLE')
					{
						$IssueId = $this->PageParams['Id']; $ArticleTranslit = $this->PageParams['article'];
						$Ret = $Issue->get_article($IssueId, $ArticleTranslit);
						//$ArticleId = $Issue->get_article_id_by_translit($IssueId, $this->PageParams['article']);
						//if ($ArticleId>0) {	$Ret=$Issue->get_article($ArticleId); }
						//else {$Ret=$Issue->get_issue_content($IssueId);}
						//$Ret = $PageParams['Year'];
					}
					break;
				}
				case 'PERSON':
				{
					include_once($GLOBALS["lib_issue_viewer"]);
					$Issue = new issue_viewer_class(@$this->DM);
					$AuthorTranslit = $this->PageParams['author'];
					$Ret = $Issue->get_author($AuthorTranslit);
					break;
				}

				case 'PERSONS':
				{
					include_once($GLOBALS["lib_issue_viewer"]);
					$Issue = new issue_viewer_class(@$this->DM);
					$Ret = $Issue->show_authors($this->PageParams['letter']);
					break;
				}

				case 'DIGEST':
				{
					include_once($GLOBALS["lib_digest_viewer"]);
					$Digest = new digest_viewer_class(@$this->DM);
					$PageNum = $this->PageParams['page_num'];
					$Ret = $Digest->show_digest($PageNum);
					break;
				}

				case 'NEWS':
				{
					include_once($GLOBALS["lib_news_viewer"]);
					$News = new news_viewer_class(@$this->DM);
					if (!isset($ScriptParts[1]))
					{
						$Page = $this->PageParams['page_num'];
						$Ret = $News->news_archive($Page);
					}
					else if ($ScriptParts[1]='SHOW_NEWS')
					{
						$NewsId = $this->PageParams['id'];
						$Ret = $News->show_news($NewsId);
					}

					break;
				}
				case 'POSTS':
				{
					include_once($GLOBALS["lib_posts_viewer"]);
					$Posts = new posts_viewer_class(@$this->DM);
					if (!isset($ScriptParts[1]))
					{
						$Page = $this->PageParams['page_num'];
						$Ret = $Posts->posts_archive($Page);
					}
					else if ($ScriptParts[1]='SHOW_POST')
					{
						$PostId = $this->PageParams['id'];
						$Ret = $Posts->show_post($PostId);
					}

					break;
				}
				case 'BLOG':
				{
					//require_once "script/class_news_viewer.php";
					//$Params = array();
					//$News = new news_viewer_class($DB, $Params);
					if ($ScriptParts[1] == 'SHORT')
					{
						$Ret="<p class='main'>Последние посты</p>";
					}
					// ToDo
					if ($ScriptParts[1] == 'TOP_POPULAR')
					{
						$Ret="<p class='main'>Самые популярные посты</p>";
					}
					// ToDo
					if ($ScriptParts[1] == 'TOP_COMMENTED')
					{
						$Ret="<p class='main'>Самые комментируемые посты</p>";
					}
					// ToDo
					if ($ScriptParts[1] == 'TOP_LAST')
					{
						$Ret="<p class='main'>Последние посты</p>";
					}
					break;
				}
				case 'PROFILE':
				{
					// ToDo

					include_once($GLOBALS["lib_profile"]);
					$Profile = new profile_class(@$this->DM);
					if ($ScriptParts[1] == 'SUBSCRIPTION')
					{
						$Ret=$Profile->show_subscribe_info();
					}
					else
					{
						$Ret = $Profile->show_page();
					}

					break;
				}
				case 'SEARCH':
				{
					// ToDo
					$Ret="<p class='main'>Поиск по сайту</p>";

					break;
				}
				case 'SITEMAP':
				{
					// ToDo
					$Ret=$this->get_sitemap();

					break;
				}
				/*
				case 'ARCHIVE':
				{
					if ($ScriptParts[1] == 'LIST')
					{
						require_once "script/class_issue_viewer.php";
						$Issue = new issue_viewer_class($DB);
						$Ret=$Issue->show_archive();
						//$Ret = 'Архив номеров';
					}
					break;
				}
				*/
				case 'ADVERTISE':
				{
					// ToDo
                    global $Debug;
                    if ($Debug == false) {
                        if ($ScriptParts[1] == '1') {
                            include_once $GLOBALS['lib_upload_banners'];
                            $Ret = ShowPics($ScriptParts[1], 0);

                            //$Ret="<p class='main'>Баннеры внешних и внутренних партнеров</p>";
                        } else {
                            include_once $GLOBALS['lib_upload_banners'];
                            $Ret = ShowPics($ScriptParts[1], 0);
                        }
                    } else {
                        $Ret = false;
                    }
					break;
				}
				case 'SHORT_LINK':
				{
                    $Ret = $this->ShortLink->ShowLink();
					break;
				}
				default:
				{
					$Ret = false;
					break;
				}
			}
		}
		else { $Ret = false; }
		return $Ret;
//		return 456;

	}
	############################################################################

	function get_breadcrumbs()
	{
		$CurrentPagePath = $this->DM->current_page_path;
		$SiteId = $this->DM->SiteId;
		$Res = '';
		$LevelsCounter = count($CurrentPagePath);
		if ($LevelsCounter>0) {
			$i=0;
			$Link = '';
			foreach ($CurrentPagePath as $Page) {
				$i++;

				$PageSql = 'SELECT ShortName FROM '.$this->DM->tbl_site_pages.'
							WHERE SiteId=? AND TranslitName=? LIMIT 0,1';
				if ($PageData = $this->DB->run($PageSql, array($SiteId, $Page))->fetch()) {
					$ShortName = $PageData['ShortName'];
					if ($i>1) {$Link .= '/'; $Res .= ' / ';}
					$Link .= "$Page";
					$PageLink = ($i==$LevelsCounter) ? $ShortName : "<a href='$Link'>$ShortName</a>";
					$Res .= $PageLink;
				}
			}
		}
		$Res = ($Res=='') ? $Res : " / $Res";
		$HomePage = $this->DM->Base;
		$Res = "<a href='$HomePage'>Начало</a>".$Res;
		return "<p class='main'>$Res</p>";
	}
	############################################################################

	function get_sitemap()
	{
//		$CurrentPagePath = $this->DM->current_page_path;
		$SiteId = $this->DM->SiteId;
		$Res = '<h1 class="main">Карта сайта</h1><p class="main">'.$this->get_sitemap_element($SiteId, 0).'</p>';
//		$LevelsCounter = count($CurrentPagePath);
		return $Res;
	}

	############################################################################

	function get_sitemap_element($SiteId, $ParentId)
	{
		$Res = '';
        $sql = "SELECT * FROM ".$this->tbl_site_map."
        		WHERE (SiteId=$SiteId) and (ParentId=$ParentId) AND IsShow=1
        		ORDER BY Rank";
		//echo $sql;
		$data = $this->DB->get_simple_sql_result($sql);

		if (count($data)>0)
		{
			foreach ($data as $SData)
			{
				$Id 			= $SData['Id'];
				$Title 			= $SData['Title'];
				$Description	= $SData['Description'];
				$Level			= $SData['Level'];
				$IsShow			= $SData['IsShow'];
				$Url			= $SData['Url'];

                $TitleToShow	= "<a href='$Url' title='$Description'>$Title</a><br>";

				$Div = ''; for ($i=0; $i<$Level; $i++) {$Div .= '&nbsp;&rArr;&nbsp;';}
				$Res .= $Div.$TitleToShow;
				$Res .= $this->get_sitemap_element($SiteId, $Id);
			}
		}
		return $Res;
	}

	############################################################################

	function show_page()
	{
		//echo "Содержимое страницы ".$this->ShortName;
		//$TemplateName = $this->DM->TemplateDir.$this->DB->get_value('page_templates', $this->TemplateId, 'FileName');
//		include $TemplateName;
		$ShowPage=$this->get_element($this->PageId, 0);
        if ($ShowPage===false) {
        	echo "<h1 class='main'>Страница ".$this->PageId." не обнаружена</h1>";
        } else {
        	echo $ShowPage;
        }
	}
	############################################################################

	function set_error($ErrorText)
	{
		$this->Valid = false;
		$this->ErrorText = $ErrorText;

	}
	############################################################################



} # end of class

################################################################################


?>