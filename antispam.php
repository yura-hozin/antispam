<?php
include_once "config.php";
include_once "log.class.php";
/**
 * Created by PhpStorm.
 * User: Hozin
 * Date: 12.02.17
 * Version: 1.2
 */

class AntispamModel
{
    /**
     * Запуск проверки ip на spam
     */
    static function Run()
    {
        $ip = self::getIpAddressUser();
        if (empty($ip)) Log::setError("IP юзера не определен");

        // Проверка системы
        self::setDefaultSystem();

        $file_name = self::getPathToBanIP();
        if (file_exists($file_name))
        {
            $list_ip = implode('', file($file_name));

            if(strpos($list_ip, $ip)){
                self::addSpamIPtoList($ip);
                include_once self::getPath().'stop_spam.html';
                die;
            }
        }
        else Log::setWarning("Не найден файл заблокированных IP");
    }

    /**
     * Возвращает IP адрес пользователя
     * @return string
     */
    static function getIpAddressUser()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
            return $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        else
            return$_SERVER['REMOTE_ADDR'];
    }

    /**
     * Увеличение счетчика на 1
     */
    static function setCounterPlus()
    {
        $file_name = self::getPath()."counter.txt";

        if (file_exists($file_name))
        {
            $file = file_get_contents($file_name);
            $file++;
            $f=fopen($file_name,'w');
            fwrite($f,$file);
            fclose($f);
        }
        else{
            $fp = fopen($file_name, "w");
            $count = fgetc($fp);
            fwrite($fp, 1);
            fclose($fp);
        }
    }

    /**
     * Добавление ip в спам-список
     * @param $ip
     */
    static function addSpamIPtoList($ip)
    {
        $file_name = self::getPathToListSpam();

        // если файл уже существует
        if (file_exists($file_name))
        {
            $file = file_get_contents($file_name);
            $file += $ip;
            $f=fopen($file_name,'a');
            fwrite($f, "\r\n" . $ip);
            fclose($f);
        }
        else{
            $fp = fopen($file_name, "w");
            fwrite($fp, $ip);
            fclose($fp);
        }
    }

    /**
     * Проверка системы
     */
    static function setDefaultSystem()
    {
        // Сегодняшняя дата
        $date = date ("d.m.y");

        $file_ban_ip = self::getPathToBanIP();

        $data_ban_ip = "";
        // дата создания файла заблокированных ip
        if (file_exists($file_ban_ip))
            $data_ban_ip = date ("d.m.y", filemtime($file_ban_ip));

        if ($date != $data_ban_ip)
        {
            $url = "http://".Config::getServerDomain()."/antispam/load_ban_ip/token-".Config::getToken();
            $ans = self::sendCurl($url);
            $arr_ans = json_decode($ans);

            if (isset($arr_ans->status)&&(!empty($arr_ans->status))&&($arr_ans->status == "success"))
            {
                $mas = $arr_ans->data;

                $f = fopen(self::getPathToBanIP(), 'w');
                foreach($mas as $k=>$v)
                {
                    fwrite($f, $mas[$k]."\r\n");
                }
                Log::setInfo("Новая база заблокированных IP установленна");
                fclose($f);
            }
            else Log::setError("Ошибки при загрузке обновленной базы заблокированных ip с сервера");
        }
    }

    /**
     * Запрос Curl на выполнение url
     * @param $url
     * @return mixed|null
     */
    static function sendCurl($url)
    {
        if( $curl = curl_init() )
        {
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            $out = curl_exec($curl);
            curl_close($curl);
            return $out;
        }
        return null;
    }

    /**
     * Возвращает спам-список ip, с которых приходил спам
     */
    static function getSpamListIP()
    {
        $file_name = self::getPathToListSpam();

        $arr_ip = array();

        // если файл уже существует
        if (file_exists($file_name))
        {
            $lines = file(AntispamModel::getPathToListSpam());
            foreach ($lines as $line_num => $line) {
                $str_ip = str_replace("\r\n", "", $line);
                $arr_ip[] = $str_ip;
            }
        }

        if (count($arr_ip) > 0)
            unlink($file_name);

        return $arr_ip;
    }

    /**
     * Возвращает ссылку на файл со списком спамовых ip
     * @return string
     */
    static function getPathToListSpam()
    {
        return self::getPath().Config::getNameListSpam();
    }

    /**
     * Возвращает путь к файлу с ip, которые нужно блокировать
     * @return string
     */
    static function getPathToBanIP()
    {
        return self::getPath().Config::getNameBanIP();
    }

    /**
     * Возвращает путь к дирректории antispam
     * @return string
     */
    static function getPath()
    {
        return $_SERVER['DOCUMENT_ROOT']."/upload/antispam/";
    }

    /**
     * Проверка есть ли в тексте ссылки
     * @param string $text проверяемый текст
     * @return bool (true - есть ссылки)
     */
    static function checkTextOnLink($text)
    {
        $key_ignor = false;
        $ignor = array('http', '.ru', '.com', '.info', '.рф', '.net', 'www');
        foreach ($ignor as $item)
            if (strpos($text, $item, 0) !== false) $key_ignor = true;
        return $key_ignor;
    }

    /**
     * Проверяет откуда пришел посетитель. Если не с нашего сайта, то false
     */
    static function checkRefLink()
    {
        $key = false;
        $ref = getenv("HTTP_REFERER");
        if (!empty($ref))
        {
            $beg = strpos($ref, 'http', 0);
            if ($beg >= 0){
                $beg = 7;
                $end = strpos($ref, '/', $beg);
                if ($end === false) $end = strlen($ref);
                $domen = substr($ref, $beg, $end-$beg);
                if ($domen == $_SERVER['SERVER_NAME']) $key = true;
            }
        }
        return $key;
    }

    /**
     * Проверяет много ли в тексте русских символов
     * @param string $text текст
     * @return bool (true - русских символов больше чем других)
     */
    static function checkRussianText($text)
    {
        $all = strlen($text);
        preg_match_all( '/[а-яё]/ui', $text, $matches);
        $rus = count($matches[0])*2;
        $norus = $all - $rus;

        if ($rus > $norus) return true;
        return false;
    }

    static function d1($arr)
    {
        echo "<pre>"; print_r($arr); echo "</pre>";
        die;
    }

    static function d2($arr)
    {
        echo "<pre>"; print_r($arr); echo "</pre>";
    }

} 