<?php

include_once "class_form_simple.php";


################################################################################

class issue_viewer_class
{
	function issue_viewer_class($DM) # конструктор
	{
		//$this->DM = $DM;
		$this->DB = $DM->MainDB;
//		$this->MainDB = $DM->MainDB;
		$this->Covers = $GLOBALS['Covers'];

		$this->tbl_issues = $GLOBALS['tbl_issues'];
		$this->tbl_rubrics = $GLOBALS['tbl_rubrics'];
		$this->tbl_articles = $GLOBALS['tbl_articles'];
		$this->tbl_authors = $GLOBALS['tbl_authors'];
		$this->tbl_articles_authors = $GLOBALS['tbl_articles_authors'];

		$this->EditionId = $DM->EditionId;
		//$this->file = new FileManagment;
	}

	############################################################################


	function show_bookcase($shift = 0)
	{
		$Sql = "SELECT *
				FROM ".$this->tbl_issues."
				WHERE IsShow = true AND EditionId=".$this->EditionId."
				ORDER BY Year DESC, Number DESC
				LIMIT 0, 6";


		$Count = $this->DB->get_count($Sql);
		$Data = $this->DB->get_simple_sql_result($Sql);

		$Ret = '
<table border="0" width="100%" id="table1" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="3" background="images/issues/bookcase.jpg" height="244">

		<div align="center">
			<table border="0" width="1022" height="16" cellspacing="0" cellpadding="0">
				<tr>
					<td>&nbsp;</td>
				</tr>
			</table>
		</div>

		<div align="center">
			<table border="0" width="1022" height="189" cellspacing="0" cellpadding="0">
				<tr>
					<td width="60">
						<p align="right">
						<br>

					</td>';

		for ($i = $Count-1; $i >= 0; $i--)
		{

			$Id = $Data[$i]['Id'];
	        $Title = $Data[$i]['Title'];
	        $Num = $Data[$i]['Number']; if ($Num != 10) $Num = '0'.$Num;
	        $CrossNum = $Data[$i]['NumberOverall'];
	        //$NumCaption = $Data[$i]['NumCaption'];
	        $Year = $Data[$i]['Year'];
			$IsShow = $Data[$i]['IsShow'];

			$Element = "c".$i;
			$CaptionText = "№ $Num ($CrossNum), $Year год";
			$AltText = "№ $Num ($CrossNum), $Year год";
			$URL = "archive/$Year/$Num";

			$SmallPic = $this->get_issue_cover_file($Year, $Num, 'small');
			$BigPic = $this->get_issue_cover_file($Year, $Num, 'middle');

			$Ret .= '<td valign="middle" width="143" align="center">';
			$Ret .= '<a href="'.$URL.'"><img name="'.$Element.'" alt="'.$AltText.'" title="'.$AltText.'" src="'.$SmallPic.'" border="0" onmouseover="document.'.$Element.".src='".$BigPic."'".'" onmouseout="document.'.$Element.".src='".$SmallPic."'".'">';
			$Ret .= '</a>&nbsp;</td>';
		}

		$Ret .= '	<td width="50">
						<br>
					</td>
				</tr>
			</table>
		</div>
		<div align="center">
			<table border="0" width="1022" height="36" cellspacing="0" cellpadding="0">
				<tr>
					<td>&nbsp;</td>
				</tr>
			</table>
		</div>
		</td>
	</tr>
</table>	';
        return $Ret;

	}

