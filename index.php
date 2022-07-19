<?php

require_once(__DIR__."/Classes/Button.php");
require_once(__DIR__."/Classes/DB.php");
require_once(__DIR__."/Classes/Tgram.php");
require_once(__DIR__."/Classes/Groups.php");
// require_once(__DIR__."/Classes/functions.php");

$bot_access_token = '5520095536:AAGUMyvqOON0MCwbAvDIuC2GHwy0Sx0_s_c';
$bot_api = 'https://api.telegram.org/bot'.$bot_access_token;





try {

    
    $content = file_get_contents('php://input');
    $update = json_decode($content); //декодируем апдейт json, пришедший с телеграмма
    file_put_contents(__DIR__ . '/content.txt', print_r($update, true)); // создаем текстовый файл для отладки(по желанию)
    file_put_contents(__DIR__ . '/button.txt', print_r($update->callback_query, true)); // создаем текстовый файл для отладки(по желанию)
    global $groups;
    $model = new Tgram();

    if ($update->message)
    {
        //Определим id
        $chat_id = $update->message->chat->id;
        if ($update->message->location)
        {
            DefTypeUser($chat_id, $update->message->location);
        }
        else
        {
        DefTypeUser($chat_id, ['message' => $update->message->text]);
        }

    }
    elseif($update->callback_query->data)
    {
        $chat_id = $update->callback_query->message->chat->id;
        $button = $update->callback_query->data;
        try
        {
            DefTypeUser($chat_id, ['button'=>$update->callback_query->data]);

        }catch(Throwable $ex)
        {

        }
    }
}catch(Throwable $ex)
{
    echo $ex;
}


// Функция определяет тип пользователя

function DefTypeUser($chat_id, $data)
{
    $model = new Tgram();
    //сделать пост запросы в ссылках
    $db = new DB();
    //ошибка
    $user = $db->GetUser("users", $chat_id);

    foreach($user as $row)
    {
        $id= $row["ID"];
        $name = $row["Name"];
        $state = $row['State'];
        $type = $row['Type'];
    }
    $user = [
        'id' => $id,
        'name' => $name,
        'state' => $state,
        'type' => $type,
    ];
    if ($type == 'teacher') {
        $teach = $db->GetUser("teachers", $chat_id);

        foreach($teach as $row)
    {
        $id= $row["ID"];
        $name = $row["Name"];
        $state = $row['State'];

    }

    $teach = [
        'id' => $id,
        'name' => $name,
        'state' => $state,

    ];
    }

    if ($type == 'student') {
        $stud = $db->GetUser("group_of_students", $chat_id);

        foreach($stud as $row)
    {
        $id= $row["ID"];
        $name = $row["Name"];
        $state = $row['State'];

    }

    $stud = [
        'id' => $id,
        'name' => $name,
        'state' => $state,
    ];
    }

    //$user = User::find()->asArray()->where(['id' => $chat_id])->one();
    switch ($type)
    {
        case ('teacher'):
            //Передаем TeacherUserController

            //проблема в передаче массива в сслыка
            $url = 'https://ppluie.ru/Botv2/Classes/TeacherController.php';
            $url = $url.'?'.'chat_id='.$chat_id.'&'.http_build_query($data).'&'.http_build_query($user);
            cURL($url);
            $user->free();
            break;
        case ('student'):
            //Передаем StudentUserController
            $url = 'https://ppluie.ru/Botv2/Classes/StudentController.php';
            $url = $url.'?'.'chat_id='.$chat_id.'&'.http_build_query($data).'&'.http_build_query($user);
            cURL($url);
            $user->free();
            break;
        default:
            //Передаем UserController
            $url = 'https://ppluie.ru/Botv2/Classes/UserController.php';
            $url = $url.'?'.'chat_id='.$chat_id.'&'.http_build_query($data);
            cURL($url);
            $user->free();
    }


}

// функция делает запрос по url
function cURL($url)
{
    $curl = curl_init($url);    //инициализируем curl по нашему урлу
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);   //здесь мы говорим, чтобы запром вернул нам ответ сервера телеграмма в виде строки, нежели напрямую.
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);   //Не проверяем сертификат сервера телеграмма.
    $result = curl_exec($curl);   // исполняем сессию curl
    curl_close($curl); // завершаем сессию
}
?>