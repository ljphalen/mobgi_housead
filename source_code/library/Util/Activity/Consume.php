<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/** 
 * 具体策略类 消费
 */  
class Util_Activity_Consume extends Util_Activity_Common implements Util_Activity_Coin{  
  

   public function __construct($config){  
        $this->mConfig = $config;  
        //初始化写日志路径
        $path = Common::getConfig('siteConfig', 'logPath');
        $fileName = date('m-d').'_consume.log';
        $this->mPath = $path;
        $this->mFileName = $fileName;
        parent::__construct($path, $fileName);
   }  
   
   public function getCoin(){
	   $uuid = $this->mConfig['uuid'];
	   $type = $this->mConfig['type'];
	   $curr_consume = $this->mConfig['money'];
	   $game_api_key = $this->mConfig['api_key'];
	   
	   //type =1 福利任务  type=2 日常任务 type=3 活动中任务
	   if($type == 1){
	   	    //福利任务的消费返利
	   	    $this->wealTaskConsume($uuid, $game_api_key);
	   } else if($type == 3){
	   	    //活动的消费返利
	   	 	$this->activityConsume($uuid, $curr_consume);
	  
	   }
	}
	
	/**
	 * 福利任务中消费
	 */
	private function wealTaskConsume($uuid, $game_api_key){	
		if(!$this->mConfig['uuid'] || !$this->mConfig['type'] ||  !$this->mConfig['task_id']){
			return false;
		}
		//写日志
		$logData= '进入福利消费赠送类uuid='.$uuid ;
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	    
		//获得缓存
		$cache = Cache_Factory::getCache();
		$cacheKey = Util_CacheKey::getUserInfoKey($uuid) ;
		//任务的进度
		$wealTaskProcess = $cache->hGet($cacheKey,'wealTaskProcess');
		//写日志
		$logData= '进入福利消费赠送类, uuid='.$uuid.',任务进度 wealTaskProcess='.$wealTaskProcess ;
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		//if($wealTaskProcess >= 1){
		//取出对应的福利任务的配置
		$wealTaskParams['id'] = 5;
		$wealTaskParams['status'] = 1;
		$wealTaskConfig = Client_Service_WealTaskConfig::getBy($wealTaskParams);
		//判断活动是否开启
		if($wealTaskConfig){
			$this->tryToDoneWealTask($wealTaskConfig);
		}else{
			//写入日志
			$logData= '进入福利消费赠送类消费任务未开启';
			Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		}
		//}else{
			//写入日志
		//	$logData= '进入福利消费赠送类用户做过登录任务uuid='.$uuid;
		//	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		//}
	}
	
