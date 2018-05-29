<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/** 
 * 具体策略类 下载
 */  
class Util_Activity_Download extends Util_Activity_Common implements Util_Activity_Coin{  
   private $mTaskLimit = null;

   public function __construct($config){  
        $this->mConfig = $config;  
        //初始化写日志路径
        $path = Common::getConfig('siteConfig', 'logPath');
        $fileName = date('m-d').'_download.log';
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
	   	if($type == Util_Activity_Context::TASK_TYPE_WEAK_TASK){
	   		//福利任务下载赠送
	   		$this->wealTaskDownload();
	   	}elseif($type == Util_Activity_Context::TASK_TYPE_DAILY_TASK){
	   		//每日任务的下载赠送
	   		$this->dailyTaskDownload();
	   	}	
   }
   
   /**
    * 每日任务的下载赠送
    */
   private function dailyTaskDownload(){
	   	if(!$this->mConfig['uuid'] || !$this->mConfig['type'] ||  !$this->mConfig['task_id']){
	   		return false;
	   	}
   	
	   	$uuid = $this->mConfig['uuid'];
	   	//记录日志
	   	$logData= '进入每日任务下载类UUID='.$uuid.',game_id='.$this->mConfig['game_id'].',task_id='.$this->mConfig['task_id'];
	   	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	   	//取出对应每日任务的配置
	   	$taskConfParams['id'] = $this->mConfig['task_id'];
	   	$taskConfParams['status'] = 1;
	   	$taskConfig = Client_Service_DailyTaskConfig::getBy($taskConfParams);
	   
   		if($taskConfig){
   			$this->mTaskLimit = $taskConfig['daily_limit'];
   			$this->tryToDoneDailyTask($taskConfig);
   		}else{
   			$logData= '进入进入每日任务下载类，活动未开启uuid='.$uuid;
   			Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
   		}

   }
   
