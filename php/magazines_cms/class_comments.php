<?php
//include_once "class_database.php";
include_once "lib_mail.php";
//echo '<pre>'; print_r($_SERVER); echo '</pre>';
class comments_class
{
	var $LastComment;

	function comments_class($MaterialTitle='материал с сайта') # конструктор
	{
		global $DM;
		global $IsEditor;
		global $IsAdmin;
		global $UserOK;

		$this->UserId = $DM->User->UserId;

		$this->DB = $DM->DB;
		$this->uDB = $DM->uDB;

		$this->tbl_editions = $GLOBALS['tbl_editions'];

		$this->tbl_blogs = $GLOBALS['tbl_blogs'];
		$this->tbl_posts = $GLOBALS['tbl_posts'];
		$this->tbl_sites_posts = $GLOBALS['tbl_sites_posts'];
		$this->tbl_news = $GLOBALS['tbl_news'];
		$this->tbl_sites_news = $GLOBALS['tbl_sites_news'];
		$this->tbl_comments = $GLOBALS['tbl_comments'];

		$this->tbl_users = $GLOBALS['tbl_users'];

		$this->SiteId = @$DM->SiteId;
		$this->EditionId = @$DM->EditionId;
		$this->EditionTitle = $this->DB->run('SELECT Title FROM '.$this->tbl_editions.' WHERE Id=?', array($this->EditionId))->fetchColumn();

		$this->MaterialTitle = $MaterialTitle;

		$this->IsEditor = $IsEditor;
		$this->IsAdmin = $IsAdmin;
		$this->UserOK = $UserOK;
		$this->CurUser = @$DM->User->Data;
		$CurUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$CurUrlParts = parse_url($CurUrl);
		$this->ActionPage = $CurUrlParts['path'];
        if (in_array($_GET['do'], array('addcomment', 'editcomment')))
        {
        	$this->SaveComment();
        }
        if ($_GET['do']=='deletecomment')
        {
        	$this->DeleteComment($_GET['id']);
        }

	}

	############################################################################

	function BuildKey($ParentKey, $Level, $Num, $MaxLevel)
	{
		$digits = 4; //количество разрядов для каждого уровня комментариев

		$foo = strval($Num);
		for ($i = 1; $i <= $digits; $i++)
		{
			if ( strlen($foo) < $digits ) { $foo = "0".$foo; }
		}

		if ( $Level == 1) //значит это - элемент верхнего уровня
		{
			for ($i = 1; $i <= $MaxLevel; $i++)
			{
				if ( strlen($foo) < $MaxLevel*$digits ) { $foo = $foo."0000"; }
			}
		}
		else
		{
	    	if ($GLOBALS['Debug'] == true) { echo "<br><i>Foo=$foo, num=$Num</i><br>"; }
			$foo = substr_replace ( $ParentKey, $foo, $digits*($Level-1), $digits );
		}

	    if ($GLOBALS['Debug'] == true) { echo "<b>BuildKey:</b> ParentKey=$ParentKey, Level=$Level, Num=$Num, MaxLevel=$MaxLevel @ <b>Result=$foo</b><br>"; }
	    return $foo;
	}

	############################################################################

