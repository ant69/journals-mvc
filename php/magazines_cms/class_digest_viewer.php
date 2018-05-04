<?php
include_once $GLOBALS['lib_ratings'];

################################################################################
class digest_viewer_class
{
	function digest_viewer_class($DM) # конструктор
	{
		$this->UserId = $DM->User->UserId;
		$this->DB = $DM->DB;

		$this->tbl_editions = $GLOBALS['tbl_editions'];
		$this->tbl_issues = $GLOBALS['tbl_issues'];
		$this->tbl_rubrics = $GLOBALS['tbl_rubrics'];
		$this->tbl_articles = $GLOBALS['tbl_articles'];
		$this->tbl_authors = $GLOBALS['tbl_authors'];
		$this->tbl_articles_authors = $GLOBALS['tbl_articles_authors'];

		$this->tbl_blogs = $GLOBALS['tbl_blogs'];
		$this->tbl_posts = $GLOBALS['tbl_posts'];
		$this->tbl_sites_posts = $GLOBALS['tbl_sites_posts'];
		$this->tbl_news = $GLOBALS['tbl_news'];
		$this->tbl_sites_news = $GLOBALS['tbl_sites_news'];
		$this->tbl_news_tags = $GLOBALS['tbl_news_tags'];
		$this->tbl_news_sources = $GLOBALS['tbl_news_sources'];
		$this->tbl_comments = $GLOBALS['tbl_comments'];
		$this->tbl_materials_stat = $GLOBALS['tbl_materials_stat'];

		$this->news_pictures_url = $GLOBALS['news_pictures_url'];
		$this->max_digest = $GLOBALS['max_digest'];

		$this->SiteId = $DM->SiteId;
		$this->EditionId = $DM->EditionId;
/*		$this->EditionTitle = $this->DB->get_value($this->tbl_editions, $this->EditionId, 'Title');
		$this->EditionTitle = $this->DB->get_value($this->tbl_editions, $this->EditionId, 'Title');*/
        $this->EditionTitle = $this->DB->run('SELECT Title FROM '.$this->tbl_editions.' WHERE Id=? LIMIT 0, 1', array($this->EditionId))->fetchColumn();


        $this->rating = new ratings_class();

	}

	############################################################################

	function show_digest($PageNum=1)
	{

		if (isset($this->max_digest)){$RecsOnPage = $this->max_digest;}else{$RecsOnPage = 10;}

		$SqlCheck = '
			SELECT p.Id as Id, p.PublishDate as PublishDate
			FROM '.$this->tbl_posts.' p, '.$this->tbl_sites_posts.' sp
			WHERE p.IsShow = 1 and sp.PostId=p.Id and sp.SiteId=?
			UNION
			SELECT n.Id as Id, n.PublishDate as PublishDate
			FROM '.$this->tbl_news.' n, '.$this->tbl_sites_news.' sn
			WHERE n.IsShow = 1 and sn.NewsId=n.Id and sn.SiteId=?
			UNION
			SELECT a.Id as Id, a.PublishDate as PublishDate
			FROM '.$this->tbl_articles.' a, '.$this->tbl_issues.' i
			WHERE a.IsFree = 1 and a.IssueId=i.Id and i.EditionId=?';
			//echo $SqlCheck;
		$AllDigestRecs = $this->DB->run($SqlCheck, array($this->SiteId, $this->SiteId, $this->EditionId))->fetchAll();
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
				$Nav .= '<a class="dotted" href="page/1">самые новые записи</a>&nbsp;&nbsp;<a class="dotted" href="page/'.((int)$PageNum - 1).'">страница '.((int)$PageNum - 1).'</a>&nbsp;&nbsp;&nbsp;&nbsp;';
			}

			$Nav .="<b>страница $PageNum (из $PagesCount)</b>";
			if ($PageNum<$PagesCount) {
				// Если текущая страница - не первая, рисуем навигацию в начало.
				$Nav .= '&nbsp;&nbsp;&nbsp;&nbsp;<a class="dotted" href="page/'.((int)$PageNum + 1).'">страница '.((int)$PageNum + 1).'</a>&nbsp;&nbsp;&nbsp;<a class="dotted" href="page/'.$PagesCount.'">самые ранние записи</a>';
			}
			$Nav = '<p class="main"  style="text-align: center; padding-top: 14px;">'.$Nav.'</p>';
		}
		// ----------------------------------------
