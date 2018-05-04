<?php
include_once $GLOBALS['lib_social_share'];
include_once $GLOBALS['lib_ratings'];
include_once $GLOBALS['lib_comments'];

################################################################################

class posts_viewer_class
{
	function posts_viewer_class($DM) # конструктор
	{
		$this->UserId = $DM->User->UserId;

		$this->DB = $DM->MainDB;
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

		$this->SiteId = $DM->SiteId;
		$this->EditionId = $DM->EditionId;
		$this->EditionTitle = $this->DB->get_value($this->tbl_editions, $this->EditionId, 'Title');
		$this->rating = new ratings_class($this->UserId);
	}


	############################################################################


	function posts_archive($PageNum=1)
	{
		$RecsOnPage = 10;

		$SqlCheck = "
			SELECT p.Id as Id, p.PublishDate as PublishDate
			FROM ".$this->tbl_posts." p, ".$this->tbl_sites_posts." sp
			WHERE p.IsShow = 1 and sp.PostId=p.Id and sp.SiteId=".$this->SiteId;
			//echo $SqlCheck;
		$AllPostsRecs = $this->DB->get_simple_sql_result($SqlCheck);
		$OverallCount = count($AllPostsRecs);
		$PagesCount = $RecsOnPage>0 ? ceil($OverallCount/$RecsOnPage) : 1;
		$PageNum = $PageNum<1 ? 1 : $PageNum;
		$PageNum =($PageNum>$PagesCount) ? $PagesCount : $PageNum;
		$StartRec = ($PageNum-1)*$RecsOnPage;
		//Строим ленту навигации по страницам дайджеста
		// ----------------------------------------
		$Nav = '';
		if ($PagesCount>1)
		{
			//Записи не умещаются на одной странице
			if ($PageNum>1)
			{
				// Если текущая страница - не первая, рисуем навигацию в начало.
				$Nav .= '<a class="dotted" href="posts/page/1">самые новые записи</a>&nbsp;&nbsp;<a class="dotted" href="posts/page/'.((int)$PageNum - 1).'">страница '.((int)$PageNum - 1).'</a>&nbsp;&nbsp;&nbsp;&nbsp;';
			}

			$Nav .="<b>страница $PageNum (из $PagesCount)</b>";
			if ($PageNum<$PagesCount)
			{
				// Если текущая страница - не первая, рисуем навигацию в начало.
				$Nav .= '&nbsp;&nbsp;&nbsp;&nbsp;<a class="dotted" href="posts/page/'.((int)$PageNum + 1).'">страница '.((int)$PageNum + 1).'</a>&nbsp;&nbsp;&nbsp;<a class="dotted" href="posts/page/'.$PagesCount.'">самые ранние записи</a>';
			}
			$Nav = '<p class="main"  style="text-align: center; padding-top: 14px;">'.$Nav.'</p>';
		}
		// ----------------------------------------

		/*$Sql = "
			SELECT Id, Title, PublishDate, Topic, BlogId
			FROM ".$this->tbl_posts."
			WHERE IsShow = 1 
			ORDER BY PublishDate DESC
			LIMIT $StartRec, $RecsOnPage
			";*/
			
		$Sql = "
			SELECT p.Id, p.Title, p.PublishDate, p.Topic, p.BlogId
			FROM ".$this->tbl_posts." p, ".$this->tbl_sites_posts." sp
			WHERE IsShow = 1 and sp.PostId=p.Id and sp.SiteId=".$this->SiteId."
			ORDER BY PublishDate DESC
			LIMIT $StartRec, $RecsOnPage
			";
			
			
		//echo $Sql;
		$Ret = '
			<table cellpadding="0px" cellspacing="0px" border="0" width="100%">
				<tr>
					<td width="20%">
						<h1 class="main">Блоги</h1>
					</td>   <!--
					<td width="80%">
						<p class="main" style="text-align: right; padding-top: 0px;">
							<a class="dotted" href="posts/blogs">Блоги</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a class="dotted" href="posts">Последние</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a class="dotted" href="posts/popular">Популярные</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a class="dotted" href="posts/commented">Обсуждаемые</a>
						</p>
					</td>      -->
				</tr>
			</table>
            <hr>
				';
		$BlogRecs = $this->DB->get_simple_sql_result($Sql);
		if (count($BlogRecs)>0)
		{
			//$Ret .= '1';
//			$this->TranslitHistory = array();
			foreach($BlogRecs as $Post)
			{
				//echo '<pre>'; print_r($Article); echo '</pre>';
				$Id = $Post['Id'];
				$Title = $Post['Title'];
				$Topic = $Post['Topic'];
				$PubDate = date('d.m.Y', strtotime($Post['PublishDate']));
				$BlogId = $Post['BlogId'];
				$TagId = $Post['TagId'];

				//$RecTypeTitle = 'Блоги';
				$RecTypeBackColor = '#868686';
				$RecTypeTextColor = '#fff';
				$ShortTitle = $this->DB->get_value($this->tbl_blogs, $BlogId, 'ShortTitle');
				$SourceLink = "<a href='posts/blogs/$BlogId'>$ShortTitle</a>";
				$PubLink = "posts/$Id";
       			$ViewsCount = $this->get_posts_views($Id);
       			$CommentsCount = $this->get_posts_comments_count($Id);

				$RatingCode = $this->rating->get_rating_code('blog', $Id);

                
                $Ret .= '<div class="one_in_digest"><p class="article_title_in_digest"><a href="'.$PubLink.'">'.$Title.'</a></p>';
				$Ret .= '<p class="article_tagline_in_digest"><font style="background-color: '.$RecTypeBackColor.'; color: '.$RecTypeTextColor.';">&nbsp;'.$ShortTitle.'&nbsp;</font></p>';
				$Ret .= '<p class="article_tagline_in_digest"><span>'.$PubDate.'</span>&nbsp;&nbsp;Просмотры:&nbsp;'.$ViewsCount.'&nbsp;&nbsp;<a href="'.$PubLink.'#comments">Комментарии</a>:&nbsp;'.$CommentsCount.'&nbsp;'.$RatingCode.'</p>
				';
				if (trim($Topic) != '') { 
				$Ret .= '<p class="digest_text"><a href="'.$PubLink.'">'.$Topic.'</a></p>';
				}
				$Ret .= '</div>';
			}
        }
        return $Ret.$Nav;

	}
	############################################################################

