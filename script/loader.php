<?php
// Конфигурация системы
include_once("l:/domains/doy7/script/config.php");
// Загружаем класс для работы с БД
print_r($GLOBALS['lib_db_pdf']); exit;
include_once($GLOBALS['lib_db_pdf']);
// Загружаем класс, обеспечивающий авторизацию пользователя
include_once($GLOBALS['lib_authorization']);

include($GLOBALS['lib_data_management']);
include_once($GLOBALS['lib_databank_download']);
$DM = New data_management_class($GLOBALS);

$DD = new databank_download_class(@$DM);
// Если локальная ссылка начинается с picture, считаем, что это - локальная картинка с сайта.
if (in_array($DD->LinkType, array('picture', 'file', 'databank')))
{
    if ($DD->LinkType == 'picture') {$DD->get_picture();}
    else if ($DD->LinkType == 'file') {$DD->get_file();}
    else if ($DD->LinkType == 'databank') {$DD->get_databank_file();}
    exit;
}

include_once($GLOBALS['lib_site_user']);
$User=new site_user_class($DM->DBSettings, $DM->UserDBSettings);
# Пользователь решил закончить сеанс
if ($_GET['mode'] == 'logout') { $User->Auth->make_logout();   header('location: '.$_SERVER['HTTP_REFERER']); exit;  }
if (isset($_POST['returnto'])) { header('location: '.$_POST['returnto']); exit;  }
if ($User->Auth->ShowPage)
{
    $UserOK=true;
    $DM->UserOK = @$UserOK;
    $DM->User = @$User;
    $IsAdmin = $User->Data['GlobalAdmin'];
    $IsEditor = $User->IsEditor;
}
else    { $UserOK=false;    }

//Определение параметров текущей страницы: Title, Description, Keywords, шаблон страницы
include_once($GLOBALS['lib_frontend']);
$FE = new frontend_class(@$DM, $IsAdmin);

$MainMenu = $FE->MainMenu;

$Title = $FE->Page->Title;
$Keywords = $FE->Page->Keywords;
$Description = $FE->Page->Description;

$JSBase = $GLOBALS['LibJS'];
$HtmlBase = $DM->Base;
$TemplateDir = $GLOBALS['TemplateDir'];
$CurrentTemplateDir = $GLOBALS['CurrentTemplateDir'];

if($FE->DM->current_page_path[0]=='profile' AND ($UserOK==false) AND !$_SESSION['SessionID'])  {session_start(); }
