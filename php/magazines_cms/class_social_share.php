<?php

################################################################################
# YANDEX SHARING
# Подробности — http://api.yandex.ru/share/
################################################################################

class social_share_class
{
	function social_share_class($PostTitle, $SocialButtons='lj,twitter,vkontakte,facebook,gplus,ya,mail') # конструктор
	{
        $ButtonsBank = array (
        	'lj'=>array('pic'=>'lj',
        				'url'=>'http://www.livejournal.com/update.bml?event=<<PostURL>>&subject=<<PostTitle>>',
        				'alt'=>'Поделиться в Живом журнале'),
        	'twitter'=>array('pic'=>'twitter',
        				'url'=>'http://twitter.com/home?status=RT%20@ru_direktor%20<<PostTitle>>:%20<<PostURL>>',
        				'alt'=>'Поделиться в Твиттере'),
        	'vkontakte'=>array('pic'=>'vkontakte',
        				'url'=>'http://vkontakte.ru/share.php?url=<<PostUrl>>',
        				'alt'=>'Поделиться ВКонтакте'),
        	'facebook'=>array('pic'=>'facebook',
        				'url'=>'http://www.facebook.com/sharer.php?u=<<PostURL>>',
        				'alt'=>'Поделиться в Фейсбуке'),
        	'gplus'=>array('pic'=>'google_plus',
        				'url'=>'https://plus.google.com/share?url=<<PostURL>>&hl=ru',
        				'alt'=>'Поделиться в Гугл Плюс'),
        	'ya'=>array('pic'=>'yandex',
        				'url'=>'http://share.yandex.ru/go.xml?service=yaru&url=<<PostURL>>&title=<<PostTitle>>',
        				'alt'=>'Поделиться в Яндекс.Блогах'),
        	'mail'=>array('pic'=>'mail',
        				'url'=>'http://connect.mail.ru/share?share_url=<<PostURL>>',
        				'alt'=>'Поделиться в Моем Мире'));


        $ButtonsToShow=explode(',',$SocialButtons);
		$Ret = '';
        if (count($ButtonsToShow)>0)
        {
        	foreach ($ButtonsToShow as $B)
        	{

        		if (isset($ButtonsBank[$B]))
        		{
        			$Ret .= $this->get_button_code($ButtonsBank[$B]['pic'], $ButtonsBank[$B]['url'], $ButtonsBank[$B]['alt']);
        		}
        	}
        }

		$PostURL = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$Ret = str_replace('<<PostURL>>', $PostURL, $Ret);
		$Ret = str_replace('<<PostTitle>>', iconv( 'windows-1251' , 'utf-8', $PostTitle), $Ret);

		$this->Code = $Ret;
	}

	############################################################################

	function get_button_code($pic, $url, $alt)
	{
		return '&nbsp;<a target="_blank" href="'.$url.'"><img border="0" src="images/social_icons/'.$pic.'.jpg" width="21" height="21" alt="'.$alt.'"></a>';
	}

} # end of class

################################################################################


?>