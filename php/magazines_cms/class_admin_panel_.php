<?php

include_once("lib_admin_interface.php");
include_once("class_form_simple.php");

class admin_panel_class
{
	var $DB;
	var $LibNews = 'class_news_editor.php';
	var $LibNewsTags = 'class_news_tags_editor.php';
	var $LibNewsSources = 'class_news_source_editor.php';
	var $LibAdmins = 'class_administrators_editor.php';
	var $LibIssues = 'class_issue_editor.php';
	var $LibRubrics = 'class_rubricator_editor.php';
	var $LibPublications = 'class_publication_editor.php';
	var $LibSitePages = 'class_site_page.php';
	var $LibSiteMenu = 'class_site_menu.php';
	var $LibPersons = 'class_persons_editor.php';
	//var $LibFiles = 'class_files_editor.php';
	var $LibHTMLBlocks = 'class_html_block_editor.php';
	var $LibTemplates = 'class_templates_editor.php';

	var $LibImport = 'class_am_import.php';

	/***************************************************************************
	 Конструктор класса
	 Конструктору передается ссылка на экземпляр базы данных, созданный вызывающим скриптом
	*/

	function admin_panel_class($DM, $User=false) # конструктор
	{
		$this->DM = @$DM;
		$this->DB = @$DM->DB;
		$this->IsAdmin = $User->Data['GlobalAdmin'];
		$this->CurUser = $User->Data['I'].' '.$User->Data['O'];
		$this->IsEditor = $User->IsEditor;
		$this->User = $User->Data;
//		$this->init_settings();
//		$this->File = new FileManagment;
	}


	############################################################################