	############################################################################
    /*
	function get_last_issue_id()
	{
		$Sql = "SELECT *
				FROM ".$this->tbl_issues."
				WHERE IsShow = true AND EditionId=".$this->EditionId."
				ORDER BY NumberOverall DESC
				LIMIT 0, 1";

		$Data = $this->DB->get_simple_sql_result($Sql);
		$Id = $Data[0]['Id'];
		return $Id;
	}
    */
	function show_current_issue_frame()
	{
		$Sql = "SELECT *, YEAR(PublishDate) as CurYear
				FROM ".$this->tbl_issues."
				WHERE IsShow = true AND EditionId=".$this->EditionId."
				ORDER BY Year DESC, Number DESC
				LIMIT 0, 1";

		//$Count = $this->MainDB->get_count($Sql)-1;
		$Data = $this->MainDB->get_simple_sql_result($Sql);

		$Id = $Data[0]['Id'];
        $Num = $Data[0]['Number']; if ($Num != 10) $Num = '0'.$Num;
        $Year = $Data[0]['Year'];
        $CurYear = $Data[0]['CurYear'];
   		$SmallPic = $this->get_issue_cover_file($Year, $Num, 'thumb');

		$Num = "№ ".$Data[0]['Number']." (".$Data[0]['NumberOverall'].")";
		$Pub = "Выходит <br>".date("d.m.Y", strtotime($Data[0]['PublishDate']));

        $Titles = '';

		if ($Titles == '') {$Titles = 'Анонсы статей не заданы';} //$Removal1.$Removal2.$Removal3.$Removal4.$Removal5;

		$AfterTitles = $data[$count]['AditionText'];
		if ($AfterTitles != '') $AfterTitles = '<p class="right_block" style="text-align: left">'.$AfterTitles;

		$AltText = $Num." ".$Pub;
		$URL = "archive/$Year/".$Data[0]['Number'];
		//return "12345";


//echo <<<END
		$Ret =
		'<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td background="template/images/frames/bubble/top_left_no_nozzle.png" width="1%" height="5"></td>
				<td background="template/images/frames/bubble/top.png" height="5" colspan="2"></td>
				<td background="template/images/frames/bubble/top_right.png" width="1%" height="5"></td>
			</tr>
			<tr>
				<td background="template/images/frames/bubble/left_no_nozzle.png" valign="top" width="1%" rowspan="2"></td>
				<td bgcolor="#ececea" width="120px" valign="top" rowspan="2">';

		$Ret .=		'<p class="right_block" style="text-align: left">
					<a href="'.$URL.'">
					<img border="0" src="'.$SmallPic.'" width="'.$this->Covers['thumb']['w'].'" height="'.$this->Covers['thumb']['h'].'"></a>
					<p class="main_tagline" style="text-align: center"><b>'.$Num.'</b></p>
					<p class="main_tagline" style="text-align: center">&nbsp;</p>
					<p class="main_tagline" style="text-align: center">'.$Pub.'<br>&nbsp;</p>
				</td>
				<td bgcolor="#ececea" valign="top">
					<p class="right_block"></p>
					<h1 class="right_block" style="text-align: left">Читайте в свежем номере:</h1>
					<p class="right_block" style="text-align: left">
						'.$Titles.'
					</p>
						'.$AfterTitles.'

				</td>
				<td background="template/images/frames/bubble/right.png" width="1%" rowspan="2"><img src="template/images/frames/bubble/right.png"></td>
			</tr>
			<tr>
				<td bgcolor="#ececea" valign="bottom">
					<p class="right_block" style="text-align: left">
					<a href="'.$URL.'">Содержание этого номера</a> &#8594;<br>';
		$Ret .= '
					<a href="podpiska">Подписка на бумажный номер</a> &#8594;';
		if ($ELink = $GLOBALS['ElectronicVersionLink'])
		{
			$Ret .= '<br><a href="'.$ELink.'">Купить в электронном виде</a> &#8594;';
		}
		$Ret .= '
					</p>
				</td>
			</tr>
			<tr>
				<td background="template/images/frames/bubble/bottom_left_no_nozzle.png" width="1%" height="5"></td>
				<td background="template/images/frames/bubble/bottom.png" height="5" colspan="2"></td>
				<td background="template/images/frames/bubble/bottom_right.png" width="1%" height="5"></td>
			</tr>
		</table>';
		return $Ret;
//END;

	}

	############################################################################

	function get_issue_cover_file($Year, $Num, $Type='')
	{
		$Pic = $this->DM->Base."/databank/covers/$Year/$Num/$Type";
		return $Pic;
    }

	############################################################################

