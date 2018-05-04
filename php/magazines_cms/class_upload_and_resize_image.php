<?php
/*
* Система управления контентом сайта журнала или иного издания, СУКОЖУР
* выпускаемого Издательской фирмой «Сентябрь»
*
* lib_images.php - общесистемная библиотека для работы с графикой
*
* Файл содержит набор функций, позволяющих осуществлять операции
* по манипуляциям изображениями
*
* Библиотека является составной частью ядра системы и используется
* как другими библиотеками ядра, так и подключаемыми к системе
* модулями расширения.
*
* Создан 19.05.2011
*
* Текущая версия: 1.0. Последние изменения - 31.05.2011
*
* © Издательская фирма «Сентябрь», Александр Наровлянский, Павел Антошкин
*
* Перечень классов:
* upload_image_class()
*/


/*
* upload_image_class - класс для работы с загружаемым изображением
*
* Класс определяет ряд методов, позволяющих обработать графические файлы,
* временно загруженные на сайт через POST, в том числе - сохранить их
* в конкретные каталоги с масштабированием их размеров
*
* Создан 19.05.2011
* Текущая версия: 1.0. Последние изменения - 31.05.2011
*
* Перечень параметров:
* $File - массив, содержащий параметры загруженного файла
* ...
*
* Перечень методов:
* Connect()
* Disconnect()
* ExecSQL()
*/

class upload_image_class
{
	// Конструктор класса
	// Для инициализации экземпляра класса конструктору передается
	// одномерный массив Files, содержащий сведения о загруженном файле
	function upload_image_class($File = false)
	{
		// Константы класса (могут быть переопределены?)
		// Сообщения об ошибках
		$this->ErrMsgText 			= 'Ошибка загрузки файла';
		$this->BigSizeMsgText 		= 'Размер файла превышает допустимый';
		$this->SmallSizeMsgText 	= 'Размер файла меньше допустимого';
		$this->UnsupportedTypeText 	= 'Загружаемый файл имеет не поддерживаемый системой тип';

		// Дополнительные параметры, используемые при загрузке изображения
		$this->MinFileSize 		= 5; // Максимальный размер загружаемого файла в байтах
		$this->MaxFileSize 		= 21000000; // Максимальный размер загружаемого файла в байтах

		// Дополнительные параметры, используемые при масштабировании картинки
		// Параметры рамки вокруг всего изображения
		$this->BorderWidth 		= 0;
		$this->BorderColor 		= array('r'=>0, 'g'=>0, 'b'=>0);
		// Параметры рамки вокруг внедренной картинки
		$this->InsertedBorderWidth 		= 0;
		$this->InsertedBorderColor 		= array('r'=>0, 'g'=>0, 'b'=>0);
		$this->BackgroundColor 	= array('r'=>127, 'g'=>127, 'b'=>127);
		// Выравнивание внедренной картинки при условии, что она по каким-то измерениям меньше заданных размеров
		$this->VAlign			= 'bottom';	// top, middle, bottom
		$this->HAlign			= 'center';	// left, center, right
		$this->AllowedImageTypes = array("image/pjpeg", "image/jpeg", "image/x-png", "image/png", "image/gif");

	    $this->FileType 	= $File['type'];
        $this->FileName 	= $File['name'];
        $this->FileSize 	= $File['size'];
        $this->FileTmp 		= $File['tmp_name'];
        $this->FileError	= $File['error'];


	    if ($this->check_file($File)===false)
		{
			//echo ('Загружаемый файл не является изображением!');
		}
        /*	DEBUG
        print_r($File);
		/**/
	}
	// *************************************************************************


    // *************************************************************************
	// Метод класса для проверки валидности загруженного файла
	function check_file()
	{
		$Log	= false;
		$Valid	= true;
        if(!is_uploaded_file($this->FileTmp))
        {
			$Log['error'] = $this->ErrMsgText;
        }

		if ($this->FileError>0)
		{
			$Log['error'] = $this->ErrMsgText.": ".$this->FileError;
			/*	ToDo - Стандартные коды ошибок.
			UPLOAD_ERR_OK Значение: 0; Ошибок не возникало, файл был успешно загружен на сервер.
			UPLOAD_ERR_INI_SIZE Значение: 1; Размер принятого файла превысил
				максимально допустимый размер, который задан директивой upload_max_filesize
				конфигурационного файла php.ini
			UPLOAD_ERR_FORM_SIZE Значение: 2; Размер загружаемого файла превысил значение MAX_FILE_SIZE,
				указанное в HTML-форме.
			UPLOAD_ERR_PARTIAL Значение: 3; Загружаемый файл был получен частично.
			UPLOAD_ERR_NO_FILE Значение: 4; Файл не загружен.
			*/
		}
		else
		{
			if($this->FileSize > ($this->MaxFileSize))
			{
				$Log['size'] = $this->BigSizeMsgText.": ".$this->FileSize;
			}
			elseif($this->FileSize <= ($this->MinFileSize))
			{
				$Log['size'] = $this->SmallSizeMsgText.": ".$this->FileSize;
			}
			if (!in_array($this->FileType, $this->AllowedImageTypes) )
			{
				$Log['type'] = $this->UnsupportedTypeText.": ".$this->FileType;
			}
		}
		$this->Log = $Log;
		return $this->Valid = ($Log === false) ? true : false;
	}
	// *************************************************************************


