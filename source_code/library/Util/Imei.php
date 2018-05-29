<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class Util_Imei{

    const EMPTY_IMEI = 'FD34645D0CF3A18C9FC4E2C49F11C510';

    static public function decryptImei($imeiEncrypt){
        $cryptAES = new Util_CryptAES();

        $decryptImei = $cryptAES->decrypt($imeiEncrypt);

        if(false === $decryptImei) {
            return null;
        }

        return $decryptImei;
    }

    static public function isValidDeviceId($deviceId) {
        $len = strlen($deviceId);
        if (15 == $len) {
            return self::isValidImei($deviceId);
        } else if (14 == $len) {
            return self::isValidMeid($deviceId);
        }

        return false;
    }
    
    static public function isEncryptImeiValid($imei) {
        $imeiDecrypt = Util_Imei::decryptImei($imei);
        return Util_Imei::isValidDeviceId($imeiDecrypt);
    }

    static private function isValidMeid($meid) {
        if(!preg_match("/^[0-9A-F]{14}$/i", $meid)) {
            return false;
        }

        $rr = substr($meid, 0, 2);

        if (strnatcasecmp($rr, 'A0') < 0) {
            return false;
        }
        if (strnatcasecmp($rr, 'FF') > 0) {
            return false;
        }

        return true;
    }

    static private function isValidImei($imei) {
        $checkDigit = self::caculateCheckDigit($imei);
        $lastDigit = intval(substr($imei, 14, 1));

        if ($lastDigit === $checkDigit) {
            return true;
        } else {
            return false;
        }
    }

    static private function caculateCheckDigit($imei) {
        if (strlen($imei) != 15) {
            return -1;
        }

        $chars = array();
        $digitSum = 0;
        $checkDigit = -1;

        for($i = 0; $i < 14; $i++) {
            $digit = intval(substr($imei, $i, 1));
            if ($i % 2 === 0) {
                $digitSum += $digit;
            } else {
                $product = 2*$digit;
                $highBit = floor($product/10);
                $lowBit = $product%10;
                $digitSum += $highBit + $lowBit;
            }
        }

        if ($digitSum % 10 === 0) {
            $checkDigit = 0;
        } else {
            $checkDigit = 10 - ($digitSum % 10);
        }

        return $checkDigit;
    }
}
