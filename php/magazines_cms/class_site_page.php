<?php

include_once "class_form_simple.php";
//include_once "class_upload_and_resize_image.php";

################################################################################

class site_page_class
{
	function site_page_class($DM) # конструктор
	{
		$this->DM = @$DM;
		$this->DB = @$DM->DB;
		$this->MenuPage = array('page'=>array('text'=>'Параметры страницы', 'link'=>'admin.htm?mode=editsitepage&id=', 'selected'=>0),
									'pagesections'=>array('text'=>'Содержание страницы', 'link'=>'admin.htm?mode=editpagesections&id=', 'selected'=>0)  );

	}

	############################################################################

	function set_menu($PageId, $CurrentPage)
	{
		$this->MenuPage['page']['link'] .= $PageId;
		$this->MenuPage['pagesections']['link'] .= $PageId;
		$this->MenuPage[$CurrentPage]['selected'] = 1;
	}

	############################################################################

	function show_list()
	{
		$Sql = 'SELECT *
				FROM site_pages
				ORDER BY Rank';
		$Count = $this->DB->get_count($Sql);
		$Data = $this->DB->get_simple_sql_result($Sql);

        echo "<h1 class='main'>Страницы сайта ($Count)</h1><p class='main'>Чтобы редактировать страницы сайта,
        	щелкните на названии страницы.</p>";
        echo "<p class='main'><a class='button' href='admin.htm?mode=addsitepage' onclick='this.blur();'><span>Добавить страницу</span></a></p>";

		for ($i = 0; $i <= $Count-1; $i++)
		{
			$Id = $Data[$i]['Id'];
            $ShortName = $Data[$i]['ShortName'];
            $Title = $Data[$i]['Title'];
            $Description = $Data[$i]['Description'];
            $TranslitName = $Data[$i]['TranslitName'];
            $Keywords = $Data[$i]['Keywords'];
            $ScriptName = $Data[$i]['ScriptName'];
            $Level = $Data[$i]['Level'];
			$ParentId = $Data[$i]['ParentId'];
			$Rank = $Data[$i]['Rank'];
			$TemplateId = $Data[$i]['TemplateId'];

echo <<<END
				<hr><p class="main">
				<a class="news_popup" target="_self" href="admin.htm?mode=editsitepage&id=$Id">
					$ShortName</a>&nbsp;&rarr;<br>
					<i>$Description</i>
				</p>
END;
		}
	}

	############################################################################

	function edit($Id)
	{
        $Header = "Редактирование/добавление страницы сайта";
        $Instructions = "<p class='main'>Вернуться к списку страниц можно <a href='admin.htm?mode=sitepages'>здесь</a>.</p>";
        $this->set_menu($Id, 'page');
		show_page_top($Header, $Instructions, $this->MenuPage);


		$Sql = "SELECT * FROM site_pages WHERE (Id = $Id)";
				$Data = $this->DB->get_simple_sql_result($Sql);
		$Form = new form_simple_class;
		$Form->form_header_multipart("admin.htm?mode=savesitepage");
		$Form->hidden_value("id", $Id);
		$Form->input_string("shortname", $Data[0]['ShortName'], "Краткое название страницы:", 100);
		$Form->input_string("title", $Data[0]['Title'], "Название страницы:", 100);
		$Form->input_text("description", $Data[0]['Description'], "Описание:", 100, 3);
		$Form->input_string("translitname", $Data[0]['TranslitName'], "Имя страницы на транслите:", 100);
		$Form->input_string("keywords", $Data[0]['Keywords'], "Ключевые слова:", 100);
    	$Form->input_string("scriptname", $Data[0]['ScriptName'], "Имя скрипта для страницы:", 100);
    	$Form->input_string("level", $Data[0]['Level'], "Уровень страницы в иерархии карты сайта:", 30);
    	$Form->input_string("parentid", $Data[0]['ParentId'], "Id страницы, для которой текущая страница является подчиненной:", 30);
    	$Form->input_string("rank", $Data[0]['Rank'], "Ранг страницы на текущем уровне иерархии страниц:", 30);
		$Form->drop_list("templateid", $Data[0]['TemplateId'], "Шаблон страницы:", $this->DM->get_templates(), 'Id', 'Title');

//    	$Form->input_string("templateid", $Data[0]['TemplateId'], "Идентификатор шаблона для отображения страницы:", 30);
//		$Form->check_box("isshow", $Data[0]['IsShow'], "Показывать номер на сайте");
        $Form->print_horizontal_line();
		$Form->form_footer();
	}

	############################################################################