    // *************************************************************************
	// Метод класса для установки параметров рамки вокруг всего изображения
	// Width - толщина рамки в пикселях
	// Color - массив, хранящий информацию о цветах рамки
	function set_border($Width=0, $Color=array('r'=>0, 'g'=>0, 'b'=>0))
	{
		$this->BorderWidth = $Width;
		$this->BorderColor['r'] = $Color['r'];
		$this->BorderColor['g'] = $Color['g'];
		$this->BorderColor['b'] = $Color['b'];
	}
	// *************************************************************************

    // *************************************************************************
	// Метод класса для установки параметров выравнивания внедряемого изображения
	// VAlign - выравнивание по вертикали
	// HAlign - выравнивание по горизонтали
	function set_image_align($VAlign='middle', $HAlign='center')
	{
		$this->VAlign = $VAlign;
		$this->Halign = $HAlign;
	}
	// *************************************************************************

    // *************************************************************************
	// Метод класса для установки параметров рамки вокруг внедренной картинки
	// Width - толщина рамки в пикселях
	// Color - массив, хранящий информацию о цветах рамки
	function set_inserted_border($Width=0, $Color=array('r'=>0, 'g'=>0, 'b'=>0))
	{
		$this->InsertedBorderWidth = $Width;
		$this->InsertedBorderColor['r'] = $Color['r'];
		$this->InsertedBorderColor['g'] = $Color['g'];
		$this->InsertedBorderColor['b'] = $Color['b'];
	}
	// *************************************************************************


    // *************************************************************************
	// Метод класса для установки цвета фона
	// Color - массив, хранящий информацию о цветах фона
	function set_background($Color=array('r'=>255, 'g'=>255, 'b'=>255))
	{
		$this->BackgroundColor['r'] = $Color['r'];
		$this->BackgroundColor['g'] = $Color['g'];
		$this->BackgroundColor['b'] = $Color['b'];
	}
    // *************************************************************************