	function get_issue_cover($Id)
	{
		$Sql = "SELECT *
				FROM ".$this->tbl_issues."
				WHERE Id = $Id";

		$Data = $this->DB->get_simple_sql_result($Sql);
        $Num = $Data[0]['Number']; if ($Num != 10) $Num = '0'.$Num;
        $Year = $Data[0]['Year'];
        $Title = $Data[0]['Title'];
        $CrossNum = $Data[0]['NumberOverall'];

		$Pic = $this->get_issue_cover_file($Year, $Num, 'base');
		$AltText = "$Title \n№ $Num ($CrossNum), $Year год";
        $Link = "archive/$Year/$Num";

		$Ret =
			'<div align="center"> <a href="'.$Link.'">
			<img border="0" src="'.$Pic.'" width="'.$this->Covers['base']['w'].'" height="'.$this->Covers['base']['h'].'" alt="'.$AltText.'" Title="'.$AltText.'" style="margin: 10px;">
			</a></div>';
		return $Ret;
    }

	############################################################################

	function get_issue_details($Id)
	{
		$Sql = "SELECT *
				FROM ".$this->tbl_issues."
				WHERE Id = $Id";
		$Data = $this->DB->get_simple_sql_result($Sql);
        $Num = $Data[0]['Number']; if ($Num != 10) $Num = '0'.$Num;
        $Year = $Data[0]['Year'];
        $Title = $Data[0]['Title'];
        $CrossNum = $Data[0]['CrossNum'];
        $CoverFaceFIO = $Data[0]['CoverFaceFIO'];
        $CoverFacePosition = $Data[0]['CoverFacePosition'];

        $Pub = date("d.m.Y", strtotime($Data[0]['PublishDate']));
		$Pic  = $this->get_issue_cover_file($Year, $Num);
		$Num = "№ $Num ($CrossNum), $TextNum $Year";
        $Titles = '';
        /*
        $SqlPub = "SELECT * FROM publications WHERE IssueId=$Id and ShowAnonceOnCover=1 ORDER BY PageNumber";
        //echo $SqlPub;
        $CoverArticles = $this->DB->get_simple_sql_result($SqlPub);
//        ;
        If (count($CoverArticles)>0)
        {
        	foreach ($CoverArticles as $CoverArticle)
        	{
               //print_r($CoverArticle);
               $Titles .= "— ".$CoverArticle['CoverAnonce']."<br>";
        	}
        }

		if ($Titles == '') {$Titles = 'Основные темы номера';} //$Removal1.$Removal2.$Removal3.$Removal4.$Removal5;

		$AfterTitles = $data[$count]['AditionText'];
		if ($AfterTitles != '') $AfterTitles = '<p class="right_block" style="text-align: left">'.$AfterTitles;
        */
		$Ret =
		'<h1 class="main">'.$Title.'</h1>'.
		'<p class="main">'.$Num.'</p>
		<p class="main">Выход в свет: '.$Pub.'</p>
		<p class="main">&nbsp;</p>
		<p class="right_block"></p>
		<h1 class="main">Основные темы номера:</h1>
		<p class="main">
			'.$Titles.'
		</p>
			'.$AfterTitles;

		return $Ret;
    }

	############################################################################

        /*
	function get_issue_id_by_yearnum($Year, $Num)
	{
		$Sql = "SELECT Id FROM issues WHERE Num=$Num and Year=$Year";
		if ($this->DB->get_count($Sql) == 1)
		{
			$Issue = $this->DB->get_simple_sql_result($Sql);
			$IssueId = $Issue[0]['Id'];
		}
		else {$IssueId = false;}
		return $IssueId;
	}
        */
	############################################################################


