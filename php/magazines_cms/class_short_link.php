<?php
class short_link_class
{
	function short_link_class($DM, $Id)
	{
		$this->DB = @$DM->MainDB;
		//$LinkInfo=MakeURL($ShortLink);
		if ($this->DB->get_value($GLOBALS['tbl_short_links'], $Id, 'Id')==$Id)
		{
			$this->Statistika($Id);
			$this->Link = $Link = $this->DB->get_value($GLOBALS['tbl_short_links'], $Id, 'main');
			$this->Title = $this->DB->get_value($GLOBALS['tbl_short_links'], $Id, 'title');
			if ($this->DB->get_value($GLOBALS['tbl_short_links'], $Id, 'redirect')==0)
			{
				header("Location: $Link"); exit;
			}
			else
			{
				$this->Content = htmlspecialchars_decode($this->DB->get_value($GLOBALS['tbl_short_links'], $Id, 'opisanie'), ENT_QUOTES);
				$this->PrePage = $this->DB->get_value($GLOBALS['tbl_short_links'], $Id, 'page');
			}
		}
		else  {header("Location: /home"); exit;}
	}

	function Statistika($id)
	{
		$ip = $_SERVER["REMOTE_ADDR"]; // Узнаем IP
		$referer = $_SERVER["HTTP_REFERER"]; // Узнаём, откуда пришёл
		$time = time(); // Берём текущее время
		$link=$id*1;
		$sql="INSERT INTO link_statistika(link,ip,time,referer) VALUES ($link,'$ip',$time,'$referer')";
		$this->DB->exec_sql($sql);
		// mysql_query($sql) or die(mysql_error()); // Добавляем запись
	}

    function ShowLink()
    {
		$Ret = '<p class="main">&nbsp;<br><b>'.$this->Title.'</b></p>';
		$Ret .= $this->Content;

		if ($this->PrePage==0)
		{
			$Ret .= '<p class="main"><a href='.$this->Link.'>Перейти по ссылке</a></p>';
	 	}

    	return $Ret;
    }
}

?>