    // *************************************************************************
	// Метод класса для сохранения загруженного файла
	// FileName - имя нового файла
	// UploadDir - каталог загрузки файла (для этого каталога должны быть установлены атрибуты записи)
	function save($FileId, $NewFileName, $UploadDir)
	{
        $ret = false;
        if ($this->Valid)
        {
			if (file_exists($UploadDir))
			{
				$UploadFileName = $UploadDir.$NewFileName;
				$ret = move_uploaded_file($_FILES[$FileId]['tmp_name'], $UploadFileName);
        	}
        }
        return $ret;

	}
    // *************************************************************************
    // *************************************************************************
	// Метод класса для сохранения загруженного файла с его масштабированием
	// FileName - имя нового файла
	// UploadDir - каталог загрузки файла (для этого каталога должны быть установлены атрибуты записи)
	// NewImgWidth - ширина нового изображения
	// NewImgHeight - высота нового изображения
	// Mode - режим масштабирования изображения
	// - inscribe - изображение при масштабировании сохраняет пропорции и вписывается
	//				в установленные параметрами NewImgWidth и NewImgHeight габариты с центрированием
	function save_resized($FileName, $UploadDir, $NewImgWidth=1, $NewImgHeight=1, $Mode='inscribe')
	{
        if ($this->Valid)
        {

        	if($this->FileType == "image/pjpeg" || $this->FileType == "image/jpeg")
        	{
            	$NewImg = imagecreatefromjpeg($this->FileTmp);
           	}
           	elseif ($this->FileType == "image/x-png" || $this->FileType == "image/png")
           	{
            	$NewImg = imagecreatefrompng($this->FileTmp);
            }
            elseif($this->FileType == "image/gif")
            {
               $NewImg = imagecreatefromgif($this->FileTmp);
			}
			// Извлечение ширины и высоты загруженного изображения
			list($UploadImgWidth, $UploadImgHeight) = getimagesize($this->FileTmp);

			if ($UploadImgHeight!=0) {$ImgRatio = $UploadImgWidth/$UploadImgHeight;}
			else {$ImgRatio = 1;}
			if ($NewImgHeight!=0) {$NewImgRatio = $NewImgWidth/$NewImgHeight;}
			else {$NewImgRatio=1;}

			switch ($Mode)
			{
				case 'inscribe':
				{
					//echo "ImgRatio = $ImgRatio; NewImgRatio $NewImgRatio<br>";
					if ($ImgRatio > $NewImgRatio)
					{
						$Width = $NewImgWidth;
						$Height = round($NewImgWidth/$ImgRatio);
						$WSpace = 0;
					}
					else
					{
						$Width = $NewImgHeight*$ImgRatio;
						$Height = $NewImgHeight;
						$WSpace = round(($NewImgWidth - $Width)/2);

					}
					break;
				}
				case 'asdefined':
				{
					$Width = $NewImgWidth;
					$Height = $NewImgHeight;
					$WSpace = 0;
					break;
				}
				default:
						$Width = $NewImgWidth;
						$Height = $NewImgHeight;
					break;
			}
			switch ($this->VAlign)
			{
				case 'middle':	{ $HSpace = round(($NewImgHeight - $Height)/2); break; }
				case 'bottom':	{ $HSpace = $NewImgHeight - $Height; break; }

				default: // case 'top':	{ $HSpace = 0; break; }
				{
					$HSpace = 0;
					break;
				}
			}

			/*	DEBUG
			echo ("Width = $Width; Height = $Height <br>");
			/**/

			if (function_exists(imagecreatetruecolor))
			{
				$BW = $this->BorderWidth;
				$IBW = $this->InsertedBorderWidth;
				$WidthInsImage = $Width-2*$BW;
				$HeightInsImage = $Height-2*$BW;

				// Создаем картинку с заданными размерами
				$ResizedImg = imagecreatetruecolor($NewImgWidth,$NewImgHeight);

				// Заливаем созданную картинку цветом, определенным для рамки
		        if ($BW>0)
		        {
			        $BorderColor = imagecolorallocate($ResizedImg, $this->BorderColor['r'],
			        	$this->BorderColor['g'], $this->BorderColor['b']);
			        imagefilledrectangle($ResizedImg, 0, 0, $NewImgWidth-1, $NewImgHeight-1, $BorderColor);
				}

				// Заливаем область картинки внутри рамки фоновым цветом
		        $BackgroundColor = imagecolorallocate($ResizedImg, $this->BackgroundColor['r'],
		        	$this->BackgroundColor['g'], $this->BackgroundColor['b']);
		        imagefilledrectangle($ResizedImg, $BW, $BW, $NewImgWidth-$BW-1,
		        		$NewImgHeight-$BW-1, $BackgroundColor);

				// Заливаем созданную картинку цветом, определенным для рамки
		        if ($IBW>0)
		        {
			        $InsertedBorderColor = imagecolorallocate($ResizedImg, $this->InsertedBorderColor['r'],
			        	$this->InsertedBorderColor['g'], $this->InsertedBorderColor['b']);

//			        echo "WidthInsImage: $WidthInsImage, HeightInsImage: $HeightInsImage <br>";
//			        echo "BW: $BW, HSpace: $HSpace, WSpace: $WSpace <br>";
			        imagefilledrectangle($ResizedImg, $BW + $WSpace, $BW + $HSpace, $WidthInsImage + $WSpace, $HeightInsImage + $HSpace, $InsertedBorderColor);
				}
			}
			else
			{
				die("Ошибка! Пожалуйста убедитесь в том, что установлена библиотека GD ver 2+");
			}
			/*	DEBUG
			echo ("WidthInsImage = $WidthInsImage; HeightInsImage = $HeightInsImage <br>");
			echo ("UploadImgWidth = $UploadImgWidth; UploadImgHeight = $UploadImgHeight <br>");
			*/

			imagecopyresampled($ResizedImg, $NewImg, $BW + $IBW + $WSpace, $BW + $IBW + $HSpace, 0, 0, $WidthInsImage - 2*$IBW, $HeightInsImage - 2*$IBW, $UploadImgWidth, $UploadImgHeight);
			//finally, save the image
			ImageJpeg ($ResizedImg, $UploadDir.$FileName,80);
			ImageDestroy ($ResizedImg);
			ImageDestroy ($NewImg);

        }

	}
    // *************************************************************************


}


?>