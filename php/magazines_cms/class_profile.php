<?php

//include_once "config.php";
//include_once "db.php";
include_once "lib_mail.php";
include_once "lib_common.php";
include_once "class_client_validation.php";

################################################################################

class profile_class {
	function profile_class($DM) {
		$this->DM = @$DM;
		$this->DB = $DM->DB;
		$this->uDB = $DM->uDB;
		$this->SubscribeDB = $DM->SubscribeDB;
		$this->UserOK = $DM->UserOK;
		$CurrentPath = @$DM->current_page_path;
		$this->Mode = isset($CurrentPath[1]) ? $CurrentPath[1] : 'profile';
		//$this->Covers = $GLOBALS['Covers'];

		$this->tbl_issues = $GLOBALS['tbl_issues'];
		$this->tbl_rubrics = $GLOBALS['tbl_rubrics'];
		$this->tbl_articles = $GLOBALS['tbl_articles'];
		$this->tbl_authors = $GLOBALS['tbl_authors'];
		$this->tbl_articles_authors = $GLOBALS['tbl_articles_authors'];

		$this->EditionId = $DM->EditionId;
		$this->RegionsList = $this->GenerateRegionsList();
		$this->GendersList = array('1'=>'Мужской', '2'=>'Женский');
		if ($_POST['SaveForm']==1) {
			//echo '<pre>'; print_r($_POST); echo '</pre>';
			if ($this->CheckPost()) {
				$this->SaveForm();
	            //$Login = $this->ClientData['Login'];
	            //$Pass = $this->ClientData['Pass'];
	            //$user->authorize_user($Login, $Pass);
	            //$UserCreated = true;
                $this->JustSaved = true;
			}
		}
			//echo '<pre>'; print_r($this->Valid); echo '</pre>';

	}

	############################################################################

	function show_page()  {
		switch ($this->Mode) {
			case 'restore': {
				$Ret = $this->show_restore_form();
				break;
			}
			default: {
				if ($this->UserOK) {
					$Ret = $this->show_profile();
				} else {
					if ($_POST['SaveForm']!=1) {
						//Если регистрационная форма открывается по ссылке,
						//задаем значения по умолчанию для отдельных полей профиля
						$this->ClientData['Subscription']=1;
					}
					$Ret = $this->show_registration_form();
				}
				break;
			}
		}
        return $Ret;
	}
	############################################################################

