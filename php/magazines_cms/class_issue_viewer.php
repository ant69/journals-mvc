<?php

include_once "class_form_simple.php";
include_once $GLOBALS['lib_social_share'];
include_once $GLOBALS['lib_ratings'];
include_once $GLOBALS['lib_comments'];

################################################################################

class issue_viewer_class
{
	function issue_viewer_class($DM) # конструктор
	{
		$this->UserId = $DM->User->UserId;

		//$this->DB = $DM->MainDB;
		$this->DB = $DM->DB;
//		$this->MainDB = $DM->MainDB;
		$this->Covers = $GLOBALS['Covers'];

		$this->tbl_editions = $GLOBALS['tbl_editions'];
		$this->tbl_issues = $GLOBALS['tbl_issues'];
		$this->tbl_rubrics = $GLOBALS['tbl_rubrics'];
		$this->tbl_articles = $GLOBALS['tbl_articles'];
		$this->tbl_authors = $GLOBALS['tbl_authors'];
		$this->tbl_articles_authors = $GLOBALS['tbl_articles_authors'];
		$this->tbl_comments = $GLOBALS['tbl_comments'];
		$this->tbl_materials_stat = $GLOBALS['tbl_materials_stat'];
		$this->tbl_files = $GLOBALS['tbl_files'];

		$this->EditionId = $DM->EditionId;
		$this->EditionTitle = $this->DB->run('SELECT Title FROM '.$this->tbl_editions.' WHERE Id=? LIMIT 0, 1', array($this->EditionId))->fetchColumn();

		$this->rating = new ratings_class($this->UserId);

		$CurUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$CurUrlParts = parse_url($CurUrl);
		$this->CurrentPageUrl = $CurUrlParts['path'];

		//$this->file = new FileManagment;
	}

	############################################################################


