<?php

require_once(__DIR__."/Tgram.php");
require_once(__DIR__."/Button.php");
require_once(__DIR__."/DB.php");

$chat_id = $_GET["chat_id"];



$model = new Tgram();

// обработчик сообещения 

switch($_GET['message'])
{
    case('/start'):
    case('/help'):
        //func
        $model->getReq('sendMessage',
            ['chat_id' => $chat_id,
                'text'=> 'функции /start, /help, /reg']);
        break;
    case('/reg'):
        $button = new Button();
        $button->AddKeyBoard('inline_keyboard');
        $button->AddButton([['text' => 'Преподаватель', 'callback_data' => 'teacher_button'],
            ['text' => 'Студент', 'callback_data' => 'student_button']], 'inline_keyboard');
        $model->getReq('sendMessage',
            ['chat_id' => $chat_id
                ,'text' => 'Вы хотите зарегистрироваться как:',
                'reply_markup' => json_encode($button->_buttons)]);
        break;

}
// обработчик кнопок
if ($_GET['button'])
{
    $db = new DB();
    
    switch($_GET['button'])
    {
        
        case('teacher_button'):
            $db->InsertTable("users", "ID", $chat_id);
            $db->InsertTable("teachers", "ID", $chat_id);
            $db->UpdateTable("users", "State",'1_teacher', $chat_id);
            $db->UpdateTable("teachers", "State",'1_teacher', $chat_id);
            $db->UpdateTable("users", "Type",'teacher', $chat_id);
            //$url = 'https://ppluie.ru/Classes/TeacherController.php';
            $model->getReq('sendMessage',
                [
                    'chat_id' => $chat_id,
                    'text' => 'Введите ФИО:'
                ]);
            break;
        case('student_button'):
            $db->InsertTable("users", "ID", $chat_id);
            $db->UpdateTable("users", "State",'1_student', $chat_id);
            $db->UpdateTable("users", "Type",'student', $chat_id);
            $model->getReq('sendMessage',
                [
                    'chat_id' => $chat_id,
                    'text' => 'Введите ФИО:'
                ]);
            break;
    }

}