	function ShowComments($Id, $TypeTable)
	{
		$UserId = $this->UserId;
		$CurUser = @$this->CurUser;
		$CommentsTable = $this->tbl_comments;
        $UsersTable = $this->tbl_users;
        $ActionPage = $this->ActionPage;
		# Получаем все комментарии к материалу
		$SqlComments = '
					SELECT c.*
					FROM '.$CommentsTable.' c
					WHERE (c.PostId = ?) and (c.TypeTable = "'.$TypeTable.'")
					ORDER BY c.Created';
		//echo $SqlComments;
		$Comments = $this->DB->run($SqlComments, array($Id))->fetchAll();
		$CommentsCount = count($Comments);
	    if ($CommentsCount != 0) # если есть хоть один комментарий...
	    {
			# загоняем их в массив, в качестве ключа пока выступит id записи, выясняем глубину дерева
			$MaxLevel = 1;
			for ($i=0; $i<$CommentsCount; $i++)
			{
				$Comment = $Comments[$i];
				$CommArray[$i]["Id"] = $Comment['Id'];
				$CommArray[$i]["PostID"] = $Comment['PostID'];
				$CommArray[$i]["Level"] = 	$Comment['Level'];
				$CommArray[$i]["PID"] = 	$Comment['PID'];

				$CommArray[$i]['AllowEdit']=($Comment['IsActive'] == "1");
				$CommArray[$i]["Text"] = $CommArray[$i]['AllowEdit'] ?	$Comment['Text'] : "<i>Комментарий удален</i>";

				$CommArray[$i]["Created"] = $Comment['Created'];
				$CommArray[$i]["CreatedTS"] = intval(strtotime( $CommArray[$i]['Created']));
				$CommArray[$i]["AuthorId"] = $Comment['AuthorId'];

				$AuthorsSql = 'SELECT * FROM $UsersTable WHERE Id=?';
				//echo $AuthorsSql;
				$CurAuthors = $this->uDB->run($AuthorsSql, array($Comment['AuthorId']))->fetchAll();
				$CurAuthor = count($CurAuthors==1) ? $CurAuthors[0] : 'автор комментария не определен';

				$CommArray[$i]["Author"] = $CurAuthor['F'].' '.$CurAuthor['I'].' '.$CurAuthor['O'];

				//$Foo = $CurUser->get_user_data_by_id($CommArray["AuthorID"]);
				$CommArray[$i]["AuthorDetails"] = is_array($CurAuthor)
					? "<b>".$CurAuthor['F']." ".$CurAuthor['I']." ".$CurAuthor['O']."</b><br>".$CurAuthor['SchoolName']
					: '';
				{}
				$CommArray[$i]["ChildCount"] = 0;
				{}
				if ($CommArray[$i]["Level"] > $MaxLevel) { $MaxLevel = $CommArray[$i]["Level"]; }
			}
			//echo '<pre>'; print_r($CommArray); echo '</pre>';
			$CurTopLevelNodeCount = 0; //счетчик узлов первого уровня
			# вычисляем новые ключи и сортируем массив
			for ($i=0; $i<$CommentsCount; $i++)
			{
			    if ( $CommArray[$i]["Level"] == 1)
			    {
				   	$CurTopLevelNodeCount++;
			    	$NewKey = $this->BuildKey('', 1, $CurTopLevelNodeCount, $MaxLevel);
			        $CommArray[$i]["NewKey"] = $NewKey;
			    }
			    else
			    {
					$PID = $CommArray[$i]["PID"];
			        //находим папу с ID = PID, узнаем его ключ, увеличиваем количество его потомков на 1
					for ($j = 0; $j < $CommentsCount; $j++)
					{
						if ($CommArray[$j]['Id'] == $PID)
						{
							$ParentKey = $CommArray[$j]['NewKey'];
							$CommArray[$j]["ChildCount"] = $CommArray[$j]["ChildCount"] + 1;
					    	$NewKey = $this->BuildKey(
					    			$ParentKey,
					    			$CommArray[$i]["Level"],
				    				$CommArray[$j]["ChildCount"],
				    				$MaxLevel);
							break;
						}
					}
			        $CommArray[$i]["NewKey"] = $NewKey;
			    }
			}
			//echo '<pre>'; print_r($CommArray); echo '</pre>';

			function cmp($a, $b) { return strcmp($a["NewKey"], $b["NewKey"]); }
			usort($CommArray, "cmp");
	    }

		//$PubDate=$pub_date;
		//$ViewsCnt=$views_count;
		//$EditLink=$edit_link;


			$Ret = '
			<a name="comments">
			<table border="0" width="100%" cellpadding="0" cellspacing="0" >
				<tr>
					<td height="180" valign="top">';

			$Ret .='
					<p class="right_block"><b>Комментарии:</b></p>';

		if (!$this->UserOK)
		{
			$Ret .='
					<!-- Начало блока разделителя -->
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td background="images/frames/bubble/top_left_no_nozzle.png" width="5" height="5"></td>
							<td background="images/frames/bubble/top.png" height="5"></td>
							<td background="images/frames/bubble/top_right.png" width="5" height="5"></td>
						</tr>
						<tr>
							<td background="images/frames/bubble/left_no_nozzle.png" valign="top" width="5">
							&nbsp;</td>
							<td bgcolor="#ececea" valign="top">
						<p class="right_block" align="center"><b>Для того, чтобы оставлять комментарии, вам нужно авторизоваться на сайте.</b></p>
						<p class="right_block" align="left">Если вы еще не являетесь пользователем этого сайта — самое время <a href="reg.htm"><font color="#000000">зарегистрироваться</font></a>.
							</td>
							<td background="images/frames/bubble/right.png" width="5">
							&nbsp;</td>
						</tr>
						<tr>
							<td background="images/frames/bubble/bottom_left_no_nozzle.png" width="5" height="5"></td>
							<td background="images/frames/bubble/bottom.png" height="5"></td>
							<td background="images/frames/bubble/bottom_right.png" width="5" height="5"></td>
						</tr>
					</table><br>';
		}
		else
		{
			$Ret .= "<script src='http://".$_SERVER['HTTP_HOST']."/js/ckeditor/ckeditor.js'></script>";
		}

        if ($CommentsCount>0)
        {
        	$Ret .= "
	<script language='javascript'>

	var editor;
	var main_editor;
	var last_editor_id;
	var last_comment_editor_id;

	last_editor_id = -1;
	last_comment_editor_id = -1;

	function CloseEditor(idElement)
	{
		document.getElementById('NewComment'+idElement).style.display = 'none';
		document.getElementById('StaticComment'+idElement).style.display = 'inline';
	}

	function CloseCommentEditor(idComment)
	{
		document.getElementById('DynamicComment' + idComment).style.display = 'none';
		document.getElementById('StaticComment'+idComment).style.display = 'inline';
	}

	function ShowEditor(idElement)
	{
		if (last_editor_id != -1) { CloseEditor(last_editor_id); } // закрываем предыдущий
		last_editor_id = idElement;
		if (last_comment_editor_id != -1) { CloseCommentEditor(last_comment_editor_id); } // закрываем предыдущий
		last_comment_editor_id = -1;

		document.getElementById('StaticComment'+idElement).style.display = 'none';
		document.getElementById('NewComment'+idElement).style.display = 'inline';
		CKEDITOR.replace( 'editor' + idElement );

	}
	function EditComment(idComment)
	{
		if (last_comment_editor_id != -1) { CloseCommentEditor(last_comment_editor_id); } // закрываем предыдущий
		last_comment_editor_id = idComment;
		if (last_editor_id != -1) { CloseEditor(last_editor_id); } // закрываем предыдущий
		last_editor_id = -1;

		document.getElementById('DynamicComment' + idComment).style.display = 'inline';
		document.getElementById('StaticComment' + idComment).style.display = 'none';
		CKEDITOR.replace( 'dynamic_editor' + idComment );

	}

	function confirmation(cid, url)
	{
		var answer = confirm('Вы действительно хотите удалить этот комментарий?');
		if (answer){
			window.location = url + '#comments';
	}
}

	</script>
        	";
        }
		# вывод дерева комментарие
		for ($i = 0; $i < $CommentsCount; $i++)
		{
			$key = key($CommArray);
			$c_Id = $CommArray[$key]["Id"];
			$c_AuthorId = $CommArray[$key]["AuthorId"];
			$c_AuthorF = $CommArray[$key]["Author"];
			$AuthorDetails = $CommArray[$key]["AuthorDetails"];
			$c_Created = date("d.m.Y в H:i", strtotime($CommArray[$key]["Created"]));
			$c_CreatedTS = $CommArray[$key]["CreatedTS"];
			$level = $CommArray[$key]["Level"];
			$nextlevel = $level + 1;
			$level_shift = 1+($level-1)*30;
			$AllowEdit=$CommArray[$key]["AllowEdit"];
			$c_Text = htmlspecialchars_decode($CommArray[$key]["Text"], ENT_QUOTES);
	   	    next($CommArray);

			$Ret .= '	<a name="'.$c_Id.'">
						<table border="0" width="100%" cellspacing="0" cellpadding="0">
							<tr>
								<td width="'.$level_shift.'" valign="top" rowspan="2">
									<p class="main">&nbsp;</p>
								</td>
								<td valign="top">';

			if ($this->IsAdmin or ($this->UserId==$c_AuthorId))
			{

				$Ret .= '
						<div id="DynamicComment'.$i.'" style="display: none;"><br>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td background="images/frames/bubble/top_left_no_nozzle.png" width="5" height="5"></td>
						<td background="images/frames/bubble/top.png" height="5"></td>
						<td background="images/frames/bubble/top_right.png" width="5" height="5"></td>
					</tr>
					<tr>
						<td background="images/frames/bubble/left_no_nozzle.png" valign="top" width="5">
						&nbsp;</td>
						<td bgcolor="#ececea" valign="top">

						<p class="comment_mode" ">Вы редактируете следующий комментарий:</p>
						<div class="comment_text">'.$c_Text.'</div>
						<form method="POST" action="'.$ActionPage.'?do=editcomment&id='.$Id.'#'.$c_Id.'">
							<input type="hidden" name="commentid" value="'.$c_Id.'">
							<p class="comment" style="text-align: left">
							<font color="#515151">
							<div class="comment_text">
							<textarea id = "dynamic_editor'.$i.'" class="comment_edit" name="comment_text"
									rows="5" id="textarea" width="600px" >'.$c_Text.'</textarea>
							</div>
							</font>
							<p class="comment_submit" style="text-align: left">
							<input type="submit" value="Сохранить" name="B1"></p>
						</form>
						</td>
						<td background="images/frames/bubble/right.png" width="5">
						&nbsp;</td>
					</tr>
					<tr>
						<td background="images/frames/bubble/bottom_left_no_nozzle.png" width="5" height="5"></td>
						<td background="images/frames/bubble/bottom.png" height="5"></td>
						<td background="images/frames/bubble/bottom_right.png" width="5" height="5"></td>
					</tr>
				</table>
	            </div>';
			}

			$Ret .= '
	            <div id="StaticComment'.$i.'">
	                <div class="comment_text">'.$c_Text.'</div>
					<p class="comment_tagline">
					<font color="#E4E4E2">
					<span style="background-color: #444444" onmouseover="tooltip.show('."'$AuthorDetails'".');" onmouseout="tooltip.hide();" >&nbsp;'.$c_AuthorF.'&nbsp;</span></font>
											&nbsp;'.$c_Created;
				//$Ret .= "UserOK=".$this->UserOK.", AllowEdit=$AllowEdit";
				if ($this->UserOK and $AllowEdit)
				{
					$Ret .= '
					&nbsp;|&nbsp;<a id="showreply0" onClick="javascript:ShowEditor('.$i.');" style="cursor: pointer; text-decoration: underline;">Комментировать</a>';
				}

	//Печатаем ссылки для редактирования и удаления комментария только если текущий пользователь - админ или автор комментария
				//if ($IsAdmin) {echo "Админ <br>" ; } else {echo "не Админ <br>" ; }
				//if ($allow_edit) {echo "Можно редактировать <br>"  ; } else {echo "Нельзя редактировать <br>" ; }
				//echo "UserId=$UserId <br>" ;
				//echo "c_AuthorId = $c_AuthorId <br>" ;
				if (($this->IsAdmin or ($UserId==$c_AuthorId))  and $AllowEdit)
				{
					$Ret .= '&nbsp;|&nbsp;<a onClick="javascript: EditComment('.$i.');" style="cursor: pointer; text-decoration: underline;"><font color="#A31911">Изменить комментарий</font></a>&nbsp;|&nbsp;<a onClick="javascript:confirmation('.$c_Id.', '."'$ActionPage".'?do=deletecomment&id='."$c_Id'".')" style="cursor: pointer; text-decoration: underline;"><font color="#A31911">Удалить</font></a>';
				}

				$Ret .= '
				</div>

				<!-- НАПИСАНИЕ КОММЕНТАРИЯ -->
				<div id="NewComment'.$i.'" style="display: none;"><br>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td background="images/frames/bubble/top_left_no_nozzle.png" width="5" height="5"></td>
						<td background="images/frames/bubble/top.png" height="5"></td>
						<td background="images/frames/bubble/top_right.png" width="5" height="5"></td>
					</tr>
					<tr>
						<td background="images/frames/bubble/left_no_nozzle.png" valign="top" width="5">
						&nbsp;</td>
						<td bgcolor="#ececea" valign="top">
			                <p class="comment_mode">Вы оставляете комментарий к следующему комментарию:</p>
			                <div class="comment_text">'.$c_Text.'</div>
							<p class="comment_tagline">
							<font color="#E4E4E2">
							<span style="background-color: #444444" onmouseover="tooltip.show('."'$AuthorDetails'".');" onmouseout="tooltip.hide();" >&nbsp;'.$c_AuthorF.'&nbsp;</span></font>
							&nbsp;'.$c_Created.'
							<form style="margin-top: 10px;" method="POST" action="'.$ActionPage.'?do=addcomment&id='.$Id.'#'.$c_Id.'">
								<input type="hidden" name="post_id" value="'.$Id.'">
								<input type="hidden" name="author_id" value="'.$UserId.'">
								<input type="hidden" name="level" value="'.$nextlevel.'">
								<input type="hidden" name="pid" value="'.$c_Id.'">
								<input type="hidden" name="type_table" value="'.$TypeTable.'">
								<p class="comment" style="text-align: left"><font color="#515151">
								<div class="comment_text">
								<textarea id = "editor'.$i.'" class="comment_edit" name="comment_text"
								 rows="5"></textarea>
								 </div></font>
								<p class="comment_submit" style="text-align: left">
								<input type="submit" value="Сохранить" name="B1"></p>
							</form>
						</td>
						<td background="images/frames/bubble/right.png" width="5">
						&nbsp;</td>
					</tr>
					<tr>
						<td background="images/frames/bubble/bottom_left_no_nozzle.png" width="5" height="5"></td>
						<td background="images/frames/bubble/bottom.png" height="5"></td>
						<td background="images/frames/bubble/bottom_right.png" width="5" height="5"></td>
					</tr>
				</table>
	            </div>

									</td>
								</tr>
							</table>';
		}
        if ($CommentsCount>0)
        {
        	$Ret .= '<br>';
        }

		if ($this->UserOK)
		{
		$Ret .= '
	<!-- Форма комментария 1 уровня -->
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td background="images/frames/bubble/top_left_no_nozzle.png" width="5" height="5"></td>
					<td background="images/frames/bubble/top.png" height="5"></td>
					<td background="images/frames/bubble/top_right.png" width="5" height="5"></td>
				</tr>
				<tr>
					<td background="images/frames/bubble/left_no_nozzle.png" valign="top" width="5">
					&nbsp;</td>
					<td bgcolor="#ececea" valign="top">
						<a name="comment0"><p class="comment_mode">Напишите свой комментарий ко всему материалу:</p>
						<p class="comment_remark">(Если вы хотите ответить на ранее оставленный комментарий,
						нажмите ссылку "комментировать",<br> расположенную под соответствующим комментарием)</p>
						<form method="POST" action="'.$this->ActionPage.'?do=addcomment#comments">
							<input type="hidden" name="post_id" value="'.$Id.'">
							<input type="hidden" name="author_id" value="'.$UserId.'">
							<input type="hidden" name="level" value="1">
							<input type="hidden" name="pid" value="0">
							<input type="hidden" name="type_table" value="'.$TypeTable.'">
							<div class="comment_text">
							<textarea id="new_comment" class="comment_edit" name="comment_text" rows="6"></textarea>
							</div>
							</font>
							<p class="comment_submit" style="text-align: left"><input type="submit" value="Сохранить" name="B1"></p>
						</form>'.
						"<script>
							CKEDITOR.replace('new_comment');
						</script>".'

					</td>
					<td background="images/frames/bubble/right.png" width="5">
					&nbsp;</td>
				</tr>
				<tr>
					<td background="images/frames/bubble/bottom_left_no_nozzle.png" width="5" height="5"></td>
					<td background="images/frames/bubble/bottom.png" height="5"></td>
					<td background="images/frames/bubble/bottom_right.png" width="5" height="5"></td>
				</tr>
			</table>';

		}

		$Ret .= '
				</td>
			</tr>
		</table>';

		return $Ret;
	}

/*	################################################################################

	function EditComment()
	{
		$this->LastComment=false;
		$CommentId = $_POST['commentid'];
		$Text = $_POST['comment_text'];
	if ($Text != '')
		{
			$this->db->update_record('comments', $CommentId, 'Text', $Text);
		}
	}

*/
	################################################################################

