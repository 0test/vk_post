# VkPost - плагин для постинга на стену группы вконтакте из админки MODx Evolution

## Описание плагина
Плагин VkPost нужен для постинга на стену группы вконтакте. Работает из админ-панели MODX Evolution.

- Понимает произвольный текстовый шаблон поста с подстановкой стандартных тегов MODx наподобие [\*pagetitle\*], [\*introtext\*] и текстовых ТВ-параметров.
- Понимает ТВ-параметры типа "image" и прикрепляет изображения из них к посту.

## Установка

### 1. Подготовка.

- Создать Standalone-приложение https://vk.com/editapp?act=create
- Вставить в код ссылки ниже АЙДИ ПРИЛОЖЕНИЯ. 
- Перейти по измененной ссылке:
```
 https://oauth.vk.com/authorize?client_id=5083447&scope=groups,wall,offline,photos,market&redirect_uri=https://oauth.vk.com/blank.html&display=page&v=5.44&response_type=token
```
- Согласиться со всем, что предложит Вконтакт. Несмотря на предупреждение, скопировать из урл значение access_token.

### 2. Установка.

- Создайте ТВ типа "текст" для нужного вам шаблона. Значение по умолчанию поставьте 0.
- Создайте плагин с названием vkpost_old
- Вставьте в код плагина строку: include($modx->config['base_path'].'assets/plugins/vkpost_old/vkpost_plugin.php');
- На вкладке "Системные события" выберите OnDocFormRender.
- На вкладке "Конфигурация" в поле "Конфигурация плагина" вставьте этот текст и нажмите "Обновить параметры".

```
&clubId=Club ID:;string; &accessToken=AccessToken:;string; &tvName=ID ТВ для кнопки:;string; &postTemplate=Шаблон поста;textarea;Товар: [\*pagetitle\*]!
Цена: [\*price\*]
Подробности на сайте [this_link]
[\*img\*]
```
### 3. Заполните все появившиеся поля.
```
- Club ID: Id группы, на стену которой вы будете постить
- AccessToken: код, который мы получили
- ID ТВ для кнопки: id тв, который мы создали
- Шаблон поста: здесь нужно ввести любой текст с тегами.
```

**Пример:**
```
 У нас акция: [\*pagetitle\*]!
Подробнее: [\*longtitle\*]
А также [\*content\*].
Цена товара всего [\*price\*] рублей.
[\*any_image_tv1\*][\*any_image_tv2\*]
Подробности на сайте [this_link]
```
Свой тег у плагина только один, [this_link] - адрес текущего документа.
Плагин разберёт шаблон и заменит теги на соответствующие значения полей и тв-параметров. Теги ТВ-параметров типа "image" будут убраны из текста, а изображения из них подгружены к посту.

Не вставляйте более 5 изображений - это ограничение самого Вконтакта на пост-запрос при загрузке файлов. Проблема решаема, читайте с 353-й строки в файле vkwall.class.php.

### 4. Cкопируйте папку vkpost_old из архива в директорию assets/plugins/ на вашем сайте.

### 5. Если всё сделано правильно, то при редактировании ресурса, в котором есть ТВ из п.1, вы должны увидеть счётчик постов и кнопку "Запостить" на месте этого ТВ.

### 6. Внимание! 
- Сначала сохраняйте документ, а только потом постите! Плагин берёт значения из сохранённых ТВ-параметров и полей.
- Если не постятся фото, попробуйте обратиться к хостеру и узнать, включена ли директива allow_url_fopen.

## От автора

Вы можете распространять, продавать и раздавать плагин, как вашей душе угодно.
