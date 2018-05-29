<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/9
 * Time: 20:50
 */

class ErrorController extends Yaf_Controller_Abstract
{

    public function init()
    {
        Yaf_Dispatcher::getInstance()->disableView();
    }

    public function errorAction($exception)
    {
        /* error occurs */
        switch ($exception->getCode()) {
            case YAF_ERR_NOTFOUND_MODULE:
            case YAF_ERR_NOTFOUND_CONTROLLER:
            case YAF_ERR_NOTFOUND_ACTION:
            case YAF_ERR_NOTFOUND_VIEW:
                echo 404, ':', $exception->getMessage();
                break;
            default:
                $errCode = $exception->getCode();
                $errStr = $exception->getMessage();
                $errFileName = $exception->getFile();
                $errFileLine = $exception->getLine();
                # ---- start ---- 将错误写入日志
                $logContent = date('H:i:s') . '|' . $errCode . '|' . json_encode($exception->getTrace()) . "\n";
                $typeSendToFile = 3;
                $fileName = 'spm_system_err_' . date('Ymd') . '.log';
                $filePath = Common::getConfig('siteConfig', 'logPath') . $fileName;
                error_log($logContent, $typeSendToFile, $filePath);
                # ---- end ---- 将错误写入日志

                Common::sendLogError('spm', 0, '', 5, $errCode, $errStr, $errFileName, $errFileLine);
                break;
        }
    }
}