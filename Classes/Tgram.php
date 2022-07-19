<?php


// Класс, в котором содержится токен и функция, которая вызывает методы telegram
class Tgram 
{
    protected $token = '5520095536:AAGUMyvqOON0MCwbAvDIuC2GHwy0Sx0_s_c'; //ваш токен

    // делает запрос в telegram api
    public function getReq($method,$params=[]){ //параметр 1 это метод, 2 - это массив параметров к методу
        $url =  "https://api.telegram.org/bot{$this->token}/$method"; //основная строка и метод

        if(count($params)){
            $url=$url.'?'.http_build_query($params);//к нему мы прибавляем парметры, в виде GET-параметров
        }
        $curl = curl_init($url);    //инициализируем curl по нашему урлу
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);   //здесь мы говорим, чтобы запром вернул нам ответ сервера телеграмма в виде строки, нежели напрямую.
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);   //Не проверяем сертификат сервера телеграмма.
        $result = curl_exec($curl);   // исполняем сессию curl
        curl_close($curl); // завершаем сессию

        return $result; //Или просто возращаем ответ в виде строки
    }

//.....
}