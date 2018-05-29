<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/** 
 * 具体策略类 分享
 */  
class Util_Activity_Share extends Util_Activity_Common implements Util_Activity_Coin{  

   private $mTaskLimit = null;


   public function __construct($config){  
        $this->mConfig = $config;  
        //初始化写日志路径
        $path = Common::getConfig('siteConfig', 'logPath');
        $fileName = date('m-d').'_share.log';
        $this->mPath = $path;
        $this->mFileName = $fileName;
        parent::__construct($path, $fileName);
   }  
   
   /**
    *具体的实现方法
    * @see Util_Activity_Coin::getCoin()
    */
   public function getCoin(){
   	
	   $type = $this->mConfig['type'];
	   //type =1 福利任务  type=2 日常任务 type=3 活动中任务
	   if($type == 2){
	   		//每日任务的分享赠送
	   		$this->dailyTaskShare();
	   }		
	   		
   }
   
   /**
    * 每日任务的分享赠送
    */
   private function dailyTaskShare(){
	   	if(!$this->mConfig['uuid'] || !$this->mConfig['type'] ||  !$this->mConfig['task_id']){
	   		return false;
	   	}
   	
	   	$uuid = $this->mConfig['uuid'];
	   	//记录日志
	   	$logData= '进入每日任务分享类UUID='.$uuid.',game_id='.$this->mConfig['game_id'].',content_type='.$this->mConfig['content_type'].',task_id='.$this->mConfig['task_id'];
	   	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	   	//取出对应每日任务的配置
	   	$taskConfParams['id'] = $this->mConfig['task_id'];
	   	$taskConfParams['status'] = 1;
	   	$taskConfig = Client_Service_DailyTaskConfig::getBy($taskConfParams);
	   
   		if($taskConfig){
   			$this->mTaskLimit = $taskConfig['daily_limit'];
   			$this->tryToDoneDailyTask($taskConfig);
   		}else{
   			$logData= '进入进入每日任务分享类，活动未开启uuid='.$uuid;
   			Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
   		}

   }
   
   /**
    * 是否做过分享任务
    */
    private function tryToDoneDailyTask($taskConfig){
    
    	$logParams['uuid']    = $this->mConfig['uuid'] ;
    	$logParams['task_id'] = $this->mConfig['task_id'] ;
    	$logParams['content_type'] = $this->mConfig['content_type'];
    	$logParams['game_id'] = $this->mConfig['game_id'] ;
    	$logParams['status'] = 0;
    	$logParams['create_time'] = array(array('>=', strtotime(date('Y-m-d 00:00:01')) ),array('<=', strtotime(date('Y-m-d 23:59:59')))) ;
    	$logRs = Client_Service_DailyTaskLog::getBy($logParams);
    	//日志
    	$logData= '进入每日任务分享类，是否做过此任务logRs='.json_encode($logRs).'任务限制次数TaskLimit='.$this->mTaskLimit;
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	  			
    	//是否分享过
        if($logRs){
        	$cache = Cache_Factory::getCache();
        	$cacheHash = Util_CacheKey::getUserInfoKey($this->mConfig['uuid']) ;
        	//每天完成任务次数与时间
        	$time = Common::getTime();
        	$cacheFinishNumKey     = 'finishDailyTaskNum'.$this->mConfig['task_id'];
        	$cacheFinishTimeKey    = 'finishDailyTaskTime'.$this->mConfig['task_id'];
        	$finishDailyTaskNum    = $cache->hGet($cacheHash, $cacheFinishNumKey);
        	$finishDailyTaskTime   = $cache->hGet($cacheHash, $cacheFinishTimeKey);
        	//初次做任务
        	if($finishDailyTaskTime == false && $finishDailyTaskNum == false){
        		$finishDailyTaskNum = 0;
        		$finishDailyTaskTime= date('Y-m-d H:i:s', $time);
        	}
        	//处理隔天时间，把任务数初始化
        	$days = Common::diffDate($finishDailyTaskTime,  date('Y-m-d H:i:s', $time));
        	if($days != 0){
        		$finishDailyTaskNum = 0;
        		$cache->hSet($cacheHash, $cacheFinishNumKey, 0);
        	}
        	//写日志
        	$logData= '进入每日任务评论类，做任务的次数finishDailyTaskNum='.$finishDailyTaskNum.'，任务限制次数TaskLimit='.$this->mTaskLimit.'，完成任务的时间finishDailyTaskTime='.$finishDailyTaskTime;
        	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
        
        	//任务已经完成到达一定次数
        	if($finishDailyTaskNum >= $this->mTaskLimit){
        		return false;
        	}
        	
	    	//send_object=1 赠送积分 send_object=2赠送A券
	    	if($taskConfig['send_object'] == 1){
	    		$result = $this->sendDailyTaskPoint($taskConfig, $logRs['id']);
	    	//赠送A券
	    	}elseif($taskConfig['send_object'] == 2){
	    		//A券奖励配置 奖励数量
	    		$result = $this->sendDailyTaskTicket($taskConfig, $logRs['id']);
	    	}
           //更新完成任务次数
		    if($result){
		    	$finishDailyTaskNum    = $cache->hIncrBy($cacheHash, $cacheFinishNumKey, 1);
		    	$finishDailyTaskTime   = $cache->hSet($cacheHash, $cacheFinishTimeKey, date('Y-m-d H:i:s', $time));
		     
		        //更新任务总数，人数
		    	if($finishDailyTaskNum == $this->mTaskLimit){
		    		$this->updateStatisticReport($taskConfig);
		    	}else{
		    		$this->updateStatisticReportTotalQuantity($taskConfig);
		    	} 	
		    }
        }
    }
   
