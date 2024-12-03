<?php
    include_once('lib/SQL.php'); 
    include_once('lib/curl_query.php'); 
    include_once('lib/simple_html_dom.php'); 

    $sql = SQL::Instance();

    $output = ""; // Инициализация переменной для вывода

    $source_data = curl_get('https://brestcity.com/blog/top-10-gorodov');
    $dom_0 = str_get_html($source_data);

    // Поиск тег meta, в котором аргумент content начинается с Данные...
    $dom_1 = $dom_0->find('meta[content^="Данные"]');

    // Проверка, найден ли элемент
    if (!empty($dom_1)) {
        // Получение контента из первого найденного элемента
        $content = $dom_1[0]->content;

        // Находим позицию слова "Минск"
        $position = strpos($content, 'Минск');

        if ($position !== false) {
            // Извлекаем подстроку начиная с позиции слова "Минск"
            $buffer_result = substr($content, $position);
            $output .= $buffer_result; // Добавляем результат в переменную вывода
        } else {
            $output .= "Слово 'Минск' не найдено."; // Добавляем сообщение в переменную вывода
        }
    } else {
        $output .= "Мета-тег с нужным контентом не найден."; // Добавляем сообщение в переменную вывода
    }

    // Заменяем "тыс." на "000"
    $output = str_replace(' тыс.', '000', $output);

    // Разбиваем строку на массив по разделителю ', '
    $output_array = explode(', ', $output);

    // Обрабатываем каждый элемент массива
    foreach ($output_array as $item) {
        $tobd = array();
        // Разбиваем элемент на части по разделителю ' - '
        list($city, $population) = explode(' - ', $item);
        $tobd['City'] = $city;
        $tobd['Population'] = (int)$population;
    
        $sql->Insert('dbo.cities_Ranking', $tobd);
    }
?>