	function get_post_page_meta($PostId)
	{
//		echo "Пост: $PostId";
		$PostTitle = $this->DB->get_value($this->tbl_posts, $PostId, 'Title');
		$PostTopic = $this->DB->get_value($this->tbl_posts, $PostId, 'Topic');
		$BlogId = $this->DB->get_value($this->tbl_posts, $PostId, 'BlogId');
		$BlogTitle = $this->DB->get_value($this->tbl_blogs, $BlogId, 'Title');
		$Ret['title'] = "Пост «".$PostTitle."»";
		$Ret['description'] = "$PostTitle, $PostTopic";
		$Ret['keywords'] = "пост, $BlogTitle";
		return $Ret;
    }

	############################################################################

	function show_post($PostId)
	{
		$Sql = "SELECT * FROM ".$this->tbl_posts."	WHERE IsShow = 1 and Id=$PostId";
		//echo $Sql;
		$BlogRecs = $this->DB->get_simple_sql_result($Sql);
		if (count($BlogRecs)>0)
		{
			$Post = $BlogRecs[0];
			//echo '<pre>'; print_r($Article); echo '</pre>';
			$Id = $Post['Id'];
			$Title = $Post['Title'];
			$Topic = $Post['Topic'];
			$PubDate = date('d.m.Y', strtotime($Post['PublishDate']));
			$BlogId = $Post['BlogId'];
			$TagId = $Post['TagId'];
			$PostText = $Post['PostText'];

	   		//Если надо сохранить комментарий, то он сохраняется при создании экземпляра класса
	   		$comments = new comments_class($Title);

			//$RecTypeTitle = 'Блоги';
			$RecTypeBackColor = '#666666';
			$RecTypeTextColor = '#eeeeee';
			$ShortTitle = $this->DB->get_value($this->tbl_blogs, $BlogId, 'ShortTitle');
			$SourceLink = "<a href='posts/blogs/$BlogId'>$Source</a>";
			$PubLink = "posts/$Id";
   			$ViewsCount = $this->get_posts_views($Id);
   			$CommentsCount = $this->get_posts_comments_count($Id);

			$Ret = '
				<table cellpadding="0px" cellspacing="0px" border="0" width="100%">
					<!--
					<tr>
						<td width="20%">
						</td>
						<td width="80%">
							<p class="main" style="text-align: right; padding-top: 0px;">
								<a class="dotted" href="posts/blogs">Блоги</a>&nbsp;&nbsp;&nbsp;&nbsp;
								<a class="dotted" href="posts">Последние</a>&nbsp;&nbsp;&nbsp;&nbsp;
								<a class="dotted" href="posts/popular">Популярные</a>&nbsp;&nbsp;&nbsp;&nbsp;
								<a class="dotted" href="posts/commented">Обсуждаемые</a>
							</p>
						</td>
					</tr> -->
					<tr>
						<td rowspan="2">
							<h1 class="main">'.$Title.'</h1>
						</td>
					</tr>
				</table>
    			';


				$Ret .= '<p class="article_title">';
				$Ret .= '<b><font style="background-color: '.$RecTypeBackColor.'; color: '.$RecTypeTextColor.';">&nbsp;'.$ShortTitle.'&nbsp;</font>
				<!--&nbsp;&nbsp;&rarr;&nbsp;&nbsp;'.$Title.'--></b></p><hr>';
				if (trim($Topic) != '') { $Ret .= '<p class="article_title"><i>'.$Topic.'</i></p><hr>';}

				$Ret .= $PostText;

	    		$Ret .= '<br>'.$this->rating->ShowRatingForm('blog', $Id);

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
												<p class="article_tagline">
												Опубликовано: '.$PubDate.'&nbsp;&nbsp;
												<a href="'.$PubLink.'#comments">Комментарии</a>:&nbsp;'
												.$CommentsCount.'&nbsp;&nbsp;
												Просмотры:&nbsp;'.$ViewsCount.'</p>
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
			        $Ret .= $comments->ShowComments($Id, 'blog');

        	//Раз мы дошли до этого места кода, можно сохранить в статистике
        	//факт еще одного посещения этой страницы сайта
        	$InsertSql = "INSERT INTO ".$this->tbl_materials_stat." (MaterialId, MaterialCategory, ActivityType, IP, UserId, SessionId) VALUES ('$Id', '1', '0', '".$_SERVER['REMOTE_ADDR']."', '".$this->UserId."', '".$_SESSION['SessionID']."')";
        	//echo $InsertSql;
        	$this->DB->exec_sql($InsertSql);
		}

        return $Ret;
	}

	############################################################################

	function get_posts_views($Id)
	{
		$Sql ="	SELECT Id
				FROM ".$this->tbl_materials_stat."
				WHERE MaterialCategory=1 AND MaterialId=$Id";
		return count($this->DB->get_simple_sql_result($Sql));
    }

	############################################################################

	function get_posts_comments_count($MaterialId)
	{
		$Sql ="	SELECT Id
				FROM ".$this->tbl_comments."
				WHERE TypeTable='blog' AND PostId=$MaterialId";
		return count($this->DB->get_simple_sql_result($Sql));
    }
}

?>