	function page_selector($Mode, $Id = 0)
	{
        if (in_array($Mode, array('news', 'editnews', 'savenews', 'addnews', 'previewnews', 'deletenews')))
        {
        	include_once $this->LibNews;
        	$NewsEditor = new news_editor_class($this->DB);
        }
        if (in_array($Mode, array('newstags', 'savenewstag', 'deletenewstag')))
        {
        	include_once $this->LibNewsTags;
        	$NewsTagsEditor = new news_tags_editor_class($this->DB);
        }
        if (in_array($Mode, array('newssource', 'savenewssource', 'deletenewssource')))
        {
        	include_once $this->LibNewsSources;
        	$NewsSourceEditor = new news_source_editor_class($this->DB);
        }
        if (in_array($Mode, array('administrators', 'saveadministrator', 'deleteadministrator')))
        {
        	include_once $this->LibAdmins;
        	$AdministratorsEditor = new administrators_editor_class($this->DB);
        }
        if (in_array($Mode, array('issues', 'addissue', 'editissue', 'saveissue', 'deleteissue',
        							'editcontent', 'savecontent', 'issuecover', 'saveissuecover')))
        {
        	include_once $this->LibIssues;
        	$IssueEditor = new issue_editor_class($this->DM);
        }
        if (in_array($Mode, array('rubricator', 'saverubrica')))
        {
        	include_once $this->LibRubrics;
        	$RubricatorEditor = new rubricator_editor_class($this->DB);
        }
        if (in_array($Mode, array('publications', 'editpublication', 'savepublication',
        							'addpublication', 'previewpublication', 'deletepublication',
        							'savepublicationauthors','publicationauthors',
        							'publicationfiles', 'savepublicationfiles',
        							'savepublicationfile', 'editfile', 'savefile')))
        {
        	include_once $this->LibPublications;
        	$PublicationEditor = new publication_editor_class($this->DM);
        }
        if (in_array($Mode, array('sitepages', 'editsitepage', 'addsitepage', 'movesitepage', 'savesitepage',
        							'editpagesections', 'savepagesections')))
        {
        	include_once $this->LibSitePages;
        	$SitePageEditor = new site_page_class($this->DM);
        }
        if (in_array($Mode, array('sitemenu', 'editsitemenu', 'addsitemenu', 'savesitemenu', 'deletesitemenu', 'movesitemenu')))
        {
        	include_once $this->LibSiteMenu;
        	$SiteMenuEditor = new site_menu_class($this->DM);
        }
        if (in_array($Mode, array('persons', 'editperson', 'addperson', 'saveperson', 'deleteperson')))
        {
        	include_once $this->LibPersons;
        	$PersonEditor = new persons_editor_class($this->DB);
        }
        if (in_array($Mode, array('htmlblockslist', 'edithtmlblock', 'savehtmlblock')))
        {
        	include_once $this->LibHTMLBlocks;
        	$HTMLBlockEditor = new html_block_editor_class($this->DM);
        }
        if (in_array($Mode, array('templates', 'savetemplate', 'edittemplate', 'savetemplatesections',
        							'deletetemplatesection')))
        {
        	include_once $this->LibTemplates;
        	$TemplatesEditor = new templates_editor_class($this->DM);
        }
		if (in_array($Mode, array('amimport')))
        {
        	include_once $this->LibImport;
        	$AMImport = new am_import_class($this->DM, $amDBSettings);
        }
/*        if (in_array($Mode, array('files', 'editfile', 'savefile')))
        {
        	include_once $this->LibFiles;
        	$FilesEditor = new files_editor_class($this->DB);
        }
        */

//		include_once "lib_news.php"; 		$NewsEditor = new NewsEditor; $NewsTagsEditor = new NewsTagsEditor; $NewsSourceEditor = new NewsSourceEditor;
//		include_once "lib_interview.php"; 	$InterviewEditor = new InterviewEditor;
//		include_once "lib_article.php"; 	$ArticleEditor = new ArticleEditor; $RubricatorEditor = new RubricatorEditor;
//		include_once "lib_persons.php"; 	$PersonEditor = new PersonEditor;
//		include_once "lib_blog.php"; 		$BlogEditor = new AdminBlogEditor;
//		include_once "lib_issue.php"; 		$IssueEditor = new IssueEditor;
//		include_once "lib_expert.php"; 		$ExpertEditor = new ExpertEditor;

		if ($this->IsAdmin || $this->IsEditor)
		{
			switch ($Mode)
			{
				case 'pass': 				$this->ShowPassScreen(); break;
				//АДМИНИСТРАТОРСКИЕ ЗАДАЧИ
				case 'logout':	 			$this->logout(); break;
	            case 'administrators':		$AdministratorsEditor->show_administrators_list(); break;
	            case 'saveadministrator':	$AdministratorsEditor->save_administrator(); break;
	            case 'deleteadministrator':	$AdministratorsEditor->delete_administrator($Id); break;

				//АДМИНИСТРАТОРСКИЕ ЗАДАЧИ
				case 'sitepages':	 		$SitePageEditor->show_list(); break;
				case 'addsitepage':	 		$SitePageEditor->edit(0); break;
				case 'editsitepage': 		$SitePageEditor->edit($Id); break;
				case 'editpagesections':	$SitePageEditor->edit_page_sections($Id); break;
				case 'savepagesections':	$SitePageEditor->save_page_sections($Id); break;
				case 'movesitepage': 		$SitePageEditor->move($Id); break;
				case 'savesitepage': 		$SitePageEditor->save($Id); break;
				case 'deletesitepage': 		$SiteMenuEditor->delete($Id); break;

				case 'htmlblockslist': 		$HTMLBlockEditor->show_list(); break;
				case 'edithtmlblock': 		$HTMLBlockEditor->edit($Id); break;
				case 'savehtmlblock': 		$HTMLBlockEditor->save($Id); break;

				case 'templates':	 		$TemplatesEditor->show_list(); break;
				case 'savetemplate': 		$TemplatesEditor->save_template(); break;
				case 'edittemplate': 		$TemplatesEditor->edit_template($Id); break;
				case 'savetemplatesections': $TemplatesEditor->save_template_sections($Id); break;
				case 'deletetemplatesection': $TemplatesEditor->delete_template_section($Id); break;

				case 'sitemenu':	 		$SiteMenuEditor->show_list(); break;
				case 'addsitemenu':	 		$SiteMenuEditor->edit(0); break;
				case 'editsitemenu': 		$SiteMenuEditor->edit($Id); break;
				case 'movesitemenu': 		$SiteMenuEditor->move($Id); break;
				case 'savesitemenu': 		$SiteMenuEditor->save($Id); break;
				case 'deletesitemenu': 		$SiteMenuEditor->delete($Id); break;

	// Импорт из базы авторских материалов
				case 'amimport':	 		$AMImport->show(); break;



				//ОБЩИЕ НАСТРОЙКИ ЖУРНАЛА
				case 'productsettings':		$this->show_product_settings(); break;
				case 'saveproductsettings':	$this->save_product_settings(); break;

	           //АРХИВ ЖУРНАЛА
				case 'issues':	 			$IssueEditor->show_list(); break;
				case 'addissue':			$IssueEditor->edit(0); break;
				case 'editissue':			$IssueEditor->edit($Id); break;
				case 'saveissue':	 		$IssueEditor->save(); break;
				case 'deleteissue':	 		$IssueEditor->delete($Id); break;
				case 'editissueteam':		$IssueEditor->edit_team($Id); break;
				case 'saveissueteam':       $IssueEditor->save_team($Id); break;
				case 'issuecover':			$IssueEditor->edit_issue_cover($Id); break;
				case 'saveissuecover':	 	$IssueEditor->save_issue_cover($Id); break;

				//ОГЛАВЛЕНИЕ ЖУРНАЛА
				case 'editcontent':			$IssueEditor->edit_issue_content($Id); break;
				case 'savecontent':			$IssueEditor->save_issue_content($Id); break;

				//КОМАНДА НОМЕРА

				//СТАТЬИ
				case 'publications':	 	$PublicationEditor->show_publications_list($Id); break;
				case 'addpublication':		$PublicationEditor->edit_publication(0, $Id); break;
				case 'editpublication':		$PublicationEditor->edit_publication($Id); break;
				case 'savepublication':		$PublicationEditor->save_publication(); break;
				case 'deletepublication':	$PublicationEditor->delete_publication($Id); break;
				case 'publicationauthors':	$PublicationEditor->edit_publication_authors($Id); break;
				case 'savepublicationauthors':
											$PublicationEditor->save_publication_authors($Id); break;
				case 'previewpublication':	$PublicationEditor->show_publication_preview($Id); break;


				//ФАЙЛЫ
				case 'publicationfiles':	$PublicationEditor->show_publication_files($Id); break;
				case 'savepublicationfile':	$PublicationEditor->save_publication_file($Id); break;
				case 'savepublicationfiles':$PublicationEditor->save_publication_files($Id); break;
				case 'editfile':			$PublicationEditor->edit_file($Id); break;
				case 'savefile':			$PublicationEditor->save_file($Id); break;


				//РУБРИКАТОР СТАТЕЙ
				case 'rubricator':	 		$RubricatorEditor->show_rubrics_list(); break;
				case 'saverubrica': 		$RubricatorEditor->save_rubrica(); break;

	            //ПЕРСОНЫ
				case 'persons':	 			$PersonEditor->show_persons(); break;
				case 'editperson':			$PersonEditor->edit_person($Id); break;
				case 'saveperson':	 		$PersonEditor->save_person(); break;
				case 'addperson':	 		$PersonEditor->edit_person(0); break;
				case 'deleteperson':		$PersonEditor->delete_person($Id); break;
				//АВТОРЫ

	            //НОВОСТИ САЙТА
				case 'news':	 			$NewsEditor->show_news_list(); break;
				case 'editnews':			$NewsEditor->edit_news($Id); break;
				case 'savenews':	 		$NewsEditor->save_news(); break;
				case 'addnews':	 			$NewsEditor->edit_news(0); break;
				case 'deletenews':			$NewsEditor->delete_news($id); break;
				case 'previewnews':	 		$NewsEditor->show_news_preview($id); break;
				//ТЭГИ НОВОСТЕЙ
				case 'newstags':	 		$NewsTagsEditor->show_tags(); break;
				case 'savenewstag': 		$NewsTagsEditor->save_tag(); break;
				case 'deletenewstag': 		$NewsTagsEditor->delete_tag($Id); break;
				//ИСТОЧНИКИ НОВОСТЕЙ
				case 'newssource':	 		$NewsSourceEditor->show_sources(); break;
				case 'savenewssource': 		$NewsSourceEditor->save_source(); break;
				case 'deletenewssource':	$NewsSourceEditor->delete_source($Id); break;

	            //ИНТЕРВЬЮ
				case 'interview':	 		$InterviewEditor->ShowInterviewList(); break;
				case 'editinterview':		$InterviewEditor->EditInterview($Id); break;
				case 'saveinterview':	 	$InterviewEditor->SaveInterview(); break;
				case 'addinterview':	 	$InterviewEditor->EditInterview(0); break;
				case 'previewinterview':	$InterviewEditor->ShowInterviewPreview($id); break;

	            //БЛОГИ
				case 'blog':	 			$BlogEditor->ShowList(); break;
				case 'editblog':			$BlogEditor->Edit($Id); break;
				case 'saveblog':	 		$BlogEditor->Save(); break;
				case 'addblog':	 			$BlogEditor->Edit(0); break;
				case 'previewblog':			$BlogEditor->Preview($Id); break;

	//			case 'addcontents':			$IssueEditor->EditArticleList($id); break;

	            //ВЫ — ЭКСПЕРТ
				case 'vote':	 			$ExpertEditor->ShowList(); break;
				case 'editvote':			$ExpertEditor->Edit($Iid); break;
				case 'savevote':	 		$ExpertEditor->Save(); break;
				case 'addvote':	 			$ExpertEditor->Edit(0); break;
				case 'previewvote':			$ExpertEditor->Preview($Id); break;

	            //case 'todo':				$this->ShowToDo(); break;
				//case 'uploadfile':	$this->file->DoFileUpload(); break;
				default:					$this->show_start_screen(); break;
			}
		}
		else
		{
			$this->show_no_admin_screen();
		}
	}