	function show_registration_form() {
    	$Ret  = '<h1 class="main">Регистрация на сайте</h1>';
    	if ($this->JustSaved || ($_POST['action']=='login')) {
			$Ret .= '
		<div align="right">
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
					<td bgcolor="#FFFFFF"><p class="right_block" style="color: green;"><b>Благодарим Вас за регистрацию на сайте нашего журнала!<b></p>
					<p class="right_block">Вы можете сразу же авторизоваться на сайте журнала с помощью формы, расположенной ниже.</p>
					<p class="right_block">В дальнейшем вы можете проходить авторизацию с помощью небольшой формы, расположенной в правом верхнем углу каждой страницы сайта.</p>

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
		<p class="main">&nbsp;</p><hr>';
			$Ret .= '
					<form method="POST" action="">
					<input type="hidden" name="Mode" value="'.$this->Mode.'">
					<input type="hidden" name="action" value="login">
						<table border="0" width="100%" id="table2" cellspacing="3" cellpadding="0">';

			$Ret .= $this->echo_text_field('Логин:', 'userName', '', '');
			$Ret .= $this->echo_pass_field('Пароль:', 'userPass', '', '');

			$Ret .= '		<tr><td colspan="3"><hr></td></tr>

							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td><p class="main">
									<input type="submit" value="Войти" name="SaveButton"
									style="text-font: Verdana; width: 120px;"></p>
								</td>
						</table>
					</form>';
    	} else {
			if ($this->FormHasErrors) {
				$Ret .= '
		<div align="right">
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
					<td bgcolor="#FFFFFF"><p class="block" style="color: #BD2D2D"><b>Внимание!</b><br>Форма содержит ошибки. Пожалуйста, проверьте заполнение полей регистрационной формы.</p></td>
					<td background="images/frames/right_white_rubber/right.png" width="5"></td>
				</tr>
				<tr>
					<td background="images/frames/right_white_rubber/bottom_left.png" height="5" width="5"></td>
					<td background="images/frames/right_white_rubber/bottom.png" height="5"></td>
					<td background="images/frames/right_white_rubber/bottom_right.png" height="5" width="5"></td>
				</tr>
			</tbody>
		</table>

				';
			}
			$Ret .= '<p class="main" style=" padding-top: 6px;">Все поля, отмеченные звездочкой (<span style="color: #BD2D2D">*</span>), являются обязательными.</p>';
			$Ret .= '
					<form method="POST">
					<input type="hidden" name="Mode" value="'.$this->Mode.'">
					<input type="hidden" name="SaveForm" value="1">
					<input type="hidden" name="ClientIsPerson" value="1">
						<table border="0" width="100%" id="table2" cellspacing="3" cellpadding="0">
							<tr>
								<td colspan="3">
								<h2 class="main">Авторизационные данные</h2>
								</td>
							</tr>
							<tr>
								<td colspan="3">
									<hr>
									<p class="main">Придумайте себе логин и пароль, с помощью которых вы сможете проходить авторизацию на нашем сайте. Поскольку при вводе пароля вводимые вами символы в целях безопасности будут скрываться, пароль необходимо ввести два раза, чтобы исключить возможность случайной ошибки.</p>
								</td>
							</tr>';
			$Ret .= $this->echo_text_field('Логин:', 'rLogin', $this->ClientData['Login'], $this->Valid->errLogin);
			$Ret .= $this->echo_pass_field('Пароль:', 'rPass', $this->ClientData['Pass'], $this->Valid->errPass);
			$Ret .= $this->echo_pass_field('Пароль (повторно):', 'rPass2', $this->ClientData['Pass2'], $this->Valid->errPass2);
			$Ret .= '		<tr>
								<td colspan="3">
								<h2 class="main">Персональные данные</h2>
								</td>
							</tr>
							<tr>
								<td colspan="3">
									<hr>
									<p class="main">Введите свои данные. Если Вы будете оставлять комментарии на сайте журнала, то Ваши ФИО будут отображаться вместе с ними.</p>
								</td>
							</tr>';
			$Ret .= $this->echo_text_field('Фамилия:', 'rF', $this->ClientData['F'], $this->Valid->errF);
			$Ret .= $this->echo_text_field('Имя:', 'rI', $this->ClientData['I'], $this->Valid->errI);
			$Ret .= $this->echo_text_field('Отчество:', 'rO', $this->ClientData['O'], $this->Valid->errO);
	        $Ret .= $this->echo_select_field('Пол:', 'rGender', $this->ClientData['Gender'], $this->Valid->errGender, $this->GendersList, '--укажите свой пол--');
			$Ret .= $this->echo_text_field('Дата рождения (дд.мм.гггг):', 'rBirthday', $this->ClientData['Birthday'], $this->Valid->errBirthday, false, false);

			$Ret .= '			<tr><td colspan="3"><hr></td></tr>
								<tr>
									<td colspan="3">
									<h2 class="main">Профессиональная информация</h2>
									</td>
								</tr>
								<tr>
									<td colspan="3">
									<p class="main">Сведения, заполняемые Вами в этой части регистрационной анкеты, не будут публиковаться без Вашего согласия. Они могут быть использованы только редакцией журнала для анализа информации о посетителях сайта, а также для возможной связи с Вами.</p>
									</td>
								</tr>';
			$Ret .= $this->echo_text_field('Название организации:', 'rWorkPlace', $this->ClientData['WorkPlace'], $this->Valid->errWorkPlace);
			$Ret .= $this->echo_text_field('Ваша должность:', 'rWorkPosition', $this->ClientData['WorkPosition'], $this->Valid->errWorkPosition);
			$Ret .= $this->echo_textarea_field('Один абзац о себе:', 'rBio', $this->ClientData['Bio'], $this->Valid->errBIO, false, false);
			$Ret .= '			<tr><td colspan="3"><hr></td></tr>
								<tr>
									<td colspan="3">
									<h2 class="main">Контактные данные</h2>
									</td>
								</tr>
								<tr>
									<td colspan="3">
									<p class="main">Ваши контактные данные могут использоваться редакцией журнала для анализа информации о посетителях сайта, а также для возможной связи с Вами.<br>
									На указанный Вами при регистрации адрес электронной почты будут приходить ответы на Ваши комментарии, а также те рассылки, на которые Вы захотите подписаться.</p>
									</td>
								</tr>';
			$Ret .= $this->echo_text_field('Электронная почта:', 'rEmail', $this->ClientData['Email'], $this->Valid->errEmail);
			$Ret .= $this->echo_text_field('Телефон:', 'rPhone', $this->ClientData['Phone'], $this->Valid->errPhone, false, false);
	        $Ret .= $this->echo_select_field('Регион:', 'rRegionId', $this->ClientData['RegionId'], $this->Valid->errRegion, $this->RegionsList, '--выберите свой регион--');
			$Ret .= $this->echo_text_field('Индекс:', 'rZipCode', $this->ClientData['ZipCode'], $this->Valid->errPostIndex, 6, false);
			$Ret .= $this->echo_textarea_field('Почтовый адрес:', 'rPostAddress', $this->ClientData['PostAddress'], $this->Valid->errPostAddress, false, false);
			$Ret .= $this->echo_text_field('Адрес личного сайта (блога):', 'rPersonalBlog', $this->ClientData['PersonalBlog'], $this->Valid->errPersonalBlog, false, false);

			$Ret .= '				<tr><td colspan="3"><hr></td></tr>

									<tr>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td><p class="main">
											<input type="submit" value="Сохранить" name="SaveButton"
											style="text-font: Verdana; width: 120px;"></p></td>
								</table>
							</form>';
		}
		return  $Ret;
	}
	############################################################################

