<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/** 
 * 具体策略类 连续登录
 */  


class Util_Activity_ContinueLogin extends Util_Activity_Common implements Util_Activity_Coin{  

   /**
    * 
    * @param unknown_type $config
    */
   public function __construct($config = array()){  
        $this->mConfig = $config;  
        //初始化写日志路径
        $path = Common::getConfig('siteConfig', 'logPath');
        $fileName = date('m-d').'_continueLogin.log';
        $this->mPath = $path;
        $this->mFileName = $fileName;
        parent::__construct($path, $fileName);
   }  

   /**
    * 
    * @see Util_Activity_Coin::getCoin()
    */
    public function getCoin(){
    	$type = $this->mConfig['type'];
    	//type =1 福利任务  type=2 日常任务 type=3 活动
    	if($type == 2){
    		$this->dailyTaskContinueLogin();
    	}
 
    }

    /**
     * 处理连续登录
     * @param int $continueLoginDay
     * @param string $uuid
     */
    private  function dailyTaskContinueLogin(){
    	//连续登录的配置
    	$taskParams['id'] = 1;
    	$taskParams['status'] = 1;
    	$taskConfig = Client_Service_ContinueLoginCofig::getBy($taskParams);
    	
    
    	//连续登录配置开启
    	if($taskConfig['status']){
    		$this->tryToDoneWealTaskContinueLogin($taskConfig);
    	}else{
    		$logData= '进入进入每日任务连续登录类，连续登录未开启uuid='.$this->mConfig['uuid'];
    		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	}
    }
   
    
    /**
     * 是否完成过
     * @param unknown_type $wealTaskConfig
     * @return boolean
     */
    private function tryToDoneWealTaskContinueLogin($taskConfig){
    	
    	if(!$this->mConfig['uuid'] || !$this->mConfig['type'] ||  !$this->mConfig['task_id']){
    		return false;
    	}
    	$cache = Cache_Factory::getCache();
    	$cacheHash = Util_CacheKey::getUserInfoKey($this->mConfig['uuid']) ;
    	//连续登录天数
    	$continueLoginDay =intval($cache->hGet($cacheHash, 'continueLoginDay'));
    	
    	//日志
    	$logData= '进入每日任务连续登录类，连续登录天数continueLoginDay='.$continueLoginDay;
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	if(!$continueLoginDay){
    		return false;
    	}
    	//用户是否做过连续登录任务
    	$logParams['uuid']    = $this->mConfig['uuid'] ;
    	$logParams['task_id'] = $this->mConfig['task_id'] ;
    	//$logParams['days'] = $continueLoginDay;
    	$logParams['create_time'] = array(array('>=', strtotime(date('Y-m-d 00:00:01')) ),array('<=', strtotime(date('Y-m-d 23:59:59')))) ;
    	$logRs = Client_Service_DailyTaskLog::getBy($logParams); 
    	$logData= '进入每日任务连续登录类，是否做过此任务logRs='.json_encode($logRs);
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	
    	//是否领取过
        if(!$logRs){
        	//奖励配置
        	$prizeConf = $this->getContinueLoginPrizeConfig($taskConfig, $continueLoginDay);
        	//send_object=1 赠送积分 send_object=2赠送A券
	    	if($prizeConf['send_object'] == 1){
	    		$result = $this->sendDailyTaskPoint($prizeConf);
	    	}elseif($prizeConf['send_object'] == 2){
	    		$result = $this->sendDailyTaskTicket($prizeConf);
	    	}
           //更新完成任务次数
		  /*   if($result){
		    	$finishDailyTaskNum    = $cache->hIncrBy($cacheHash, $cacheFinishNumKey, 1);
		    	$finishDailyTaskTime   = $cache->hSet($cacheHash, $cacheFinishTimeKey, date('Y-m-d H:i:s', $time));
		    	//把任务置完成状态	    	
		    } */
        }
    }
    