	function SaveComment()
	{
		if (isset($_POST) && $_POST['author_id']>0)
		{
			$referer_url=parse_url($_SERVER['HTTP_REFERER']);
			$cur_page = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
			$ref_page = $referer_url['host'].$referer_url['path'];
			if ($referer_url['host'] != $_SERVER['HTTP_HOST'])
			{
				exit;
			}

			$this->LastComment=@$_POST;
			$PostID = $_POST['post_id'];
			$Level = $_POST['level'];
			$PID = $_POST['pid'];
			$AuthorId = $_POST['author_id'];
			$TypeTable = $_POST['type_table'];
			//$Text = strip_tags($_POST['comment_text']);
			$Text = htmlspecialchars($_POST['comment_text'], ENT_QUOTES);
			$sql_check="SELECT Id FROM ".$this->tbl_comments."
						WHERE AuthorId='$AuthorId' and Text='$Text' and PostID='$PostID'
								and PID='$PID' and TypeTable='$TypeTable'";
			//echo $sql_check;
			if (!$this->DB->get_count($sql_check))
			{

				if ($Text != '')
				{
					# если это новая запись, то создаем ее...
					if (isset($_POST['commentid']))
					{
						$this->LastComment=false;
						$this->DB->update_record($this->tbl_comments, $_POST['commentid'], 'Text', $Text);
						$SendComment=false;
					}
					else
					{
						$rec_id =  $this->DB->create_new_record($this->tbl_comments);
						$this->DB->update_record($this->tbl_comments, $rec_id, 'PostID', $PostID);
						$this->DB->update_record($this->tbl_comments, $rec_id, 'Level', $Level);
						$this->DB->update_record($this->tbl_comments, $rec_id, 'PID', $PID);
						$this->DB->update_record($this->tbl_comments, $rec_id, 'AuthorId', $AuthorId);
						$this->DB->update_record($this->tbl_comments, $rec_id, 'Text', $Text);
						$this->DB->update_record($this->tbl_comments, $rec_id, 'IsActive', 1);
						$this->DB->update_record($this->tbl_comments, $rec_id, 'TypeTable', $TypeTable);
						$SendComment=true;
					}
					//$this->UpdateCommentCounter($PostID, $TypeTable);
		//			$foo = $this->db->GetValue($TypeTable, $PostID, 'CommentCounter');
		//			$foo = $foo + 1;
		//			$this->db->UpdateRecord($TypeTable, $PostID, 'CommentCounter', $foo);
					$this->LastComment['CommentId']=$rec_id;
				}
	/**/		if ($SendComment) {$this->SendComment();}
			}
			//$this->UpdateCommentCounter($PostID, $TypeTable);
		}
	}

/*
	################################################################################

	function UpdateCommentCounter($PostId, $TypeTable='blog')
	{
		$foo = $this->DB->get_count("SELECT Id FROM ".$this->tbl_comments." WHERE PostID='$PostId' and TypeTable='$TypeTable'");
		//echo "SELECT Id FROM comments WHERE PostID='$PostId' and TypeTable='$TypeTable'";
		//echo $foo;
		$this->DB->update_record($TypeTable, $PostId, 'CommentCounter', $foo);
    }
*/
	################################################################################