	/**
	 * 是否做过福利任务
	 */
	 private function tryToDoneWealTask($wealTaskConfig){

	 	$logData= '进入福利消费赠送类, uuid='.$this->mConfig['uuid'].',game_api_key='.$this->mConfig['api_key'].',subject_id='.$wealTaskConfig['subject_id'] ;
	 	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 	//是否在专题中
	 	if($wealTaskConfig['subject_id'] && $this->checkApiKeyIsSuject($this->mConfig['api_key'], $wealTaskConfig['subject_id'])){
	 		$logParams['uuid'] = $this->mConfig['uuid'];
	 		$logParams['send_type'] = 1;
	 		$logParams['sub_send_type'] = 5;
	 		$logRs = Client_Service_TicketTrade::getBy($logParams);
	 		//验证用户是否领取过
	 		if(!$logRs){
	 		
	 			//获取赠送的奖励
	 			$wealTaskPrize = json_decode($wealTaskConfig['award_json'],true);
	 		
	 			$desc = '福利任务';
	 			$time = Common::getTime();
	 			
	 			//获取赠送的数组,用来保存A券信息
	 			$prizeArr =  $this->getWealTaskAwardResult($wealTaskPrize, $desc);
	 			//写日志
	 			$logData= '进入福利消费赠送类，组装的数组prize_arr='.json_encode($prizeArr);
	 			Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 			
	 			//保存赠送A券记录
	 			$savaRs = $this->saveWealTaskSendTickets($prizeArr, $time);
	 			if(!$savaRs){
	 				//写日志
	 				$logData= '进入福利消费赠送类，保存赠送A券失败sava_rs'.$savaRs;
	 				Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 				return false;
	 			}
	 			 
	 			//组装发送到支付post数组
	 			$postPrizeArr = $this->postToPaymentData($prizeArr);
	 			//给支付发请求
	 			$paymentResult =  $this->postToPayment($postPrizeArr);
	 			//写入日志
	 			$logData= '进入福利消费赠送类，PSOT请求到支付组服务器返回结果paymentResult='.json_encode($paymentResult);
	 			Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 			//校验支付返回的结果
	 			$responseData =  $this->verifyPaymentResult($paymentResult);
	 			if(!$responseData){
	 				return false;
	 			}
	 			//更新A券的状态
	 			if($this->updateSendTickets($responseData)){
	 				//赠送消息入队列
	 				$this->saveWealTaskMsg($prizeArr, $wealTaskConfig['task_name'], $time);
	 				$this->saveNewTicket(null, $prizeArr, $this->mConfig['uuid']);
	 				//保存用户做的任务进度
	 				$cache = Cache_Factory::getCache();
	 				$cacheKey = Util_CacheKey::getUserInfoKey($this->mConfig['uuid']) ;
	 				//保存用户做的任务进度
	 				$arr = json_decode($cache->hget($cacheKey,'finishTaskid'),true);
	 				if(!in_array(5, $arr)){
						$wealTaskProcess = $cache->hget($cacheKey,'wealTaskProcess');
						if($wealTaskProcess){
							$cache->hIncrBy($cacheKey,'wealTaskProcess',1);
						}else{
							$cache->hSet($cacheKey,'wealTaskProcess',1);
						}
						if($arr){
							array_push($arr,5);
						}else{
							$arr = array(5);
						}	
						$cache->hSet($cacheKey,'finishTaskid',json_encode($arr));
					}
					$this->updateStatisticReport($wealTaskConfig);
	 			}	
	 		}else{
	 			//写入日志
	 			$logData= '进入福利消费赠送类用户已经做过这个任务uuid='.$this->mConfig['uuid'].'send_type=1,sub_send_type=5';
	 			Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 		}
	 	 }else{
	 		//写入日志
	 		$logData= '游戏不在专题里';
	 		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 	}  
	 		
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
                    'sub_send_type'=>$this->mConfig['task_id'],
                );
            }
	 	}
	 	$prizeArr =$this->getAwardResult($awardArr);
	 	return $prizeArr;
	 }
	 
	 /**
	  * 福利任务保存赠送的A券
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
	 	return $tmp;
	 }
	  
	 
	 /**
	  * 保存福利任务赠送消息到消息队列中
	  */
	 private function saveWealTaskMsg($msg_arr , $task_name, $time){
	 	if(!is_array($msg_arr) || empty($msg_arr)) return false;
	 	foreach ($msg_arr as $val){
	 		$denomination +=$val['denomination'];
	 	}
	 	$desc = '恭喜，您已完成福利任务-'.$task_name.'，获得'.$denomination.'A券奖励！请在有效期内使用！';
	 	$rs = $this->saveMsg($this->mConfig['uuid'], $denomination, $desc);
	 	return $rs;
	 }
	 
	 
	 
	 /** 
	  * 处理活动累计消费
	  */
	 public function processActivityTotalConsume($uuid){
	 	//查找当前有效的累计返利消费活动
	 	$params =  $search = array();
	 	$params['status'] = 1;
	 	$params['htype'] = 3;          //赠送场景 消费赠送
	 	$params['condition_type'] = 3; //赠送条件 3累计消费　2单次消费
	 	$params['rule_type'] = 3;      //赠送规则 3满xx送yy
	 	$params['hd_start_time'] = array('<',Common::getTime());
	 	$params['hd_end_time'] = array('>',Common::getTime());
	 	$items = Client_Service_TaskHd::getsBy($params,array('hd_start_time'=>'DESC','id'=>'DESC'));
	 	//组装奖励配置
	 	foreach($items as $key=>$value){
	 		$logData= '进入活动消费赠送类, uuid='.$uuid.',赠送的对象game_object='.$value['game_object'].',game_api_key='.$this->mConfig['api_key'].',subject_id= '.$value['subject_id'] ;
	 		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	
	 		$gameObject = $this->checkSendGameObject($value['game_object'], $value['subject_id'], $this->mConfig['api_key']);
	 		if(!$gameObject){
	 			continue;
	 		}
	 		$result= $this->processTotalConSumeCondition($uuid, $value);
	 		if($result){
	 			$prizeConfig[]= $result;
	 		}
	 		
	 		
	 	}	
	 	//写日志
	 	$logData= '进入活动消费赠送类,当前的活动items='.json_encode($items);
	 	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 	//没有在线的活动
	 	if(empty($items)) return false;
	 	
	 	//没有配置
	 	if(!count($prizeConfig)){
	 		return false;
	 	}
	 	//获取赠送的数组
	 	$prizeArr =  $this->getAwardResultForTotalConsume($prizeConfig);
	 	$logData= '进入活动登录类，组装的数组prize_arr='.json_encode($prizeArr);
	 	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 	$time= Common::getTime();
	 	//保存赠送A券记录
	 	$savaRs = $this->saveAcitivityTaskSendTickets($prizeArr, $time);
	 	if(!$savaRs){
	 		//写日志
	 		$logData= '进入活动消费任务类，保存赠送A券失败sava_rs'.$savaRs;
	 		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 		return false;
	 	}
	 	
	 	//组装发送到支付post数组
	 	$postPrizeArr = $this->postToPaymentData($prizeArr);
	 	//给支付发请求
	 	$paymentResult =  $this->postToPayment($postPrizeArr);
	 		
	 	//写入日志
	 	$logData= '进入活动消费任务类，PSOT请求到支付组服务器返回结果paymentResult='.json_encode($paymentResult);
	 	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 	//校验支付返回的结果
	 	$responseData =  $this->verifyPaymentResult($paymentResult);
	 	if(!$responseData){
	 		return false;
	 	}
	 	
	 	//更新A券的状态
	 	if($this->updateSendTickets($responseData)){
	 		//赠送消息入队列
	 		$this->saveTotalConsumeMsg($prizeConfig, $time);
	 	}
	 	return $prizeConfig;
	 }
	 
	 /**
	  * 处理活动单次消费
	  */
	 public function processActivitySingleConsume($uuid){ 	 
	 	$params =  $search = array();
	 	$params['status'] = 1;
	 	$params['htype'] = 3;          //赠送场景 消费赠送
	 	$params['condition_type'] = 2; //赠送条件 3累计消费　2单次消费
	 	$params['hd_start_time'] = array('<',Common::getTime());
	 	$params['hd_end_time'] = array('>',Common::getTime());
	 	$items = Client_Service_TaskHd::getsBy($params,array('hd_start_time'=>'DESC','id'=>'DESC'));
	 	//组装奖励配置
	 	foreach($items as $key=>$value){
	 		$logData= '进入活动消费赠送类, uuid='.$uuid.',赠送的对象game_object='.$value['game_object'].',game_api_key='.$this->mConfig['api_key'].',subject_id= '.$value['subject_id'] ;
	 		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);

	 		$gameObject = $this->checkSendGameObject($value['game_object'], $value['subject_id'], $this->mConfig['api_key']);
	 		if(!$gameObject){
	 			continue;
	 		}
	 		$result = $this->processSingleConSumeCondition($uuid, $value);
	 		if($result){
	 			$prizeConfig[]= $result;
	 		}
	 	}
	 	//写日志
	 	$logData= '进入活动消费赠送类11,当前的活动items='.json_encode($items).'test1='.count($items).'test2='.count($prizeConfig);
	 	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 	//没有在线的活动
	 	if(!count($items)){
	 		return false;
	 	} 
	 	 
	 	//没有配置
	 	if(!count($prizeConfig)){
	 		return false;
	 	}
	 	//获取赠送的数组
	 	$prizeArr =  $this->getAwardResultForSingleConsume($prizeConfig);
	 	$logData= '进入活动消费任务类，组装的数组prize_arr='.json_encode($prizeArr);
	 	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 	$time= Common::getTime();
	 	//保存赠送A券记录
	 	$savaRs = $this->saveAcitivityTaskSendTickets($prizeArr, $time);
	 	if(!$savaRs){
	 		//写日志
	 		$logData= '进入活动消费任务类，保存赠送A券失败sava_rs'.$savaRs;
	 		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 		return false;
	 	}
	 	 
	 	//组装发送到支付post数组
	 	$postPrizeArr  = $this->postToPaymentData($prizeArr);
	 	//给支付发请求
	 	$paymentResult =  $this->postToPayment($postPrizeArr);
	 	
	 	//写入日志
	 	$logData= '进入活动消费任务类，PSOT请求到支付组服务器返回结果paymentResult='.json_encode($paymentResult);
	 	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 	//校验支付返回的结果
	 	$responseData =  $this->verifyPaymentResult($paymentResult);
	 	if(!$responseData){
	 		return false;
	 	}
	 	//更新A券的状态
	 	if($this->updateSendTickets($responseData)){
	 		//赠送消息入队列
	 		$this->saveSingleConsumeMsg($prizeConfig, $time);
	 	}
	 	return $prizeConfig;

	 }
	 
	 
	/**
	 * 处理活动中的返利消费
	 * @param string $uuid
	 * @param int $send_type
	 */
	public function  activityConsume($uuid, $curr_consume){
		if(!$uuid) return false;
		
		$logData= '进入活动消费赠送类uuid='.$uuid.',gameApiKey='.$this->mConfig['api_key'].',消费金额curr_consume='.$curr_consume ;
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		
		//累计消费
		$totalConsumePrizeArr = $this->processActivityTotalConsume($uuid);
		//单次消费
		$singleConsumePrizeArr = $this->processActivitySingleConsume($uuid);
    	if ($totalConsumePrizeArr || $singleConsumePrizeArr) {	    
    	}		
	}
	
	/**
	 * 保存活动赠送消息到消息队列中
	 */
	private function saveTotalConsumeMsg($msg_arr , $time){
		if(!is_array($msg_arr) || empty($msg_arr)) return false;
		foreach ($msg_arr as $val){
			$denomination = 0;
			foreach ($val as $va){
				$denomination+=$va['denomination'];
				$taskName =$va['task_name'];
			}
			$desc = '恭喜，您已完成'.$taskName.'活动，获得'.$denomination.'A券奖励！请在有效期内使用！';
			$rs = $this->saveMsg($this->mConfig['uuid'], $denomination, $desc);
		}
		return $rs;
	}
	

	/**
	 * 保存单次消费赠送消息到消息队列中
	 */
	private function saveSingleConsumeMsg($msg_arr , $time){
		if(!is_array($msg_arr) || empty($msg_arr)) return false;
		foreach ($msg_arr as $val){
			$denomination+=$val['denomination'];
			$taskName =$val['task_name'];
			$desc = '恭喜，您已完成'.$taskName.'活动，获得'.$denomination.'A券奖励！请在有效期内使用！';
			$rs = $this->saveMsg($this->mConfig['uuid'], $denomination, $desc);
		}
		return $rs;
	}
	
	private function saveNewTicket($totalConsumePrizeArr, $singleConsumePrizeArr, $uuid) {
	    $uuid = trim($uuid);
	    $ticketValues = array();
	    if ($totalConsumePrizeArr) {
    	    foreach ($totalConsumePrizeArr as $prizeArr){
    	        foreach ($prizeArr as $prize){
    	            $ticketDenomination = $prize['denomination'];
    	            if ($ticketDenomination && $ticketDenomination > 0) {
    	               $ticketValues[] = $ticketDenomination;
    	            }
    	        }
    	    }
	    }
	    
	    if ($singleConsumePrizeArr) {
            foreach ($singleConsumePrizeArr as $singlePrize){
                $ticketDenomination = $singlePrize['denomination'];
                if ($ticketDenomination && $ticketDenomination > 0) {
                    $ticketValues[] = $ticketDenomination;
                }
            }
	    }
	    if (!$ticketValues) {
	    	return;
	    }
	    $cache = Cache_Factory::getCache();
	   if ($cache->get(Util_CacheKey::SDK_TICKET_CONSUME.$uuid)) {
	    	$oldTicket = json_decode($cache->hGet(Util_CacheKey::SDK_TICKET_CONSUME, $uuid), true);
	    	$ticketValues = array_merge($oldTicket, $ticketValues);
	    } 
	    $cache->set(Util_CacheKey::SDK_TICKET_CONSUME.$uuid, true, 2);
	    $cache->hSet(Util_CacheKey::SDK_TICKET_CONSUME, $uuid, json_encode($ticketValues), 300);
	}
	
	/**
	 * 保存活动任务赠送的A券
	 * @param unknown_type $send_arr
	 */
	private function saveAcitivityTaskSendTickets($sendArr, $time ){
		//保存赠送A券记录
		foreach ($sendArr as $key=>$val){
			$tmp[$key]['uuid'] = $val['uuid'];
			$tmp[$key]['aid'] = $val['aid'];
			$tmp[$key]['denomination'] = $val['denomination'];
			$tmp[$key]['status'] = 0;
			$tmp[$key]['send_type'] = $this->mConfig['type'];
			$tmp[$key]['sub_send_type'] = $val['sub_send_type'];
			$tmp[$key]['consume_time'] = $time;
			$tmp[$key]['start_time'] = strtotime($val['startTime']);
			$tmp[$key]['end_time'] = strtotime($val['endTime']);
			$tmp[$key]['description'] = $val['desc'];
			$tmp[$key]['densection'] = $val['densection'];
		}
		$rs = $this->saveSendTickets($tmp);
		return $rs;
	}
	
	/**
	 * 组装活动的累计消费奖励数组
	 */
	private function getAwardResultForTotalConsume($taskPrize, $desc){
		if(!is_array($taskPrize)){
			return false;
		}
	  //组装多个活动A券奖励规则
		foreach ($taskPrize as $val){
			foreach ($val as $va){
				$awardArr[] = array(  'denomination'=>$va['denomination'],
						'section_start'=>$va['section_start'],
						'section_end'=>$va['section_end'],
						'desc'=>$va['desc'],
						'uuid'=>$va['uuid'],
						'sub_send_type'=> $va['sub_send_type'],
						'task_name'=>$va['task_name'],
						'densection'=>$va['densection'],
				);
			}
		}
		$prizeArr =$this->getAwardResult($awardArr);
		return $prizeArr;
	}
	
	/**
	 * 组装活动的累计消费奖励数组
	 */
	private function getAwardResultForSingleConsume($taskPrize, $desc){
		if(!is_array($taskPrize)){
			return false;
		}
		//组装多个活动A券奖励规则
		foreach ($taskPrize as $va){
				$awardArr[] = array(  'denomination'=>$va['denomination'],
						'section_start'=>$va['section_start'],
						'section_end'=>$va['section_end'],
						'desc'=>$va['desc'],
						'uuid'=>$va['uuid'],
						'sub_send_type'=> $va['sub_send_type'],
						'task_name'=>$va['task_name'],
						'densection'=>$va['densection'],
				);	
		}
		$prizeArr =$this->getAwardResult($awardArr);
		return $prizeArr;
	}
	
	
	/**
	 * 检查用户累计消费的赠送条件
	 * @param unknown_type $uuid
	 * @param unknown_type $hd
	 */
	private function processSingleConSumeCondition($uuid, $hd){		
	
		$awardJson = json_decode($hd['rule_content'], true);
		//记录日志
		$logData= '进入活动登录类,活动的配置hd='.json_encode($hd).',活动的ID='.$hd['id'].',uuid='.$uuid.'消费金额money='.$this->mConfig['money'].'赠送的金额范围'.$awardJson['denomination'];
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		
		if( $this->mConfig['money'] >= $awardJson['denomination']){
			$sendMoney = round(($this->mConfig['money']*$awardJson['restoration'])/100);
			if($sendMoney < 1){
				return false;
			}
			$awardArr= array(
					'denomination'=>$sendMoney,
					'section_start'=>1,
					'section_end'=> $awardJson['deadline'],
					'desc'=>'活动赠送',
					'uuid'=>$uuid,
					'sub_send_type' => $hd['id'],
					'task_name'=>$hd['title']
						
			);
			return $awardArr;
			
		}
		return false;
	
		
	}

	/**
	 * 检查用户累计消费的赠送条件
	 * @param unknown_type $file_name
	 */
	private function  processTotalConSumeCondition($uuid, $hd){
			//查找当前活动时间内A币消费的总和
		    $search['event'] = 1;
			$search['trade_time'][0] = array('>=', $hd['hd_start_time']);
			$search['trade_time'][1] = array('<=', $hd['hd_end_time']);
			$search['uuid'] = $uuid;
			$total = Client_Service_MoneyTrade::getCount($search);
			//写日志
			$logData= '进入活动消费赠送类uuid='.$uuid.',hd='.json_encode($hd).',用户消费总额total='.$total ;
			Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
			
			//用户消费总额
			if($total <= 0){
				return false;
			}	
			unset($search);
			//查找当前活动时间内A券发放记录
			$search['uuid'] = $uuid;
			$search['send_type'] = 3;
			$search['consume_time'][0] = array('>=', $hd['hd_start_time']);
			$search['consume_time'][1] = array('<=', $hd['hd_end_time']);
			$search['sub_send_type'] = $hd['id'];
			//是否赠送过
			$sent_ticket = Client_Service_TicketTrade::getsBy($search, array('consume_time'=>'DESC'));
			$max_section = 0;
			$sent_section = array();
			foreach ($sent_ticket as $val){
				$sent_section = json_decode($val['densection'], true);
				if($max_section < $sent_section['section_end']){
					$max_section = $sent_section['section_end'];
				} 
			}
			//写日志
			$logData= '进入活动消费赠送类uuid='.$uuid.',是否赠送过ticket = '.json_encode($sent_ticket).',活动的ID='.$hd['id'].'，上次赠送的金额'.$max_section;
			Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
			//返回活动的赠送规则配置
			return $this->activityComsumeSendRule($uuid, $hd, $total, $max_section);	
	}
	
	/**
	 * 活动消费的赠送规则
	 */
	
	private function  activityComsumeSendRule($uuid, $hd, $total, $last_section){
		//获取该活动赠送的奖励规则
		$temp = $section = array();
		$rule_prize = json_decode($hd['rule_content'],true);
		if(!$rule_prize)  return false;
		
		//写日志
		$logData= '进入活动消费赠送类uuid='.$uuid.',赠送规则rule_prize='.json_encode($rule_prize).',活动的ID='.$hd['id'].',total='.$total.',last_section='.$last_section;
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		$findFirstSection = 0;
		//单笔消费在该活动的某个区间
		foreach($rule_prize as $key=>$value){
            //有赠送记录
			if($last_section){
				//取出上一个赠送区间
				if ($last_section >= $value['section_end']) {
					continue;
				}
				if($last_section >= $value['section_start']){
					$findFirstSection = 1;
				}
			//没有赠送记录
			} else {
				$findFirstSection = 1;
			}
            //每个区间赠送组装
			if($findFirstSection == 1 && $total >= $value['section_start']) {
				$section[] = $value;
			}
		}
		//赠送
		if($section){
			return  $this->getConsumeTicketPrizeConfig($uuid, $hd, $section);
		}else{
			return false;
		}
	}
	
	/**
	 * 组装A券赠送数组
	 * @param unknown_type $uuid
	 * @param unknown_type $hd
	 * @param unknown_type $denarr
	 * @return multitype:string multitype:string unknown
	 */
	private function  getConsumeTicketPrizeConfig($uuid, $hd, $section){
		if(!is_array($section)){
			return false;
		}
		foreach($section as $key=>$val){
			$temp = array(
					    'section_start'=>$val['section_start'],
					    'section_end'=>$val['section_end'],
					   );
			foreach($val['denarr'] as $key=>$va){
				$prizeConfig[] = array('denomination'=>$va['Step'],
						              'section_start'=>$va['effect_start_time'],
						              'section_end'=>$va['effect_end_time'],
									  'desc'=>'活动赠送',
									  'uuid'=>$uuid,
									  'sub_send_type'=> $hd['id'],
									  'task_name'=>$hd['title'],
						              'densection'=>json_encode($temp,true),
				);
			}
		}
	    return $prizeConfig;
	}
	
	


	public function __destruct(){     //应用析构函数自动释放连接资源
		unset($this->mConfig);
		unset($this->mPath);
		unset($this->mFileName);
	      
	}
}   
