<?php


use mysqli;
require_once(__DIR__."/Tgram.php");
// класс работы с базой данных 
class DB
{
    // Возвращает данные пользователя из таблицы
    public function GetUser($name_table, $chat_id)
    {
        $link =new mysqli("sergeije.beget.tech", "sergeije_qwe", "aedqws132A", "sergeije_qwe");
        $sql = "SELECT * FROM $name_table WHERE ID=$chat_id";
        $result = $link->query($sql);
        $link->close();
        return $result;
    }
    // возращает элементы таблицы пользователя
    public function GetItem($name_table, $category, $chat_id)
    {
        $link =new mysqli("sergeije.beget.tech", "sergeije_qwe", "aedqws132A", "sergeije_qwe");
        $sql = "SELECT $category FROM $name_table WHERE ID=$chat_id";
        $result = $link->query($sql);
        $link->close();
        return $result;
    }
    // вставляет элемент в таблицу
    public function InsertTable($name_table, $categories, $values)
    {
        $link =new mysqli("sergeije.beget.tech", "sergeije_qwe", "aedqws132A", "sergeije_qwe");
        $sql = "INSERT INTO $name_table ($categories) VALUES ($values)";
        $link->query($sql);
        $link->close();
    }
    // обновляет данные в таблице
    public function UpdateTable($name_table, $category,$value, $chat_id)
    {
        $link =new mysqli("sergeije.beget.tech", "sergeije_qwe", "aedqws132A", "sergeije_qwe");
        $sql = "UPDATE $name_table SET $category = '$value' WHERE ID = $chat_id";
        $link->query($sql);
        $link->close();
    }
    // обновляет все данные в таблице
    public function UpdateAllTable($name_table, $category, $value)
    {
        $link =new mysqli("sergeije.beget.tech", "sergeije_qwe", "aedqws132A", "sergeije_qwe");
        $sql = "UPDATE $name_table SET $category = '$value'" ;
        $link->query($sql);
        $link->close();
    }
    // оповещает всех пользователей в таблице
    public function AttentionAllUsers($name_table, $message)
    {
        $tg = new Tgram();
        $link =new mysqli("sergeije.beget.tech", "sergeije_qwe", "aedqws132A", "sergeije_qwe");
        $sql = "SELECT * FROM $name_table";

        $users = $link->query($sql);
        foreach ($users as $row)
        {
            $chat_id= $row["ID"];
            $tg->getReq('sendMessage', [
                'chat_id' => $chat_id,
                'text' => $message
            ]);
        }
        $link->close();
    }
    // Возвращает массив всех пользователей в таблице
    public function GetAllUsers($name_table)
    {
        $link =new mysqli("sergeije.beget.tech", "sergeije_qwe", "aedqws132A", "sergeije_qwe");
        $sql = "SELECT * FROM $name_table";
        $result = $link->query($sql);
        $link->close();
        return $result;
    }
    // создает таблицу
    public function CreateTable($name_table)
    {

        $sql = "CREATE TABLE IF NOT EXISTS $name_table
                (
                    ID INT(20) NOT NULL,
                    Name TEXT NOT NULL,
                    State TEXT NOT NULL,
                    Longitude DOUBLE NOT NULL,
                    Latitude DOUBLE NOT NULL,
                    Attendance MEDIUMTEXT NOT NULL,
                    PRIMARY KEY(ID)
);";
        $link =new mysqli("sergeije.beget.tech", "sergeije_qwe", "aedqws132A", "sergeije_qwe");
        $link->query($sql);
        $link->close();
    }
    
    // Показывает имя таблицы
    public function ShowTable($chat_id)
    {
        global $groups_table;
        foreach ($groups_table as $key => $value)
        {
            $sql = "SELECT * FROM $value WHERE ID = $chat_id";
            $link =new mysqli("sergeije.beget.tech", "sergeije_qwe", "aedqws132A", "sergeije_qwe");
            $res = $link->query($sql);
            $link->close();
            foreach ($res as $row)
            {
                if (isset($row['Longitude']))
                {
                    return $value;
                }
            }

        }
    }




}