    /**
     * 每日任务赠送A券
     */
    private function sendDailyTaskTicket($taskConfig, $logID){
    	$awardConfig = json_decode($taskConfig['award_json'], true);
    	//取得福利任务的分享的配置奖励
    	$time = Common::getTime();
    	$desc = '每日任务-分享';
    	
    	//获取赠送的数组
    	$prizeArr =  $this->getTaskAwardResult($awardConfig, $desc);
    	//写日志
    	$logData= '进入每日任务评论，组装的数组prize_arr='.json_encode($prizeArr);
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	    	
    	//保存赠送A券记录
    	$savaRs = $this->saveWealTaskSendTickets($prizeArr, $time);
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
    		$this->updateDailyTaskLog($prizeArr, $logID);
    		//赠送消息入队列
    		$this->saveWealTaskMsg($prizeArr, '每日任务-'.$taskConfig['task_name'], $time);
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
    private function getTaskAwardResult($taskPrize, $desc){
    	if(!is_array($taskPrize)){
    		return false;
    	}
    	//取得福利任务的配置奖励
    	$time = Common::gettime();
    	foreach ($taskPrize as $val){
            if($val['denomination']){
                $awardArr[] = array(
                    'denomination'=>$val['denomination'],
                    'section_start'=>$val['section_start'],
                    'section_end'=> $val['section_end'],
                    'desc'=>$desc,
                    'uuid'=>$this->mConfig['uuid'],
                    'send_type'=>$this->mConfig['type'],
                    'sub_send_type'=>sprintf("%d%d", $this->mConfig['task_id'], $this->mConfig['content_type']),
                );
            }
    	}
    	$prizeArr =$this->getAwardResult($awardArr);
    	return $prizeArr;
    }
    
    /**
     * 保存赠送的A券
     * @param unknown_type $send_arr
     */
    private function saveWealTaskSendTickets($sendArr, $time ){
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
    	$uuid = $this->mConfig['uuid'];
    	$desc = '恭喜，您已完成'.$task_name.'，获得'.$denomination.'A券奖励！请在有效期内使用！';
    	$rs = $this->saveMsg($this->mConfig['uuid'], $denomination, $desc);
    	return $rs;
    }
	
    
    /**
     * 更新日志
     */
    private function updateDailyTaskLog($prizeArr, $logID){
    	foreach ($prizeArr as $val){
    		$denomination +=$val['denomination'];
    	}
    	$taskData['send_object'] = 2;
    	$taskData['denomination'] = $denomination;
    	$taskData['status'] = 1;
    	$taskData['update_time'] = Common::getTime();
    	return Client_Service_DailyTaskLog::update($taskData, $logID);
    }
    
    /**
     * 每日任务赠送积分
     */
    private function sendDailyTaskPoint($taskConfig, $logID){
    	$data['uuid'] = $this->mConfig['uuid'];
    	$data['gain_type'] = 2;
    	$data['gain_sub_type'] = 4;
    	$data['points'] = $taskConfig['points'];
    	$data['create_time'] = Common::getTime();
    	$data['update_time'] = Common::getTime();
    	$data['status'] = 1;
    	$rs = Point_Service_User::gainPoint($data);
    	//日志
    	$logData= '进入进入每日任务分享类，插入积分表的结果rs='.$rs.',日志表的logID='.$logID;
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		if($rs){
			$taskData['send_object'] = 1;
			$taskData['denomination'] = $taskConfig['points'];
			$taskData['status'] = 1;
			$taskData['update_time'] = Common::getTime();
			return Client_Service_DailyTaskLog::update($taskData, $logID);
		}
		return false;
    }
  
	
	public function __destruct(){     //应用析构函数自动释放连接资源
		unset($this->mTaskLimit);
		unset($this->mConfig);
		unset($this->mPath);
		unset($this->mFileName);
		   
	}
}   