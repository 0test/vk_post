<?php

include_once $_SERVER['DOCUMENT_ROOT'].'/manager/includes/config.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/manager/includes/document.parser.class.inc.php';
$modx = new DocumentParser;
$modx->getSettings();
startCMSSession();
$modx->minParserPasses=2;
global $e;
$e = &$modx->Event;

require_once('vkwall.class.php');	// Класс для работы со стеной


// Получаем значения полей из конфигурации плагина
if (isset($modx->pluginCache['vkpost_oldProps'])) {
        $params = $modx->parseProperties($modx->pluginCache['vkpost_oldProps']);	
}
else{
	echo 'Ошибка. Вы переименовали плагин.';
	return;
}
// Присваиваем значения из конфига плагина переменным.
// Проверку делать не будем, сюда никто не доберется,
// т.к. мы проверяли значения ещё на старте плагина.

$clubId=$params['clubId']; 				// ID группы
$accessToken=$params['accessToken']; 		// Логин юзера
$tvName=$params['tvName'];				// Тв, к которой прилеплен плагин
$postTemplate=$params['postTemplate'];	// Шаблон поста

$docid=$_POST['id'];
if(!$docid){return false;}
$this_link=$modx->makeUrl($docid, '', '', 'full');
$text=$postTemplate;	// текст поста

/*
	Задаём два массива.
	В одном будем хранить все текстовые поля и тв, а в другом изображения.
*/
$textVariables=array();
$imageVariables=array();

/*
	Парсим строку шаблона из конфигурации плагина.
	Получаем имена всех полей без кавычек и звёздочек в массив $out[1].
	В массиве $out[0] то же, но нераспарсенное.
*/
preg_match_all("|\[\*(.*)\*\]|U", $postTemplate,$out);



// Проходим по полученному массиву
foreach ($out[1] as &$value) {
	// Если переменная не массив, то это не ТВшка, а просто поле
	if(!is_array($txt=$modx->getDocumentObject('id',$docid)[$value])){
		// Добавляем в массив имя и значение.
		$textVariables[$value]=$txt;
		//Здесь мы не проверяем на пустое значение, угадайте почему.
	}
	else{
		// А если массив, то ТВ. Разбираем значения и имена.
		$tvtype=$modx->getDocumentObject('id',$docid)[$value][4];		// Тип ТВ
		$tvname=$modx->getDocumentObject('id',$docid)[$value][0];		// Название ТВ
		$tvContent=$modx->getDocumentObject('id',$docid)[$value][1];	// Значение тв
		if ($tvtype==='text'){
			// Если ТВ типа текст, то добавляем его имя и значение в массив.
			$textVariables[$tvname]=$tvContent;
			// И тут не проверяем на пустое значение.
		}
		if ($tvtype==='image'){
			/*
				Если ТВ типа image, добавляем его в другой массив.

			*/
				$imageVariables[$tvname]=$tvContent;
		}
	}
}
/*
	Теперь у нас есть два массива с переменными нашего шаблона,
	которым сопоставлены значения полей в документе.
	Наша задача: заменить все теги в шаблоне поста на значения полей в документе,
	вырезать все теги изображений и получить готовый текстовый пост.
	Изображения вконтакте грузятся отдельно от поста, подробнее в файле vkwall.class.php
*/

	// разберем массив полей и заменяем названия тегов из шаблона поста на их значения.
	foreach ($textVariables as $name => $value){
		//$name название поля
		//$value значение поля
		$name="/\[\*$name\*\]/"; // шаблон-регулярка
		$value=strip_tags($value);
		$text=preg_replace($name,$value,$text);		
	}
	$text=preg_replace('/\[this_link\]/',$this_link,$text);	// Парсим и вставляем урл страницы, если надо
	// текст поста готов
	
	/*
		Практически также для изображений, но тут просто удаляем все включения тегов,
		так как изображения мы загружаем отдельно от поста и они уже в массиве.
	*/
	$images=array();
	foreach ($imageVariables as $name => $value){
		$name="/\[\*($name)\*\]/"; // шаблон-регулярка
		if($value==''){
			$text=preg_replace($name,'',$text);	// Если тв пустой, ничего не пишем в массив, просто вырезаем
		}
		else{
			$text=preg_replace($name,'',$text);
			/*	Если не пустой, то добавляем путь к изображению в массив.
				Перед значением вписываем путь до файла, так как
				он нужен будет для загрузки фото в контакт.			
			*/
			$images[]=$modx->config['base_path'].$value;
		}

	}
$vkAPI = new \BW\Vkontakte(['access_token' => $accessToken]);
if ($vkAPI->postToPublic($clubId, $text, $images)){
	echo '<div class="ok">Запостили в базу.</div>';
	}
	else{
		echo '<div class="err">Ошибка в ajax.php!</div>';
}

?>
