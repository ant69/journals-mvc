<?php
################################################################################

class site_menu_class
{
	function site_menu_class($DM) # конструктор
	{
		$this->DM = @$DM;
		$this->DB = @$DM->DB;
		$this->tbl_site_menu = $GLOBALS['tbl_site_menu'];
	}

	############################################################################

	function show_main_menu()
	{
		global $User;
    	$MenuItems = $this->DM->get_main_menu_items();
    	//echo '<pre>'; print_r($MenuItems); echo '</pre>';
    	$MenuImagesDir = $this->DM->MenuImages;
    	$Divider = $MenuImagesDir."top_menu_divider.png";
		$DividerImg = '<img border="0" src="'.$Divider.'" width="1" height="37">';
    	//$Menu = $DividerImg.'<a href="home"><img border="0" src="'.$MenuImagesDir.'top_menu_home.png'.'" width="36" height="37" alt="Домашняя страница сайта" name="m0" onmouseover="document.m0.src='."'".$MenuImagesDir."top_menu_home_hover.png'".'" onmouseout="document.m0.src='."'$MenuImagesDir"."top_menu_home.png'".'" border="0"></a>';
    	$Menu = '';
    	if (is_array($MenuItems))
    	{
	    	foreach ($MenuItems as $MenuItem)
	    	{
	    		$ItemId="Item".$MenuItem['Id'];
	    		if ($MenuItem['Rank']>0)
	    		{
	    			if ($ImageInfo = getimagesize($MenuImagesDir.$MenuItem['NormalImgFile']))
	    			{
        				$MenuWidth = $ImageInfo[0];
		    			$Menu .= $DividerImg.'<a href="'.$MenuItem['Link'].'"><img border="0" src="'.$MenuImagesDir.$MenuItem['NormalImgFile'].'" width="'.$MenuWidth.'" height="37" alt="" name="'.$ItemId.'" onmouseover="document.'.$ItemId.'.src='."'$MenuImagesDir".$MenuItem['ActiveImgFile']."'".'" onmouseout="document.'.$ItemId.'.src='."'$MenuImagesDir".$MenuItem['NormalImgFile']."'".'" border="0"></a>';
		 			}
	    		}
	    	}
	  	}
        if ($User->IsEditor or $User->Data['GlobalAdmin'])
        {
        	$AdminEntrance = '<td width="38">'.$DividerImg.'<a href="admin"><img border="0" src="'.$MenuImagesDir.'top_menu_to_adminpanel.png'.'" width="36" height="37" alt="Домашняя страница сайта" name="m50" onmouseover="document.m50.src='."'".$MenuImagesDir."top_menu_to_adminpanel_hover.png'".'" onmouseout="document.m50.src='."'$MenuImagesDir"."top_menu_to_adminpanel.png'".'" border="0"></a></td>';
        	$UserMenuWidth = 984;
        }
        else
        {
        	$AdminEntrance = '';
        	$UserMenuWidth = 1023;
        }
        $MenuBackground = $MenuImagesDir.'top_menu_background.png';
echo <<<END
		<table border="0" width="100%" cellspacing="0" cellpadding="0" background="$MenuBackground" height="37">
			<tr>
				<td><p align="left" class="top">&nbsp;</p></td>
				<td width="$UserMenuWidth">
					<p align="left" class="top">$Menu$DividerImg</p>
				</td>
				$AdminEntrance
				<td width="1">
					<p align="right" class="top">
					$DividerImg</p>
				</td>
				<td><p align="left" class="top">&nbsp;</p></td>
			</tr>
		</table>
END;

	}


} # end of class

################################################################################


?>