	function get_issue_content($IssueId)
	{
		$Sql = "SELECT c.*, r.Caption as RTitle
				FROM ".$this->tbl_articles." c, ".$this->tbl_rubrics." r
				WHERE (c.IssueId = $IssueId) and (r.Id = c.RubricatorId) and (c.IsShow = 1)
				ORDER BY c.Rank
				";
				//echo $Sql;
		/*
		$Sql = "SELECT *
				FROM ".$this->tbl_issues."
				WHERE IsShow = true AND EditionId=".$this->EditionId."
				ORDER BY Year DESC, Number DESC
				LIMIT 0, 6";


		$this->tbl_rubrics = $GLOBALS['tbl_rubrics'];
		$this->tbl_articles = $GLOBALS['tbl_articles'];
		$this->tbl_authors = $GLOBALS['tbl_authors'];

		*/
		$Ret = "<h1 class='main'>Содержание номера</h1>";
        $HTML = $Ret;

        //$Count = $this->MainDB->get_count($Sql)-1;
		$Ret .= '<table width="100%" class="main" cellspacing="0" cellpadding="0" >';
		$Articles = $this->DB->get_simple_sql_result($Sql);
		if (count($Articles)>0)
		{

			foreach($Articles as $Article)
			{
				//echo '<pre>'; print_r($Article); echo '</pre>';
				$Id = $Article['Id'];
				$Title = $Article['Title'];
				$Topic = $Article['Topic'];
				//if (trim($Caption)!='') {$Caption = "<br>".trim($Caption);}
				$PublicationType = ($Article['EPublication']==0)? 'Опубликовано в журнале' : 'Дополнительный материал для сайта';
				//$Rubrica = $this->DB->get_value('rubrics', $Data[$i]['RubricId'], 'Title');
				$Rubrica = $Article['RTitle'];
				$SqlAuthors = "
						SELECT a.AuthorId, p.F, p.I, p.O
						FROM ".$this->tbl_articles_authors." a
						INNER JOIN ".$this->tbl_authors." p ON a.AuthorId=p.Id
						WHERE a.ArticleId=$Id";
				$Authors = $this->DB->get_simple_sql_result($SqlAuthors);
				if ($Article['EPublication']==1)
				{
					$Title .= '<font color="#FFFFFF"><sup><font size="1">
					<span style="background-color: #A31911">&nbsp;на&nbsp;сайте&nbsp;</span></font></sup></font>';
				}
				$AuthorsStr = '';
				if (is_array($Authors))
				{
					foreach ($Authors as $Author)
					{
						if ($AuthorsStr!='') {$AuthorsStr .= ', ';}
						$AuthorsStr .= $Author['F']." ".$Author['I'].' '.$Author['O'];
					}
				}
				//if ($AuthorsStr != '') {if (trim($Caption)== '') {$Caption = '<br>'.$Caption;}}

                if ($LastRubrica!=$Rubrica)
                {
                	$LastRubrica = $Rubrica;

					$Ret .= '

				<tr>
					<td colspan="3" style="font-size: 3px; border-bottom-width: 2px;  bgcolor="#999999"">&nbsp;</td>
				</tr>
				<tr  bgcolor="#EEEEEE">
					<td valign="top" colspan="3" style="padding-left: 5px; padding-right: 5px;">
						<h2 class="main"><font color="#FFFFFF"><span style="background-color: #423E35">&nbsp;<span style="font-weight: 400">'.$Rubrica.'</span>&nbsp;</span></font></h2>
					</td>
				</tr>';

					$HTML .= '<h2 class="main"><font color="#FFFFFF"><span style="background-color: #423E35">&nbsp;<span style="font-weight: 400">'.$Rubrica.'</span>&nbsp;</span></font></h2>';
				}
//<font color="#FFFFFF"><sup><font size="1"><span style="background-color: #A31911">&nbsp;на сайте&nbsp;</span></font></sup></font>
				$Ret .= '

				<tr  bgcolor="#EEEEEE">
					<td valign="top" colspan="3" style="padding-left: 5px; padding-right: 5px;">
						<p class="main">
							<b>'.$Title.'</b><!-- &#8594;--><br>
							<i>'.$AuthorsStr.'</i>
							'.$Topic.'
						</p>
					</td>

				</tr>';
				//'<a name="'.$i.'"/>'.
                $HTML .= '
 						<p class="main">
							<b>'.$Title.'</b><!-- &#8594;--></p>
						<p class="main_tagline">';
				if ($AuthorsStr != '')
				{
					$HTML .= "<b>$AuthorsStr</b>";
					if (trim($Topic) != '') { $HTML .= '<br>';}
				}
				$HTML .= $Topic.'
						</p>';
				$SqlFiles = "SELECT * FROM files WHERE PublicationId=$Id ORDER BY Rank";
				$FilesCount = $this->DB->get_count($SqlFiles);
				$CurrentPageURI = $_SERVER['REQUEST_URI'];
				if ($FilesCount>0)
				{
				    $PubFiles = $this->DB->get_simple_sql_result($SqlFiles);
				    $HTML .= '<div id="ShowBlockCaption'.$i.'">'.'<p class="main" align="left"><a class="dotted" style="cursor:pointer; cursor: hand;"    onClick='."'javascript:ShowBlock($i);'".' onMouseOver='."'javascript:this.style.color = ".'"#BD2D2D"'.";'".' onMouseOut='."'javascript:this.style.color = ".'"#1F1C17"'.";'".' >Файлы для скачивания ('.$FilesCount.')</a>&nbsp;&#8594;</p></div>
				    <div id="Block'.$i.'" style="display: none;"><p class="main" align="left"><a class="dotted"  style="cursor:pointer; cursor: hand;"   onClick='."'javascript:HideBlock($i);'".' onMouseOver='."'javascript:this.style.color = ".'"#BD2D2D"'.";'".' onMouseOut='."'javascript:this.style.color = ".'"#1F1C17"'.";'".' >Скрыть файлы для скачивания ('.$FilesCount.')</a>&nbsp;&#8592;<br>&nbsp;</p>
					<table border="0" width="100%" cellspacing="0" cellpadding="0">';

				    foreach ($PubFiles as $PubFile)
				    {
						$FileName = $PubFile['Title'];
						//$FileLink = 'files/'.$PubFile['TranslitName'];
						$FileLink = $PubFile['NewFileName'];
						$ViewLink = 'http://docs.google.com/viewer?embedded=true&url=http://law.direktor.ru/'.$PubFile['NewFileName'];
						$HTML .= '

							<tr>
								<td width="4">&nbsp;</td>
								<td width="3" bgcolor="#C0C0C0">&nbsp;</td>
								<td width="11">&nbsp;</td>
								<td>
						<p class="main" style="text-align: left; margin-bottom: 6px; margin-top: 3px;"><b>
						<a href="'.$FileLink.'">'.$FileName.'</a>&nbsp;&#8594;</b></p>
								</td>
							</tr>';

				    }


				    $HTML .=
				    '</table>
				    </div><br>';

				    foreach ($PubFiles as $PubFile)
				    {
						$FileName = $PubFile['Title'];
						//$FileLink = 'files/'.$PubFile['TranslitName'];
						$FileLink = $PubFile['NewFileName'];
						$ViewLink = 'http://docs.google.com/viewer?embedded=true&url=http://law.direktor.ru/'.$PubFile['NewFileName'];
						$Ret .= '

				<tr  bgcolor="#EFEFEF" >
					<td valign="top"  style="padding-left: 5px; padding-right: 5px; border-top-width: 1px; border-top-style: dotted; border-color: silver;">
						<p class="main">
                             <a href="'.$FileLink.'">'.$FileName.'</a>
						</p>
					</td>
					<td valign="top" width="15px">&nbsp;</td>
					<td valign="middle" width="50px" align="center" style="border-top-width: 1px; border-top-style: dotted; border-color: silver;">
						<p class="comment_line" style="text-align: center;">
                             <a href="'.$FileLink.'">скачать</a><br>
                             <a href="'.$ViewLink.'" target="_blank">просмотреть</a>

						</p>
					</td>
				</tr>';

				    }

				}

				$Ret .= '
				<tr  bgcolor="#EEEEEE">
					<td valign="top" colspan="3" style="padding: 5px;">
						<p class="comment_line">
							'.$PublicationType.'
						</p>
					</td>
				</tr>
				<tr>
					<td colspan="3" style="font-size: 1px; border-top-width: 1px; border-top-style: dotted;  bgcolor="#999999"">&nbsp;</td>
				</tr>';
			}
			$Ret .= '
			</table>';
    	}

    	return $HTML;

    }