	function edit_page_sections($Id)
	{
		$PageName = $this->DB->get_value('site_pages', $Id, 'ShortName');
		$TemplateId = $this->DB->get_value('site_pages', $Id, 'TemplateId');
		$TemplateName = $this->DB->get_value('page_templates', $TemplateId, 'Title');
        $Header = "Редактирование разделов страницы сайта «".$PageName."»";
        $Instructions = "<p class='main'>Вернуться к списку страниц можно <a href='admin.htm?mode=sitepages'>здесь</a>.<br>
        На этой странице админпанели доступны для редактирования отдельные блоки страницы «".$PageName."».<br>
        Состав редактируемых блоков определяется выбранным для текущей страницы шаблоном.<br>
        Будьте осторожны при редактировании этих параметров и текстов!<br>
        Для страницы используется <b>шаблон «".$TemplateName."»</b> (<a href='admin.htm?mode=edittemplate&id=$TemplateId'>редактировать</a>)</p>";
        $this->set_menu($Id, 'pagesections');
		show_page_top($Header, $Instructions, $this->MenuPage);

		$TemplateId = $this->DB->get_value('site_pages', $Id, 'TemplateId');
		$SqlTemplate = "SELECT *
						FROM template_sections
						WHERE TemplateId=$TemplateId
						ORDER BY Code";
		$TemplateSections = $this->DB->get_simple_sql_result($SqlTemplate);
		if (is_array($TemplateSections)){
			$Form = new form_simple_class;
			$Form->form_header("admin.htm?mode=savepagesections&id=$Id");
			$Form->hidden_value("id", $Id);

			foreach ($TemplateSections as $TemplateSection)
			{
				$SectionCode = $TemplateSection['Code'];
				$SectionCaption = $TemplateSection['Caption'];
				$SectionDescription = $TemplateSection['Description'];
				$Sql = "SELECT s.*
						FROM page_sections p
						INNER JOIN sections s ON p.SectionId=s.Id
						WHERE p.PageId=$Id and p.TemplateSectionCode='$SectionCode'";
						//echo $Sql;
				$Sections = $this->DB->get_simple_sql_result($Sql);
				echo "<p class='main'><b>Блок «".$SectionCaption."»</b>&nbsp;<i>($SectionCode, ".$Sections[0]['Id'].")</i><br>
				<i>$SectionDescription</i><br></p>";

				if (is_array($Sections))
				{
					$ContentType = $Sections[0]['ContentType'];
					if ($ContentType == 1)
					{
						$Form->input_text_CKEditor("htmlpagesection$SectionCode", $Sections[0]['HTMLContent'], "Содержание блока:");
					}
					else
					{
						$Form->input_string("scriptname$SectionCode", $Sections[0]['ScriptName'], "Имя скрипта:", 100);
					}

				}
				else
				{
					echo "<p class='main'>Содержание блока ".$SectionCode.' <b>пока не определено!</b><br>
					Перед определением содержимого задайте его тип и сохраните форму.<br>
					После сохранения формы появится возможность задать содержимое блока в соответствии с его установленным типом.</p>';
					$ContentType = 1;
				}
				$HTMLBlockId = $Sections[0]['BlockId'];
				$Blocks = $this->DM->get_html_blocks();
				$Blocks[] = array('Id'=>0, 'Title'=>'Контейнер не задан');

				$ContentTypeList = array('0' => array('key' => 'HTML-текст', 'value' => '1'),
									'1' =>  array('key' => 'Скрипт', 'value' => '2'));
				$Form->drop_list("contenttype$SectionCode", $ContentType, "Тип содержимого блока:",
								$ContentTypeList, 'value', 'key', true);

				$Form->drop_list("contentblock$SectionCode", $HTMLBlockId, "&nbsp;&nbsp;&nbsp;HTML-контейнер:",
								$Blocks, 'Id', 'Title', true);
		        //	function drop_list($vname, $vvalue, $title, $list_values, $id_field_name, $value_field_name, $InOneLine = false)
		        //("rubrica$Id", $Data[$i]['RubricId'], "", $AllRubrics, 'Id', 'Title', true);

		        $Form->print_horizontal_line();
			}
			$Form->form_footer();
		}
		else
		{
			echo "<p class='main'>Похоже, что для текущей страницы не определен шаблон
			либо в выбранном шаблоне не определены блоки для редактирования!</p>";
		}

	}

	############################################################################