	function show_bookcase($shift = 0)
	{
		$Sql = 'SELECT *
				FROM '.$this->tbl_issues.'
				WHERE IsShow = true AND EditionId=?
				ORDER BY PublishDate DESC
				LIMIT 0, 6';

		//$Count = $this->DB->get_count($Sql);
		$Data = $this->DB->run($Sql, array($this->EditionId))->fetchAll();

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

		$EditionTitle = $this->EditionTitle;
		for ($i = count($Data)-1; $i >= 0; $i--) {

			$Id = $Data[$i]['Id'];
	        $Title = $Data[$i]['Title'];
	        $Num = $Data[$i]['Number']; if ($Num != 10) $Num = '0'.$Num;
	        $CrossNum = $Data[$i]['NumberOverall'];
	        //$NumCaption = $Data[$i]['NumCaption'];
	        $Year = $Data[$i]['Year'];
			$IsShow = $Data[$i]['IsShow'];

			$Element = "c".$i;
			$CaptionText = $AltText = "$EditionTitle \n$Title";
			//$AltText = "№ $Num ($CrossNum), $Year год";
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

	function get_last_issue_id()
	{
		$Sql = 'SELECT Id
				FROM '.$this->tbl_issues.'
				WHERE IsShow = true AND EditionId=?
				ORDER BY PublishDate DESC
				LIMIT 0, 1';

		$Data = $this->DB->run($Sql, array($this->EditionId))->fetch();
		$Id = $Data['Id'];
		return $Id;
	}

	############################################################################
	function show_current_issue_frame()
	{
        $Ret = '';
        $Sql = 'SELECT *, YEAR(PublishDate) as CurYear
				FROM '.$this->tbl_issues.'
				WHERE IsShow = true AND EditionId=?
				ORDER BY PublishDate DESC
				LIMIT 0, 1';

		//$Count = $this->MainDB->get_count($Sql)-1;
        $IssueData = $this->DB->run($Sql, array($this->EditionId));
		while ($Data = $IssueData->fetch()) {

            $Id = $Data['Id'];
            $Num = $Data['Number'];
            if ($Num != 10) $Num = '0' . $Num;
            $Year = $Data['Year'];
            $CurYear = $Data['CurYear'];
            $Title = $Data['Title'];
            $CoverPic = $this->get_issue_cover_file($Year, $Num, 'middle');

            $Num = "№ " . $Data['Number'] . " (" . $Data['NumberOverall'] . ")";
            $Pub = "Выходит <br>" . date("d.m.Y", strtotime($Data['PublishDate']));

            $AltText = $Num . " " . $Pub;
            $URL = "archive/$Year/" . $Data['Number'];
            //return "12345";

            $Ret .= '<h1 class="right_block">Свежий номер ' . $Title . '</h1>
                        <p class="right_block" style="text-align: center">
                        <a href="' . $URL . '">
                        <img border="0" src="' . $CoverPic . '" width="' . $this->Covers['middle']['w'] . '" height="' . $this->Covers['middle']['h'] . '"></a>
                        <p class="right_block" style="text-align: left">
                        <a href="' . $URL . '">Содержание этого номера</a> &#8594;<br>';
        }
		return $Ret;

	}

	############################################################################

	function get_issue_cover_file($Year, $Num, $Type='')
	{
		$Pic = $this->DM->Base."/databank/covers/$Year/$Num/$Type";
		return $Pic;
    }

	############################################################################

	function get_issue_cover($Id) {
		$Sql = 'SELECT *
				FROM '.$this->tbl_issues.'
				WHERE Id = ?';

		if ($Data = $this->DB->run($Sql, array($Id))->fetch()) {
            $Num = $Data['Number'];
            if ($Num != 10) $Num = '0' . $Num;
            $Year = $Data['Year'];
            $Title = $Data['Title'];
            $EditionTitle = $this->EditionTitle;

            $Pic = $this->get_issue_cover_file($Year, $Num, 'base');
            $AltText = "$EditionTitle \n$Title";
            $Link = "archive/$Year/$Num";

            $Ret =
                '<div align="center"> <a href="' . $Link . '">
                <img border="0" src="' . $Pic . '" width="' . $this->Covers['base']['w'] . '" height="' . $this->Covers['base']['h'] . '" alt="' . $AltText . '" Title="' . $AltText . '" style="margin: 10px;">
                </a></div>';
            return $Ret;
        }
		return false;
    }

	############################################################################

	function get_issue_details($Id)
	{
		if ($Data = $this->DB->run('SELECT * FROM '.$this->tbl_issues.' WHERE Id=?', array($Id))->fetch()) {
            $Num = $Data['Number'];
            if ($Num != 10) $Num = '0' . $Num;
            $Year = $Data['Year'];
            $Title = $Data['Title'];
            $CrossNum = $Data['CrossNum'];
            $EditionDescription = $Data['Description'];

            $Pub = date("d.m.Y", strtotime($Data['PublishDate']));

            $EditionData = $this->DB->run('SELECT Slogan, Description FROM '.$this->tbl_editions.' WHERE Id=?', array($this->EditionId))->fetch();

            if (empty($EditionDescription)) {
                $EditionDescription = $EditionData['Description'];
            }
            $EditionSlogan = $EditionData['Slogan'];

            $Ret =
                '<h1 class="main">' . $this->EditionTitle . '<br>' .
                $Title . '</h1>
            <p class="main">Выход в свет: ' . $Pub . '</p>
            <p class="right_block"></p>
            <!--<h1 class="main">О журнале</h1>
            <p class="main">«<i><b>' . $EditionSlogan . '»</b></i></p>-->
            <p class="main">' . strip_tags(htmlspecialchars_decode($EditionDescription)) . '</p>';
            return $Ret;
        }
        return false;
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

	function translit($CyrStr, $Len=100)
	{

		//$CyrStr = str_replace(chr(160), chr(32), $CyrStr);

		$trans = array
		(
			"а" => "a", "б" => "b", "в" => "v", "г" => "g",
			"д" => "d", "е" => "e", "ё" => "e", "ж" => "zh",
			"з" => "z", "и" => "i", "й" => "y", "к" => "k",
			"л" => "l", "м" => "m", "н" => "n", "о" => "o",
			"п" => "p", "р" => "r", "с" => "s", "т" => "t",
			"у" => "u", "ф" => "f", "х" => "kh", "ц" => "ts",
			"ч" => "ch", "ш" => "sh", "щ" => "shch", "ы" => "y",
			"э" => "e", "ю" => "yu", "я" => "ya", "А" => "A",
			"Б" => "B", "В" => "V", "Г" => "G", "Д" => "D",
			"Е" => "E", "Ё" => "E", "Ж" => "Zh", "З" => "Z",
			"И" => "I", "Й" => "Y", "К" => "K", "Л" => "L",
			"М" => "M", "Н" => "N", "О" => "O", "П" => "P",
			"Р" => "R", "С" => "S", "Т" => "T", "У" => "U",
			"Ф" => "F", "Х" => "Kh", "Ц" => "Ts", "Ч" => "Ch",
			"Ш" => "Sh", "Щ" => "Shch", "Ы" => "Y", "Э" => "E",
			"Ю" => "Yu", "Я" => "Ya", "ь" => "", "Ь" => "",
			"ъ" => "", "Ъ" => "", " " => "_", "№" => "N",
			"#" => "N", "«" => "", "»" => "", "-" => "",
			"—" => "", ":" => "", ";" => "", "?" => "",
			"." => "", "," => "", "+" => "_plus_"
	    );

		if(preg_match("/[а-яА-Я ]/", $CyrStr)) { $Ret = strtr($CyrStr, $trans); }
		else { $Ret = $CyrStr; }

		while (strpos($Ret, '__')!==false) { $Ret = str_replace('__', '_', $Ret) ;}

		if (strlen($Ret)>$Len) { $Ret = substr($Ret, 0, $Len); }
		/*
		if (in_array($Ret, $this->TranslitHistory))
		{
			$i=2;
			$RetLength = strlen($Ret);
			while(in_array($Ret, $this->TranslitHistory))
			{
				$Ret=substr_replace($Ret, "$i", $RetLength-1, 1);
				$i++;
			}
		}
		$this->TranslitHistory[] = $Ret;
		*/
		return $Ret;
	}

	############################################################################

	function get_issue_page_meta($IssueId)
	{
        $Ret = array();
        $IssueData = $this->DB->run('SELECT Title, Description, Year FROM '.$this->tbl_issues.' WHERE Id=?', array($IssueId))->fetch();
        $IssueYear = $IssueData['Year'];
        $IssueDescription = $IssueData['Description'];
        $IssueTitle = $IssueData['Title'];
        $EditionData = $this->DB->run('SELECT Title FROM '.$this->tbl_editions.' WHERE Id=?', array($this->EditionId))->fetch();
        $EditionName = $EditionData['Title'];

		$Ret['title'] = "Содержание номера ".$IssueTitle;
		$Ret['description'] = trim($IssueDescription)>'' ? $IssueDescription : "Содержание номера «$EditionName,  $IssueTitle"."»";
		$Ret['keywords'] = "$IssueTitle, $IssueYear, содержание номера";
		return $Ret;
    }

	############################################################################

	function get_issue_content($IssueId)
	{
		$IssueData = $this->DB->run('SELECT Year, Number FROM '.$this->tbl_issues.' WHERE Id=?', array($IssueId))->fetch();
	    $IssueYear = $IssueData['Year'];
	    $IssueNum = $IssueData['Number'];
		$EditionData = $this->DB->run('SELECT Title FROM '.$this->tbl_editions.' WHERE Id=?', array($this->EditionId))->fetch();
	    $EditionName = $EditionData['Title'];

		$Sql = 'SELECT c.*, r.Caption as RTitle
				FROM '.$this->tbl_articles.' c, '.$this->tbl_rubrics.' r
				WHERE (c.IssueId = ?) and (r.Id = c.RubricatorId) and (c.IsShow = 1)
				ORDER BY c.Rank
				';
				//echo $Sql;
        $HTML = "<h1 class='main'>Содержание номера</h1>";
        $LastRubrica = '';
        //$Count = $this->MainDB->get_count($Sql)-1;
		//$Ret .= '<table width="100%" class="main" cellspacing="0" cellpadding="0" >';
		$Articles = $this->DB->run($Sql, array($IssueId));
		$this->TranslitHistory = array();
        while($Article = $Articles->fetch())
        {
            //echo '<pre>'; print_r($Article); echo '</pre>';
            $Id = $Article['Id'];
            $Title = $Article['Title'];
            $Topic = $Article['Topic'];
            $PublicationType = ($Article['EPublication']==0)? 'Опубликовано в журнале' : 'Дополнительный материал для сайта';
            $Rubrica = $Article['RTitle'];
            $TranslitTitle = $this->translit($Title, 50);
            $TitleLink = "archive/$IssueYear/$IssueNum/$TranslitTitle";

            $ShowOnSiteFlag = $Article['IsFree'];
            $FilesCount = 0;
            if ($ShowOnSiteFlag) {
                $Title .= '&nbsp;<sup><span size="1" color="#FFFFFF" style="background-color: #A31911">
                &nbsp;на&nbsp;сайте&nbsp;</span></sup>';
                if ($Article['FilePDF']>0) {$FilesCount ++;}
                if ($Article['FileDOC']>0) {$FilesCount ++;}
                if ($Article['FileEPUB']>0) {$FilesCount ++;}
            }
            //$Title .= " ($TranslitTitle)";
            $AuthorsStr = '';
            $SqlAuthors = '
                    SELECT a.AuthorId, p.F, p.I, p.O
                    FROM '.$this->tbl_articles_authors.' a
                    INNER JOIN '.$this->tbl_authors.' p ON a.AuthorId=p.Id
                    WHERE a.ArticleId=?';
            $Authors = $this->DB->run($SqlAuthors, array($Id));
            while ($Author= $Authors->fetch()) {
                if ($AuthorsStr!='') {$AuthorsStr .= ', ';}
                $FIO = $Author['F']." ".$Author['I'].' '.$Author['O'];
                $FIOTranslit = $this->translit($FIO);
                $AuthorsStr .= "<a href='archive/persons/$FIOTranslit'>$FIO</a>";
            }
            if ($LastRubrica!=$Rubrica) {
                $LastRubrica = $Rubrica;

                $RubricaTranslit = $this->translit($Rubrica);
                $HTML .= '<p class="article_rubrica"><font class="grey_ribbon"><a href="archive/rubrics/'.$RubricaTranslit.'">&nbsp;'.$Rubrica.'&nbsp;</a></font></p>';
            }
            $HTML .= "<p class='title_link'><a href='$TitleLink'>".$Title."</a></p>";
            if ($AuthorsStr != '')	{	$HTML .= "<p class='autor'>$AuthorsStr</p>";	}
            if (trim($Topic) != '') { $HTML .= '<p class="topic">'.$Topic;}

            $ViewsCount = $this->get_article_views($Id);
            $CommentsCount = $this->get_article_comments_count($Id);

            $HTML .= '</p><p class="article_tagline"><a href="'.$TitleLink.'#comments">Комментарии</a>:&nbsp;'.$CommentsCount.'&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.$TitleLink.'">Просмотры</a>:&nbsp;'.$ViewsCount.'&nbsp;&nbsp;|&nbsp;&nbsp;Файлы:&nbsp;'.$FilesCount.'</p>';
        }
        $SB = new social_share_class("$EditionName №$IssueNum $IssueYear");
        $SocialButtons = $SB->Code;
        $HTML .= '<p class="comment_text" align="left" style="text-align: right"><br>'.$SocialButtons;

    	return $HTML;

    }

	############################################################################

	function show_open_articles($PageNum=1)	{
		$RecsOnPage = 10;

		$SqlCheck = '
			SELECT a.Id as Id, a.PublishDate as PublishDate
			FROM '.$this->tbl_articles.' a, '.$this->tbl_issues.' i
			WHERE a.IsFree = 1 and a.IssueId=i.Id and i.EditionId=?';
			//echo $SqlCheck, $this->EditionId;
		$AllDigestRecs = $this->DB->run($SqlCheck, array($this->EditionId))->fetchAll();
		$OverallCount = count($AllDigestRecs);
		$PagesCount = $RecsOnPage>0 ? ceil($OverallCount/$RecsOnPage) : 1;
		$PageNum = $PageNum<1 ? 1 : $PageNum;
		$PageNum =($PageNum>$PagesCount) ? $PagesCount : $PageNum;
		$StartRec = ($PageNum-1)*$RecsOnPage;

		//Строим ленту навигации по страницам дайджеста
		// ----------------------------------------
		$Nav = '';
		if ($PagesCount>1) {
			//Записи не умещаются на одной странице
			if ($PageNum>1) {
				// Если текущая страница - не первая, рисуем навигацию в начало.
				$Nav .= '<a class="dotted" href="articles/page/1">самые новые записи</a>&nbsp;&nbsp;<a class="dotted" href="articles/page/'.((int)$PageNum - 1).'">страница '.((int)$PageNum - 1).'</a>&nbsp;&nbsp;&nbsp;&nbsp;';
			}

			$Nav .="<b>страница $PageNum (из $PagesCount)</b>";
			if ($PageNum<$PagesCount) {
				// Если текущая страница - не первая, рисуем навигацию в начало.
				$Nav .= '&nbsp;&nbsp;&nbsp;&nbsp;<a class="dotted" href="articles/page/'.((int)$PageNum + 1).'">страница '.((int)$PageNum + 1).'</a>&nbsp;&nbsp;&nbsp;<a class="dotted" href="articles/page/'.$PagesCount.'">самые ранние записи</a>';
			}
			$Nav = '<p class="main"  style="text-align: center; padding-top: 14px;">'.$Nav.'</p>';
		}
		// ----------------------------------------

		$Ret = '
			<table cellpadding="0px" cellspacing="0px" border="0" width="100%">
				<tr>
					<td width="20%">
						<h1 class="main">Статьи&nbsp;в&nbsp;свободном&nbsp;доступе</h1>
					</td>
					<td width="80%">
						<p class="main" style="text-align: right; padding-top: 14px;">
							<a class="dotted" href="articles">Статьи</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a class="dotted" href="articles">Популярные</a>

						</p>
					</td>
				</tr>
			</table>
            <hr>
				';
        $Sql = '
			SELECT a.Id, a.Title, a.PublishDate, a.Topic, "article" AS RecType, a.IssueId as SourceId, a.RubricatorId as TagId,
			      i.Number as SourceNumber, i.Year as SourceYear, i.Title as SourceTitle
			FROM '.$this->tbl_articles.' a, '.$this->tbl_issues.' i
			WHERE a.IsFree = 1 and a.IssueId=i.Id and i.EditionId=?
			ORDER BY PublishDate DESC
			LIMIT '.$StartRec.', '.$RecsOnPage;
		$DigestRecs = $this->DB->run($Sql, array($this->EditionId))->fetchAll();
		if (count($DigestRecs)>0) {
			//$Ret .= '1';
//			$this->TranslitHistory = array();
			foreach($DigestRecs as $Digest) {
				//echo '<pre>'; print_r($Article); echo '</pre>';
				$Id = $Digest['Id'];
				$Title = $Digest['Title'];
				$Topic = $Digest['Topic'];
				$RecType = $Digest['RecType'];
				$PubDate = date('d.m.Y', strtotime($Digest['PublishDate']));
				$SourceId = $Digest['SourceId'];
				$TagId = $Digest['TagId'];
				$AuthorsStr='';
				switch ($RecType) {
					case 'article': {
						$Num = $Digest['SourceNumber'];
						$Year = $Digest['SourceYear'];
						$IssueTitle = $Digest['SourceTitle'];
						$Source = $this->EditionTitle.", ".$IssueTitle;
						$SourceLink = "<a href='archive/$Year/$Num'>$Source</a>";
						$PubLink = "archive/$Year/$Num/".$this->translit($Title, 50);
		       			$ViewsCount = $this->get_article_views($Id);
		       			$CommentsCount = $this->get_article_comments_count($Id);

						$RatingCode = $this->rating->get_rating_code('article', $Id);

						$SqlAuthors = '
								SELECT a.AuthorId, p.F, p.I, p.O
								FROM '.$this->tbl_articles_authors.' a
								INNER JOIN '.$this->tbl_authors.' p ON a.AuthorId=p.Id
								WHERE a.ArticleId=?';
						$Authors = $this->DB->run($SqlAuthors, array($Id));
						$AuthorsStr = '';
						if (is_array($Authors)) {
							foreach ($Authors as $Author) {
								if ($AuthorsStr!='') {$AuthorsStr .= ', ';}
								$FIO = $Author['F']." ".$Author['I'][0].'. '.$Author['O'][0].'.';
								$FIOTranslit = $this->translit($Author['F']." ".$Author['I'].' '.$Author['O']);
								$AuthorsStr .= "<a href='archive/persons/$FIOTranslit'>$FIO</a>";
							}
							$AuthorsStr .= "&nbsp;&nbsp;";
						}
						break;
					}
				}
                $Ret .= '<div class="one_in_digest"><p class="article_title_in_digest">'.$AuthorsStr.'<a href="'.$PubLink.'">'.$Title.'</a></p><p class="article_tagline_in_digest plaski"><font style="background-color: #868686";>&nbsp;'.$SourceLink.'&nbsp;</font></p>';

				$Ret .= '<p class="article_tagline_in_digest"><span>'.$PubDate.'</span>&nbsp;&nbsp;Просмотры:&nbsp;'.$ViewsCount.'&nbsp;&nbsp;<a href="'.$PubLink.'#comments">Комментарии</a>:&nbsp;'.$CommentsCount.'&nbsp;'.$RatingCode.'</p>';
				if ((trim($Topic) != '') AND (substr_count($Topic, 'class=')==0)) {
					$Ret .= '<p class="digest_text"><a href="'.$PubLink.'">'.$Topic.'</a></p>';
				} elseif (trim($Topic) != '') {
					$Ret .= '<a class="digest_text topic" href="'.$PubLink.'">'.$Topic.'</a>';
				}
				$Ret .= '</div>';
			}
    	}

	   	return $Ret.$Nav;

    }

	############################################################################

	function get_article_id_by_translit($IssueId, $ArticleTranslit) {
		$Sql = 'SELECT a.Id, a.Title FROM '.$this->tbl_articles.' a WHERE (a.IssueId = ?)';
		$ArticleId = false;
		$Articles = $this->DB->run($Sql, array($IssueId));
		if (count($Articles)>0) {
			$this->TranslitHistory = array();
			foreach($Articles as $Article) {
				if ($ArticleTranslit == $this->translit($Article['Title'], 50)) {
					$SelArticle = $Article;
					$ArticleId = $Article['Id'];
					break;
				}
			}
		}
		return $ArticleId;
    }

	############################################################################

	function get_author_id_by_translit($AuthorTranslit){
		$Authors = $this->DB->run('SELECT Id, F, I, O FROM '.$this->tbl_authors)->fetchAll();
		if (count($Authors)>0) {
			$this->TranslitHistory = array();
			foreach($Authors as $Author) {
				$FIO = $Author['F'].' '.$Author['I'].' '.$Author['O'];
				if ($AuthorTranslit == $this->translit($FIO)) {
					return $Author['Id'];
					break;
				}
			}
		}
		return false;
    }

	############################################################################

	function get_rubric_id_by_translit($RubricTranslit)	{
		$Sql = 'SELECT r.Id, r.Caption FROM '.$this->tbl_rubrics.' r WHERE r.EditionId=?';

		$RubricId = false;
		$Rubrics = $this->DB->run($Sql, array($this->EditionId))->fetchAll();
		if (count($Rubrics)>0) {
			//echo $AuthorTranslit.'<br>';
			$this->TranslitHistory = array();
			foreach($Rubrics as $Rubric) {
				if ($RubricTranslit == $this->translit($Rubric['Caption'])) {
					$RubricId = $Rubric['Id'];
					break;
				}
			}
		}
		return $RubricId;
    }

	############################################################################

	function get_article_page_meta($ArticleId) {
		$Meta = $this->DB->run('SELECT a.Title as title, CONCAT(a.Title, ", ", a.Description) as description, a.Keywords as keywords 
		FROM '.$this->tbl_articles.' a WHERE (a.Id = ?)', array($ArticleId))->fetch();
		return $Meta;
    }

	############################################################################

	function get_article($IssueId, $ArticleTranslit) {
		$ArticleId = $this->get_article_id_by_translit($IssueId, $ArticleTranslit);
		if ($ArticleId>0) {
            //Фиксируем в статистике факт очередного открытия статьи
            $InsertSql = 'INSERT INTO '.$this->tbl_materials_stat.' (MaterialId, MaterialCategory, ActivityType, IP, UserId, SessionId) VALUES (?, ?, ?, ?, ?, ?)';
            //echo $InsertSql;
            $this->DB->run($InsertSql, array($ArticleId, 3, 0, $_SERVER['REMOTE_ADDR'], $this->UserId, $_SESSION['SessionID']));


            $Sql = 'SELECT a.*, r.Caption as Rubric FROM '.$this->tbl_articles.' a, '.$this->tbl_rubrics.' r WHERE (a.Id = ? AND r.Id=a.RubricatorId)';
            //echo $ArticleId, $Sql;

            $A = $this->DB->run($Sql, array($ArticleId))->fetch();
            //Если надо сохранить комментарий, то он сохраняется при создании экземпляра класса
            $comments = new comments_class($A['Title']);

            if ($IssueData = $this->DB->run('SELECT i.Year, i.Number, i.PublishDate, e.Title as EditionName FROM '.$this->tbl_issues.' i, '.$this->tbl_editions.' e
                    WHERE i.EditionId=e.Id AND i.Id=?', array($IssueId))->fetch()) {
                $IssueYear = $IssueData['Year'];
                $IssueNum = $IssueData['Number'];
                $EditionName = $IssueData['EditionName'];
            }
            $IssueContent = "<a href='archive/$IssueYear/$IssueNum'>$EditionName, №$IssueNum $IssueYear г.</a>";
            $PubDateText = date('d.m.Y', strtotime($IssueData['PublishDate']));
            $Rubric = $A['Rubric'];
            $ViewsCount = $this->get_article_views($ArticleId);
            $CommentsCount = $this->get_article_comments_count($ArticleId);


            $SqlAuthors = 'SELECT *
                            FROM '.$this->tbl_articles_authors.' aa
                            INNER JOIN '.$this->tbl_authors.' a ON a.Id=aa.AuthorId
                            WHERE aa.ArticleId=?';
            $Authors = $this->DB->run($SqlAuthors, array($ArticleId))->fetchAll();
            $AuthorsCaption = '';
            if (count($Authors)>0) {
                foreach ($Authors as $Author) {
                    $AuthorsCaption .= $AuthorsCaption=='' ? '' : ', ';
                    $FIO = $Author['F'].' '.$Author['I'].' '.$Author['O'];
                    $FIOTranslit = $this->translit($FIO);
                    $AuthorsCaption .= '<a href="archive/persons/'.$FIOTranslit.'">'.$FIO.'</a>';
                }
                $AuthorsCaption = "<b>Авторы:</b> ".$AuthorsCaption;
            }
            $FilesCaption = '';
            $ArticlesPath = $GLOBALS['DatabankArticlesPath'];
            if ($A['FilePDF']>0 and $A['IsFree']) {
                $FilesCaption .= "<br><span style='background-color: #990000; color: white;'><b>&nbsp;pdf&nbsp;</b></span> <a href='databank/articles/$ArticleId/pdf'>скачать</a>&nbsp;&rarr; (".$A['FilePDF']." байт)";
            }
            if ($A['FileDOC']>0 and $A['IsFree']) {
                if(file_exists($ArticlesPath.$ArticleId.".doc")){
                    $FilesCaption .= "<br><span style='background-color: #004499; color: white;'><b>&nbsp;doc&nbsp;</b></span> <a href='databank/articles/$ArticleId/doc'>скачать</a>&nbsp;&rarr; (".$A['FileDOC']." байт)";
                } else {
                    $FilesCaption .= "<br><span style='background-color: #004499; color: white;'><b>&nbsp;doc&nbsp;</b></span> <a href='databank/articles/$ArticleId/docx'>скачать</a>&nbsp;&rarr; (".$A['FileDOC']." байт)";
                }
            }
            if ($A['FileEPUB']>0 and $A['IsFree']) {
                $FilesCaption .= "<br><span style='background-color: #006600; color: white;'><b>&nbsp;epub&nbsp;</b></span> <a href='databank/articles/$ArticleId/epub'>скачать</a>&nbsp;&rarr; (".$A['FileEPUB']." байт)";
            }
            if ($FilesCaption>'') {
                $FilesCaption = "</p><p class='right_block'><b>Файлы материала:</b> ".$FilesCaption;
            }
            // Дополнительные файлы
            $SqlAddFiles = 'SELECT *
                            FROM '.$this->tbl_files.'
                            WHERE MaterialId=? and MaterialType="article"
                            ORDER BY Rank';
            $AddFiles = $this->DB->run($SqlAddFiles, array($ArticleId))->fetchAll();
            $AddFilesCaption = '';
            if (count($AddFiles)>0) {
                foreach ($AddFiles as $AddFile) {
                    $AddFilesCaption .= $AddFilesCaption=='' ? '' : '<br>';
                    $FId = $AddFile['Id'];
                    $NameInBank = $AddFile['NameInBank'];
                    $fileNameParts = explode('.', $AddFile['OriginalFileName']);
                    $Ext = $fileNameParts[count($fileNameParts)-1];
                    $AddFilesCaption .= '<a href="databank/materials/'.$FId.'">'."$NameInBank.$Ext".'</a>';
                }
                $AddFilesCaption = "</p><p class='right_block'><b>Дополнительные файлы:</b><br> ".$AddFilesCaption;
            }

            $Ret = "<h1 class='main'>Материал «".$A['Title']."»</h1>";

            $Ret .= '
                <div align="left">
                <p class="main_tagline">&nbsp;</p>
                </div>
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tbody>
                        <tr>
                            <td background="images/frames/right_white_rubber/top_left.png" height="5"></td>
                            <td background="images/frames/right_white_rubber/top.png" height="5"></td>
                            <td background="images/frames/right_white_rubber/top_right.png" height="5" width="5"></td>
                        </tr>
                        <tr>
                            <td background="images/frames/right_white_rubber/left.png" valign="top" width="5"></td>
                            <td bgcolor="#FFFFFF">
                                ';

            $Ret .= "
                    <p class='right_block'><b>Опубликовано:</b> $IssueContent<br>
                    <b>Рубрика материала:</b> $Rubric<br>
                    $AuthorsCaption
                    $FilesCaption
                    $AddFilesCaption
                    </p>".'		<table cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td width="50%" valign="bottom">';

                    $SB = new social_share_class($A['Title']);
                    $SocialButtons = $SB->Code;

            $Ret .= '<p class="article_tagline" style="margin: 3px 6px 3px 12px; height: 15px;">Опубликовано: '.$PubDateText.'&nbsp;|&nbsp;<a href="'.$this->CurrentPageUrl.'#comments">Комментарии</a>:&nbsp;'.$CommentsCount.'&nbsp;|&nbsp;Просмотры:&nbsp;'.$ViewsCount.'<br>&nbsp;</p>';
            $Ret .=	'					</td>
                                        <td width="50%">
                                            <p class="article_tagline" style="margin: 3px 12px 3px; text-align: right">'.$SocialButtons.'
                                        </td>
                                    </tr>
                                </table>';
            $Ret .= '		</td>
                            <td background="images/frames/right_white_rubber/right.png" width="5"></td>
                        </tr>
                        <tr>
                            <td background="images/frames/right_white_rubber/bottom_left.png" height="5" width="5"></td>
                            <td background="images/frames/right_white_rubber/bottom.png" height="5"></td>
                            <td background="images/frames/right_white_rubber/bottom_right.png" height="5" width="5"></td>
                        </tr>
                    </tbody>
                </table>
                ';
            $Ret .= '<br>'.$this->rating->ShowRatingForm('article', $ArticleId);

            $ArticleTopic = $A['Topic'];
            //$Ret .= "<p class='main'><i>$ArticleTopic</i></p>";

            $Ret .= '
                <div align="left">
                <p class="main_tagline">&nbsp;</p>
                </div>
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tbody>
                        <tr>
                            <td background="images/frames/right_white_rubber/top_left.png" height="5"></td>
                            <td background="images/frames/right_white_rubber/top.png" height="5"></td>
                            <td background="images/frames/right_white_rubber/top_right.png" height="5" width="5"></td>
                        </tr>
                        <tr>
                            <td background="images/frames/right_white_rubber/left.png" valign="top" width="5"></td>
                            <td bgcolor="#FFFFFF" style="padding: 10px;">
                                ';

            if ($A['IsFree']) {
                $ArticleHTML = $A['HTMLText'];
                $Ret .= $ArticleHTML;
            } else {
                $Ret .= '<p class="main"><b>Текст этого материала недоступен.</b></p>';
            }
            $Ret .= '		</td>
                            <td background="images/frames/right_white_rubber/right.png" width="5"></td>
                        </tr>
                        <tr>
                            <td background="images/frames/right_white_rubber/bottom_left.png" height="5" width="5"></td>
                            <td background="images/frames/right_white_rubber/bottom.png" height="5"></td>
                            <td background="images/frames/right_white_rubber/bottom_right.png" height="5" width="5"></td>
                        </tr>
                    </tbody>
                </table>
                        <div align="left">
                        <p class="main_tagline">&nbsp;</p>
                        </div>
                        ';
                        //$comments = new comments_class($A['Title']);
                        $Ret .= $comments->ShowComments($ArticleId, 'article');


            /*
            $Ret .= '
                <div align="left">
                <p class="main_tagline">&nbsp;</p>
                </div>
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tbody>
                        <tr>
                            <td background="images/frames/right_white_rubber/top_left.png" height="5"></td>
                            <td background="images/frames/right_white_rubber/top.png" height="5"></td>
                            <td background="images/frames/right_white_rubber/top_right.png" height="5" width="5"></td>
                        </tr>
                        <tr>
                            <td background="images/frames/right_white_rubber/left.png" valign="top" width="5"></td>
                            <td bgcolor="#FFFFFF">
                                ';

            $Ret .= "<p class='right_block'><b>Комментарии:</b> </p> 		";
            $Ret .= '		</td>
                            <td background="images/frames/right_white_rubber/right.png" width="5"></td>
                        </tr>
                        <tr>
                            <td background="images/frames/right_white_rubber/bottom_left.png" height="5" width="5"></td>
                            <td background="images/frames/right_white_rubber/bottom.png" height="5"></td>
                            <td background="images/frames/right_white_rubber/bottom_right.png" height="5" width="5"></td>
                        </tr>
                    </tbody>
                </table>
                ';
            */
        } else {
      	    $Ret = "<h1 class='main'>Материал «".$ArticleTranslit."» не обнаружен</h1>";
        }

    	return $Ret;
    }

	############################################################################

	function get_author_page_meta($AuthorId){
		$Sql = 'SELECT CONCAT(F, " ", I, " ", O) as FIO FROM '.$this->tbl_authors.' WHERE (Id = ?)';
		if ($FIO = $this->DB->run($Sql, array($AuthorId))->fetchColumn()) {
            $EditionName = $this->DB->run('SELECT Title FROM '.$this->tbl_editions.' WHERE Id=?', array($this->EditionId))->fetchColumn();
            return array('title'=>"Автор издания $FIO", 'description'=>"Автор издания «".$EditionName."» $FIO", 'keywords'=>$FIO);
        }
        return false;
    }

	############################################################################

	function get_author($AuthorTranslit) {
		//$IssueYear = $this->DB->get_value($this->tbl_issues, $IssueId, 'Year');
		//$IssueNum = $this->DB->get_value($this->tbl_issues, $IssueId, 'Number');
		$AuthorsPath = $GLOBALS['DatabankAuthorsPath'];
		$EditionId = $this->EditionId;
		$AuthorId = $this->get_author_id_by_translit($AuthorTranslit);
		//$Sql = "SELECT a.* FROM ".$this->tbl_authors." a WHERE (a.Id = $AuthorId)";
		if ($A = $AuthorData = $this->DB->run('SELECT * FROM '.$this->tbl_authors.' WHERE Id = ?', array($AuthorId))->fetch()) {
            $WorkPlace = $A['WorkPlace'];
            $WorkPosition = $A['Position'];
            $ShortDescription = $A['ShortDescription'];
            $FullDescription = $A['FullDescription'];
            $Email = $A['Email'];
            //return 123; exit;
            $Ret = '<h1 class="main">' . $A['F'] . ' ' . $A['I'] . ' ' . $A['O'] . '</h1>';

            $Ret .= '
                <div align="left">
                <p class="main_tagline">&nbsp;</p>
                </div>
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tbody>
                        <tr>
                            <td background="images/frames/right_white_rubber/top_left.png" height="5"></td>
                            <td background="images/frames/right_white_rubber/top.png" height="5"></td>
                            <td background="images/frames/right_white_rubber/top_right.png" height="5" width="5"></td>
                        </tr>
                        <tr>
                            <td background="images/frames/right_white_rubber/left.png" valign="top" width="5"></td>
                            <td bgcolor="#FFFFFF">
                                <table height="100%">
                                    <tr >';
            if (trim($WorkPlace) > '' && trim($WorkPosition) > '') {

                $FileName = $AuthorsPath . $AuthorId . '_' . $EditionId . '_base.jpg';
                if (file_exists($FileName)) {
                    $Ret .= '<td width="210px" rowspan="2" valign="top">';
                    $Ret .= "
                            <p class='right_block'><img src='databank/persons/$AuthorId/base' width='200px' height='200px'></p>";
                    $Ret .= '</td>';
                }

                $Ret .= "
                                        <td valign='top'>
                            <p class='right_block'><b>$WorkPosition</b>,<br>$WorkPlace</p>";
                if (trim($ShortDescription) > '') {
                    $Ret .= "	$ShortDescription";
                }
                $Ret .= "				</td>
                                    </tr>";
                if (trim($Email) > '') {
                    $Ret .= "		<tr height='15px'>
                                        <td height='15px'>
                            <p class='right_block'>Электронная почта: <a href='mailto:$Email'>$Email</a></p>
                                        </td>
                                    </tr>";
                }
            } else {
                $Ret .= "				<td valign='top'>
                                            <p class='right_block'>Информация об авторе недоступна.</p>
                                        </td>
                                    </tr>
                ";
            }
            $Ret .= '		</table>
                        </td>
                        <td background="images/frames/right_white_rubber/right.png" width="5"></td>
                    </tr>
                    <tr>
                        <td background="images/frames/right_white_rubber/bottom_left.png" height="5" width="5"></td>
                        <td background="images/frames/right_white_rubber/bottom.png" height="5"></td>
                        <td background="images/frames/right_white_rubber/bottom_right.png" height="5" width="5"></td>
                    </tr>
                </tbody>
            </table>
            ';

            $EditionId = $this->EditionId;
            $Sql = 'SELECT c.*, i.Year, i.Number, i.PublishDate as IssuePublishDate
                    FROM ' . $this->tbl_articles . ' c, ' . $this->tbl_articles_authors . ' aa, ' . $this->tbl_issues . ' i
                    WHERE (aa.AuthorId = ?) and (aa.ArticleId = c.Id) and c.IssueId=i.Id and i.EditionId=? and (i.IsShow=1)
                    ORDER BY c.Title';
            //echo $Sql;

            $Articles = $this->DB->run($Sql, array($AuthorId, $EditionId));
            if (count($Articles) > 0) {
                $this->TranslitHistory = array();
                $Ret .= "<h2 class='main'><br>Материалы автора</h2>";
                foreach ($Articles as $Article) {
                    //echo '<pre>'; print_r($Article); echo '</pre>';
                    $Id = $Article['Id'];
                    $Title = $Article['Title'];
                    $Topic = $Article['Topic'];
                    $IssueYear = $Article['Year'];
                    $IssueNum = $Article['Number'];
                    $PublishDate = date('d.m.Y', strtotime($Article['IssuePublishDate']));
                    //if (trim($Caption)!='') {$Caption = "<br>".trim($Caption);}
                    $PublicationType = ($Article['EPublication'] == 0) ? 'Опубликовано в журнале' : 'Дополнительный материал для сайта';
                    //$Rubrica = $this->DB->get_value('rubrics', $Data[$i]['RubricId'], 'Title');
                    $TranslitTitle = $this->translit($Title, 50);
                    $Title = "<a href='archive/$IssueYear/$IssueNum/$TranslitTitle'>$Title</a>";
                    $ShowOnSiteFlag = $Article['IsFree'];
                    if ($ShowOnSiteFlag) {
                        $Title .= '&nbsp;<sup><font size="1" color="#FFFFFF" style="background-color: #A31911">
                        &nbsp;на&nbsp;сайте&nbsp;</font></sup>';
                    }
                    $Ret .= '<p class="article_title"><b>' . $Title . '</b>';
                    if (trim($Topic) != '') {
                        $Ret .= '<br><i>' . $Topic . '</i>';
                    }
                    $Ret .= '</p><p class="article_tagline">Опубликовано: ' . $PublishDate . ' в <a href="archive/' . $IssueYear . '/' . $IssueNum . '">№' . $IssueNum . ', ' . $IssueYear . ' г.</a> </p>';

                }
            }
        }
    	$Ret .= '

    	';

		return $Ret;
	}
	############################################################################

	function show_authors() {
		//$IssueYear = $this->DB->get_value($this->tbl_issues, $IssueId, 'Year');
		//$IssueNum = $this->DB->get_value($this->tbl_issues, $IssueId, 'Number');
		$AuthorsPath = $GLOBALS['DatabankAuthorsPath'];
		$EditionId = $this->EditionId;
		//$AuthorId = $this->get_author_id_by_translit($AuthorTranslit);
		$Sql = '
				SELECT a.Id, a.F, a.I, a.O, count(aa.Id) as ArticlesCount, a.WorkPlace, a.Position
				FROM '.$this->tbl_authors.' a
				INNER JOIN '.$this->tbl_articles_authors.' aa ON aa.AuthorId=a.Id
				INNER JOIN '.$this->tbl_articles.' ar ON ar.Id=aa.ArticleId
				INNER JOIN '.$this->tbl_issues.' i ON i.Id=ar.IssueId
				WHERE (i.EditionId = ?) and (i.IsShow=1)
				GROUP BY a.Id
				ORDER BY a.F, a.I, a.O';
		//echo $Sql;
		$AuthorsData = $this->DB->run($Sql, array($EditionId))->fetchAll();
		//echo '<pre>'; print_r($AuthorsData); echo '</pre>';

        $AuthorsCount = count($AuthorsData);
		$Ret = '<h1 class="main">Авторы ('.$AuthorsCount.')</h1><hr>';

        if ($AuthorsCount>0) {
        	foreach ($AuthorsData as $A) {
        		$F = $A['F'];
        		$I = $A['I'];
        		$O = $A['O'];
        		$ArticlesCount = $A['ArticlesCount'];
        		$WorkPlace = $A['WorkPlace'];
        		$WorkPosition = $A['Position'];
        		$AuthorTranslit = $this->translit($F.' '.$I.' '.$O);
       			$Work = ((trim($WorkPlace)>'') && (trim($WorkPosition)>'')) ? "<br><i>$WorkPosition, $WorkPlace</i>" : '';
        		$Ret .= '
        			<p class="main"><b><a href="archive/persons/'.$AuthorTranslit.'">'."$F $I $O".'</a>&nbsp;&rarr;</b>'.$Work.'</p>
        			<p class="main_tagline">Количество материалов:'.$ArticlesCount.'</p>
        			<hr>

        		';
        	}
        }

		return $Ret;

		//echo $Sql;
	}
	############################################################################

	function get_rubric_page_meta($RubricId)
	{
		$RubricCaption = $this->DB->get_value($this->tbl_rubrics, $RubricId, 'Caption');
		$RubricDescription = $this->DB->get_value($this->tbl_rubrics, $RubricId, 'Description');
		$EditionName = $this->DB->get_value($this->tbl_editions, $this->EditionId, 'Title');
		$Ret['title'] = "Рубрика «".$RubricCaption."»";
		$Ret['description'] = trim($RubricDescription)>'' ? $RubricDescription : "Рубрика «".$RubricCaption."»";
		$Ret['keywords'] = "$RubricCaption, рубрика";
		return $Ret;
    }

	############################################################################

	function get_rubric($RubricTranslit) {
		$RubricId = $this->get_rubric_id_by_translit($RubricTranslit);
		$RubricCaption = $this->DB->get_value($this->tbl_rubrics, $RubricId, 'Caption');
		$RubricDescription = $this->DB->get_value($this->tbl_rubrics, $RubricId, 'Description');
        //return 123; exit;
		$Ret = '<h1 class="main">Рубрика «'.$RubricCaption.'»</h1>';

		$EditionId = $this->EditionId;
		$Sql = "SELECT c.*, i.Year, i.Number, i.PublishDate as IssuePublishDate
				FROM ".$this->tbl_articles." c, ".$this->tbl_issues." i
				WHERE c.IssueId=i.Id and i.EditionId=$EditionId and c.RubricatorId=$RubricId and i.IsShow=1
				ORDER BY c.Title
				";
		//		echo $Sql;

		$Articles = $this->DB->get_simple_sql_result($Sql);
        $ACount = count($Articles);
        $CountCaption = $ACount>0 ? "Количество материалов: $ACount" : "В этой рубрике нет материалов";

        $Ret .= '
			<div align="left">
			<p class="main_tagline">&nbsp;</p>
			</div>
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td background="images/frames/right_white_rubber/top_left.png" height="5"></td>
						<td background="images/frames/right_white_rubber/top.png" height="5"></td>
						<td background="images/frames/right_white_rubber/top_right.png" height="5" width="5"></td>
					</tr>
					<tr>
						<td background="images/frames/right_white_rubber/left.png" valign="top" width="5"></td>
						<td bgcolor="#FFFFFF">
							';

        $Ret .= "
        				<p class='right_block'><i>$RubricDescription</i></p>
        				<p class='right_block_small'>$CountCaption</p>";

		$Ret .= '
						</td>
						<td background="images/frames/right_white_rubber/right.png" width="5"></td>
					</tr>
					<tr>
						<td background="images/frames/right_white_rubber/bottom_left.png" height="5" width="5"></td>
						<td background="images/frames/right_white_rubber/bottom.png" height="5"></td>
						<td background="images/frames/right_white_rubber/bottom_right.png" height="5" width="5"></td>
					</tr>
				</tbody>
			</table>
	        ';
		if ($ACount>0) {
			$this->TranslitHistory = array();
            //$EditionTitle = $this->DB->get_value($this->tbl_editions, $EditionId, 'Title');
			foreach($Articles as $Article) {
				//echo '<pre>'; print_r($Article); echo '</pre>';
				$Id = $Article['Id'];
				$Title = $Article['Title'];
				$Topic = $Article['Topic'];
				$IssueYear = $Article['Year'];
				$IssueNum = $Article['Number'];
				$PublishDate = date('d.m.Y', strtotime($Article['IssuePublishDate']));

				$TranslitTitle = $this->translit($Title, 50);
				$Title = "<a href='archive/$IssueYear/$IssueNum/$TranslitTitle'>$Title</a>";
				$ShowOnSiteFlag = $Article['IsFree'];
				if ($ShowOnSiteFlag) {
					$Title .= '&nbsp;<sup><font size="1" color="#FFFFFF" style="background-color: #A31911">
					&nbsp;на&nbsp;сайте&nbsp;</font></sup>';
				}
                $Ret .= '<p class="article_title"><b>'.$Title.'</b>';
				if (trim($Topic) != '') { $Ret .= '<br><i>'.$Topic.'</i>';}
				$Ret .= '</p><p class="article_tagline">Опубликовано: '.$PublishDate.' в <a href="archive/'.$IssueYear.'/'.$IssueNum.'">№'.$IssueNum.', '.$IssueYear.' г.</a> </p>';
			}
    	}

    	$Ret .= '

    	';
		return $Ret;
	}
	############################################################################

	function show_rubrics() {
		$Ret = '<h1 class="main">Рубрики журнала</h1>';

		$Sql = 'SELECT r.*, count(a.Id) as ArticlesCount
				FROM '.$this->tbl_issues.' i, '.$this->tbl_articles.' a
				INNER JOIN '.$this->tbl_rubrics.' r ON a.RubricatorId=r.Id
				WHERE a.IssueId=i.Id and i.EditionId=?
				GROUP BY r.Id
				ORDER BY r.Caption';
				//echo $Sql;

		$Rubrics = $this->DB->run($Sql, array($this->EditionId));
		if (count($Rubrics)>0) {
			$this->TranslitHistory = array();
			foreach($Rubrics as $Rubric) {
				//echo '<pre>'; print_r($Article); echo '</pre>';
				$Id = $Rubric['Id'];
				$Caption = $Rubric['Caption'];
				$Description = $Rubric['Description'];
				$ArticlesCount = $Rubric['ArticlesCount'];

				$RubricaTranslit = $this->translit($Caption);
				$Ret .= '
			<div align="left">
			<p class="main_tagline">&nbsp;</p>
			</div>
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td background="images/frames/right_white_rubber/top_left.png" height="5"></td>
						<td background="images/frames/right_white_rubber/top.png" height="5"></td>
						<td background="images/frames/right_white_rubber/top_right.png" height="5" width="5"></td>
					</tr>
					<tr>
						<td background="images/frames/right_white_rubber/left.png" valign="top" width="5"></td>
						<td bgcolor="#FFFFFF">
							';
				$Ret .=	'<h2 class="right_block"><a href="archive/rubrics/'.$RubricaTranslit.'">'.$Caption.'</a></h2>';
        		$Ret .= "
        				<p class='right_block'><i>$Description</i></p>
        				<p class='right_block_small'>Количество материалов: $ArticlesCount</p>";

				$Ret .= '
						</td>
						<td background="images/frames/right_white_rubber/right.png" width="5"></td>
					</tr>
					<tr>
						<td background="images/frames/right_white_rubber/bottom_left.png" height="5" width="5"></td>
						<td background="images/frames/right_white_rubber/bottom.png" height="5"></td>
						<td background="images/frames/right_white_rubber/bottom_right.png" height="5" width="5"></td>
					</tr>
				</tbody>
			</table>
	        ';
           }
    	}

		return $Ret;
	}
	############################################################################


	function get_archive_block($IssueId) {
		if ($IssueId<1) {$IssueId = $this->get_last_issue_id();}
		$Sql = 'SELECT * FROM '.$this->tbl_issues.' WHERE IsShow = true AND EditionId=? ORDER BY PublishDate';
		$IssuesData = $this->DB->run($Sql, array($this->EditionId))->fetchAll();
		$IssuesCount = count($IssuesData);
		if ($IssuesCount>0) {
			$i=0;
			foreach ($IssuesData as $IssueData) {
				if ($IssueId==$IssueData['Id']) {$CurrentNum = $i;}
				$i++;
			}

            $EditionTitle = $this->EditionTitle;

			if ($CurrentNum>0) {
				$PrevId = $IssuesData[$CurrentNum-1]['Id'];
				$PrevNum = $IssuesData[$CurrentNum-1]['Number'];
				$PrevNum = ($PrevNum<10) ? '0'.$PrevNum : $PrevNum;
				$PrevYear = $IssuesData[$CurrentNum-1]['Year'];
		   		$PrevPic  = $this->get_issue_cover_file($PrevYear, $PrevNum, 'thumb');
				$PrevAltText = "$EditionTitle \n".$IssuesData[$CurrentNum-1]['Title'];
				$PrevURL = "archive/$PrevYear/$PrevNum";
				$PrevNumberCode =
				'<p class="right_block" style="text-align: center">
					<a href="'.$PrevURL.'"><img border="0" src="'.$PrevPic.'" width="'.$this->Covers['thumb']['w'].'" height="'.$this->Covers['thumb']['h'].'" alt="'.$PrevAltText.'" title="'.$PrevAltText.'"></a>
				</p>
				<p class="right_block" style="text-align: left">&#8592;
				<a href="'.$PrevURL.'">№'.$PrevNum.', '.$PrevYear.'</a></p>';

			}
			if ($CurrentNum < ($IssuesCount-1)) {
				$NextId = $IssuesData[$CurrentNum+1]['Id'];
				$NextNum = $IssuesData[$CurrentNum+1]['Number'];
					$NextNum = ($NextNum<10) ? '0'.$NextNum : $NextNum;
				$NextYear = $IssuesData[$CurrentNum+1]['Year'];
		   		$NextPic  = $this->get_issue_cover_file($NextYear, $NextNum, 'thumb');
				$NextAltText = "$EditionTitle \n".$IssuesData[$CurrentNum+1]['Title'];
				$NextURL = "archive/$NextYear/$NextNum";
				$NextNumberCode =
				'<p class="right_block" style="text-align: center">
					<a href="'.$NextURL.'"><img border="0" src="'.$NextPic.'" width="'.$this->Covers['thumb']['w'].'" height="'.$this->Covers['thumb']['h'].'" alt="'.$NextAltText.'" title="'.$NextAltText.'"></a>
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

	function show_archive() {

		$Sql = 'SELECT *
				FROM '.$this->tbl_issues.'
				WHERE IsShow = true AND EditionId=?
				ORDER BY PublishDate';
		//echo $Sql;
		$Data = $this->DB->run($Sql, array($this->EditionId))->fetchAll();
		$EditionTitle = $this->EditionTitle;
        $Ret = "<h1  class='main'>Архив номеров</h1>";
		for ($i = count($Data)-1; $i >= 0; $i--) {
	        $Num = $Data[$i]['Number']; if ($Num != 10) $Num = '0'.$Num;
	        $Year = $Data[$i]['Year'];
			$IsShow = $Data[$i]['IsShow'];
			$Pub = date("d.m.Y", strtotime($Data[$i]['PublishDate']));

			$Caption = "$EditionTitle<br>".$Data[$i]['Title'];

			$Url = "archive/$Year/".$Data[$i]['Number'];
			$SmallPic  = $this->get_issue_cover_file($Year, $Num, 'thumb');

//echo <<<END
			$Ret .= '
			<table border="0" width="100%" cellpadding="0" style="border-collapse: collapse">
				<tr>
					<td>
						<h2 class="main"><br><a href="'.$Url.'">
						<img border="0" src="'.$SmallPic.'" width="'.$this->Covers['thumb']['w'].'" height="'.$this->Covers['thumb']['h'].'" hspace="10px;"></a>

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

    function get_article_views($Id) {
        $ViewsRecs = $this->DB->run('SELECT Id FROM '.$this->tbl_materials_stat.' WHERE MaterialCategory=3 AND MaterialId=?', array($Id))->fetchAll();
        return count($ViewsRecs);
    }

    ############################################################################

    function get_article_comments_count($MaterialId) {
        $CommentsRecs = $this->DB->run('SELECT Id FROM '.$this->tbl_comments.' WHERE TypeTable="article" AND PostId=?', array($MaterialId))->fetchAll();
        return count($CommentsRecs);
    }

    /*
        ############################################################################

        function get_article_files($Id)
        {
            $Sql ="	SELECT
                    FROM ".$this->tbl_articles."
                    WHERE MaterialCategory=3 AND MaterialId=$Id";
            return count($this->DB->get_simple_sql_result($Sql));
        }
    */
	############################################################################

	function get_rss_data()	{

		$sql = 'SELECT * FROM issues WHERE IsShow = true ORDER BY CrossNum DESC LIMIT 0,30';
        //echo $sql.'<br>';
        $CurRubrica = '';
		//$count = $this->DB->get_count($sql);
		$data = $this->DB->run($sql)->fetchAll();
        $RSSData = array();
		for ($i = 0; $i < count($data); $i++)
		{
			$Id = $data[$i]['Id'];
	        $Title = $data[$i]['Title'];
	        $Num = $data[$i]['Num']; if ($Num != 10) $Num = '0'.$Num;
	        $CrossNum = $data[$i]['CrossNum'];
	        $NumCaption = $data[$i]['NumCaption'];
	        $Year = $data[$i]['Year'];
			$Pub = date("d.m.Y", strtotime($data[$i]['OutDate']));

			$AltText = "$Title № $Num ($CrossNum), $Year";

			$sql = 'SELECT c.Id, c.Title as ITitle, c.Caption, r.Title as RTitle
				FROM publications c
				LEFT JOIN rubrics r ON r.Id = c.RubricId
				WHERE (c.IssueId = ?)
				ORDER BY Rank';
			//echo $Title.'<br>';
			//echo $sql.'<br>';
			$idata = $this->DB->run($sql, array($Id))->fetchAll();
			//$count2 = $this->DB->get_count($sql);
            $Topic='';
			for ($j = 0; $j < count($idata); $j++) {
				$IId = $idata[$j]['Id'];
	            $ITitle = $idata[$j]['ITitle'];
	            $RTitle = $idata[$j]['RTitle'];

	            if ($RTitle == $CurRubrica) {
	                $RTitle = '';
	            } else {
	            	$CurRubrica = $RTitle;
	            }

                $sql_authors = 'SELECT p.F, p.I, p.O
                				FROM authors a
                				INNER JOIN persons p ON p.Id=a.PersonId
                				WHERE a.PublicationId=?
                				ORDER BY a.Rank';
                $AuthorsData = $this->DB->run($sql_authors, array($IId))->fetchAll();
                if (count($AuthorsData)>0) {
                	$Authors = '';
                	foreach ($AuthorsData as $AData) {
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