	############################################################################


	function get_archive_block($IssueId)
	{
		$EditionId = $this->EditionId;
		$Sql = "SELECT *
				FROM ".$this->tbl_issues."
				WHERE IsShow = true AND EditionId=$EditionId
				ORDER BY Year, Number";
		$IssuesData = $this->DB->get_simple_sql_result($Sql);
		$IssuesCount = count($IssuesData);
		if ($IssuesCount>0)
		{
			$i=0;
			foreach ($IssuesData as $IssueData)
			{
				if ($IssueId==$IssueData['Id']) {$CurrentNum = $i;}
				$i++;
			}
			if ($CurrentNum>0)
			{
				$PrevId = $IssuesData[$CurrentNum-1]['Id'];
				$PrevNum = $IssuesData[$CurrentNum-1]['Number'];
					$PrevNum = ($PrevNum<10) ? '0'.$PrevNum : $PrevNum;
				$PrevYear = $IssuesData[$CurrentNum-1]['Year'];
		   		$PrevPic  = $this->get_issue_cover_file($PrevYear, $PrevNum, 'thumb');
				$PrevAltText = $PrevNum;
				$PrevURL = "archive/$PrevYear/$PrevNum";
				$PrevNumberCode =
				'<p class="right_block" style="text-align: center">
					<a href="'.$PrevURL.'"><img border="0" src="'.$PrevPic.'" width="'.$this->Covers['thumb']['w'].'" height="'.$this->Covers['thumb']['h'].'" alt="'.$PrevAltText.'"></a>
				</p>
				<p class="right_block" style="text-align: left">&#8592;
				<a href="'.$PrevURL.'">№'.$PrevNum.', '.$PrevYear.'</a></p>';

			}
			if ($CurrentNum < ($IssuesCount-1))
			{
				$NextId = $IssuesData[$CurrentNum+1]['Id'];
				$NextNum = $IssuesData[$CurrentNum+1]['Number'];
					$NextNum = ($NextNum<10) ? '0'.$NextNum : $NextNum;
				$NextYear = $IssuesData[$CurrentNum+1]['Year'];
		   		$NextPic  = $this->get_issue_cover_file($NextYear, $NextNum, 'thumb');
				$NextAltText = $NextNum;
				$NextURL = "archive/$NextYear/$NextNum";
				$NextNumberCode =
				'<p class="right_block" style="text-align: center">
					<a href="'.$NextURL.'"><img border="0" src="'.$NextPic.'" width="'.$this->Covers['thumb']['w'].'" height="'.$this->Covers['thumb']['h'].'" alt="'.$NextAltText.'"></a>
				</p>
				<p class="right_block" style="text-align: left">
				<a href="'.$NextURL.'">№'.$NextNum.', '.$NextYear.'</a> &rarr;</p>';

			}


		}
		//$Foo = $this->MainDB->get_value($this->tbl_issues, $IssueId, 'NumberOverall')-1;
        $Ret = '
        <h1 class="right_block" style="text-align: left">Архив номеров &nbsp;</h1>
		<table border="0" width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td bgcolor="#FFFFFF" width="48%">'.$PrevNumberCode.'
				</td>
				<td bgcolor="#FFFFFF" width="48%">'.$NextNumberCode.'</td>
				</tr>
		</table>
		<hr>
		<p class="right_block" style="text-align: left">
			<a href="archive">Полный архив журнала</a> &rarr;</p>
';


/*

*/

		return $Ret;
    }

