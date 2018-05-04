<?php
include_once $GLOBALS['lib_social_share'];
include_once $GLOBALS['lib_ratings'];
include_once $GLOBALS['lib_comments'];

################################################################################

class news_viewer_class
{
	function news_viewer_class($DM) # конструктор
	{
		$this->UserId = $DM->User->UserId;

		$this->DB = $DM->DB;
		$this->tbl_editions = $GLOBALS['tbl_editions'];

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

		$this->SiteId = $DM->SiteId;
		$this->EditionId = $DM->EditionId;
		$this->EditionTitle = $this->DB->run('SELECT Title FROM '.$this->tbl_editions.' WHERE Id=?', array($this->EditionId))->fetchColumn();
		$this->rating = new ratings_class($this->UserId);
	}


	############################################################################


	function news_archive($PageNum=1) {
		$RecsOnPage = 10;
        $RatingCode = '';
		$SqlCheck = '
			SELECT n.Id as Id, n.PublishDate as PublishDate
			FROM '.$this->tbl_news.' n, '.$this->tbl_sites_news.' sn
			WHERE n.IsShow = 1 and sn.NewsId=n.Id and sn.SiteId=?';
			//echo $SqlCheck;
		$AllNewsRecs = $this->DB->run($SqlCheck, array($this->SiteId))->fetchAll();
		$OverallCount = count($AllNewsRecs);
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
				$Nav .= '<a class="dotted" href="news/page/1">самые новые записи</a>&nbsp;&nbsp;<a class="dotted" href="news/page/'.((int)$PageNum - 1).'">страница '.((int)$PageNum - 1).'</a>&nbsp;&nbsp;&nbsp;&nbsp;';
			}

			$Nav .="<b>страница $PageNum (из $PagesCount)</b>";
			if ($PageNum<$PagesCount) {
				// Если текущая страница - не первая, рисуем навигацию в начало.
				$Nav .= '&nbsp;&nbsp;&nbsp;&nbsp;<a class="dotted" href="news/page/'.((int)$PageNum + 1).'">страница '.((int)$PageNum + 1).'</a>&nbsp;&nbsp;&nbsp;<a class="dotted" href="news/page/'.$PagesCount.'">самые ранние записи</a>';
			}
			$Nav = '<p class="main"  style="text-align: center; padding-top: 14px;">'.$Nav.'</p>';
		}
		// ----------------------------------------

		$Ret = '
			<table cellpadding="0px" cellspacing="0px" border="0" width="100%">
				<tr>
					<td width="20%">
						<h1 class="main">Новости</h1>
					</td>     <!--
					<td width="80%">
						<p class="main" style="text-align: right; padding-top: 0px;">
							<a class="dotted" href="news">Новости</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a class="dotted" href="news">Последние</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a class="dotted" href="news/popular">Популярные</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a class="dotted" href="news/commented">Обсуждаемые</a>
						</p>
					</td>  -->
				</tr>
			</table>
            <hr>
				';
        $Sql = '
			SELECT n.Id, n.Title, n.PublishDate, n.Topic, n.SourceId, n.TagId, nt.BackColor as TagBackColor,  nt.TextColor as TagTextColor,
			 nt.Title as TagTitle, ns.Title as SourceTitle   
			FROM '.$this->tbl_news.' n, '.$this->tbl_sites_news.' sn, 
			'.$this->tbl_news_tags.' nt, '.$this->tbl_news_sources.' ns   
			WHERE IsShow = 1 and sn.NewsId=n.Id and nt.Id=n.TagId and ns.Id=n.SourceId and sn.SiteId=?
			ORDER BY PublishDate DESC, Id DESC
			LIMIT '.$StartRec.', '.$RecsOnPage;
        //echo $Sql; echo $this->SiteId;
		$NewsRecs = $this->DB->run($Sql, array($this->SiteId));
			//$Ret .= '1';