	function save()
	{
		# если это новая запись, то создаем ее...
		//foreach ($_POST as $k=>$v) {echo "$k => $v <br>";}
		if ($_POST['id'] == 0)	$_POST['id'] = $this->DB->create_new_record('site_pages');

		$this->DB->update_record('site_pages', $_POST['id'], 'shortname', $_POST['shortname']);
		$this->DB->update_record('site_pages', $_POST['id'], 'title', $_POST['title']);
		$this->DB->update_record('site_pages', $_POST['id'], 'description', $_POST['description']);
		$this->DB->update_record('site_pages', $_POST['id'], 'keywords', $_POST['keywords']);
		$this->DB->update_record('site_pages', $_POST['id'], 'translitname', $_POST['translitname']);
		$this->DB->update_record('site_pages', $_POST['id'], 'scriptname', $_POST['scriptname']);
		$this->DB->update_record('site_pages', $_POST['id'], 'level', $_POST['level']);
		$this->DB->update_record('site_pages', $_POST['id'], 'parentid', $_POST['parentid']);
		$this->DB->update_record('site_pages', $_POST['id'], 'rank', $_POST['rank']);
		$this->DB->update_record('site_pages', $_POST['id'], 'templateid', $_POST['templateid']);

		$this->edit($_POST['id']);
	}

	############################################################################