	function show_profile()  {
    	$this->ClientData = @$this->DM->User->Data;

    	$Ret  = '<h1 class="main">Профиль пользователя сайта</h1>';
		$Ret .= '<p class="main" style=" padding-top: 6px;">Все поля, отмеченные звездочкой (<span style="color: #BD2D2D">*</span>), являются обязательными.<br>Вы можете редактировать свои данные, распложенные в этой форме, однако Вам не удастся их сохранить, если какие-то поля из тех, которые являются обязательными на нашем сайте, вы не укажете.</p>';
		if ($this->FormHasErrors > '') {
			$Ret .= '
	<div align="right">
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
				<td bgcolor="#FFFFFF"><p class="block" style="color: #BD2D2D"><b>Внимание!</b><br>Форма содержит ошибки. Пожалуйста, проверьте правильность заполнение полей своего профиля.</p></td>
				<td background="images/frames/right_white_rubber/right.png" width="5"></td>
			</tr>
			<tr>
				<td background="images/frames/right_white_rubber/bottom_left.png" height="5" width="5"></td>
				<td background="images/frames/right_white_rubber/bottom.png" height="5"></td>
				<td background="images/frames/right_white_rubber/bottom_right.png" height="5" width="5"></td>
			</tr>
		</tbody>
	</table>

			';
		}
		$Ret .= '
				<form method="POST">
				<input type="hidden" name="Mode" value="'.$this->Mode.'">
				<input type="hidden" name="SaveForm" value="1">
				<input type="hidden" name="ClientIsPerson" value="1">
					<table border="0" width="100%" id="table2" cellspacing="3" cellpadding="0">
						<tr>
							<td colspan="3">
							<h2 class="main">Авторизационные данные</h2>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<hr>
								<p class="main">Вы можете изменить свои логин и пароль. По умолчанию поля для ввода пароля в этой форме пустые. Если Вы не будете их заполнять, при сохранении формы старый пароль останется без изменений.</p>
							</td>
						</tr>';
		$Ret .= $this->echo_text_field('Логин:', 'rLogin', $this->ClientData['Login'], $this->Valid->errLogin);
		$Ret .= $this->echo_pass_field('Пароль:', 'rPass', '', $this->Valid->errPass);
		$Ret .= $this->echo_pass_field('Пароль (повторно):', 'rPass2', $this->ClientData['Pass2'], $this->Valid->errPass2);
		$Ret .= '		<tr>
							<td colspan="3">
							<h2 class="main">Персональные данные</h2>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<hr>
								<p class="main">Если Вы будете оставлять комментарии на сайте журнала, то Ваши ФИО будут отображаться рядом с ними.</p>
							</td>
						</tr>';
		$Ret .= $this->echo_text_field('Фамилия:', 'rF', $this->ClientData['F'], $this->Valid->errF);
		$Ret .= $this->echo_text_field('Имя:', 'rI', $this->ClientData['I'], $this->Valid->errI);
		$Ret .= $this->echo_text_field('Отчество:', 'rO', $this->ClientData['O'], $this->Valid->errO);
        $Ret .= $this->echo_select_field('Пол:', 'rGender', $this->ClientData['Gender'], $this->Valid->errGender, $this->GendersList, '--укажите свой пол--');
		$Birthday = date('d.m.Y', strtotime($this->ClientData['Birthday']));
		$Ret .= $this->echo_text_field('Дата рождения (дд.мм.гггг):', 'rBirthday', $Birthday, $this->Valid->errBirthday, false, false);

		$Ret .= '			<tr><td colspan="3"><hr></td></tr>
							<tr>
								<td colspan="3">
								<h2 class="main">Профессиональная информация</h2>
								</td>
							</tr>
							<tr>
								<td colspan="3">
								<p class="main">Сведения, хранящиеся в этой части Вашего профиля, не будут публиковаться без Вашего согласия. Они могут быть использованы только редакцией журнала для анализа информации о посетителях сайта, а также для возможной связи с Вами.</p>
								</td>
							</tr>';
		$Ret .= $this->echo_text_field('Название организации:', 'rWorkPlace', $this->ClientData['WorkPlace'], $this->Valid->errWorkPlace);
		$Ret .= $this->echo_text_field('Ваша должность:', 'rWorkPosition', $this->ClientData['WorkPosition'], $this->Valid->errWorkPosition);
		$Ret .= $this->echo_textarea_field('Один абзац о себе:', 'rBio', $this->ClientData['Bio'], $this->Valid->errBIO, false, false);
		$Ret .= '			<tr><td colspan="3"><hr></td></tr>
							<tr>
								<td colspan="3">
								<h2 class="main">Контактные данные</h2>
								</td>
							</tr>
							<tr>
								<td colspan="3">
								<p class="main">Ваши контактные данные могут использоваться редакцией журнала для анализа информации о посетителях сайта, а также для возможной связи с Вами.<br>
								На указанный Вами адрес электронной почты будут приходить ответы на Ваши комментарии, а также те рассылки, на которые Вы захотите подписаться.</p>
								</td>
							</tr>';
		$Ret .= $this->echo_text_field('Электронная почта:', 'rEmail', $this->ClientData['Email'], $this->Valid->errEmail);
		$Ret .= $this->echo_text_field('Телефон:', 'rPhone', $this->ClientData['Phone'], $this->Valid->errPhone, false, false);
        $Ret .= $this->echo_select_field('Регион:', 'rRegionId', $this->ClientData['RegionId'], $this->Valid->errRegion, $this->RegionsList, '--выберите свой регион--');
		$Ret .= $this->echo_text_field('Индекс:', 'rZipCode', $this->ClientData['ZipCode'], $this->Valid->errPostIndex, 6, false);
		$Ret .= $this->echo_textarea_field('Почтовый адрес:', 'rPostAddress', $this->ClientData['PostAddress'], $this->Valid->errPostAddress, false, false);
		$Ret .= $this->echo_text_field('Адрес личного сайта (блога):', 'rPersonalBlog', $this->ClientData['PersonalBlog'], $this->Valid->errPersonalBlog, false, false);
		$Ret .= '               <tr><td colspan="3"><hr></td></tr>
								<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><p class="main">
										<input type="submit" value="Сохранить" name="SaveButton"
										style="text-font: Verdana; width: 120px;"></p></td>
							</table>
						</form>';
		/*
		$AuthCode = md5($this->ClientData['Email']);
		$ShiftedId = $this->ClientData['Id'] * 3 + 2012;
		$AuthLink = "http://news.direktor.ru/subscribe.htm?sId=$ShiftedId&auth=$AuthCode";
        */


		return  $Ret;//"<p class='main'>Показываем форму регистрации</p>";



	}
	############################################################################

