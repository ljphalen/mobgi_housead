<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/** 
 * 具体策略类 评论
 */  
class Util_Activity_Comment extends Util_Activity_Common implements Util_Activity_Coin{  

   private $mTaskLimit = null;

   public function __construct($config){  
        $this->mConfig = $config;  
        //初始化写日志路径
        $path = Common::getConfig('siteConfig', 'logPath');
        $fileName = date('m-d').'_comment.log';
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
	   		//每日任务的评论赠送
	   		$this->dailyTaskComment();
	   	}
	   
	   	
   }
   
   /**
    * 每日任务的评论赠送
    */
   private function dailyTaskComment(){
   	
	   	if(!$this->mConfig['uuid'] || !$this->mConfig['type'] ||  !$this->mConfig['task_id']){
	   		return false;
	   	}
	   	$uuid = $this->mConfig['uuid'];
	   	//记录日志
	   	$logData= '进入每日任务评论类UUID='.$uuid.',game_id='.$this->mConfig['game_id'].',task_id='.$this->mConfig['task_id'];
	   	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	   	//取出对应每日任务的配置
	   	$taskConfParams['id'] = $this->mConfig['task_id'];
	   	$taskConfParams['status'] = 1;
	   	$taskConfig = Client_Service_DailyTaskConfig::getBy($taskConfParams);
	 
   		if($taskConfig){
   			$this->mTaskLimit = $taskConfig['daily_limit'];
   			$this->tryToDoneDailyTask($taskConfig);
   		}else{
   			$logData= '进入进入每日任务评论类，活动未开启uuid='.$uuid;
   			Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
   		}

   }
   
   /**
    * 是否做过任务
    */
    private function tryToDoneDailyTask($taskConfig){
    
    /* 	$logParams['task_id'] = $this->mConfig['task_id'] ;
    	$logParams['uuid']    = $this->mConfig['uuid'] ;
    	$logParams['create_time'] = array(array('>=', strtotime(date('Y-m-d 00:00:01')) ),array('<=', strtotime(date('Y-m-d 23:59:59')))) ;
    	$logRs = Client_Service_DailyTaskLog::getsBy($logParams);
    	//日志
    	$logData= '进入每日任务评论类，是否做过此任务logRs='.json_encode($logRs).'任务限制次数TaskLimit='.$this->mTaskLimit;
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData); */
    	
    	$cache = Cache_Factory::getCache();
    	$cacheHash = Util_CacheKey::getUserInfoKey($this->mConfig['uuid']) ;
    	
    	//每天完成任务次数与时间
    	$time = Common::getTime();
    	$cacheFinishNumKey      = 'finishDailyTaskNum'.$this->mConfig['task_id'];
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
  
        //评论过的游戏ID
        $cacheCommentGameIDKey  = 'commentGameID'.$this->mConfig['task_id'];
        $commentGameID = json_decode($cache->hGet($cacheHash, $cacheCommentGameIDKey),true);
        //写日志
        $logData= '进入每日任务评论类，评论过的游戏的commentGameID='.$commentGameID.'，正在评论gameID='.$this->mConfig['game_id'];
        Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
        //是否对此游戏做过评论
        if(in_array($this->mConfig['game_id'], $commentGameID)){
        	return false;
        } 

	    //send_object=1 赠送积分 send_object=2赠送A券
	    if($taskConfig['send_object'] == 1){
	    	$result = $this->sendDailyTaskPoint($taskConfig);
	    }elseif($taskConfig['send_object'] == 2){
	    	//A券奖励配置 奖励数量
	    	$result = $this->sendDailyTaskTicket($taskConfig);
	    }
	    //写日志
	    $logData= '进入每日任务评论类，赠送的类型send_object='.$taskConfig['send_object'].'返回的赠送结果result='.$result;
	    Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	    //更新缓存完成任务次数
	    if($result){
	    	$finishDailyTaskNum    = $cache->hIncrBy($cacheHash, $cacheFinishNumKey, 1);
	    	$finishDailyTaskTime   = $cache->hSet($cacheHash, $cacheFinishTimeKey, date('Y-m-d H:i:s', $time));
	    	if($commentGameID){
				array_push($commentGameID, $this->mConfig['game_id']);
				$cache->hSet($cacheHash,$cacheCommentGameIDKey, json_encode($commentGameID));		
			}else{
				$cache->hSet($cacheHash,$cacheCommentGameIDKey, json_encode(array($this->mConfig['game_id'])));
			}   
    		//更新任务总数，人数
	    	if($finishDailyTaskNum == $this->mTaskLimit){
	    		$this->updateStatisticReport($taskConfig);
	    	}else{
	    		$this->updateStatisticReportTotalQuantity($taskConfig);
	    	}
	    }
       
    }
   
    /**
     * 每日任务评论赠送A券
     */
    private function sendDailyTaskTicket($taskConfig){
    	
    	$awardConfig = json_decode($taskConfig['award_json'], true);
    	//取得福利任务的评论的配置奖励
    	$time = Common::getTime();
    	$desc = '每日任务-评论';
    	
    	//获取赠送的数组,用来保存A券信息
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
    		$this->insertDailyTaskLog($prizeArr);
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
                    'sub_send_type'=>$this->mConfig['task_id'],
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
     * 处理支付返回结果，更新赠送A券的状态
     */
    private function insertDailyTaskLog($prizeArr){
    	foreach ($prizeArr as $val){
    		$denomination +=$val['denomination'];
    	}
    	$taskData['send_object'] = 2;
    	$taskData['uuid'] = $this->mConfig['uuid'];
    	$taskData['task_id'] = $this->mConfig['task_id'];
    	$taskData['denomination'] = $denomination;
    	$taskData['create_time'] = Common::getTime();
    	$taskData['game_id']     = $this->mConfig['game_id'];
    	$taskData['status'] = 1;
    	$taskData['update_time'] = Common::getTime();
    	return Client_Service_DailyTaskLog::insert($taskData);
    }
    
   
  
    /**
     * 每日任务评论赠送积分
     */
    private function sendDailyTaskPoint($taskConfig){
    	$data['uuid'] = $this->mConfig['uuid'];
    	$data['gain_type'] = 2;
    	$data['gain_sub_type'] = 3;
    	$data['points'] = $taskConfig['points'];
    	$data['create_time'] = Common::getTime();
    	$data['update_time'] = Common::getTime();
    	$data['status'] = 1;
    	$rs = Point_Service_User::gainPoint($data);
    	//日志
    	$logData= '进入每日任务评论类，插入积分表的结果rs='.$rs;
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		if($rs){
			$taskData['send_object'] = 1;
			$taskData['uuid'] = $this->mConfig['uuid'];
			$taskData['task_id'] = $this->mConfig['task_id'];
			$taskData['denomination'] = $taskConfig['points'];
			$taskData['create_time'] = Common::getTime();
			$taskData['game_id']     = $this->mConfig['game_id'];
			$taskData['status'] = 1;
			$taskData['update_time'] = Common::getTime();
			return Client_Service_DailyTaskLog::insert($taskData);
		}
		return $rs;
    }
 
	public function __destruct(){     //应用析构函数自动释放连接资源
		 unset($this->mConfig);
		 unset($this->mPath);
		 unset($this->mFileName);
		 unset($this->mTaskLimit);
	}
}   