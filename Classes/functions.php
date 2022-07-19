<?php

require_once(__DIR__."/Tgram.php");
require_once(__DIR__."/Groups.php");

// делает запрос по url
function cURL($url)
{
    $curl = curl_init($url);    //инициализируем curl по нашему урлу
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);   //здесь мы говорим, чтобы запром вернул нам ответ сервера телеграмма в виде строки, нежели напрямую.
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);   //Не проверяем сертификат сервера телеграмма.
    $result = curl_exec($curl);   // исполняем сессию curl
    curl_close($curl); // завершаем сессию
}

/** Расстояние между двумя точками*/
function calculateTheDistance ($lat_1, $long_1, $lat_2, $long_2) {
    define('EARTH_RADIUS', 6372795);
    define('M_PI', 3.1415926535898);
    // перевести координаты в радианы
    $lat1 = $lat_1 * M_PI / 180;
    $lat2 = $lat_2 * M_PI / 180;
    $long1 = $long_1 * M_PI / 180;
    $long2 = $long_2 * M_PI / 180;

    // косинусы и синусы широт и разницы долгот
    $cl1 = cos($lat1);
    $cl2 = cos($lat2);
    $sl1 = sin($lat1);
    $sl2 = sin($lat2);
    $delta = $long2 - $long1;
    $cdelta = cos($delta);
    $sdelta = sin($delta);

    // вычисления длины большого круга
    $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
    $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;

    $ad = atan2($y, $x);
    return $ad * EARTH_RADIUS;
}

// Возращает название группы
function GetGroup($name_group)
{
    global $groups_table;
    foreach ($groups_table as $key => $value)
    {
        if ($name_group == $key)
        {
            return $value;
        }
    }return '';
}
// Возращает название группы
function GetGroup2($name_group)
{
    global $groups_table2;
    foreach ($groups_table2 as $key => $value)
    {
        if ($name_group == $key)
        {
            return $value;
        }
    }return '';
}