	function show_restore_form() {
	    /*todo: pavel Отказ от явного хранения паролей.
	    Восстановление доступа - с обязательной сменой пароля.*/
    	$Ret  = '<h1 class="main">Восстановление пароля</h1>';
        $this->JustSaved = false;
    	if ($_POST['action']=='restore') {
			//$ShowForm=true;
			$Email = $_POST['Email'];
			if ($Data = $this->uDB->run('SELECT F, I, O, Login, Pass_support, Email FROM auth_users WHERE Email=?', array($Email))->fetch()) {
                $I=$Data['I'];
                $O=$Data['O'];
                $Login=$Data['Login'];
                $Pass=$Data['Pass_support'];

                $SiteName=$this->DM->SiteName;
                $SiteUrl="http://".$_SERVER['SERVER_NAME'];

                //письмо пользователю
                $subject = "Восстановление авторизационных данных для сайта издания \"$SiteName\"";
                $body =
                    "Здравствуйте, $I $O!\n\n".
                    "На сайте издания \"$SiteName\" ($SiteUrl) был осуществлен запрос на восстановление авторизационных данных.\n\n".
                    "Ваш Логин - $Login\n".
                    "Ваш Пароль - $Pass\n\n".
                    "Если Вы не отправляли запроса на восстановление логина и пароля, Вы можете сообщить об этом в службу технической поддержки сайта.\n\n".
                    "Если вы хотите изменить свои персональные данные для доступа к сайту издания \"$SiteUrl\", войдите в Личный кабинет пользователя сайта ($SiteUrl/profile) и внесите желаемые изменения.\n\n".
                    "-----------------------\n".
                    "C уважением,\nСлужба технической поддержки сайта издания \"SiteName\"\n\n".
                    "Издательская фирма \"Сентябрь\"\n".
                    "Телефон: (495) 710-30-01\nЭлектронная почта: support@direktor.ru\nИнтернет-сайт: http://shop.direktor.ru";

                send_smtp_mail($Email,				// имя получателя
                    $Email,							// email получателя
                    $subject, 						// тема письма
                    $body, 							// текст письма
                    'Служба технической поддержки ИФ Сентябрь'		// имя отправителя
                );
                /*send_mime_mail(	'Служба технической поддержки ИФ Сентябрь',	// имя отправителя
                                      'support@direktor.ru', 			// email отправителя
                                 $Email,			 				// имя получателя
                                $Email, 						// email получателя
                                'CP1251', 						// кодировка переданных данных
                                'KOI8-R', 						// кодировка письма
                                $Subject, 						// тема письма
                                $Body 							// текст письма
                                );*/

                $this->JustSaved = true;

            }
    	}

    	if ($this->JustSaved == false) {
			$Ret .= '
		<div align="right">
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
					<td bgcolor="#FFFFFF"><p class="right_block">Для восстановления своего пароля Вам нужно внести в расположенную ниже форму адрес своей электронной почты. В результате по указанному Вами адресу придет письмо, содержащее информацию о Ваших авторизационных данных.</p>

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
		<p class="main">&nbsp;</p><hr>';
			$Ret .= '
					<form method="POST">
					<input type="hidden" name="Mode" value="'.$this->Mode.'">
					<input type="hidden" name="action" value="restore">
						<table border="0" width="100%" id="table2" cellspacing="3" cellpadding="0">';

			$Ret .= $this->echo_text_field('Адрес Вашей электронной почты:', 'Email', '', '');

			$Ret .= '		<tr><td colspan="3"><hr></td></tr>

							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td><p class="main">
									<input type="submit" value="Отправить" name="SaveButton"
									style="text-font: Verdana; width: 120px;"></p>
								</td>
						</table>
					</form>';
    	} else {
    		$Ret .= '
				<p class="main">
					Письмо с Вашими авторизационными данными (Логин и Пароль) было выслано на адрес
					<b>'.$Email.'</b><br><br>
				    После получения этого письма Вы сможете авторизоваться на сайте, введя присланные Вам логин и пароль для входа.
				    <br><br>
				    Если по каким-то причинам Вы не получили письма с авторизационными данными,
				    свяжитесь пожалуйста со <a href="mailto:support@direktor.ru">службой технической поддержки</a>.
				</p>
    		';
    	}
    	return $Ret;
	}
	############################################################################

