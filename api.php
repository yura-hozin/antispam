<?php
include_once "antispam.php";
include_once "config.php";
/**
 * Created by PhpStorm.
 * User: Peaceful
 * Date: 21.04.17
 * Time: 8:38
 * http://ds129penza.ru/upload/antispam/api.php?com=load_list_ip&token=12345
 * http://ds129penza.ru/upload/antispam/api.php?com=get_version&token=12345
 */
$mod = new ApiAntispam();

class ApiAntispam
{
    protected $_const_token = "12345";

    function __construct()
    {
        if (isset($_GET["token"])&&(!empty($_GET["token"]))) $token = $_GET["token"];
        else $this->setError('empty auth');

        if (!$this->avtorization($token)) $this->setError('no auth');

        $command = "";
        if (isset($_GET["com"])&&(!empty($_GET["com"]))) $command = $_GET["com"];

        switch ($command) {
            case 'load_list_ip':
                $this->getSpamListIP();
                break;
            case 'get_version':
                $this->getVersion();
                break;
            case '':
                echo "Не указана команда";
                break;
        }
    }

    /**
     * Проверка авторизованного токена
     * @param $token
     * @return bool
     */
    protected function avtorization($token)
    {
        if ($token == Config::getToken())
            return true;
        return false;
    }

    protected function setError($message)
    {
        echo json_encode(array('status' => 'error', 'message' => $message));
        die;
    }

    /**
     * Возвращает список IP, с которых приходил спам
     */
    protected function getSpamListIP()
    {
        $arr_ip = AntispamModel::getSpamListIP();
        $data = array();
        $data["status"] = "success";
        $data["data"] = $arr_ip;
        echo json_encode($data);
        die;
    }

    /**
     * Возвращает версию модуля в json
     */
    private function getVersion()
    {
        $data = array();
        $data["status"] = "success";
        $data["data"] = Config::getVersion();
        echo json_encode($data);
        die;
    }
}