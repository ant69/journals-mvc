<?php
//print_r($_SERVER);
class ratings_class
{
	function ratings_class($UserId=0) {
		global $DM;
		$this->UserOK = $UserId>0;
		$this->UserId = $UserId;

		$this->DB = $DM->DB;
		$this->tbl_ratings = $GLOBALS['tbl_ratings'];
	}

	############################################################################

	function ShowRatingForm($MaterialType, $MaterialId)	{
		$UserId = $this->UserId > 0 ? $this->UserId : 0;
   		$SqlMark = 'SELECT UserMark FROM '.$this->tbl_ratings.' WHERE UserId=? and MaterialId=? and MaterialType=?';
   		$UserMarkData = $this->DB->run($SqlMark, array($UserId, $MaterialId, $MaterialType))->fetch();

   		$UserMark = count($UserMarkData)==1 ? $UserMarkData['UserMark'] : 1;

        $this->calculate_rating($MaterialType, $MaterialId);
        $Yes = $this->yes;
        $No = $this->no;

		$params = "material_type=$MaterialType&material_id=$MaterialId&user_mark=$UserMark&cur_yes=$Yes&cur_no=$No&user=$UserId";

		return '
			<a name="rating">
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
	   					<iframe src="rating.htm?'.$params.'" width="100%" height="70" align="left" frameborder="0" scrolling="no">Ваш браузер не поддерживает плавающие фреймы!</iframe>
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

	################################################################################

	function SaveRating($MaterialType, $MaterialId, $UserMark) {
		$attr = array($UserMark, $this->UserId, $MaterialId, $MaterialType);
		if ($this->UserOK) {
   			$SqlMark = 'SELECT UserMark FROM '.$this->tbl_ratings.' WHERE UserId=? and MaterialId=? and MaterialType=?';
   			if ($this->DB->run($SqlMark, arrray($this->UserId, $MaterialId, $MaterialType))==0) {
                // Редактируем отметку пользоавтеля
                $Sql = 'UPDATE '.$this->tbl_ratings.' SET UserMark=? WHERE UserId=? and MaterialId=? and MaterialType=?';
   			} else {
                // Добавляем отметку пользователя
                $attr[] = $_SERVER['REMOTE_ADDR'];
                $Sql = 'INSERT INTO '.$this->tbl_ratings.' (UserMark, UserId, MaterialId, MaterialType, IP)
   				VALUES (?, ?, ?, ?, ?)';
   			}
            $this->DB->run($Sql, $attr);
            $this->calculate_rating($MaterialType, $MaterialId);
   			return true;
		}
		return false;
	}

	################################################################################
	function calculate_rating($MaterialType, $MaterialId) {
		$attr = array($MaterialId, $MaterialType);
   		$SqlMarkYes = $this->DB->run('SELECT Id FROM '.$this->tbl_ratings.' WHERE MaterialId=? and MaterialType=? and UserMark=2', $attr)->fetchAll();
   		$this->yes = count($SqlMarkYes);
   		$SqlMarkNo = $this->DB->run('SELECT Id FROM '.$this->tbl_ratings.' WHERE MaterialId=? and MaterialType=? and UserMark=0', $attr)->fetchAll();
   		$this->no = count($SqlMarkNo);

	}
	################################################################################
	function get_rating_code($MaterialType, $MaterialId)	{
		$this->calculate_rating($MaterialType, $MaterialId);
		$Ret = '';
		if ($this->yes>0) {
			$Ret .= '<b><img class="hand" border="0" src="images/icons/hand_up.gif" width="16" height="16" alt="Понравилось"><span color="#006600">'.$this->yes.'</span></b>';
		}
		if ($this->no>0) {
			$Ret .= '<b><img class="hand" border="0" src="images/icons/hand_dn.gif" width="16" height="16" alt="Не понравилось"><span color="#A31911">'.$this->no.'</span></b>';
		}
		if ($Ret>'') { $Ret = "&nbsp;|&nbsp;$Ret";}
		return $Ret;
	}

} // end of class

?>