$Sql_count = '
			SELECT count(p.Id) as NumeroPost
			FROM '.$this->tbl_posts.' p, '.$this->tbl_sites_posts.' sp
			WHERE p.IsShow = 1 and sp.PostId=p.Id and sp.SiteId=?';
			$Numero = $this->DB->run($Sql_count, array($this->SiteId))->fetchAll();
			$NumeroPost=$Numero[0]['NumeroPost'];

		$Ret = '
			<table cellpadding="0px" cellspacing="0px" border="0" width="100%">
				<tr>
					<td width="20%">
						<h1 class="main">Дайджест&nbsp;публикаций</h1>
					</td>
					<td width="80%">
						<p class="main" style="text-align: right; padding-top: 14px;">';
		if($NumeroPost>0){
					$Ret.= '<a class="dotted" href="posts">Блоги</a>&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		$Ret.= '
							<a class="dotted" href="news">Новости</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a class="dotted" href="articles">Статьи</a>
						</p>
					</td>
				</tr>
			</table>
            <hr>
				';
$Sql = '
			SELECT p.Id as Id, p.Title as Title, p.PublishDate, p.Topic as Topic, "post" AS RecType, p.BlogId AS SourceId, "" AS TagId
			FROM '.$this->tbl_posts.' p, '.$this->tbl_sites_posts.' sp
			WHERE p.IsShow = 1 and sp.PostId=p.Id and sp.SiteId=?
			UNION
			SELECT n.Id, n.Title, n.PublishDate, n.Topic, "news" AS RecType, n.SourceId, n.TagId
			FROM '.$this->tbl_news.' n, '.$this->tbl_sites_news.' sn
			WHERE n.IsShow = 1 and sn.NewsId=n.Id and sn.SiteId=?
			UNION
			SELECT a.Id, a.Title, a.PublishDate, a.Topic, "article" AS RecType, a.IssueId as SourceId, a.RubricatorId as TagId
			FROM '.$this->tbl_articles.' a, '.$this->tbl_issues.' i
			WHERE a.IsFree = 1 and a.IssueId=i.Id and i.EditionId=?
			ORDER BY PublishDate DESC, Id DESC
			LIMIT '.$StartRec.', '.$RecsOnPage
			;
		//echo $Sql;
		$DigestRecs = $this->DB->run($Sql, array($this->SiteId, $this->SiteId, $this->EditionId))->fetchAll();
		if (count($DigestRecs)>0)
		{
			//$Ret .= '1';
//			$this->TranslitHistory = array();
			foreach($DigestRecs as $Digest)
			{
				//echo '<pre>'; print_r($Article); echo '</pre>';
				$Id = $Digest['Id'];
				$Title = $Digest['Title'];
				$Topic = $Digest['Topic'];
				$RecType = $Digest['RecType'];
				$PubDate = date('d.m.Y', strtotime($Digest['PublishDate']));
				$SourceId = $Digest['SourceId'];
				$TagId = $Digest['TagId'];
				$AuthorsStr='';
				switch ($RecType)
				{
					case 'post':
					{
						$RecTypeTitle = 'Блоги';
						$RecTypeBackColor = '#d35400';
						$RecTypeTextColor = '#fff';
						$Source = $this->DB->run('SELECT Title FROM '.$this->tbl_blogs.' WHERE Id=? LIMIT 0, 1', array($SourceId))->fetchColumn();
						$SourceLink = "<a href='posts/blogs/$SourceId'>$Source</a>";
						$PubLink = "posts/$Id";
		       			$ViewsCount = $this->get_materials_views(1, $Id);
		       			$CommentsCount = $this->get_materials_comments_count(1, $Id);
						$RatingCode = $this->rating->get_rating_code('blog', $Id);
						break;
					}
					case 'news':
					{
						$NewsSmallPicture = $this->news_pictures_url."small_news_".$Id.".jpg";
						$Image='';
						if($f=@fopen($NewsSmallPicture, 'rb'))
						{
							$Image= "<img class='img' src='$NewsSmallPicture'>";
							fclose($f);
						}
						$RecTypeTitle = 'Новости';
						$RecTypeBackColor = '#27ae60';
						$RecTypeTextColor = '#fff';
                        $Source = $this->DB->run('SELECT Title FROM '.$this->tbl_news_sources.' WHERE Id=? LIMIT 0, 1', array($SourceId))->fetchColumn();
						$SourceLink = "<a href='news/sources/$SourceId'>$Source</a>";
						$PubLink = "news/$Id";
		       			$ViewsCount = $this->get_materials_views(2, $Id);
		       			$CommentsCount = $this->get_materials_comments_count(2, $Id);
						$RatingCode = $this->rating->get_rating_code('news', $Id);
						break;
					}
					case 'article':
					{
						$RecTypeTitle = 'Статьи';
						$RecTypeBackColor = '#2980b9';
						$RecTypeTextColor = '#fff';
                        $Num = $this->DB->run('SELECT Number FROM '.$this->tbl_issues.' WHERE Id=? LIMIT 0, 1', array($SourceId))->fetchColumn();
                        $Year = $this->DB->run('SELECT Year FROM '.$this->tbl_issues.' WHERE Id=? LIMIT 0, 1', array($SourceId))->fetchColumn();
                        $IssueTitle = $this->DB->run('SELECT Title FROM '.$this->tbl_issues.' WHERE Id=? LIMIT 0, 1', array($SourceId))->fetchColumn();
						$Source = $this->EditionTitle.", ".$IssueTitle;
						$SourceLink = "<a href='archive/$Year/$Num'>$Source</a>";
						$PubLink = "archive/$Year/$Num/".$this->translit($Title, 50);
		       			$ViewsCount = $this->get_materials_views(3, $Id);
		       			$CommentsCount = $this->get_materials_comments_count(3, $Id);
						$RatingCode = $this->rating->get_rating_code('article', $Id);

						$SqlAuthors = '
								SELECT a.AuthorId, p.F, p.I, p.O
								FROM '.$this->tbl_articles_authors.' a
								INNER JOIN '.$this->tbl_authors.' p ON a.AuthorId=p.Id
								WHERE a.ArticleId=?';
						$Authors = $this->DB->run($SqlAuthors, array($Id));
						$AuthorsStr = '';
						if (is_array($Authors))
						{
							foreach ($Authors as $Author)
							{
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
				$Ret .='<div class="one_in_digest">'.$Image;
                $Ret .= '<p class="article_title_in_digest">';

                $Ret .= $AuthorsStr.'<a href="'.$PubLink.'">'.$Title.'</a></p><p class="article_tagline_in_digest plaski"><font style="background-color: '.$RecTypeBackColor.'; color: '.$RecTypeTextColor.';">&nbsp;'.$RecTypeTitle.'&nbsp;</font>';


				if($Source!=''){
				$Ret .= '&nbsp;<font style="background-color: #868686";>&nbsp;'.$SourceLink.'&nbsp;</font>';}
				$Ret .= '</p>';

				$Ret .= '<p class="article_tagline_in_digest"><span>'.$PubDate.'</span>&nbsp;&nbsp;Просмотры:&nbsp;'.$ViewsCount.'&nbsp;&nbsp;<a href="'.$PubLink.'#comments">Комментарии</a>:&nbsp;'.$CommentsCount.'&nbsp;'.$RatingCode.'</p>';
				if ((trim($Topic) != '') AND (substr_count($Topic, 'class=')==0)) {
					$Ret .= '<p class="digest_text"><a href="'.$PubLink.'">'.$Topic.'</a></p>';
				}elseif (trim($Topic) != '') {
					$Ret .= '<a class="digest_text" href="'.$PubLink.'">'.$Topic.'</a>';
				}

				//if($Image!=''){$Ret .="<br clear='all'>";}
				$Ret .='</div>';
				$Image='';
			}
/*			$HTML .= '
					<script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>
					<div class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,odnoklassniki,moimir,lj,friendfeed,moikrug,gplus" style="text-align: right;"></div>
					';*/
    	}

    	return $Ret.$Nav;
	}
	############################################################################

	function get_materials_views($MaterialCategory, $MaterialId) {
		$ViewsRecs =$this->DB->run('SELECT Id FROM '.$this->tbl_materials_stat.' WHERE MaterialCategory=? AND MaterialId=?', array($MaterialCategory, $MaterialId))->fetchColumn();
		return count($ViewsRecs);
    }

	############################################################################

	function get_materials_comments_count($MaterialCategory, $MaterialId) {
		switch ($MaterialCategory) {
			case 1: {$TypeTable = 'blog'; break;}
			case 2: {$TypeTable = 'news'; break;}
			case 3: {$TypeTable = 'article'; break;}
		}
		$CommentsRecs = $this->DB->run('SELECT Id FROM '.$this->tbl_comments.' WHERE TypeTable="'.$TypeTable.'" AND PostId=?', array($MaterialId))->fetchColumn();
		return count($CommentsRecs);
    }
	############################################################################

	function translit($CyrStr, $Len=100)
	{
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
		return $Ret;
	}


}
?>