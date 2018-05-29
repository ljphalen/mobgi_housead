<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class Util_ReportData {
    private static $agentIP = '';//"192.168.3.20";
    private static $agentPort = ''; //6969
    private static $agentRatio = 10; //6969
    const       MAX_MSGLEN = 500;

    //ratio 控制记录日志流水的比例,10表示存10%
    public static function ratioInit($ratio) {
        if (!isset($ratio) || !is_int($ratio)) {
            return;
        }
        if ($ratio >= 1 && $ratio <= 100) {
            self::$agentRatio = $ratio;
        }
    }

    //ip:通常就是本机ip
    public static function loginit($ip, $port) {
        self::$agentIP = $ip;
        self::$agentPort = $port;
    }

    // 与printf的用法相似
    // logprintf(format, data1, data2, ....);
    // 至少要包含8个参数，其中第一个为格式串，其余为对应的数据内容(至少7个必填字段。请参考接口文档说明)
    // return 0:成功 -1:失败
    private static function logprintf() {
        $num = func_num_args();
        // 7个必填的字段，再加一个格式串
        if ($num < 5) {
            return -1;
        }
        $args = func_get_args();
        $log = vsprintf($args [0], array_slice($args, 1));
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket < 0) {
            return -1;
        }

        if (!@socket_connect($socket, self::$agentIP, self::$agentPort)) {
            socket_close($socket);
            return -1;
        }
        $len = strlen($log);
        $ret = socket_write($socket, $log, strlen($log));
        if ($ret != $len) {
            socket_close($socket);
            return -1;
        }
        socket_close($socket);
        return 0;
    }

    /********************* demo usage for logprintf
     * $project 整形 1为SDK,2为游戏中心,3为17玩等等.
     *
     * $ip = "192.168.1.1";
     * $playerid = 12345;
     * $biz = "finance.stock.dpfx";
     * $op = "login";
     * $status = 0;
     * $logid = 119;
     * $flowid = 345678;
     * $custom = "custom message from php";
     * loginit("127.0.0.1", 6578);
     * if(logprintf("%s,%d,%s,%s,%d,%d,%d,%s", $ip, $playerid, $biz, $op, $status, $logid, $flowid, $custom) < 0)
     * {
     * echo "logprintf failed\n";
     * }
     **********************/

    /**********error log api*****错误日志API********/
    //level: LOG_DEBUG, LOG_INFO, LOG_WARN, LOG_ERROR, LOG_FATAL

    /*
     * $module 模块名,比如订单处理等
     * $playerid 玩家ID,无则0
     * $cmd 命名名称
     * $level 错误级别 1,2,3,4,5
     * $errcode 错误码比如404
     * $msg 错误消息 比如mysql has gone away
     * $project 项目类型 比如sdk,17玩,游戏中心等, support:1, sdk_feed:2, secure:3
     * $exectime 执行时间,单位毫秒 比如30
     * $useagent useagent 无则为""
     */
    public static function SEND_LOG_ERROR($module, $playerid, $cmd, $level, $errcode, $msg, $project, $exectime, $useagent, $file_name = '', $file_line = '') {
        self::sendError($module, $playerid, $cmd, $level, $errcode, $msg, $project, $exectime, $useagent, $file_name, $file_line);
    }
    /**********error log api*****错误日志API********/

    /*
     *$playerid 玩家ID,无则0
     * $module 模块名,比如订单处理等
    * $oper 方法名称
    * $level 错误级别 1,2,3,4,5
    * $retcode 返回值,比如200
    * $msg 错误消息 比如mysql has gone away
    * $project 项目类型 比如sdk,17玩,游戏中心等
    * $exectime 执行时间,单位毫秒 比如30
    * $useagent useagent 无则为""
    */
    /**********access log api******流水日志API****/
    public static function SEND_LOG_ACCESS($playerid, $module, $oper, $retcode, $msg, $project, $exectime, $useagent) {
        //获取随机数
        $radio = rand(1, 100);
        //如果不在存取范围内,则丢弃。默认只保存10分之一的数据.
        if ($radio > self::$agentRatio) {
            return;
        }
        try {
            self::sendAccessLog($playerid, $module, $oper, $retcode, $msg, $project, $exectime, $useagent);
        } catch (Exception $e) {

        }
    }
    /**********access log api*******流水日志API****/


    //====================================================================================================
    private static function sendError($module, $playerid, $cmd, $level, $errcode, $msg, $project, $exectime, $useagent, $file = null, $line = null) {
        if (!isset($module) || !isset($cmd) || !isset($level) || !isset($errcode) || !isset($msg) || !isset($project)) {
            return -1;
        }

        if (!isset($playerid)) {
            $playerid = "";
        }

        if (!isset($exectime)) {
            $exectime = 0;
        }
        if (!isset($useagent)) {
            $useagent = "";
        }

        $localip = $_SERVER['SERVER_ADDR'];
        if ($localip == "") $localip = "unkown";
        if (function_exists("posix_getpid")) {
            $pid = posix_getpid();
        } else {
            $pid = get_current_user();
        }


        if (!empty($file) && !empty($line)) {
            $srcfile = $file;
            $func = '';
            $srcline = $line;
        } else {
            $e = new Exception("");
            $trace = $e->getTrace();
            if (isset($trace[2]) && isset($trace[2]["file"]) && isset($trace[2]["line"]) && isset($trace[2]["function"])) {
                $srcfile = $trace[2]["file"];
                $func = $trace[2]["function"];
                $srcline = $trace[1]["line"];
            } else if (isset($trace[1]) && isset($trace[1]["file"]) && isset($trace[1]["line"]) && isset($trace[1]["function"])) {
                $srcfile = $trace[1]["file"];
                $func = $trace[1]["function"];
                $srcline = $trace[1]["line"];
            } else {
                $srcfile = $trace[0]["file"];
                $func = $trace[0]["function"];
                $srcline = $trace[0]["line"];
            }
        }

        if (strlen($msg) > self::MAX_MSGLEN) $msg = substr($msg, 0, self::MAX_MSGLEN);

        $msg = str_replace("&", " ", $msg);
        $msg = str_replace(",", "&", $msg);
        $useagent = str_replace("&", " ", $useagent);
        $useagent = str_replace(",", "&", $useagent);
        $request_time = time();
        $type = 1; //type=1为错误上报,type=2为流水访问上报

        /*return logprintf("%s,%u,%s,%s,%d,%d,%d,%s,%d,%s,%s,%d,%d,%s",
            $localip, $playerid, $module, $cmd, $errcode, 479, 0,
            $srcfile, $srcline, $func, "httpd", $pid, $level, $msg);*/

        return self::logprintf("%s,%s,%u,%s,%s,%d,%d,%d,%s,%d,%s,%d,%d,%s,%d,%s,%d", 'Error', $localip, $playerid, $module, $cmd, $errcode, 0, $project, $srcfile, $srcline, $func, $pid, $level, $msg, $exectime, $useagent, $request_time);
    }


    private static function sendAccessLog($playerid, $module, $oper, $retcode, $msg, $project, $exectime, $useagent) {
        //echo self::$agentIP;
        //echo ":";
        //echo self::$agentPort;

        if (!isset($module) || !isset($playerid) || !isset($oper) || !isset($retcode) || !isset($msg) || !isset($project)) {
            return -1;
        }
        if (!isset($playerid)) {
            $playerid = "";
        }

        if (!isset($exectime)) {
            $exectime = 0;
        }
        if (!isset($useagent)) {
            $useagent = "";
        }

        $localip = $_SERVER['SERVER_ADDR'];
        if ($localip == "") $localip = "unkown";
        if (function_exists("posix_getpid")) {
            $pid = posix_getpid();
        } else {
            $pid = get_current_user();
        }

        $e = new Exception("");
        $trace = $e->getTrace();
        //var_dump($trace);
        if (isset($trace[2]) && isset($trace[2]["file"]) && isset($trace[2]["line"]) && isset($trace[2]["function"])) {
            $srcfile = $trace[2]["file"];
            $func = $trace[2]["function"];
            $srcline = $trace[2]["line"];
        } else if (isset($trace[1]) && isset($trace[1]["file"]) && isset($trace[1]["line"]) && isset($trace[1]["function"])) {
            $srcfile = $trace[1]["file"];
            $func = $trace[1]["function"];
            $srcline = $trace[1]["line"];
        } else {
            $srcfile = $trace[0]["file"];
            $func = $trace[0]["function"];
            $srcline = $trace[0]["line"];
        }

        if (strlen($msg) > self::MAX_MSGLEN) $msg = substr($msg, 0, self::MAX_MSGLEN);

        $msg = str_replace("&", " ", $msg);
        $msg = str_replace(",", "&", $msg);
        $useagent = str_replace("&", " ", $useagent);
        $useagent = str_replace(",", "&", $useagent);

        $request_time = time();

        /*return logprintf("%s,%u,%s,%s,%d,%d,%u,%s,%s,%d,%s",
                $localip, $playerid, $module, $oper, $retcode, 534, $iflow,
                $srcfile, $func, $srcline, $msg);*/
        return self::logprintf("%s,%s,%u,%s,%s,%d,%d,%d,%s,%d,%s,%d,%d,%s,%d,%s,%d", 'Access', $localip, $playerid, $module, $oper, 0, $retcode, $project, $srcfile, $srcline, $func, $pid, 0, $msg, $exectime, $useagent, $request_time);
    }
}