<?php
defined('IN_MANAGER_MODE') or die();
global $e;
$e = &$modx->Event;
	if(!isset($e->params['clubId'])) {
		show_error(1);
		return false;
	}
	else{
		$clubId=$e->params['clubId'];
	}
	
	if(!isset($e->params['accessToken'])) {
		show_error(2);
		return false;
	}
	else{
		$accessToken=$e->params['accessToken'];
	}

	if(!isset($e->params['tvName'])) {
		show_error(3);
		return false;
	}
	else{
		$tvName=$e->params['tvName'];
	}
	
	if(!isset($e->params['postTemplate'])) {
		show_error(4);
		return false;
	}
	else{
		$postTemplate=$e->params['postTemplate'];
	}	

function show_error($errNum){
	$errors=array('','Вы не ввели id группы','Вы не ввели accessToken','Вы не ввели ID TV','Шаблон поста пустой');
	echo '<div class="error">Ошибка в плагине vkpost: '.$errors[$errNum].'</div>';
}

$output = '';
if ($e->name == 'OnDocFormRender') //Здесь код, который выполнится при открытии документа
{
	$output .='<!-- VK POST -->'."\n";
	$output .='<script>'."\n";
	$output .='$j("head").append(\'<link rel="stylesheet" href="'. $modx->config['site_url'] .'assets/plugins/vkpost_old/widget.css?21">\');' ."\n";//Подключаем стиль css
	$output .='mytv="#tv'.$tvName.'";'."\n"; //значение ТВ из конфига передаём в переменную для JS.
	$output .='thisid='.$id.';' ."\n"; //ID текущего документа передаём в переменную для JS
	//Переделываем внешний вид ТВшки
	$output .='elems=\'<div class="vk_post_wrapper actionButtons">\';'."\n";
	$output .='elems = elems + \'<div class="vk_post_count">Постов: \'+$j(mytv).val()+\'</div>\';'."\n";
	$output .='elems = elems +\'<div class="vk_post_result">&nbsp;</div>\';' . "\n";
	$output .='elems = elems + \'<div><input class="vk_post_action" type="button" value="Запостить"></div>\';'."\n";
	$output .='elems = elems + \'</div>\';'."\n";
	
	$output .= '$j(mytv).parents("td").append(elems);'."\n";//Добавляем упр. элементы рядом с твшкой
	$output .= '$j(mytv).hide();'."\n"; //Скрываем оригинальную твшку
	$output .='$j("head").append(\'<script type="text/javascript" src="'. $modx->config['site_url'] .'assets/plugins/vkpost_old/events.js?2"></scr\'+\'ipt>\');' ."\n";	//Добавляем скрипт с аяком
	$output .='</script>'."\n";
	$output .='<!-- END VK POST -->'."\n";
	
}

$e->output($output . "\n");
?>