	############################################################################


	function get_prev_number($IssueId)
	{
		//echo "IssueId=".$IssueId;
		$Foo = $this->MainDB->get_value($this->tbl_issues, $IssueId, 'NumberOverall')-1;
		$Sql = "SELECT * FROM ".$this->tbl_issues." WHERE (NumberOverall = $Foo) AND (IsShow = 1)";
		//echo $Sql;
		$Count = $this->MainDB->get_count($Sql);

		if ($Count > 0)
		{
			$Data = $this->MainDB->get_simple_sql_result($Sql);
			$Id = $Data[0]['Id'];
	        $Num = $Data[0]['Number']; if ($Num != 10) $Num = '0'.$Num;
	        $Year = $Data[0]['Year'];
	   		$Pic  = $this->get_issue_cover_file($Year, $Num, 'thumb');
			$AltText = $Num." ".$Pub;
			$URL = "archive/$Year/$Num";

			$Ret =
			'<p class="right_block" style="text-align: center">
				<a href="'.$URL.'"><img border="0" src="'.$Pic.'" width="'.$this->Covers['thumb']['w'].'" height="'.$this->Covers['thumb']['h'].'" alt="'.$AltText.'"></a>
			</p>
			<p class="right_block" style="text-align: left">&#8592;
			<a href="'.$URL.'">№'.$Num.', '.$Year.'</a></p>';

		}
		return $Ret;
    }

