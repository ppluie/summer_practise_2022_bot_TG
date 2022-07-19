<?php


// Класс, который порождает кнопки

class Button
{
    public $_buttons; // массив с клавиатурой и кнопками

    public function __construct()
    {
        $this->_buttons = [];
    }
    // добавляет ряд кнопок
    public function AddRowButton($button, $keyboard)
    {
        array_push($this->_buttons[$keyboard], $button);
    }
    // создаёт кнопку
    public function AddButton($button, $keyboard)
    {
        if (isset($this->_buttons[$keyboard][0]))
        {
            array_push($this->_buttons[$keyboard][0], $button);
        }
        elseif(isset($button[0]))
        {
            array_push($this->_buttons[$keyboard], $button);
        }
        else
        {
            array_push($this->_buttons[$keyboard], [$button]);
        }
    }
    // добавляет клавиатуру
    public function AddKeyBoard($keyboard)
    {
        $this->_buttons[$keyboard] = [];
    }
    // Добавляет дополнительные настройки
    public function AddOtherSettings($data)
    {
        array_push($this->_buttons, $data);

    }
    // удаляет клавиатуру
    public function DeleteKeyBoard($keyboard)
    {
        unset($this->_buttons[$keyboard]);
    }
    // удаляет кнопку
    public function DeleteButton($button_name, $keyboard)
    {
        foreach($this->_buttons[$keyboard][0] as $buttons)
        {
            $i = 0;
            if ($buttons['callback_data'] == $button_name)
            {
                break;
            }
            $i += 1;
        }
        unset($this->_buttons[$keyboard][0][$i]);
    }
//.....
}