    /**
     * 每日任务连续登录赠送A券
     */
    private function sendDailyTaskTicket($awardConfig){
    	//取得福利任务的连续登录的配置奖励
    	$time = Common::getTime();
    	$desc = '连续登录第'.$awardConfig['days'].'天';
    
    	//获取赠送的数组,用来保存A券信息
    	$prizeArr =  $this->getTaskAwardResult($awardConfig, $desc);
    	//写日志
    	$logData= '进入每日任务评论，组装的数组prize_arr='.json_encode($prizeArr);
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	
    	//保存赠送A券记录
    	$savaRs = $this->saveWealTaskSendTickets($prizeArr, $time, $awardConfig['days']);
    	if(!$savaRs){
    		//写日志
    		$logData= '进入每日任务评论，保存赠送A券失败sava_rs'.$savaRs;
    		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    		return false;
    	}
    	
    	//组装发送到支付post数组
    	$postPrizeArr = $this->postToPaymentData($prizeArr);
    	//给支付发请求
    	$paymentResult =  $this->postToPayment($postPrizeArr);
    	//写入日志
    	$logData= '进入每日任务评论，PSOT请求到支付组服务器返回结果paymentResult='.json_encode($paymentResult);
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	//校验支付返回的结果
    	$responseData =  $this->verifyPaymentResult($paymentResult);
    	if(!$responseData){
    		return false;
    	}

    	//更新赠送A券的状态，外部A券编号
    	if($this->updateSendTickets($responseData)){
    		//更新日志
    		$this->insertDailyTaskLog($prizeArr, $awardConfig['days']);
    		//赠送消息入队列
    		$this->saveWealTaskMsg($prizeArr, $desc, $time);
    		return true;
    	}else{
    		return false;
    	}
    
    }
    
    
    /**
     * 组装任务的数组
     * @param unknown_type $wealTaskPrize
     * @return boolean
     */
    private function getTaskAwardResult($awardConfig, $desc){
    	if(!is_array($awardConfig)){
    		return false;
    	}

        if($awardConfig['denomination']){
            $awardArr[] = array(
                'denomination'=>$awardConfig['denomination'],
                'section_start'=>1,
                'section_end'=>$awardConfig['deadline'],
                'desc'=>'每日任务',
                'uuid'=>$this->mConfig['uuid'],
                'send_type'=>$this->mConfig['type'],
                'sub_send_type'=>$this->mConfig['task_id'],
            );
        }
    	
    	
    	$prizeArr =$this->getAwardResult($awardArr);
    	return $prizeArr;
    }
    
    /**
     * 保存赠送的A券
     * @param unknown_type $send_arr
     */
    private function saveWealTaskSendTickets($sendArr, $time, $days ){
    	//保存赠送A券记录
    	foreach ($sendArr as $key=>$val){
    		$tmp[$key]['uuid'] = $val['uuid'];
    		$tmp[$key]['aid'] = $val['aid'];
    		$tmp[$key]['denomination'] = $val['denomination'];
    		$tmp[$key]['status'] = 0;
    		$tmp[$key]['send_type'] = $this->mConfig['type'];
    		$tmp[$key]['sub_send_type'] = $this->mConfig['task_id'];
    		$tmp[$key]['consume_time'] = $time;
    		$tmp[$key]['start_time'] = strtotime($val['startTime']);
    		$tmp[$key]['end_time'] = strtotime($val['endTime']);
    		$tmp[$key]['description'] = $val['desc'];
    		$tmp[$key]['third_type'] = $days;
    	}
    	$rs = $this->saveSendTickets($tmp);
    	return $rs;
    }
     
    
     
