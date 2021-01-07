<?php

/* 
 * Функционал для ведения логирования работы web-сервиса
 */
class Log
{
    // Путь к лог-файлу
    public static $_path_file = "log.txt";

    /**
     * Записать ошибку
     * @param string $text текст сообщения
     */
    public static function setError($text)
    {
        $date = date("r");
        $mess = $date." | ERROR | ".$text;
        self::addMessage($mess);
    }

    public static function setInfo($text)
    {
        $date = date("r");
        $mess = $date." | INFO | ".$text;
        self::addMessage($mess);
    }

    /**
     * Записать варнинг
     * @param string $text текст сообщения
     */
    public static function setWarning($text)
    {
        $date = date("r");
        $mess = $date." | Warning | ".$text;
        self::addMessage($mess);
    }

    /**
     * Записо сообщения в файл
     * @param type $mess
     */
    public static function addMessage($mess)
    {
        $path = "upload/antispam/log.txt";
	    file_put_contents($path, PHP_EOL . $mess."\r\n", FILE_APPEND);
    }

}