	function SendComment()
	{
        $MaterialTitle = $this->MaterialTitle;
		$ActionPage = $this->ActionPage;
		if ($Com=@$this->LastComment)
		{
			//echo '<pre>'; print_r($Com); echo '</pre>';
			$PostID = $Com['post_id'];
			$Level = $Com['level'];
			$PID = $Com['pid'];
			$AuthorId = $Com['author_id'];
			$TypeTable = $Com['type_table'];
            $SiteName = $this->EditionTitle;

			$AuthorSql = "SELECT F, I, O, Email FROM ".$this->tbl_users." WHERE Id=$AuthorId";
			$CurAuthors = $this->uDB->get_simple_sql_result($AuthorSql);
			$CommentAuthorFIO = $CurAuthors[0]['F'].' '.$CurAuthors[0]['I'].' '.$CurAuthors[0]['O'];

	        $CommentTextToSend=stripslashes(strip_tags($Com['comment_text']));
	        $CommentText=$Com['comment_text'];
	        $SiteUrl ="http://".$_SERVER['HTTP_HOST'];
	        $ReplyLink= $SiteUrl.$ActionPage."#".$Com['CommentId'];

		    //Отправка уведомления автору того комментария, на который был дан новый комментарий
		    if ($PID>0)
		    {
   				$ParentAuthorId=$this->DB->get_value($this->tbl_comments, $PID, 'AuthorId');
   				$ParentCommentText=$this->DB->get_value($this->tbl_comments, $PID, 'Text');
                $ParentCommentText=substr(stripslashes(strip_tags(htmlspecialchars_decode($ParentCommentText, ENT_QUOTES))),0,100).' ...';
				if ($ParentAuthorId!=$AuthorId)
				{
					$ParentSql = "SELECT F, I, O, Email FROM ".$this->tbl_users." WHERE Id=$ParentAuthorId";
					$CurAuthors = $this->uDB->get_simple_sql_result($ParentSql);
					if (count($CurAuthors)==1)
					{
				        $F=$CurAuthors[0]['F'];
				        $I=$CurAuthors[0]['I'];
				        $O=$CurAuthors[0]['O'];
				        $Email=$CurAuthors[0]['Email'];

						$subject = "Новый комментарий с сайта издания $SiteName";
						$body =
						"Здравствуйте, $I $O!\n\n".
						"Пользователь $CommentAuthorFIO ответил на ваш комментарий \"$ParentCommentText\" к материалу \"$MaterialTitle\" на сайте издания \"$SiteName\":\n\n".
						"\"$CommentTextToSend\"\n\n".

						"Чтобы ответить на этот комментарий на сайте, перейдите по ссылке ($ReplyLink).\n\n".
						"-----------------------\nC уважением,\nСлужба технической поддержки Издательской фирмы \"Сентябрь\"\n\n".
						"Телефон: (495) 710-30-01\nЭлектронная почта: support@direktor.ru\nИнтернет-сайт: $SiteUrl";


						send_smtp_mail($F.' '.$I.' '.$O,				// имя получателя
			            $Email,							// email получателя
		                $subject, 						// тема письма
		                $body, 							// текст письма
		                'Техподдержка ИФ Сентябрь'		// имя отправителя
		                );                      /* */
						/*send_mime_mail(	'Редакция сайта',		// имя отправителя
						               	$GLOBALS['FeedbackEmail'],		// email отправителя
									 	$F.' '.$I.' '.$O,		 		// имя получателя
							            $Email, 						// email получателя
						                'CP1251', 						// кодировка переданных данных
						                'KOI8-R', 						// кодировка письма
						                $subject, 						// тема письма
						                $body 							// текст письма
						                );*/
					}
				}
		    }
			//Отправка уведомлений администраторам сайта
	        $Email=$GLOBALS['FeedbackEmail'];

	//письмо зарегистрировавшемуся пользователю
			$subject = "Новый комментарий с сайта издания $SiteName";
			$body =
	"Пользователь $CommentAuthorFIO оставил комментарий к материалу \"$MaterialTitle\" на сайте издания \"$SiteName\":\n\n".
	"\"$CommentTextToSend\"\n\n".

	"Посмотреть комментарий можно здесь: ($ReplyLink).\n\n".
	"-----------------------\nC уважением,\nСлужба технической поддержки Издательской фирмы \"Сентябрь\"\n\n".
	"Телефон: (495) 710-30-01\nЭлектронная почта: support@direktor.ru\nИнтернет-сайт: $SiteUrl";


			send_smtp_mail('Редактор сайта '.$SiteUrl,				// имя получателя
			            $Email,							// email получателя
		                $subject, 						// тема письма
		                $body, 							// текст письма
		                'Техподдержка ИФ Сентябрь'		// имя отправителя
		                );
			/**/
			/*send_mime_mail(	'Служба технической поддержки ИФ Сентябрь',	// имя отправителя
	               	'support@direktor.ru', 			// email отправителя
				 	'Редактор сайта '.$SiteUrl,		// имя получателя
		            $Email, 						// email получателя
	                'CP1251', 						// кодировка переданных данных
	                'KOI8-R', 						// кодировка письма
	                $subject, 						// тема письма
	                $body 							// текст письма
	                );*/

		}

	}

	################################################################################

function DeleteComment($id)
{

	$IsAdmin = $this->IsAdmin;
	if ($this->UserOK)
    {
		//echo "SELECT AuthorId FROM ".$this->tbl_comments." WHERE Id='".$id."'";
		$Comment = $this->DB->get_simple_sql_result("SELECT AuthorId FROM ".$this->tbl_comments." WHERE Id='".$id."'");
		$IsCommentAuthor=($Comment[0]['AuthorId']==$this->UserId);
		if ($IsAdmin || $IsCommentAuthor)
		{
			//echo "Можно удалять!";
			$this->DB->update_record($this->tbl_comments, $id, 'IsActive', 0);
		}
    }
}

} // end of class

?>