//			$this->TranslitHistory = array();
        while($News = $NewsRecs->fetch()) {
            //echo '<pre>'; print_r($Article); echo '</pre>';

            $Id = $News['Id'];
            $Title = $News['Title'];
            $Topic = $News['Topic'];
            $PubDate = date('d.m.Y', strtotime($News['PublishDate']));
            $SourceId = $News['SourceId'];
            $TagId = $News['TagId'];
            $NewsSmallPicture = $this->news_pictures_url."small_news_".$Id.".jpg";
            $Image='';
            if($f=@fopen($NewsSmallPicture, 'rb'))
            {
                $Image= "<img class='img' src='$NewsSmallPicture'>";
                fclose($f);
            }
            $TagBackColor = $News['TagBackColor'];
            $TagTextColor = $News['TagTextColor'];
            $TagTitle = $News['TagTitle'];
            $SourceTitle = $News['SourceTitle'];

/*            $TagBackColor = $this->DB->get_value($this->tbl_news_tags, $TagId, 'BackColor');
            $TagTextColor = $this->DB->get_value($this->tbl_news_tags, $TagId, 'TextColor');
            $TagTitle = $this->DB->get_value($this->tbl_news_tags, $TagId, 'Title');
            $SourceTitle = $this->DB->get_value($this->tbl_news_sources, $SourceId, 'Title');*/

            $SourceLink = "<a href='news/sources/$SourceId'>$SourceTitle</a>";
            $PubLink = "news/$Id";
            $ViewsCount = $this->get_news_views($Id);
            $CommentsCount = $this->get_news_comments_count($Id);

            $Ret .= '<div class="one_in_digest">'.$Image.'<p class="article_title_in_digest"><a href="'.$PubLink.'">'.$Title.'</a></p>';
            if ((trim($SourceTitle)>'') OR (trim($TagTitle)>'')){
                $Ret .= '<p class="article_tagline_in_digest plaski">';
                if (trim($SourceTitle)>'')
                {
                    $Ret .= '<font style="background-color: #868686";>&nbsp;'.$SourceLink.'&nbsp;</font>';
                }
                if (trim($TagTitle)>'')
                {
                    $Ret .= '&nbsp;<font style="background-color: '.$TagBackColor.'; color: '.$TagTextColor.';">&nbsp;'.$TagTitle.'&nbsp;</font>';
                }
                $Ret .= '</p>';
            }

            $Ret .= '<p class="article_tagline_in_digest"><span>'.$PubDate.'</span>&nbsp;&nbsp;Просмотры:&nbsp;'.$ViewsCount.'&nbsp;&nbsp;<a href="'.$PubLink.'#comments">Комментарии</a>:&nbsp;'.$CommentsCount.' '.$RatingCode.'</p>';

            if (trim($Topic) != '') { $Ret .= '<p class="digest_text"><a href="'.$PubLink.'">'.$Topic.'</a></p>'; }
            //if($Image!=''){$Ret .="<br clear='all'>";}
            $RatingCode = $this->rating->get_rating_code('news', $Id);
            $Ret .='</div>';

        }
    return $Ret.$Nav;

	}
	############################################################################

	function get_news_page_meta($NewsId) {
        $NewsData = $this->DB->run('SELECT n.Title as title, CONCAT(n.Title, ", ", n.Topic) as description, CONCAT(", новость, ", tTitle) as keywords  
                                    FROM '.$this->tbl_news.' n, '.$this->tbl_news_tags.' t 
                                    WHERE n.TagId=t.Id AND n.Id=?', array($NewsId))->fetch();
		return $NewsData;
    }

	############################################################################


	function show_news($NewsId) {
		$Ret = '';

		if ($News = $this->DB->run('SELECT n.*, nt.BackColor as TagBackColor,  nt.TextColor as TagTextColor, nt.Title as TagTitle, ns.Title as SourceTitle 
                    FROM '.$this->tbl_news.' n, '.$this->tbl_news_tags.' nt, '.$this->tbl_news_sources.' ns  
		            WHERE n.IsShow = 1 and n.Id=? and n.TagId=nt.Id AND n.SourceId=ns.Id',
                    array($NewsId))->fetch()) {

			$Id = $News['Id'];
			$NewsSmallPicture = $this->news_pictures_url."news_".$Id.".jpg";
			$Image='';
			if($f=@fopen($NewsSmallPicture, 'rb')) {
				$Image= "<img src='$NewsSmallPicture' align='left'  style='margin: 10px 10px 0px 0px;'>";
				fclose($f);
			}
			$Title = htmlspecialchars_decode($News['Title'], ENT_QUOTES);
			$Topic = htmlspecialchars_decode($News['Topic'], ENT_QUOTES);
			$PubDate = date('d.m.Y', strtotime($News['PublishDate']));
			$SourceId = $News['SourceId'];
			$TagId = $News['TagId'];
			$NewsText = htmlspecialchars_decode($News['NewsText'], ENT_QUOTES);

	   		//Если надо сохранить комментарий, то он сохраняется при создании экземпляра класса
	   		$comments = new comments_class($Title);

			$TagBackColor = $News['TagBackColor'];
			$TagTextColor = $News['TagTextColor'];
			$TagTitle = $News['TagTitle'];
			$SourceTitle = $News['SourceTitle'];

			$SourceLink = "<a href='news/sources/$SourceId'>$SourceTitle</a>";
			$ViewsCount = $this->get_news_views($Id);
   			$CommentsCount = $this->get_news_comments_count($Id);
			$PubLink = "news/$Id";

			$Ret = '
				<table cellpadding="0px" cellspacing="0px" border="0" width="100%">
					<tr>
						<td rowspan="2">
							<h1 class="main">'.$Title.'</h1>
						</td>
					</tr>
				</table>
    			';

                $Ret .= '<p class="article_tagline"><b>'.$PubDate.'</b> <span style="background-color: '.$TagBackColor.'; color: '.$TagTextColor.';">&nbsp;'.$TagTitle.'&nbsp;</span>';
                //$Ret .= '<p class="article_title"><b>'.$Title.'</b></p>';
                if (trim($SourceTitle)>'') {
                	$Ret .= '<p class="article_tagline"><b>'.$SourceLink.'</b>&nbsp;&rarr;</p>';
                }

				$Ret .=$Image;
				if (trim($Topic) != '') { $Ret .= '<p class="article_title"><i>'.$Topic.'</i></p><hr>';}

				$Ret .= $NewsText;
	    		//$rating = new ratings_class();
	    		$Ret .= '<br>'.$this->rating->ShowRatingForm('news', $Id);

				$SB = new social_share_class($Title);
                $SocialButtons = $SB->Code;

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
									<table cellpadding="0px" cellspacing="0px" border="0" width="100%">
										<tr>
											<td>
												<p class="article_tagline">&nbsp;Опубликовано: '.$PubDate.'&nbsp;&nbsp;<a href="'.$PubLink.'#comments">Комментарии</a>:&nbsp;'.$CommentsCount.'&nbsp;&nbsp;Просмотры:&nbsp;'.$ViewsCount.'</p>
											</td>
											<td width="250px">

									<p class="comment_text" align="left" style="text-align: right">
									'.$SocialButtons.'

											</td>
										</tr>
									</table>
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
					<div align="left">
					<p class="main_tagline">&nbsp;</p>
					</div>
			        ';
		    		//$comments = new comments_class($Title);
			        $Ret .= $comments->ShowComments($Id, 'news');

        	//Раз мы дошли до этого места кода, можно сохранить в статистике
        	//факт еще одного посещения этой страницы сайта
        	$InsertSql = 'INSERT INTO '.$this->tbl_materials_stat.' (MaterialId, MaterialCategory, ActivityType, IP, UserId, SessionId) VALUES (?,?,?,?,?,?)';
        	//echo $InsertSql;
        	$this->DB->run($InsertSql, array($Id, 2, 0, $_SERVER['REMOTE_ADDR'], $this->UserId, $_SESSION['SessionID']));
		}

        return $Ret;
	}


	############################################################################

	function get_news_views($Id) {
		$ViewsRecs = $this->DB->run('SELECT Id FROM '.$this->tbl_materials_stat.' WHERE MaterialCategory=2 AND MaterialId=?', array($Id))->fetchAll();
		return count($ViewsRecs);
    }

	############################################################################

	function get_news_comments_count($MaterialId) {
		$CommentsRecs = $this->DB->run('SELECT Id FROM '.$this->tbl_comments.' WHERE TypeTable="news" AND PostId=?', array($MaterialId))->fetchAll();
		return count($CommentsRecs);
    }


}

?>