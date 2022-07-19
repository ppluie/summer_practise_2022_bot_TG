<?php

require_once(__DIR__."/Tgram.php");
require_once(__DIR__."/Button.php");
require_once(__DIR__."/DB.php");
require_once(__DIR__."/functions.php");
require_once(__DIR__."/Groups.php");
require_once(__DIR__."/PDF.php");

require_once( "fpdf/fpdf.php" );

define('FPDF_FONTPATH',"fpdf/font/");



$model = new Tgram();
$db = new DB();
$chat_id = $_GET['chat_id'];
$id= $_GET['id'];
$name = $_GET['name'];
$state =$_GET['state'];
$type = $_GET['type'];
$group_students = $_GET['Group_Students'];
global $groups_table;
$groups_values = array_values($groups_table);
$groups_keys = array_keys($groups_table);
$groups = [];

// обработчик сообщений 
switch($_GET['message'])
{
    case('/start'):
    case('/help'):
        $db->UpdateTable('users', 'State', '0_teacher', $chat_id);
        $db->UpdateTable('teachers', 'State', '0_teacher', $chat_id);
        $model->getReq('sendMessage',
            ['chat_id' => $chat_id,
                'text' => "Функции /start, /help, /account, /showgroup"]);
        break;
    case('/account'):
/*        $db->UpdateTable('users', 'State', '0_teacher', $chat_id);
        $db->UpdateTable('teachers', 'State', '0_teacher', $chat_id);*/

        $button = new Button();
        $button->AddKeyBoard('keyboard');
        $button->AddButton(['text' =>'Отправить геолокацию.',
            'request_location' => true], 'keyboard');

        $db->UpdateTable('users', 'State', '10_teacher', $chat_id);
        $db->UpdateTable('teachers', 'State', '10_teacher', $chat_id);

        $model->getReq('sendMessage',
            ['chat_id' => $chat_id,
                'text' => "Отправьте свою геолокацию: ".$type,
                'reply_markup' => json_encode($button->_buttons)]);
        break;

        case('/showgroup'):
            $db->UpdateTable('users', 'State', '14_teacher', $chat_id);
            $db->UpdateTable('teachers', 'State', '14_teacher', $chat_id);
            $model->getReq('sendMessage',
            ['chat_id' => $chat_id,
                'text' => "Введите группу:"]);

            
            break;
}
// обработчик кнопок
if ($_GET['button']){
    switch($_GET['button'])
    {
        case(mb_strpos($_GET['button'],'stop_accounting_button' )):
            $term = explode('/', $_GET['button']);
            $groups = $term[1];
            //$db->UpdateAllTable($groups, 'Name', 'ЛЕЖАТЬ');
            $teacher_user = $db->GetUser('teachers', $chat_id);
            $time = getdate();
            $time_str = $time['mday'].'.'.$time['mon'].'.'.$time['year'].
                '/'.$time['hours'].':'.$time['minutes'].'/';
            $T = $db->GetUser('teachers', $chat_id);
            foreach($T as $row)
            {
                $group_students = $row['Group_Students'];
            }
            $group_students = explode("\n", $group_students);
            foreach ($teacher_user as $row)
            {
                $longitude = $row['Longitude'];
                $latitude = $row['Latitude'];
            }

            foreach ($group_students as $group)
            {
                $users = $db->GetAllUsers($group);
                $db->AttentionAllUsers($group, 'Преподаватель остановил(-а) учет.');
                foreach ($users as $user)
                {
                    $user_id = $user['ID'];
                    $longitude_user = $user['Longitude'];
                    $latitude_user = $user['Latitude'];
                    $Attendance = $user['Attendance'];
                    $Attendance = $Attendance.PHP_EOL.$time_str;
                    if ($longitude_user >= 0)
                    {
                        $distant = calculateTheDistance($latitude,$longitude,$latitude_user,$longitude_user);
                        if ($distant < 50)
                        {
                            $model->getReq('sendMessage',
                                ['chat_id' => $user_id,
                                    'text' => 'Вы учтены.'.
                                        PHP_EOL.'Расстояние составляет: '.$distant.' м']);

                            $db->UpdateTable($group, 'Attendance',$Attendance.'+/'.$chat_id,$user_id);
                        }
                        else
                        {
                            $model->getReq('sendMessage',
                                ['chat_id' => $user_id,
                                    'text' => 'Вы не учтены. Подойтите к преподавателю после пары.'.PHP_EOL.
                                        $distant.' метров до преподавателя.']);
                            $db->UpdateTable($group, 'Attendance',$Attendance.'-/'.$chat_id,$user_id);
                        }
                    }else
                    {
                        $model->getReq('sendMessage',
                            ['chat_id' => $user_id,
                                'text' => 'Видимо, геолокация не была отправлена.']);
                        $db->UpdateTable($group, 'Attendance',$Attendance.'Геолокация не была отправлена/'.$chat_id,$user_id);
                    }
                }

            }
            $model->getReq('sendMessage',
                [
                    'chat_id' => $chat_id,
                    'text' => 'Вы остановили учет.'
                ]);
            $db->UpdateTable("users", "State", "0_teacher", $chat_id);
            $db->UpdateTable("teachers", "State", "0_teacher", $chat_id);
            $db->UpdateTable("teachers", "Group_Students", "", $chat_id);
            break;
        case(in_array($_GET['button'], $groups_values)):
            $T = $db->GetUser('teachers', $chat_id);
            foreach($T as $row)
            {
                $group_students = $row['Group_Students'];
            }
            if ($group_students){
                $db->UpdateTable('teachers', 'Group_Students', $group_students.PHP_EOL.$_GET['button'], $chat_id);
            }else{
                $db->UpdateTable('teachers', 'Group_Students', $_GET['button'], $chat_id);
            }
            break;
        case('stop_choose_button'):
            $db->UpdateTable("users", "State", "12_teacher", $chat_id);
            $db->UpdateTable("teachers", "State", "12_teacher", $chat_id);
            $state = "12_teacher";
            break;
    }

}
// обработчик состояний
switch($state)
{
    case('1_teacher'):
        //введите имя
        $db->UpdateTable("users", "Name", $_GET['message'], $chat_id);
        $db->UpdateTable("teachers", "Name", $_GET['message'], $chat_id);
        $db->UpdateTable("users", "State", "0_teacher", $chat_id);
        $db->UpdateTable("teachers", "State", "0_teacher", $chat_id);
        $model->getReq('sendMessage',
            [
                'chat_id' => $chat_id,
                'text' => 'Преподаватель записан.'.PHP_EOL.'Функции /start, /help, /account, /showgroup'
            ]);
        break;
    //состояние начало учета студентов
    case('12_teacher'):

        $T = $db->GetUser('teachers', $chat_id);
        foreach($T as $row)
        {
            $groups = $row['Group_Students'];
        }
        $groups = explode("\n", $groups);
        //$groups = $_GET['message']; //тут предпологается массив строк, разделенных пробелом
/*        $groups = explode(' ', $groups);
        foreach ($groups as $group)
        {
            $db->UpdateAllTable($group, 'longitude', 0);
            $db->UpdateAllTable($group, 'latitude', 0);
        }*/
        foreach ($groups as $group)
        {
            $db->UpdateAllTable($group, 'Longitude', -1000);
            $db->UpdateAllTable($group, 'Latitude', -1000);
            $db->AttentionAllUsers($group, 'Преподаватель '.$name.' запустил(-а) учет студентов.'.PHP_EOL.
                'Используйте /sendloc для отправки геолокации.');
        }
        $button = new Button();
        $button->AddKeyBoard('inline_keyboard');
        $button->AddButton(['text' =>'Остановить учет студентов.',
            'callback_data' => 'stop_accounting_button/'.$groups], 'inline_keyboard');
        $model->getReq('sendMessage',
            ['chat_id' => $chat_id,
                'text' => "Студенты оповещены. Подождите немного, ".$name.".",
                'reply_markup' => json_encode($button->_buttons)]);
        $db->UpdateTable("users", "State", "0_teacher", $chat_id);
        $db->UpdateTable("teachers", "State", "0_teacher", $chat_id);
        break;
    // состояние запись геолокции препода
    case('10_teacher'):
        if($_GET['longitude'])
        {

            $db->UpdateTable("teachers", "Longitude", $_GET['longitude'], $chat_id);
            $db->UpdateTable("teachers", "Latitude", $_GET['latitude'], $chat_id);
            $db->UpdateTable("users", "State", "11_teacher", $chat_id);
            $db->UpdateTable("teachers", "State", "11_teacher", $chat_id);
            $remove_keyboard = ['remove_keyboard' => true];
            $model->getReq('sendMessage',
                [
                    'chat_id' => $chat_id,
                    'text' => 'Ваша геолокация записана.',
                    'reply_markup' => json_encode($remove_keyboard)
                ]);

            $button = new Button();
            $button->AddKeyBoard('inline_keyboard');
            foreach ($groups_table as $call_group => $name_group)
            {
                $button->AddRowButton([[
                    'text' => $call_group,
                    'callback_data' => $name_group ]],'inline_keyboard');
            }
            $button->AddRowButton([[
                'text' => 'Закончить выборку.',
                'parse_mode' => 'html',
                'callback_data' => 'stop_choose_button']],'inline_keyboard');
            $model->getReq('sendMessage',
                [
                    'chat_id' => $chat_id,
                    'text' => 'Выберите группы:',
                    'reply_markup' => json_encode($button->_buttons)
                ]);
          /*  $groups = array_keys($groups);
            $model->getReq('sendPoll',
                ['chat_id' => $chat_id,
                    'question' => 'Выберите группы, которые вы хотите отчитать',
                    'allows_multiple_answers' => true,
                    'options' => json_encode($groups)]);*/

        }else
        {
            $model->getReq('sendMessage',
                [
                    'chat_id' => $chat_id,
                    'text' => 'Ошибка.'
                ]);
        }
        break;
    case("11_teacher"):
        break;
    case("14_teacher"):
        $model = new Tgram();
        $admin_id = 836098977;
        $gr = mb_strtolower($_GET['message'],'utf-8');
        $group_m = GetGroup($gr);
        $model->getReq('sendMessage',
            [
                'chat_id' => $chat_id,
                'text' => $group_m
            ]);
        $pdf = CreatePDF($group_m,$chat_id);
        $pdf->Output($chat_id.".pdf", "F");
        $model->getReq('sendMessage',
            [
                'chat_id' => $chat_id,
                'text' => 'Файл на сервере.'
            ]);
        try {
            $file = $chat_id.".pdf";
            //$file = "error_log.txt";
            $name2 = "pr";
            $file_ext = explode('.', $file)[1];
            $cfile = new CURLFile('https://ppluie.ru/Botv2/Classes/'."$file");
            $url_file = 'https://ppluie.ru/Botv2/Classes/'."$file";
            $cfile->setPostFilename($name2.'.'.$file_ext);
            
            $model->getReq('sendMessage',
            [
                'chat_id' => $chat_id,
                'text' => 'https://ppluie.ru/Botv2/Classes/'."$file",
                'disable_web_page_preview' => false
                //document => false
            ]);

          /*$model->getReq('sendDocument',
        [
            'chat_id' => $chat_id,
            'document' => $url_file
        ]);*/
        } catch (Throwable $ex) {
            $model->getReq('sendMessage',
                [
                    'chat_id' => $chat_id,
                    'text' => '.'.$ex
                ]);
        }
        

        $db->UpdateTable("users", "State", "0_teacher", $chat_id);
        $db->UpdateTable("teachers", "State", "0_teacher", $chat_id);
        break;
    case("13_teacher"):
        break;
}