	############################################################################


	function get_next_number($IssueId)
	{
		$Foo = $this->MainDB->get_value($this->tbl_issues, $IssueId, 'NumberOverall')+1;
		$Sql = "SELECT * FROM ".$this->tbl_issues." WHERE (NumberOverall = $Foo) AND (IsShow = 1)";
		$Count = $this->MainDB->get_count($Sql);

		if ($Count > 0)
		{
			$Data = $this->MainDB->get_simple_sql_result($Sql);
			$Id = $Data[0]['Id'];
	        $Num = $Data[0]['Number']; if ($Num != 10) $Num = '0'.$Num;
	        $Year = $Data[0]['Year'];
	   		$Pic  = $this->get_issue_cover_file($Year, $Num, 'thumb');
			$AltText = $Num." ".$Pub;
			$URL = "archive/$Year/$Num";
			$Ret =
			'<p class="right_block" style="text-align: center">
				<a href="'.$URL.'"><img border="0" src="'.$Pic.'" width="'.$this->Covers['thumb']['w'].'" height="'.$this->Covers['thumb']['h'].'" alt="'.$AltText.'"></a>
			</p>
			<p class="right_block" style="text-align: right">
			<a href="'.$URL.'">№'.$Num.', '.$Year.'</a>&nbsp;&#8594;</p>';

		}
		return $Ret;
    }

	############################################################################


	function ShowFaceOnTheCoverPage($id, $part)
	{
/*		$sql = "SELECT *
				FROM issue
				WHERE Id = $id";
		$data = $this->db->GetSimpleSQLResult($sql);
        $Num = $data[0]['Num']; if ($Num != 10) $Num = '0'.$Num;
        $Year = $data[0]['Year'];
        $Title = $data[0]['Title'];
        $CrossNum = $data[0]['CrossNum'];
        $CoverFaceFIO = $data[0]['CoverFaceFIO'];
        $CoverFacePosition = $data[0]['CoverFacePosition'];
        $CoverFaceText = $data[0]['CoverFaceText'];

		$Pic = "../data/covers/$Year$Num.jpg";
		$AltText = "Журнал $Title \n№ $Num ($CrossNum), $Year год";

		switch ($part) {
		    case "cover": echo "<img border='0' src='$Pic' width='162' height='232'>";
	        break;
		    case "text": echo $CoverFaceText;
	        break;
       	    case "title":
       	    {
echo <<<END
	<h2 class="main"><font color="#FFFFFF"><span style="background-color: #423E35">&nbsp; <span style="font-weight: 400"> Лицо с обложки </span>&nbsp; </span></font></h2>
	<h1 class="main">$CoverFaceFIO</h1>
	<p class="main">$CoverFacePosition</p>
END;
			}
	        break;
		}
*/    }

	############################################################################

	function show_archive()
	{
		$EditionId = $this->EditionId;
		$Sql = "SELECT *
				FROM ".$this->tbl_issues."
				WHERE IsShow = true AND EditionId=$EditionId
				ORDER BY Year, Number";
		//echo $Sql;
		$Count = $this->DB->get_count($Sql);
		$Data = $this->DB->get_simple_sql_result($Sql);
        $Ret = "<h1  class='main'>Архив номеров</h1>";
		for ($i = $Count-1; $i >= 0; $i--)
		{
			$Id = $Data[$i]['Id'];
	        $Title = $Data[$i]['Title'];
	        $Num = $Data[$i]['Number']; if ($Num != 10) $Num = '0'.$Num;
	        $CrossNum = $Data[$i]['NumberOverall'];
//	        $NumCaption = $Data[$i]['NumCaption'];
	        $Year = $Data[$i]['Year'];
			$IsShow = $Data[$i]['IsShow'];
			$Pub = date("d.m.Y", strtotime($Data[$i]['PublishDate']));

			$Element = "c".$i;
			$AltText = "$Title \n№ $Num ($CrossNum), $Year";
			$Caption = "$Title <br>№ $Num ($CrossNum), $Year";
			$Url = "archive/$Year/".$Data[$i]['Number'];
			$SmallPic  = $this->get_issue_cover_file($Year, $Num, 'thumb');

//echo <<<END
			$Ret .= '
			<table border="0" width="100%" cellpadding="0" style="border-collapse: collapse">
				<tr>
					<td>
						<h2 class="main"><br><a href="'.$Url.'">
						<img border="0" src="'.$SmallPic.'" width="'.$this->Covers['thumb']['w'].'" height="'.$this->Covers['thumb']['h'].'"></a>

						</h2>
					</td>
					<td valign="middle">
						<h2 class="main">'.$Caption.'</h1>
						<p class="main">Выход в свет: '.$Pub.'</p>
						<p class="main"><a href="'.$Url.'">Содержание номера</a> &#8594;</td>
					</tr>
			</table>';

//END;
		}
		return $Ret;
	}

