<?php
/**
 * Created by PhpStorm.
 * User: Peaceful
 * Date: 21.04.17
 * Time: 16:58
 */
class Config
{
    /**
     * Версия модуля Antispam
     * @return string
     */
    static function getVersion()
    {
        return "1.3";
    }

    /**
     * Возвращает авторизованный токен
     * @return string
     */
    static function getToken()
    {
        return "p94kisW3ihT9";
    }

    /**
     * Название файла со списком ежедневных спамовых ip
     * @return string
     */
    static function getNameListSpam()
    {
        return "list_spam_ip.hoz";
    }

    /**
     * Возвращает название файла со списком ip, которые нужно блокировать
     * @return string
     */
    static function getNameBanIP()
    {
        return "ban_ip.hoz";
    }

    /**
     * Возвразает доменное имя сервера
     * @return string
     */
    static function getServerDomain()
    {
        return "admin.host-pnz.ru";
    }

}