	function show_subscribe_info() {
    	$this->ClientData = @$this->DM->User->Data;

		$AuthCode = md5($this->ClientData['Email']);
		$ShiftedId = $this->ClientData['Id'] * 3 + 2012;
		$AuthLink = "http://news.direktor.ru/subscribe.htm?sId=$ShiftedId&auth=$AuthCode";

		$Ret ='
				<p class="right_block" style=" padding-top: 6px;">Являясь зарегистрированным пользователем сайта издания «'.$this->DM->SiteName.'», вы также можете использовать свои регистрационные данные и для авторизации на других сайтах Издательской фирмы «Сентябрь». Список наших сайтов Вы можете найти в "подвале" этой страницы.</p>
				<h1 class="right_block" style="text-align: left"> Подписка на рассылки</h1>
				<p class="right_block">
				<a href="http://news.direktor.ru/subscribe.htm" target="_blank">
				Актуальная информация для<br>
				руководителей образования
				</a>
				</p>
		';
		return $Ret;
	}
	############################################################################

	function CheckPost() {
	    //todo:pavel Проверить функцию CheckPost!
		//exit;
		$ClientData = array();
		$P = stripslashes_array($_POST);
		$P = htmlspecialchars_array($P);
		$ClientData['Login'] = $P['rLogin'];
		$ClientData['Pass'] = $P['rPass'];
		$ClientData['Pass2'] = $P['rPass2'];
		$ClientData['F'] = $P['rF'];
		$ClientData['I'] = $P['rI'];
		$ClientData['O'] = $P['rO'];
		$ClientData['Gender'] = $P['rGender'];
		$ClientData['Birthday'] = $P['rBirthday'];
		$ClientData['Email'] = $P['rEmail'];
		$ClientData['Phone'] = $P['rPhone'];
		$ClientData['RegionId'] = $P['rRegionId'];
		$ClientData['ZipCode'] = $P['rZipCode'];
		$ClientData['PostAddress'] = $P['rPostAddress'];
		$ClientData['PersonalBlog'] = $P['rPersonalBlog'];
		$ClientData['WorkPlace'] = $P['rWorkPlace'];
		$ClientData['WorkPosition'] = $P['rWorkPosition'];
		$ClientData['Bio'] = $P['rBio'];
		$ClientData['Subscription'] = $P['rSubscription'];
		$ClientData['Captcha'] = $P['rCaptcha'];
		$this->ClientData = @$ClientData;

		$ClientValidation = new ClientValidation_class($this->DM, $ClientData);

		$ClientValidation->Validate($this->DM->User->Data['Id']);

		$this->FormHasErrors = $ClientValidation->HasErrors;
		$this->Valid = @$ClientValidation;
		return !$this->FormHasErrors;
	}


