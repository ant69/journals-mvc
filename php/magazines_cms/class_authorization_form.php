<?php
//include_once "config.php";

function ShowLoginForm()
{
	$LoginPage = "http://".$_SERVER['HTTP_HOST'];
	$CurPage = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
echo <<<END
	<div align="right">
		<form method="POST" action="$LoginPage">
            <input type="hidden" name="action" value="login">
			<table border="0" width="100%" cellspacing="0" cellpadding="0" style="margin-top: 12px;">

				<tr>
					<td>&nbsp;</td>
					<td valign="bottom" width="120">
					<p class="top" align="left" style="margin-bottom: 0px; margin-right: 5px;">Логин или email</p></td>
					<td valign="bottom" width="90">
					<p class="top" align="left" style="margin-bottom: 0px; margin-right: 5px;">Пароль</p></td>
					<td width="10">&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td align="left" width="120">
					<input type="text" name="userName" value="" class="top_text" style="margin-top: 0px; margin-right: 5px; width: 95%">
					<input type="hidden" name="returnto" value="$CurPage"></td>
					<td align="left" width="90">
					<input type="password" name="userPass" size="15" value="" class="top_text" style="margin-top: 0px; margin-right: 5px;"></td>
					<td width="10">
					<input type="submit" value=" Войти " name="B1" class="top_button" style="margin-top: 0px; "></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan="3">	<p class="top" align="left"><b>
					<a href="profile/registration">Регистрация</a>&nbsp;&nbsp;&nbsp;<a href="profile/restore">Напомнить&nbsp;пароль</a></p></td>
				</tr><!---->
			</table>
		</form>
	</div>
END;

}

function ShowAfterLoginForm()
{
	//$ref=$_SERVER['REQUEST_URI'];
	global $IsAdmin;
	global $IsEditor;
	global $CurUser;
	global $User;

	//$CurUser->cross_auth();
    $CurUserData = @$User->Data;
	$UserId = $CurUserData['uId'];
	$UserFIO=$CurUserData['F'].'&nbsp;'.$CurUserData['I'].'&nbsp;'.$CurUserData['O'];

	if ($IsAdmin or $IsEditor) {$AdminPanel="&nbsp;&nbsp;<a href='admin.htm'>Админпанель</a>";} else {$AdminPanel="";}
	if ($CurUserData['IsBlogger']==1) {$EditBlog="&nbsp;&nbsp;<a href='blog.htm?pid=".$UserId."'>Мой&nbsp;блог</a>";} else {$EditBlog="";}
	$ScriptName = $_SERVER['PHP_SELF'].'?';
    $UriGet = $_GET;
	$UriGet['mode'] = 'logout';
	$Params = '';
	foreach ($UriGet as $k=>$v) {$Params = ($Params == '') ? "$k=$v" : $Params .= "&$k=$v";}
	$ScriptName .= $Params;
echo <<<END
	<form method="POST">
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td>&nbsp;</td>
				<td width="200px"><p class="top" align="left">&nbsp;</p></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td width="200px"><p class="top" align="right"><b>$UserFIO</b></p></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td  width="200px"><p class="top" align="right">
				<input type="hidden" name="action" value="login">
					<a href="profile">
		Профиль</a>$EditBlog&nbsp;&nbsp;<a href="$ScriptName">Выход</a>
				</p></td>
			</tr>
		</table>
	</form>

END;
}

function ShowErrorLoginForm()
{
	$ref=$_SERVER['REQUEST_URI'];
echo <<<END
	<form method="POST">
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td>&nbsp;</td>
				<td width="200px"><p class="top" align="left">&nbsp;</p></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td width="200px"><p class="top" align="left"><b>Неверное имя пользователя или пароль</b></p></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td  width="200px"><p class="top" align="left">
				<input type="hidden" name="action" value="login">
					<a href="$ref">Повторить&nbsp;вход</a>&nbsp;&nbsp;&nbsp;<a href="profile/registration">Регистрация</a>&nbsp;&nbsp;<a href="profile/restore">Напомнить&nbsp;пароль</a>&nbsp;
				</p></td>
			</tr>
		</table>
	</form>
END;
}

/*
function valid_email($email) {
  $regexp="/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i";
  if ( !preg_match($regexp, $email) ) {
       //echo 'Email address is not correct';
       return false;
  }
  return true;
}
*/

?>