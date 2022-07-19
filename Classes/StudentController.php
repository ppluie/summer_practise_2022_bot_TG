<?php


require_once(__DIR__."/Tgram.php");
require_once(__DIR__."/Button.php");
require_once(__DIR__."/DB.php");
require_once(__DIR__."/functions.php");

$model = new Tgram();
$db = new DB();
$chat_id = $_GET['chat_id'];
$id= $_GET['id'];
$name = $_GET['name'];
$state =$_GET['state'];
$type = $_GET['type'];
switch($_GET['message'])
{
    case('/start'):
    case('/help'):
        $db->UpdateTable('users', 'State', '0_student', $chat_id);
        $group = $db->ShowTable($chat_id);
        $db->UpdateTable($group, 'State', '0_student', $chat_id);
        $model->getReq('sendMessage',
            ['chat_id' => $chat_id,
                'text' => "функции /start, /help, /sendloc"]);
        break;
    case('/sendloc'):
        $group = $db->ShowTable($chat_id);
        $db->UpdateTable('users', 'State', '0_student', $chat_id);
        $db->UpdateTable($group, 'State', '0_student', $chat_id);
        
        $button = new Button();
        $button->AddKeyBoard('keyboard');
        $button->AddButton(['text' =>'Отправить геолокацию?',
            'request_location' => true], 'keyboard');

        $db->UpdateTable('users', 'State', '10_student', $chat_id);
        $db->UpdateTable($group, 'State', '10_student', $chat_id);

        $model->getReq('sendMessage',
            ['chat_id' => $chat_id,
                'text' => "Отправьте свою геолокацию ".$type,
                'reply_markup' => json_encode($button->_buttons)]);
        break;
//            case('/showgroup'):
//                break;
}
switch($state)
{
    case('1_student'):
        //введите имя
        $db->UpdateTable("users", "Name", $_GET['message'], $chat_id);
//        $db->UpdateTable("group_of_students", "Name", $_GET['message'], $chat_id);
        $db->UpdateTable("users", "State", "2_student", $chat_id);
        $model->getReq('sendMessage',
            [
                'chat_id' => $chat_id,
                'text' => 'Введите группу'
            ]);

        break;
    case('2_student'):
        $group = mb_strtolower($_GET['message'],'utf-8');
        $group_m = GetGroup($group);

        if ($group_m != '')
        {
            $db->UpdateTable("users", "State", "0_student", $chat_id);
            $db->InsertTable($group_m, "ID", $chat_id);
            $db->UpdateTable($group_m,'Name', $name, $chat_id);
            $db->UpdateTable($group_m,'State', "0_student", $chat_id);
            $model->getReq('sendMessage',
                [
                    'chat_id' => $chat_id,
                    'text' => 'Студент записан'.PHP_EOL.$group_m.PHP_EOL.'функции /start, /help, /sendloc'
                ]);
        }
        else
        {
            $model->getReq('sendMessage',
                [
                    'chat_id' => $chat_id,
                    'text' => 'Либо такой группы не существует, либо группа написана неправильно'.PHP_EOL.$group.PHP_EOL
                ]);
        }
        break;

     case('10_student'):
         if($_GET['longitude'])
         {
             $group = $db->ShowTable($chat_id);
             $db->UpdateTable($group, "Longitude", $_GET['longitude'], $chat_id);
             $db->UpdateTable($group, "Latitude", $_GET['latitude'], $chat_id);
             $db->UpdateTable("users", "State", "0_student", $chat_id);
             $db->UpdateTable($group, "State", "0_student", $chat_id);
             $remove_keyboard = ['remove_keyboard' => true];
             $model->getReq('sendMessage',
                 [
                     'chat_id' => $chat_id,
                     'text' => 'Ваша геолокация записана',
                     'reply_markup' => json_encode($remove_keyboard)
                 ]);
         }else
         {
             $model->getReq('sendMessage',
                 [
                     'chat_id' => $chat_id,
                     'text' => 'Вы не отправили геолокацию или ошибка'
                 ]);
         }
         break;
}