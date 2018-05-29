<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class Util_CryptAES {
    private $mCipher = MCRYPT_RIJNDAEL_128;
    private $mMCryptMode = MCRYPT_MODE_CBC;
    private $mPaddingMode = 'pkcs5';
    private $mSecretKey = 'GIONEE2012061900';
    private $mIv = '0102030405060708';


    public function setCipher($cipher){
        $this->mCipher = $cipher;
    }

    public function setMCryptMode($mode){
        $this->mMCryptMode = $mode;
    }

    public function setIv($iv){
        $this->mIv = $iv;
    }

    public function setKey($key){
        $this->mSecretKey = $key;
    }

    public function setPaddingMOde($paddingMode) {
        $this->mPaddingMode = $paddingMode;
    }

    public function encrypt($plaintext) {
        $plaintextPadded = $this->pad($plaintext);
        $td = mcrypt_module_open($this->mCipher, '', $this->mMCryptMode, '');

        if (empty($this->mIv)) {
            $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        } else {
            $iv = $this->mIv;
        }

        mcrypt_generic_init($td, $this->mSecretKey, $iv);
        $ciphertext = mcrypt_generic($td, $plaintextPadded);
        $rt = bin2hex($ciphertext);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $rt;
    }

    public function decrypt($ciphertext){
        $td = mcrypt_module_open($this->mCipher, '', $this->mMCryptMode, '');

        if (empty($this->mIv)) {
            $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        } else {
            $iv = $this->mIv;
        }

        mcrypt_generic_init($td, $this->mSecretKey, $iv);
        $decryptedText = mdecrypt_generic($td, $this->hex2bin($ciphertext));
        $rt = $decryptedText;
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $this->unpad($rt);
    }

    private function hex2bin($hextData) {
        $binData = '';
        $length = strlen($hextData);
        for ($i=0; $i < $length; $i += 2) {
            $binData .= chr(hexdec(substr($hextData, $i, 2)));
        }
        return $binData;
    }

    private function pad($str) {
        return $this->padOrUnPad($str, '');
    }

    private function unpad($str) {
        return $this->padOrUnPad($str, 'Un');
    }

    private function padOrUnPad($str, $ext){
        if (is_null($this->mPaddingMode) ) {
            return $str;
        } else  {
            $funcName = __CLASS__ . '::' . $this->mPaddingMode . $ext . 'Pad';
            if ( is_callable($funcName) ) {
                $size = mcrypt_get_block_size($this->mCipher, $this->mMCryptMode);
                return call_user_func($funcName, $str, $size);
            }
        }

        return $str;
    }

    public static function pkcs5Pad($text, $blockSize){
        $pad = $blockSize - (strlen($text) % $blockSize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public static function pkcs5UnPad($text){
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }
}