	function save_page_sections($Id)
	{
//		echo "Сохраняем форму!";
		# если это новая запись, то создаем ее...
		//foreach ($_POST as $k=>$v) {echo "$k => $v <br>";}

		// Вначале перебираем переменные, переданные в POST, для определения того,
		// нужно ли создавать записи в таблице page_sections
		foreach ($_POST as $Key=>$Value)
		{
			//echo "$Key => $Value<br>";
			if (strpos($Key, 'contenttype')===0)
			{
				$SectionCode = substr($Key, 11, strlen($Key)-11);
				//$this->DB->update_record('publications', $PubId, 'PageNumber', $Value);
				if ($this->DB->get_count("SELECT Id FROM page_sections WHERE PageId=$Id and TemplateSectionCode='$SectionCode'")==0)
				{
					// Блок отсутствует. Вначале нужно создать новую запись в таблице "Sections",
					// а затем создать новую запись в таблице page_sections
                    $NewSectionId = $this->DB->create_new_record('sections');
//                    $this->DB->update_record('sections', $NewSectionId, $Value);
                    $this->DB->exec_sql("INSERT INTO page_sections (PageId, SectionId, TemplateSectionCode)
                    					VALUES ($Id, $NewSectionId, '$SectionCode')");
				}
				else
				{
					//echo "Сохраняем секцию $SectionCode<br>";
				}

			}
		}

		foreach ($_POST as $Key=>$Value)
		{
			//echo "$Key => $Value<br>";
			$FieldToUpdate = '';
			if (strpos($Key, 'htmlpagesection')===0)
			{
				$SectionCode = substr($Key, 15, strlen($Key)-15);
				$FieldToUpdate = 'HTMLContent';
				//$this->DB->update_record('publications', $PubId, 'PageNumber', $Value);
				//echo "Код секции - $SectionCode<br>";
			}
			if (strpos($Key, 'contenttype')===0)
			{
				$SectionCode = substr($Key, 11, strlen($Key)-11);
				$FieldToUpdate = 'ContentType';
			}
			if (strpos($Key, 'contentblock')===0)
			{
				$SectionCode = substr($Key, 12, strlen($Key)-12);
				$FieldToUpdate = 'BlockId';
			}
			if (strpos($Key, 'scriptname')===0)
			{
				$SectionCode = substr($Key, 10, strlen($Key)-10);
				$FieldToUpdate = 'ScriptName';
			}
			if ($FieldToUpdate != '')
			{
				//echo "SELECT SectionId FROM page_sections
				//												WHERE PageId=$Id and TemplateSectionCode='$SectionCode'<br>";

				$PageSection = $this->DB->get_simple_sql_result("SELECT SectionId FROM page_sections
																WHERE PageId=$Id and TemplateSectionCode='$SectionCode'");
				$SectionId = $PageSection[0]['SectionId'];
				//echo "<br>SectionId = $SectionId <br>";
				$this->DB->update_record('sections', $SectionId, $FieldToUpdate, $Value);
			}

		}
		//$this->db->UpdateRecord('issue_content', $_POST['id'], 'issueid', $_POST['issueid']);
/*
		if ($_POST['id'] == 0)	$_POST['id'] = $this->DB->create_new_record('site_pages');

		$this->DB->update_record('site_pages', $_POST['id'], 'shortname', $_POST['shortname']);
		$this->DB->update_record('site_pages', $_POST['id'], 'title', $_POST['title']);
		$this->DB->update_record('site_pages', $_POST['id'], 'description', $_POST['description']);
		$this->DB->update_record('site_pages', $_POST['id'], 'keywords', $_POST['keywords']);
		$this->DB->update_record('site_pages', $_POST['id'], 'translitname', $_POST['translitname']);
		$this->DB->update_record('site_pages', $_POST['id'], 'scriptname', $_POST['scriptname']);
		$this->DB->update_record('site_pages', $_POST['id'], 'level', $_POST['level']);
		$this->DB->update_record('site_pages', $_POST['id'], 'parentid', $_POST['parentid']);
		$this->DB->update_record('site_pages', $_POST['id'], 'rank', $_POST['rank']);
		$this->DB->update_record('site_pages', $_POST['id'], 'templateid', $_POST['templateid']);
*/
		$this->edit_page_sections($Id);
	}

	############################################################################
/*
	function edit_article_list($IssueId)
	{
		$Sql = "SELECT *
				FROM issues
				WHERE Id = $IssueId";
		$Data = $this->DB->get_simple_sql_result($Sql);

        echo "<h1 class='main'>Содержание номера журнала ".$Data[0]['Title']." №".$Data[0]['Num']." (".$Data[0]['CrossNum'].") за ".$Data[0]['Year']." год</h1>";

		$Dql = "SELECT *
				FROM issue_content
				WHERE IssueId = $issue_id
				ORDER BY Rank";
		$Data = $this->DB->get_simple_sql_result($Sql);
		$count = $this->DB->get_count($Sql);

		$Data[$Count]['Id'] = 0;
		$Count++;

		for ($i = 0; $i <= $Count-1; $i++)
		{
			$Id = $Data[$i]['Id'];
            $RubricaId = $Data[$i]['RubricaId'];
            $Title = $Data[$i]['Title'];
            $Caption = $Data[$i]['Caption'];
            $URL = $Data[$i]['URL'];
            $Authors = $Data[$i]['Authors'];
            $IsInterview = $Data[$i]['IsInterview'];
            $Rank = $Data[$i]['Rank'];

			$Form = new form_simple_class;
			$Form->form_header("admin.htm?mode=savecontents", true);
			$Form->hidden_value("id", $Id);
			$Form->hidden_value("issueid", $issue_id);
			$Form->input_string("title", $data[$i]['Title'], "Статья: ", 100, true);
			$Form->input_string("rank", $data[$i]['Rank'], "  Номер в оглавлении: ", 5, true);
			$Form->print_break();
			$Form->input_string("authors", $data[$i]['Authors'], "Автор(ы) или интервьюируемый: ", 50, true);
			$Form->input_string("url", $data[$i]['URL'], "  Ссылка на текст на сайте: ", 50, true);
			$Form->input_text("caption", $data[$i]['Caption'], "Аннотация статьи:", 100,3);
			$Form->drop_list("rubricaid", $data[$i]['RubricaId'], "Рубрика:", $this->DB->get_simple_sql_result("SELECT Id, Title FROM rubrics ORDER BY Title"), 'Id', 'Title');
			$Form->check_box("isinterview", $data[$i]['IsInterview'], "Это интервью?", true);
			$Form->form_footer();
	        $Form->print_horizontal_line();
		}

        //echo "<p class='main'><a class='button' href='admin.htm?mode=addсontents&id=$issue_id' onclick='this.blur();'><span>Добавить статью</span></a></p><p>&nbsp;</p>";
	}

	############################################################################

	function SaveArticle()
	{
		# если это новая запись, то создаем ее...
		if ($_POST['id'] == 0)	$_POST['id'] = $this->db->CreateNewRecord('issue_content');

		$this->db->UpdateRecord('issue_content', $_POST['id'], 'issueid', $_POST['issueid']);
		$this->db->UpdateRecord('issue_content', $_POST['id'], 'title', $_POST['title']);
		$this->db->UpdateRecord('issue_content', $_POST['id'], 'rank', $_POST['rank']);
		$this->db->UpdateRecord('issue_content', $_POST['id'], 'authors', $_POST['authors']);
		$this->db->UpdateRecord('issue_content', $_POST['id'], 'url', $_POST['url']);
		$this->db->UpdateRecord('issue_content', $_POST['id'], 'caption', $_POST['caption']);
		$this->db->UpdateRecord('issue_content', $_POST['id'], 'rubricaid', $_POST['rubricaid']);
		$this->db->UpdateRecord('issue_content', $_POST['id'], 'isinterview', $_POST['isinterview']);

		$this->EditArticleList($_POST['issueid']);
	}

	############################################################################

*/

} # end of class

################################################################################


?>