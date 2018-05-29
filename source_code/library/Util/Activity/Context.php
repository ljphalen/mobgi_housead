<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/** 
 * 具体类
 *   
//登录 
$activity = new Util_Activity_Context(new LoginStrategy( $config = array('total'=>10) ));  
$activity ->sendTictket();  

$activity->setStrategy(new DownloadStrategy($config = array('total'=>20) ));  
$activity->sendTictket();  
 */  
class Util_Activity_Context {  
	 private $mStrategy = null;
	 const	TASK_TYPE_WEAK_TASK = 1;
	 const	TASK_TYPE_DAILY_TASK = 2;
	 const  TASK_TYPE_ACTIVITY_TASK = 3;
	 const  TASK_TYPE_AUTO_TASK = 4;
	 const  TASK_TYPE_LOTTERY_TASK = 5;
	 const  TASK_TYPE_MALL_TASK = 6;
	 
	 const  CONTENT_TYPE_SHARE_GAME = 1;
	 const  CONTENT_TYPE_SHARE_ACTIVITY = 2;
	 
	 const  DAILY_TASK_CONTINUELOGIN_TASK_ID = 1;
	 const  DAILY_TASK_DOWNLOAD_TASK_ID = 2;
	 const  DAILY_TASK_COMMENT_TASK_ID = 3;
	 const  DAILY_TASK_SHARE_TASK_ID = 4;
	 
	 const  WEAL_TASK_LOING_TASK_ID =1;
	 const  WEAL_TASK_UNIONLOGIN_TASK_ID =4;
	 const  WEAL_TASK_CONSUME_TASK_ID =5;
	 
	 
	 const GAME_OBJECT_ALL = 1;
	 
	 const GAME_OBJECT_SINGLE = 2;
	 
	 const  SEND_POINTS = 1;
	 const  SEND_TICKET = 2;
	 
	 const FINISHED_STATUS = 1;

    
  
    public function __construct(Util_Activity_Coin $cion){  
        $this->mStrategy = $cion;  
    }  
   
    public function setStrategy(Util_Activity_Coin $cion){  
        $this->mStrategy = $cion;  
    }  
 
   
    public function sendTictket(){  
        return $this->mStrategy->getCoin();  
    } 
   

    public function __destruct(){     //应用析构函数自动释放连接资源
    	
    	
    	   
    }
}   

  