   /**
    * 完成每日任务的下载任务
    */
    private function tryToDoneDailyTask($taskConfig){
    
    	$logParams['task_id'] = $this->mConfig['task_id'] ;
    	$logParams['uuid']    = $this->mConfig['uuid'] ;
    	$logParams['game_id'] = $this->mConfig['game_id'] ;
    	$logParams['download_status'] = 3;
    	$logParams['status'] = 0;
    	$logParams['create_time'] = array(array('>=', strtotime(date('Y-m-d 00:00:01')) ),array('<=', strtotime(date('Y-m-d 23:59:59')))) ;
    	$logRs = Client_Service_DailyTaskLog::getBy($logParams);
    	//日志
    	$logData= '进入每日任务下载类，是否做过此任务logRs='.json_encode($logRs).'任务限制次数TaskLimit='.$this->mTaskLimit;
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	//是否下载过
        if(!$logRs){
        	return false;
        }
        $cache = Cache_Factory::getCache ();
		$cacheHash = Util_CacheKey::getUserInfoKey($this->mConfig['uuid']) ;
		// 每天完成任务次数与时间
		$time = Common::getTime ();
		$cacheFinishNumKey = 'finishDailyTaskNum' . $this->mConfig ['task_id'];
		$cacheFinishTimeKey = 'finishDailyTaskTime' . $this->mConfig ['task_id'];
		$finishDailyTaskNum = $cache->hGet ( $cacheHash, $cacheFinishNumKey );
		$finishDailyTaskTime = $cache->hGet ( $cacheHash, $cacheFinishTimeKey );
		
		// 初次做任务
		if ($finishDailyTaskTime === false && $finishDailyTaskNum === false) {
			$finishDailyTaskNum = 0;
			$finishDailyTaskTime = date ( 'Y-m-d H:i:s', $time );
		}
		
		$days = Common::diffDate ( $finishDailyTaskTime, date ( 'Y-m-d H:i:s', $time ) );
		// 任务已经完成到达一定次数
		if ($finishDailyTaskNum >= $this->mTaskLimit && $days == 0) {
			return false;
		}
		
		// 处理隔天时间，把任务数初始化
		if ($days != 0) {
			$finishDailyTaskNum = 0;
			$cache->hSet ( $cacheHash, $cacheFinishNumKey, 0 );
		}
		
	
		
		// 写日志
		$logData = '进入每日任务下载类，做任务的次数finishDailyTaskNum=' . $finishDailyTaskNum . '，任务限制次数TaskLimit=' . $this->mTaskLimit . '，完成任务的时间finishDailyTaskTime=' . $finishDailyTaskTime;
		Common::WriteLogFile ( $this->mPath, $this->mFileName, $logData );		
	
		
		// send_object=1 赠送积分 send_object=2赠送A券
		$sendObject = $taskConfig ['send_object'];
		if ($sendObject == Util_Activity_Context::SEND_POINTS) {
			$result = $this->sendDailyTaskPoint ( $taskConfig, $logRs ['id'] );
			// 赠送A券
		} elseif ($sendObject == Util_Activity_Context::SEND_TICKET) {
			// A券奖励配置 奖励数量
			$result = $this->sendDailyTaskTicket ( $taskConfig, $logRs ['id'] );
		}
		if (!$result) {
			return false;
		}
		// 更新完成任务次数
		$finishDailyTaskNum  = $cache->hIncrBy ( $cacheHash, $cacheFinishNumKey, 1 );
		$finishDailyTaskTime = $cache->hSet ( $cacheHash, $cacheFinishTimeKey, date ( 'Y-m-d H:i:s', $time ) );
		// 更新任务总数，人数
		if ($finishDailyTaskNum == $this->mTaskLimit) {
			$this->updateStatisticReport ( $taskConfig );
		} else {
			$this->updateStatisticReportTotalQuantity ( $taskConfig );
		}
		
        
    }
    

    
    
   
    /**
     * 每日任务赠送A券
     */
    private function sendDailyTaskTicket($taskConfig, $logID){
    	$awardConfig = json_decode($taskConfig['award_json'], true);
    	//取得福利任务的下载的配置奖励
    	$time = Common::getTime();
    	$desc = '每日任务-下载';
    
    	//保存赠送A券记录,用来保存A券信息
    	$prizeArr =  $this->getWealTaskAwardResult($awardConfig, $desc);
    	
    	$logData= '进入每日任务下载类，组装的数组prize_arr='.json_encode($prizeArr);
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
   
    	//保存赠送A券记录
    	$savaRs = $this->saveWealTaskSendTickets($prizeArr, $time);
    	if(!$savaRs){
    		//写日志
    		$logData= '进入每日任务下载类，保存赠送A券失败sava_rs'.$savaRs;
    		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    		return false;
    	}
    	 
    	//组装发送到支付post数组
    	$postPrizeArr = $this->postToPaymentData($prizeArr);
    	//给支付发请求
    	$paymentResult =  $this->postToPayment($postPrizeArr);
    	//写入日志
    	$logData= '进入每日任务下载类，PSOT请求到支付组服务器返回结果paymentResult='.json_encode($paymentResult);
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
     * 处理支付返回结果，更新赠送A券的状态
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
    	$data['gain_type'] = $this->mConfig['type'];
    	$data['gain_sub_type'] = $this->mConfig['task_id'];
    	$data['points'] = $taskConfig['points'];
    	$data['create_time'] = Common::getTime();
    	$data['update_time'] = Common::getTime();
    	$data['status'] = 1;
    	$rs = Point_Service_User::gainPoint($data);
    	//日志
    	$logData= '进入进入每日任务下载类，插入积分表的结果rs='.$rs.',日志表的logID='.$logID;
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		if($rs){
			$taskData['send_object'] = 1;
			$taskData['denomination'] = $data['points'];
			$taskData['status'] = 1;
			$taskData['update_time'] = Common::getTime();
			return Client_Service_DailyTaskLog::update($taskData, $logID);
		}
		return false;
    }
  
   /**
    * 福利任务的下载赠送
    * @return boolean
    */
   private function wealTaskDownload(){
	   	if(!$this->mConfig['uuid'] || !$this->mConfig['type'] ||  !$this->mConfig['task_id']){
	   		return false;
	   	}
	   	$uuid = $this->mConfig['uuid'];
	   	//记录日志
	   	$logData= '进入福利任务下载类UUID='.$uuid.',game_id='.$this->mConfig['game_id'].',sub_send_type='.$this->mConfig['task_id'];
	   	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	
	   	//取出对应的福利任务的下载任务
	   	$weal_task_params['id'] = $this->mConfig['task_id'];
	   	$weal_task_params['status'] = 1;
	   	$wealTaskConfig = Client_Service_WealTaskConfig::getBy($weal_task_params);
	   	//此下载任务是否开启
	   	if($wealTaskConfig){
	   		$logParams['uuid'] = $uuid;
	   		$logParams['send_type'] = 1;
	   		$logParams['sub_send_type'] = $this->mConfig['task_id'];
	   		$logRs = Client_Service_TicketTrade::getBy($logParams);
	   		//用户是否做过此任务
	   		if(!$logRs){
	   			//判断是否在专题中
	   			if($wealTaskConfig['subject_id'] && $this->checkGameIdIsSuject($this->mConfig['game_id'], $wealTaskConfig['subject_id'])){
	   				 $this->tryToDoneWealTask($wealTaskConfig);
	   			}else{
	   				//写入日志
	   				$logData= '此游戏ID不再专题中game_id'.$this->mConfig['game_id'].',subject_id = '.$wealTaskConfig['subject_id'];
	   				Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	   			}  
	   		}else{
	   			//写入日志
	   			$logData= '用户做过此任务uuid='.$uuid;
	   			Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	   		}
	   	}else{
	   		//写入日志
	   		$logData= '此下载任务未开启id='.$this->mConfig['task_id'];
	   		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	   	}
	   	
	}
	
