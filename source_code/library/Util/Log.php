<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 日志类
 * @author rock.luo
 *
 */
class Util_Log {
    // 日志级别 从上到下，由高到低
    const ASSERT    = 4;  // 严重错误: 导致系统崩溃无法使用
    const ERR       = 3;  // 一般错误: 一般性错误
    const WARNING   = 2;  // 警告性错误: 需要发出警告的错误
    const INFO      = 1;  // 信息: 程序输出信息
    const DEBUG     = 0;  // 调试: 调试信息

    const LOG_LEVEL = self::DEBUG;

    private static $sLogLevel = array(
                                   self::ASSERT => 'A',
                                   self::ERR => 'E',
                                   self::WARNING => 'W',
                                   self::INFO=> 'I',
                                   self::DEBUG => 'D',
                                  );

    /**
     * 日志记录, 系统级严重错误
     * @static
     * @access public
     * @param string $tag 日志标签，建议使用类文件名
     * @param string $logFile 保存日志的文件名
     * @param string $msg  消息内容
     * @return void
     */
    static public function assert($tag, $logFile, $msg) {
        self::log(self::ASSERT, $tag, $logFile, $msg);
    }

    /**
     * 日志输出, 出错信息
     * @static
     * @access public
     * @param string $tag 日志标签，建议使用类文件名
     * @param string $logFile 保存日志的文件名
     * @param string $msg  消息内容
     * @return void
     */
    static public function err($tag, $logFile, $msg) {
        self::log(self::ERR, $tag, $logFile, $msg);
    }

    /**
     * 日志输出, 警告信息
     * @static
     * @access public
     * @param string $tag 日志标签，建议使用类文件名
     * @param string $logFile 保存日志的文件名
     * @param string $msg  消息内容
     * @return void
     */
    static public function warning($tag, $logFile, $msg) {
        self::log(self::WARNING, $tag, $logFile, $msg);
    }

    /**
     * 日志输出, 重要信息
     * @static
     * @access public
     * @param string $tag 日志标签，建议使用类文件名
     * @param string $logFile 保存日志的文件名
     * @param string $msg  消息内容
     * @return void
     */
    static public function info($tag, $logFile, $msg) {
        self::log(self::INFO, $tag, $logFile, $msg);
    }

    /**
     * 日志输出, 调试信息
     * @static
     * @access public
     * @param string $tag 日志标签，建议使用类文件名
     * @param string $logFile 保存日志的文件名
     * @param string $msg  消息内容
     * @return void
     */
    static public function debug($tag, $logFile, $msg) {
        self::log(self::DEBUG, $tag, $logFile, $msg);
    }

    static private function log($level, $tag, $fileName, $msg) {
        if (self::LOG_LEVEL > $level) {
            return;
        }

        $logTime = date('Y-m-d H:i:s') . '.' . self::getMillisecond();
        $pid = getmypid();
        $logLevel = self::$sLogLevel[$level];

        $logContent = $logTime . '   ' . $pid . '   ' . $logLevel;
        $logContent = $logContent . '   ' . $tag . ':';

        $logContent = $logContent . json_encode($msg, JSON_UNESCAPED_UNICODE);

        $logContent = $logContent . "\n";
        $typeSendToFile = 3;
        $fileName = date('Y-m-d') . '_' . $fileName;
        $filePath = Common::getConfig('siteConfig', 'logPath') .  $fileName;

        error_log($logContent, $typeSendToFile, $filePath);
    }

    static private function getMillisecond() {
        list($millisecond, $second) = explode(' ', microtime()); 
        return sprintf('%03d', floatval($millisecond) * 1000); 
    }
}