    /**
     * 保存赠送消息到消息队列中
     */
    private function saveWealTaskMsg($msg_arr , $task_name, $time){
    	if(!is_array($msg_arr) || empty($msg_arr)) return false;
    	foreach ($msg_arr as $val){
    		$denomination +=$val['denomination'];
    	}
    	$desc = '恭喜，您已完成'.$task_name.'，获得'.$denomination.'A券奖励！请在有效期内使用！';
    	$rs = $this->saveMsg($this->mConfig['uuid'], $denomination, $desc);
    	return $rs;
    }
    
    
    /**
     * 插入日志表
     */
    private function insertDailyTaskLog($prizeArr, $days){
    	foreach ($prizeArr as $val){
    		$denomination +=$val['denomination'];
    	}
    	$taskData['send_object'] = 2;
    	$taskData['uuid'] = $this->mConfig['uuid'];
    	$taskData['task_id'] = $this->mConfig['task_id'];
    	$taskData['denomination'] = $denomination;
    	$taskData['days'] = $days;
    	$taskData['create_time'] = Common::getTime();
    	$taskData['status'] = 1;
    	$taskData['update_time'] = Common::getTime();
    	return Client_Service_DailyTaskLog::insert($taskData);
    }
    
    /**
     * 每日任务登录赠送积分
     */
    private function sendDailyTaskPoint($prizeConf){
    	
    	$data['uuid'] = $this->mConfig['uuid'];
    	$data['gain_type'] = 2;
    	$data['gain_sub_type'] = $this->mConfig['task_id'];
    	$data['points'] = $prizeConf['denomination'];
    	$data['days']   = $prizeConf['days'];
    	$data['create_time'] = Common::getTime();
    	$data['update_time'] = Common::getTime();
    	$data['status'] = 1;
    	$rs = Point_Service_User::gainPoint($data);
    	//日志
    	$logData= '进入每日任务连续登录类，插入积分表的结果rs='.$rs;
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	if($rs){
    		$taskData['send_object'] = 1;
    		$taskData['uuid'] = $this->mConfig['uuid'];
    		$taskData['task_id'] = $this->mConfig['task_id'];
    		$taskData['denomination'] = $prizeConf['denomination'];
    		$taskData['create_time'] = Common::getTime();
    		$taskData['days'] = $prizeConf['days'];
    		$taskData['status'] = 1;
    		$taskData['update_time'] = Common::getTime();
    		return Client_Service_DailyTaskLog::insert($taskData);
    	}
    	return $rs;
    }
    
    /**
     * 获取连续登录某天的奖励配置
     * @param array $PrizeConf
     */
    private function getContinueLoginPrizeConfig($taskConfig, $continueLoginDay){
    	//连续登录的节日活动在有效期
    	$time = Common::getTime();
    	$activityParams['status'] = 1;
    	$activityParams['start_time'] = array('<=', $time);
    	$activityParams['end_time']   = array('>=', $time);
    	$activityConfig = Client_Service_ContinueLoginActivityConfig::getBy($activityParams, array('id'=>'DESC','end_time'=>'DESC','start_time'=>'DESC'));
    	//取得福利任务的基本奖励配置奖励
    	$basePrizeArr = json_decode($taskConfig['award_json'], true);
    	$currentDayPrize = $basePrizeArr[$continueLoginDay-1];   
    	$currentDayPrize['days']= $continueLoginDay;
    	//节日活动开启
    	if($activityConfig){
	    	if($activityConfig['award_type'] == 1){
	    		 $currentDayPrize['denomination'] = $currentDayPrize['denomination']+$activityConfig['award'];
	    	}else{
	    		 $currentDayPrize['denomination'] = $currentDayPrize['denomination']*$activityConfig['award'];
	    	}
    	}

    	//写日志
    	$logData= '进入连续登录类，登录的奖励配置currentDayPrize='.json_encode($currentDayPrize);
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);

    	return $currentDayPrize;
    }
    
  
  
 
   
   
   public function __destruct(){     //应用析构函数自动释放连接资源
   		unset($this->mConfig);
   		unset($this->mPath);
   		unset($this->mFileName);
   }
   
}   
  