	/**
	 * 做福利任务的下载任务
	 */
	private function tryToDoneWealTask($wealTaskConfig){
		//写日志
		$logData= '进入福利任务，做任务开始UUID'.$this->mConfig['uuid'];
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		
		//获取赠送的奖励
		$wealTaskPrize = json_decode($wealTaskConfig['award_json'],true);
		
		$time = Common::getTime();
		$desc ='福利任务';
		
		//获取赠送的数组,用来保存A券信息
		$prizeArr =  $this->getWealTaskAwardResult($wealTaskPrize, $desc);
		
		//写日志
		$logData= '进入福利任务登录类，组装的数组prize_arr='.json_encode($prizeArr);
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		 
		//保存赠送A券记录
		$savaRs = $this->saveWealTaskSendTickets($prizeArr, $time);
		if(!$savaRs){
			//写日志
			$logData= '进入福利任务登录类，保存赠送A券失败sava_rs'.$savaRs;
			Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
			return false;
		}
		
		//组装发送到支付post数组
		$postPrizeArr = $this->postToPaymentData($prizeArr);
		//给支付发请求
		$paymentResult =  $this->postToPayment($postPrizeArr);
		//写入日志
		$logData= '进入福利任务登录类，PSOT请求到支付组服务器返回结果paymentResult='.json_encode($paymentResult);
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		//校验支付返回的结果
		$responseData =  $this->verifyPaymentResult($paymentResult);
		if(!$responseData){
			return false;
		}
		//更新A券的状态
		if($this->updateSendTickets($responseData)){
			//赠送消息入队列
			$this->saveWealTaskMsg($prizeArr, '福利任务-'.$wealTaskConfig['task_name'], $time);
			//保存用户做的任务进度
			$cache = Cache_Factory::getCache();
			$cacheKey = Util_CacheKey::getUserInfoKey($this->mConfig['uuid']) ;
			$arr = json_decode($cache->hget($cacheKey,'finishTaskid'),true);
			if(!in_array($this->mConfig['task_id'], $arr)){
				$wealTaskProcess = $cache->hget($cacheKey,'wealTaskProcess');
				if($wealTaskProcess){
					$cache->hIncrBy($cacheKey,'wealTaskProcess',1);
				}else{
					$cache->hSet($cacheKey,'wealTaskProcess',1);
				}
				if($arr){
					array_push($arr,$this->mConfig['task_id']);
				}else{
					$arr = array($this->mConfig['task_id']);
				}	
				$cache->hSet($cacheKey,'finishTaskid',json_encode($arr));
			}
			//更新人数与总金额
			$this->updateStatisticReport($wealTaskConfig);
			return true;
		}else{
			return false;
		}
		 
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
	 * 组装福利任务的数组
	 * @param unknown_type $wealTaskPrize
	 * @return boolean
	 */
	private function getWealTaskAwardResult($wealTaskPrize, $desc){
		if(!is_array($wealTaskPrize)){
			return false;
		}
		//取得福利任务的配置奖励
		$time = Common::gettime();
		foreach ($wealTaskPrize as $val){
            if($val['denomination']){
                $awardArr[] = array(
                    'denomination'=>$val['denomination'],
                    'section_start'=>$val['section_start'],
                    'section_end'=> $val['section_end'],
                    'desc'=>$desc,
                    'uuid'=>$this->mConfig['uuid'],
                    'send_type'=>$this->mConfig['type'],
                    'sub_send_type'=>$this->mConfig['task_id']
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
	


	public function __destruct(){     //应用析构函数自动释放连接资源
		unset($this->mConfig);
		unset($this->mPath);
		unset($this->mFileName);
		unset($this->mTaskLimit);
		   
	}
}   
