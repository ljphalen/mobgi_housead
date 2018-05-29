<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class Util_Inspector{

    /**
     * @param $keyParam: array('apiName' > xxxx, 'imei' => yyyy, 'uname' => zzzz); imei must be decrypt
     * @param $ivParam: string which content is uuid
     * @param $serverId: string, encrypt serverId field.
     * @param $serverIdParam: array('clientVersion' > xxxx, 'imei' => yyyy, 'uname' => zzzz); imei must be decrypt
     * @return true if verify pass, else false
     */
    static public function verifyServerId($keyParam, $ivParam, $serverId, $serverIdParam) {
        if ((!$keyParam) || (!$ivParam) || (!$serverId) || (!$serverIdParam)) {
            return false;
        }
        if((!is_array($keyParam)) || (!is_array($serverIdParam))) {
            return false;
        }

        $clientVersion = $serverIdParam['clientVersion'];
        $imei = $serverIdParam['imei'];
        $uname = $serverIdParam['uname'];
        $serverIdVeriFied = $clientVersion . '_' . $imei . '_' . $uname;
        
        $decriptServerId = self::decryptServerId($keyParam, $ivParam, $serverId);
        if ($decriptServerId === $serverIdVeriFied) {
            return true;
        }
        return false;
     }

    /**
     * @param $keyParam: array('apiName' > xxxx, 'imei' => yyyy, 'uname' => zzzz); imei must be decrypt
     * @param $ivParam: string which content is uuid
     * @param $serverId: string, encrypt serverId field.
     * @return string, decrypt serverId field
     */
    static private function decryptServerId($keyParam, $ivParam, $serverId) {
        $apiName = strtoupper($keyParam['apiName']);
        $imei = $keyParam['imei'];
        $uname = $keyParam['uname'];

        $key = md5($apiName . $imei . $uname);
        $key = substr($key, 0, 16);

        $iv = md5($ivParam);
        $iv = substr($iv, 0, 16);
        
        $cryptAES = new Util_CryptAES();
        $cryptAES->setIv($iv);
        $cryptAES->setKey($key);
        return $cryptAES->decrypt($serverId);
    }

}