	############################################################################
    // Метод не работает!! Базовая аутентификация оказалась сложнее, чем казалось.
    // Помогает лишь закрытие браузера..
	function logout()
	{
		$_SESSION['realm']="realm".time();
		$_SESSION['logout']='active';
		$CurUser = $_SERVER['PHP_AUTH_USER'];
echo <<<END
		<h1 class="main">Уважаемый $CurUser!</h2>
		<p class="main">Вы пытаетесь завершить сеанс работы с администраторской панелью сайта.</p>
		<p class="main">Ваша текущая сессия до сих пор активна, но если вы попытаетесь перейти к любому из пунктов меню, расположенных слева, откроется окно авторизации. <br>
		Если Вы введете там авторизационные данные нового пользователя,
		то Вы войдете в систему под новым именем. <br>
		Если по каким-то причинам Вы передумали выходить из системы и хотели бы остаться в ней
		со своими текущими данными, просто нажмите кнопку "Отмена" в диалоге авторизации.</p>
		<p class="main">Если Вы хотите целиком прервать свою сессию, придется закрывать браузер.</p>
END;

	}
	############################################################################

	function show_start_screen()
	{
	//global $CurUserData;

	$CurUser=$this->CurUser;
echo <<<END
		<h1 class="main">Здравствуйте, $CurUser!</h2>
		<p class="main">Вы авторизованы в системе управления контентом сайта с правами администратора.</p>
		<p class="main">В меню, расположенном слева, содержатся ссылки на страницы, содержащие инструменты для управления содержимым сайта журнала Директор школы. <br></p>
		<p class="main">Пожалуйста, будьте внимательны при работе с админпанелью. Не забывайте о том, что любые ошибки, допущенные Вами, будут видны посетителям сайта.

END;
	}

