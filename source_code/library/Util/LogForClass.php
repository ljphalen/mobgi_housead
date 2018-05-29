<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 日志类，对Util_Log做一个简单封装
 * @author zzw
 *
*/
class Util_LogForClass {
    protected $tag;
    protected $logFile;
    
    /**
     * 构造函数
     * @param string $logFile  日志文件名
     * @param string $tag      日志标识，比如类名
     * @return void
     */
    public function __construct($logFile, $tag) {
        $this->tag = $tag;
        $this->logFile = $logFile;
    } 
    
    /**
     * 日志输出, 出错信息
     * @access public
     * @param string $msg  消息内容
     * @return void
     */
    public function err($msg) {
        Util_Log::err($this->tag, $this->logFile, $msg);
    }
    
    /**
     * 日志输出, 警告信息
     * @access public
     * @param string $msg  消息内容
     * @return void
     */
    public function warning($msg) {
        Util_Log::warning($this->tag, $this->logFile, $msg);
    }
    
    /**
     * 日志输出, 重要信息
     * @access public
     * @param string $msg  消息内容
     * @return void
     */
    public function info($msg) {
        Util_Log::info($this->tag, $this->logFile, $msg);
    }
    
    /**
     * 日志输出, 调试信息
     * @access publicS
     * @param string $msg  消息内容
     * @return void
     */
    public function debug($msg) {
        Util_Log::debug($this->tag, $this->logFile, $msg);
    }
}