	############################################################################

	function SaveForm() {
        //todo:pavel Проверить функцию SaveForm!
		//exit;
		$P = stripslashes_array($_POST);
		//$P = htmlspecialchars_array($P);
		$Login=$P['rLogin'];
		$Pass_support=$P['rPass'];
		//$Pass=md5($Pass_support);
        $PassHash = password_hash($P['rPass'], PASSWORD_DEFAULT);
		$F=$P['rF'];
		$I=$P['rI'];
		$O=$P['rO'];
		$Birthday=date('Y-m-d', strtotime($P['rBirthday']));
		$Gender=$P['rGender'];

		$Email=$P['rEmail'];
		$Phone=addslashes($P['rPhone']);
		$ZipCode=$P['rZipCode'];
		$PostAddress=addslashes($P['rPostAddress']);
		$RegionId=$P['rRegionId'];
		$PersonalBlog=addslashes($P['rPersonalBlog']);

		$WorkPlace=addslashes($P['rWorkPlace']);
		$WorkPosition=addslashes($P['rWorkPosition']);
		$Bio=addslashes($P['rBio']);

		$SiteName=$this->DM->SiteName;
		$SiteUrl="http://".$_SERVER['SERVER_NAME'];

		$Region = $this->uDB->run('SELECT Name FROM auth_regions WHERE Id=?', array($RegionId))->fetchColumn();
		//добавляем пустую запись в БД
		//$timestamp = time();

		//$UserDB=@$this->uDB;
		//$UserDB->reconnect();

		//$Region = $UserDB->get_value('auth_regions', $RegionId, 'Name');

		$UserTable=$this->auth->tbl;
        $SqlParams = array($Login, $F, $I, $O, $Gender, $Birthday, $Email, $Phone, $ZipCode, $PostAddress, $RegionId, $PersonalBlog, $WorkPlace, $WorkPosition, $Bio);
		if ($this->UserOK) {
			$UserId = $this->DM->User->UserId;
			//$PassForSql = (trim($P['rPass'])>'') ? "Pass='$Pass', Pass_support='$Pass_support', " : "";
			$PassForSql = '';
			if (trim($P['rPass'])>'') {
			    $PassForSql = 'PassHash=?';
			    $SqlParams[]=$PassHash;
            }

			$Sql = "UPDATE auth_users ";
			$Sql .= 'SET Login=?, F=?, I=?, O=?, Gender=?, Birthday=?, Email=?, Phone=?, ZipCode=?, PostAddress=?, RegionId=?, PersonalBlog=?, WorkPlace=?, WorkPosition=?, Bio=? $PassForSql
			 WHERE Id=$UserId';
            $ToSendMessage = false;
		} else {
			$Sql = "INSERT INTO auth_users";
            $SqlParams[]=$PassHash;
            $SqlParams[]=$SiteName;
			$Sql .= "(RegisteredDate, Login, F, I, O, Gender, Birthday, Email, Phone, ZipCode, PostAddress, RegionId, PersonalBlog, WorkPlace, WorkPosition, Bio, PassHash, RecordSource)
			VALUES (CURRENT_TIMESTAMP, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $ToSendMessage = true;
		}
		//echo $Sql;

		$SqlRes = $this->uDB->run($Sql, $SqlParams);

		/*$CurUserSql = "SELECT Id FROM auth_users WHERE Login=? and Email=?";
		$CurUserData = $this->uDB->run($CurUserSql, array($Login, $Email));*/
		//$UserId=$CurUserData[0]['Id'];
		$UserId=$this->uDB->insertId();

		//вывод на страницу браузера

		if ($ToSendMessage) {
			// рассылка писем-уведомлений
			$Phone=$_POST['rPhone'];
			$PostAddress=$_POST['rPostAddress'];
			$WorkPlace=$_POST['rWorkPlace'];

			//письмо зарегистрировавшемуся пользователю
			$subject = "Регистрация на сайте издания \"$SiteName\"";
			$body =
			"Здравствуйте, $I $O!\n\n".
			"Спасибо вам за регистрацию на сайте издания \"$SiteName\" ($SiteUrl)!\n\n".
			"Для авторизации на этом сайте используйте, пожалуйста, следующие данные:\n".
			"Логин: $Login \n".
			"Пароль: $Pass_support \n\n".
			"Для управления своим аккаунтом авторизуйтесь на сайте $SiteUrl и войдите в Личный кабинет пользователя сайта ($SiteUrl/profile).\n\n".
			"Обращаем Ваше внимание на то, что теперь вы имеете возможность пользоваться этими же логином и паролем и для авторизации на других сайтах Издательской фирмы \"Сентябрь\":
			http://direktor.ru - официальный сайт журнала \"Директор школы\";
			http://konkurs.direktor.ru - Официальный сайт Всероссийского конкура \"Директор школы\"\n\n".
			"Мы приглашаем Вас подписаться на наши бесплатные новостные рассылки:\n".
			"http://news.direktor.ru/subscribe.htm\n\n".

			"-----------------------\n".
			"C уважением,\nСлужба технической поддержки сайта издания \"$SiteName\"\n\n".
			"Издательская фирма \"Сентябрь\"\n".
			"Телефон: (495) 710-30-01\nЭлектронная почта: support@direktor.ru\nИнтернет-сайт: $SiteUrl";

			/*send_smtp_mail($F.' '.$I.' '.$O,		// имя получателя
			            $Email,							    // email получателя
		                $subject, 						    // тема письма
		                $body, 							    // текст письма
		                'Служба технической поддержки ИФ Сентябрь'		// имя отправителя
		                );*/
			send_mime_mail(	'Служба технической поддержки ИФ Сентябрь',	// имя отправителя
			               	'support@direktor.ru', 			// email отправителя
						 	$F.' '.$I.' '.$O,		 		// имя получателя
				            $Email, 						// email получателя
			                'CP1251', 						// кодировка переданных данных
			                'KOI8-R', 						// кодировка письма
			                $subject, 						// тема письма
			                $body 							// текст письма
			                );

			//письмо в службу техподдержки
			$subject = "Зарегистрирован новый пользователь на сайте издания \"$SiteName\"";
			$body =
			"На сайте $SiteUrl зарегистрирован новый пользователь:\n\n".
			"Логин: $Login \n".
			"Фамилия: $F \n".
			"Имя: $I \n".
			"Отчество: $O \n".
			"Электронная почта: $Email \n".
			"Телефон: $Phone \n".
			"Регион: $Region \n".
			"Индекс: $ZipCode \n".
			"Адрес: $PostAddress \n".
			"Место работы: $WorkPlace \n".
			"Должность: $WorkPosition \n";

			/*send_smtp_mail('Саппорт',				    // имя получателя
			            'development@direktor.ru',		// email получателя
		                $subject, 						        // тема письма
		                $body, 							        // текст письма
		                'Техподдержка ИФ Сентябрь'	// имя отправителя
		                );*/
			send_mime_mail(	'Служба технической поддержки ИФ Сентябрь',	// имя отправителя
			               	'support@direktor.ru', 			    // email отправителя
						 	'Саппорт',				 		    // имя получателя
				            'development@direktor.ru',			// email получателя
			                'CP1251', 						    // кодировка переданных данных
			                'KOI8-R', 						    // кодировка письма
			                $subject, 						    // тема письма
			                $body 							    // текст письма
			                );
		}
	}

	############################################################################

	function restore($Email) {
		//$ShowForm=true;

		$SqlCheck='SELECT F, I, O, Login, Pass_support, Email FROM '.$this->auth->tbl.' WHERE Email=?';
		if ($Data = $this->uDB->run($SqlCheck, array($Email))->fetch()) {
            $I = $Data['I'];
            $O = $Data['O'];
            $Login = $Data['Login'];
            $Pass = $Data['Pass_support'];
            //письмо пользователю
            $subject = "Восстановление авторизационных данных для сайта интернет-магазина \"Образовательный квартал\"";
            $body =
                "Здравствуйте, $I $O!\n\n" .
                "На сайте интернет-магазина \"Образовательный квартал\" (shop.direktor.ru) был осуществлен запрос на восстановление авторизационных данных.\n\n" .
                "Ваш Логин - $Login\n" .
                "Ваш Пароль - $Pass\n\n" .
                "Если Вы не отправляли запроса на восстановление логина и пароля, Вы можете сообщить об этом в службу технической поддержки сайта.\n\n" .
                "Если вы хотите изменить свои персональные данные для доступа к сайту интернет-магазина \"Образовательный квартал\", войдите в Личный кабинет пользователя сайта (http://shop.direktor.ru/profile.htm) и внесите желаемые изменения.\n\n" .
                "-----------------------\n" .
                "C уважением,\nСлужба технической поддержки интернет-магазина \"Образовательный квартал\"\n\n" .
                "Издательская фирма \"Сентябрь\"\n" .
                "Телефон: (495) 710-30-01\nЭлектронная почта: support@direktor.ru\nИнтернет-сайт: http://shop.direktor.ru";

            send_smtp_mail($Email,                // имя получателя
                $Email,                            // email получателя
                $subject,                        // тема письма
                $body,                            // текст письма
                'Техподдержка ИФ Сентябрь'        // имя отправителя
            );

            echo <<<END
<p class="main">
	Письмо с Вашими авторизационными данными (Логин и Пароль) было выслано на адрес $Email
	<br><br>
    После получения этого письма Вы сможете авторизоваться на сайте, введя присланные Вам логин и пароль для входа.
    <br><br>
    Если по каким-то причинам Вы не получили письма с авторизационными данными,
    свяжитесь пожалуйста со <a href="mailto:support@direktor.ru">службой технической поддержки</a>.
</p>
END;
        }
//Форму отправки заявки на восстановление авторизационных данных показывать не надо


	}

	############################################################################
	function GenerateRegionsList() {
        return $this->uDB->run('SELECT Id, Name FROM auth_regions ORDER BY Name')->fetchAll(PDO::FETCH_KEY_PAIR);
	}

	############################################################################
	function echo_text_field($fCaption, $fField, $fValue, $fError, $MaxLength=false, $Required=true) {
		$MaxLengthCode = ($MaxLength) ? "maxlength='$MaxLength'" : "";
		$fFieldCode = '<input type="text"	name="'.$fField.'" value="'.$fValue.
			'"	style="text-font: Verdana; width: 280px;" '.$MaxLengthCode.'>';
		return $this->echo_reg_field($fCaption, $fFieldCode, $fError, $Required);
	}

    ################################################################################
	function echo_captcha_field($fCaption, $fField, $fValue, $fError, $MaxLength=false, $Required=true) {
		$MaxLengthCode = ($MaxLength) ? "maxlength='$MaxLength'" : "";
		//echo 'http://'.$_SERVER['HTTP_HOST'].'/images/captcha/captcha.png';
		$fFieldCode = '<img src="http://'.$_SERVER['HTTP_HOST'].'/images/captcha/captcha.png?.png" alt="CAPTCHA" /><br><input type="text"	name="'.$fField.'" value=""
		style="text-font: Verdana; width: 280px;" '.$MaxLengthCode.'>';
		return $this->echo_reg_field($fCaption, $fFieldCode, $fError, $Required);
	}

    ################################################################################
	function echo_pass_field($fCaption, $fField, $fValue, $fError) {
		$fFieldCode = '<input type="password"	name="'.$fField.'" value="'.$fValue.
			'"	style="text-font: Verdana; width: 280px;">';
		return $this->echo_reg_field($fCaption, $fFieldCode, $fError);
	}

    ################################################################################
	function echo_check_field($fCaption, $fField, $fValue, $fError, $Required=false) {
		$Checked = ($fValue==1) ? ' checked ' : '';
		$fFieldCode = '<input type="hidden"	name="'.$fField.'" value="0">
		<input type="checkbox"	name="'.$fField.'" value="1" '.$Checked.'> '.$fCaption;
		return $this->echo_reg_field('', $fFieldCode, $fError, $Required);
	}

    ################################################################################
	function echo_textarea_field($fCaption, $fField, $fValue, $fError, $Required=false) {
		$fFieldCode = '<textarea	name="'.$fField.'" style="text-font: Verdana; width: 280px;">'.
		$fValue.'</textarea>';
		return $this->echo_reg_field($fCaption, $fFieldCode, $fError, $Required);
	}

    ################################################################################
	function echo_select_field($fCaption, $fField, $fValue, $fError, $ItemsList, $DefaultCaption) {
		$Options = '';
   		foreach ($ItemsList as $ItemId=>$ItemName) {
   			$Selected = ($ItemId == $fValue) ? ' selected ' : '';
   			$Options .= "<option $Selected value='$ItemId'>$ItemName</option>";
   		}
		$fFieldCode = '<select name="'.$fField.'" style="text-font: Verdana; width: 280px">
		    	<option value="0">'.$DefaultCaption.'</option>'.$Options.'</select>';
		return $this->echo_reg_field($fCaption, $fFieldCode, $fError);
	}

    ################################################################################
  	function echo_reg_field($fCaption, $fFieldCode, $fError, $fRequired=true) {
  		$fErrorCaption = ($fError) ? "<br><span style='padding: 0px; margin: 0px;
  		color: #BD2D2D;'>$fError</span>" : "";
  		$RequiredCode = ($fRequired) ? '<span style="color: #BD2D2D;"> * </span>' : '' ;
		$Ret = '<tr>
					<td width="150px" valign="top">
						<p class="main" style="text-align: right; padding-top: 4px;">'.$fCaption.'</td>
		<td width="10px">&nbsp;</td>
		<td width="300px" valign="top">
			<p class="main">'.$fFieldCode.' '.$RequiredCode.' '.$fErrorCaption.'</p>
		</td>
		</tr>';
		return $Ret;
  	}


} # end of class

################################################################################


?>