	############################################################################

	function get_rss_data()
	{

		$sql = "SELECT *
				FROM issues
				WHERE IsShow = true
				ORDER BY CrossNum DESC
				";
        //echo $sql.'<br>';
		$count = $this->DB->get_count($sql);
		$data = $this->DB->get_simple_sql_result($sql);
        $RSSData = array();
		for ($i = 0; $i < $count; $i++)
		{
			$Id = $data[$i]['Id'];
	        $Title = $data[$i]['Title'];
	        $Num = $data[$i]['Num']; if ($Num != 10) $Num = '0'.$Num;
	        $CrossNum = $data[$i]['CrossNum'];
	        $NumCaption = $data[$i]['NumCaption'];
	        $Year = $data[$i]['Year'];
			$Pub = date("d.m.Y", strtotime($data[$i]['OutDate']));

			$AltText = "$Title № $Num ($CrossNum), $Year";

			$sql = "SELECT c.Id, c.Title as ITitle, c.Caption, r.Title as RTitle
				FROM publications c
				LEFT JOIN rubrics r ON r.Id = c.RubricId
				WHERE (c.IssueId = $Id)
				ORDER BY Rank
				";
			//echo $Title.'<br>';
			//echo $sql.'<br>';
			$idata = $this->DB->get_simple_sql_result($sql);
			$count2 = $this->DB->get_count($sql);
            $Topic='';
			for ($j = 0; $j < $count2; $j++) //$count-1;
			{
				$IId = $idata[$j]['Id'];
	            $RubricaId = $idata[$j]['RubricaId'];
	            $ITitle = $idata[$j]['ITitle'];
	            $RTitle = $idata[$j]['RTitle'];
	            $Caption = $idata[$j]['Caption'];
	            //$Authors = $idata[$j]['Authors'];
	            $Rank = $idata[$j]['Rank'];

	            if ($RTitle == $CurRubrica) {$RTitle = '';}
	            else
	            {
	            	$CurRubrica = $RTitle;
	            }

                $sql_authors = "SELECT p.F, p.I, p.O
                				FROM authors a
                				INNER JOIN persons p ON p.Id=a.PersonId
                				WHERE a.PublicationId=$IId
                				ORDER BY a.Rank";
                $AuthorsData = $this->DB->get_simple_sql_result($sql_authors);
                if (count($AuthorsData)>0)
                {
                	$Authors = '';
                	foreach ($AuthorsData as $AData)
                	{
                		if ($Authors != '') {$Authors .= ', ';}
                		$Authors .= '<i><b>'.$AData['F'].' '.$AData['I'][0].'.'.$AData['F'][0].'.'.'</b></i>';
                	}
                }

	            $Topic .= "<b>$RTitle</b> <br> $ITitle <br> $Authors <br><br>";
			}



			$RSSData[$i]['news_id']=$data[$i]['Id'];
			$RSSData[$i]['news_title']=iconv("Windows-1251","UTF-8",trim($AltText));
			$RSSData[$i]['news_text']=iconv("Windows-1251","UTF-8",$Topic);
			$RSSData[$i]['news_date']=$data[$i]['OutDate'];
			$RSSData[$i]['news_url']=htmlspecialchars("http://direktor.ru/issue.htm?id=".$data[$i]['Id']);
		}
        return $RSSData;

	}

	############################################################################

}

?>