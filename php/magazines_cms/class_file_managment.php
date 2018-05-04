<?php

class file_managment_class
{
	function file_managment_class() # конструктор
	{
	}

	############################################################################

	function file_upload_form($FormAction, $TrgDir, $DefFileName, $TrgURL, $NameTemplates = false) # форма загрузки файла
	{
echo <<<END
		<form enctype="multipart/form-data" method="POST" action="$FormAction">
			<input type="hidden" name="TrgDir" value="$TrgDir">
			<input type="hidden" name="TrgURL" value="$TrgURL">
            <p>
			Выберите файл:<br>
			<input name="filename" type="file" size="60">
			<br>
			Задать новое имя для файла:<br>
END;
	if ($NameTemplates == true)
		{
echo <<<END
			<select size="1" name="newname">
				<option>mini.jpg</option>
				<option>normal.jpg</option>
				<option>reg.pdf</option>
				<option>sert.pdf</option>
				<option>sez.pdf</option>
				<option>manual.pdf</option>
			</select>
END;
		}
	else
		{
echo <<<END
			<input type="text" name="newname" size="60" value="$DefFileName">
END;
        }
echo <<<END
			<br>
			Описание файла (не обязательно):<br>
			<input type="text" name="filedesc" size="60" value="">
			<br>
			<br>
			<input type="submit" value="Загрузить" name="B1">&nbsp;
			</p>
		</form>
END;
	}

	############################################################################

	function do_file_upload()
	{
		#echo $TrgURL = $_POST['TrgURL'];

		#загружаем файл
		switch ($_FILES['filename']['error'])
		{
			case 0: $error_string = "Файл был успешно загружен на сервер";  break;
			case 1: $error_string = "Размер принятого файла превысил максимально допустимый размер";  break;
			case 2: $error_string = "Размер загружаемого файла превысил максимальный размер для формы";  break;
			case 3: $error_string = "Загружаемый файл был получен только частично";  break;
			case 4: $error_string = "Файл не был загружен";  break;
		}

		$OriginalFileName = $_FILES['filename']['name'];

		if ($OriginalFileName != '')
		{
			$FileType = $_FILES['filename']['type'];
			$Size = $_FILES['filename']['size'];
		    $Ext = substr(strrchr($OriginalFileName,'.'), 1);

		    if ($_POST['NewName'] == '')
		    	$NameToSave = $OriginalFileName;
		    else
		    	$NameToSave = $_POST['NewName'];

			$uploaddir = $_POST['TrgDir'];
			$uploadfilename = $uploaddir.$NameToSave;

			if (move_uploaded_file($_FILES['filename']['tmp_name'], $uploadfilename))
			{
			    #все в порядке, файл загружен
			    /*
			    if ($_POST['TrgURL'] != '') $Msg = "<a href='".$_POST['TrgURL']."''>Продолжить...</a>";
   			    echo "<p><br>Файл ".$NameToSave." успешно загружен.<br><br>$Msg</p>";
   			    */
			}
			else
			{
			    echo $error_string;
			}
		}

	}

	############################################################################

	function get_file_list($Dir, $PrintList = false) # возвращает (и печатает) список файлов в каталоге
	{
		if (file_exists($Dir))
		{
			// Вывести список всех файлов в каталоге Оператор !== не существовал до версии 4.0.0-RC2
			$i = 0;
			if ($handle = opendir($Dir))
			{
			    #echo "Дескриптор каталога: $handle\n";
			    #echo "Файлы:\n";
			    while (false !== ($file = readdir($handle)))
			    {
			    	if (($file != '.') and ($file != '..'))
			    	{
				    	$i++;
				        $files[$i] = $file;
				        if ($PrintList == true) echo "$file<br>";
					}
			    }
			    closedir($handle);
			}
			return $files;
		}
		else
		{
			return false;
		}

	}

	############################################################################


	function file_force_download($file, $FileName=false)
	{
		//echo $file;

		if (file_exists($file))
		{
			// сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
			// если этого не сделать файл будет читаться в память полностью!
			if (ob_get_level())
			{
			  ob_end_clean();
			}
			//$FileName=false;
			$FileName = $FileName ? $FileName : basename($file);
			$FileName = $this->translit($FileName);
			// заставляем браузер показать окно сохранения файла
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.$FileName);
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));

			// читаем файл и отправляем его пользователю
			if ($fd = fopen($file, 'rb'))
			{
			  while (!feof($fd))
			  {
			    print fread($fd, 1024);
			  }
			  fclose($fd);
			}
			exit;
		}/*  */
	}
	############################################################################	}

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
			"." => ".", "," => ""
	    );

		if(preg_match("/[а-яА-Я ]/", $CyrStr)) { $Ret = strtr($CyrStr, $trans); }
		else { $Ret = $CyrStr; }

		while (strpos($Ret, '__')!==false) { $Ret = str_replace('__', '_', $Ret) ;}

		if (strlen($Ret)>$Len) { $Ret = substr($Ret, 0, $Len); }
		return $Ret;
	}


}
?>