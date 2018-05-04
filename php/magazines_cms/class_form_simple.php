<?php

include_once "class_file_managment.php";

class form_simple_class //простая форма со стандартным для проекта форматированием
{
	function form_simple_class() # конструктор
	{
	}
	function form_header($action, $InOneLine = false)
	{
		echo "<form action='$action' method='post'>";
		if ($InOneLine) echo '<p class="main">';
    }
	function form_subheader($text)
	{
		echo "<h2 class='main'>$text</p>";
    }
	function form_header_multipart($action, $InOneLine = false)
	{
		echo "<form action='$action' method='post' enctype='multipart/form-data'>";
		if ($InOneLine) echo '<p class="main">';
    }
	function form_footer($InOneLine = false)
	{
		if (!$InOneLine) echo '<br><br>'; else echo "&nbsp;&nbsp;";
		echo '<input type="submit" value="Сохранить" name="B3">&nbsp;&nbsp;<input type="reset" value="Отмена" name="B2">';
		echo '</form>';
    }
	function hidden_value($vname, $vvalue)
	{
		echo "<input type='hidden' name='$vname' value='$vvalue'>";
	}
	function input_string($vname, $vvalue, $title, $size, $InOneLine = false)
	{
		if (!$InOneLine)
			echo "<p class='main'>$title<br><input type='text' name='$vname' size='$size' value='$vvalue'>";
		else
			echo "$title<input type='text' name='$vname' size='$size' value='$vvalue'>";
	}
	function input_text($vname, $vvalue, $title, $cols, $rows)
	{
		echo "<p class='main'>$title<br><textarea name='$vname' cols='$cols' rows='$rows'>$vvalue</textarea>";
	}
	function input_text_CKEditor($vname, $vvalue, $title)
	{
		echo "<p class='main'>$title<br><textarea name='$vname'>$vvalue</textarea>";
		echo "<script type='text/javascript'>	CKEDITOR.replace( '$vname' ); </script>";
	}
	function check_box($vname, $vvalue, $title, $InOneLine = false)
	{
		if ($vvalue == 1) $checked = 'checked'; else $checked = '';
		if (!$InOneLine) echo "<p class='main'>";
		echo "<input type='hidden' name='$vname' value='0'>";
		echo "<input type='checkbox' name='$vname' value='1' $checked>$title";
		if (!$InOneLine) echo "</p>";
	}
	function drop_list($vname, $vvalue, $title, $list_values, $id_field_name, $value_field_name, $InOneLine = false)
	{
		if (!$InOneLine) { echo "<p class='main'>$title<br>"; }
		else  { echo "$title &nbsp;"; }
		echo "<select size='1' name='$vname'>";

  		for ($i = 0; $i <= count($list_values)-1; $i++)
		{
			$id = $list_values[$i][$id_field_name];
			$value = $list_values[$i][$value_field_name];
			if ($vvalue == $id) $selected = 'selected'; else $selected = '';
			echo "<option $selected value='$id'>$value</option>";
        }
		echo "</select>";
	}

/*
	function drop_list($vname, $vvalue, $title, $list_values, $id_field_name, $value_field_name)
	{
		echo "<p class='main'>$title<br>";
		echo "<select size='1' name='$vname'>";

  		for ($i = 0; $i <= count($list_values)-1; $i++)
		{
			$id = $list_values[$i][$id_field_name];
			$value = $list_values[$i][$value_field_name];
			if ($vvalue == $id) $selected = 'selected'; else $selected = '';
			echo "<option $selected value='$id'>$value</option>";
        }
		echo "</select>";
	}
*/
	function file_upload($title, $target_dir, $new_file_name, $max_file_size=1000000, $Name='filename')
	{
		echo "<p class='main'>$title<br>";
		echo "<input type='hidden' name='TrgDir' value='$target_dir'>";
		echo "<input type='hidden' name='NewName' value='$new_file_name'>";
		echo "<input type='hidden' name='MAX_FILE_SIZE' value='$max_file_size'>";
		echo "<input name='$Name' type='file' size='60'>";
	}

	function print_horizontal_line()
	{
       echo "<p><hr></p>";
    }

	function print_break()
	{
       echo "<br>&nbsp;<br>";
    }


} # end of class

class form_list_to_list_class //форма со стандартным для проекта форматированием
{
	function form_list_to_list_class() # конструктор
	{
	}
	function form_header($action, $InOneLine = false)
	{
		echo "<form action='$action' method='post'>";
		if ($InOneLine) echo '<p class="main">';
    }
	function form_footer($InOneLine = false)
	{
		echo '</form>';
    }
	function hidden_value($vname, $vvalue)
	{
		echo "<input type='hidden' name='$vname' value='$vvalue'>";
	}
	// $selected_values - массив, содержащий в качестве элементов Id записей, которые выбраны
	// $all_values- массив, содержащий пары Id - Value
	function lists($width, $CurList, $AllList)
	{

	$SelectedListOptions = '';
	$OthersListOptions = '';
	if (is_array($AllList))
	{
		foreach ($AllList as $ItemId=>$ItemValue)
		{
	//	  $FIO=$user['F'].' '.$user['I'].' '.$user['O'].' (Login: '.$user['Login'].', Id='.$user['Id'].')';
		  $CurOption="<option value='$ItemId'>$ItemValue</option>";
		  if (in_array($ItemId, $CurList)) { $SelectedListOptions .= $CurOption; }
		  else {$OthersListOptions .= $CurOption; }
		}
	}

echo <<<END
	<table width="$width">
		<tr>
			<td width="100%" valign="middle" align="center">
				<p class='main'><b>Файлы, "привязанные" к текущей статье</b></p>
			  <select size="5" name="CurListName[]" multiple="multiple" style="width: 100%;">
			   	$SelectedListOptions
	       	  </select>
			</td>
		</tr>
		<tr>
			<td valign="middle" align="center">
				<input type="submit" value="&uarr;&nbsp;Добавить" name="addbutton">&nbsp;&nbsp;
				<input type="submit" value="Удалить&nbsp;&darr;" name="deletebutton">
			</td>
		</tr>
		<tr>
			<td width="100%" valign="middle" align="center">
				<p class='main'><b>Все файлы, имеющиеся на сайте</b></p>
			  <select size="20" name="AllListName[]" multiple="multiple" style="width: 100%;">
			    $OthersListOptions
	       	  </select>
			</td>
		</tr>
	</table>

END;
	}

}
?>