	############################################################################

	function show_no_admin_screen()
	{
	$CurUser=$this->CurUser;
echo <<<END
		<h1 class="main">Здравствуйте, $CurUser!</h2>
		<p class="main">Вы не имеете права работать с панелью управления сайтом.</p>

END;
	}

	############################################################################

	function show_product_settings()
	{
	//global $CurUserData;
		$Header = "Общая информация о журнале";
		$Instructions = '<p class="main">На этой странице админпанели чуть позже появится возможность редактировать
		отдельные параметры сайта журнала<br>
		(Название журнала, ключевые слова, описание и т.п.)';
		show_page_top($Header, $Instructions, array());
		echo $this->msg;
		$Id = $this->DM->ProductId;
		$Sql = "SELECT * FROM site_config WHERE (ProductId = $Id)";
			$Data = $this->DB->get_simple_sql_result($Sql);

		$Form = new form_simple_class;
		$Form->form_header("admin.htm?mode=saveproductsettings");
		$Form->hidden_value("id", $Id);

		foreach ($Data as $Param)
		{
			$Form->input_text($Param['Name'], $Param['Value'], $Param['Description'], 100, 3);
	        $Form->print_horizontal_line();
		}

		$Form->form_footer();
	}


	############################################################################

	function save_product_settings()
	{
		if ($_POST['id'] == $this->DM->ProductId )
		{
			$this->msg = '';
			$ProductId = $this->DM->ProductId;
			foreach ($_POST as $Param=>$Value)
			{
				$Sql = "SELECT Id, Description FROM site_config WHERE (ProductId = '$ProductId' and Name='$Param')";
				//echo $Sql;
				if ($this->DB->get_count($Sql)==1)
				{
					$Data = $this->DB->get_simple_sql_result($Sql);
					$Id = $Data[0]['Id'];
					$this->DB->update_record('site_config', $Id, 'Value', $Value);
					$this->msg .= "Значение параметра <b>'".$Data[0]['Description']."'</b> обновлено.<br>";
				}
			}
			$this->msg = '<p class="main">'.$this->msg.'</p><hr>';
		}


		$this->show_product_settings();
	}


	############################################################################

	function ShowPassScreen()
	{
echo <<<END
		<h1>Вы не имеете доступа к администраторской панели!</h1>

END;
// header("Location: index.htm");
	}



} # end of class

?>