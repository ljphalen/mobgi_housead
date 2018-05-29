<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/** 
 * 具体策略类 联运游戏登录 
 */  
class Util_Activity_UnionLogin extends Util_Activity_Common implements Util_Activity_Coin{  
	private $mGameName =null;
	//网络
	private $condition_type = array(
			1 => '登录1次',
			2 => '每日登录',
	);
	   
	
   /*
    * 构造函数
    */
	public function __construct($config){
		$this->mConfig = $config;
		//初始化写日志路径
		$path = Common::getConfig('siteConfig', 'logPath');
		$fileName = date('m-d').'_unionlogin.log';
		$this->mPath = $path;
		$this->mFileName = $fileName;
		parent::__construct($path, $fileName);
	}
	
	/***
	 * 实施方法
	 */
	public function getCoin(){
	  	$uuid = $this->mConfig['uuid'];
	  	$type = $this->mConfig['type'];
	  	$loginTime = $this->mConfig['loginTime'];
	  	$game_api_key = $this->mConfig['apiKey'];
	  	
	  	$logData= '进入活动联运游戏登录，用户的uuid='.$uuid.',type'.$type.',apiKey'.$this->mConfig['apiKey'];
	  	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	  	
	  	//$type =1 为福利任务的中联运网游登录 3活动中联运网游登录	
	   	if($type == 1){
	   		return $this->wealTaskLogin($uuid);
	   	//活动的联运游戏登录
	   	} else if ($type == 3){
	   		return $this->activityTaskLogin($uuid, $type, $loginTime);
	   	}
	}
	
	/**
	 * 处理福利任务中联运游戏登录
	 * @param string $uuid
	 * @param int $send_type
	 */
	private function  wealTaskLogin($uuid){
		if(!$this->mConfig['uuid'] || !$this->mConfig['type'] ||  !$this->mConfig['task_id']){
			return false;
		}
		$user = Account_Service_User::getUser(array('uuid'=>$uuid));
		//用户不存在
		if (!$user) {
			return false;
		}
		//缓存的用户的信息
		$cache = Cache_Factory::getCache();
		$cacheKey = Util_CacheKey::getUserInfoKey($uuid) ;
		$clientVersion = $cache->hGet($cacheKey,'clientVersion');
		//记录日志
		$logData= '进入福利任务联运游戏登录类UUID='.$uuid.',game_api_key='.$this->mConfig['apiKey'].' ,clientVertion='.$clientVersion;
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		if(strnatcmp($clientVersion, '1.5.5') >= 0){
			//处理福利任务的你网游任务是否开启
			$wealTaskParams['id'] = 4;
			$wealTaskParams['status'] = 1;
			$wealTaskConfig = Client_Service_WealTaskConfig::getBy($wealTaskParams);
			if($wealTaskConfig){
				  $this->tryToDoneWealTask($wealTaskConfig);
			}else{
				return false;
				//写入日志
				$logData= '进入福利任务联运游戏登录类，游戏登录任务未开启';
				Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
			}
		}else{
			return false;
			//写入日志
			$logData= '进入福利任务联运游戏登录类，用户做过登录任务uuid='.$uuid;
			Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		}
	}
	
	/**
	 * 是否完成福利任务
	 * @param unknown_type $wealTaskConfig
	 * @return boolean
	 */
	private function tryToDoneWealTask($wealTaskConfig){

		//写日志
		$logData= '进入福利任务联运游戏登录类，game_api_key= '.$this->mConfig['apiKey'].',subject_id='.$wealTaskConfig['subject_id'];
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		//验证是否在专题中
		if($wealTaskConfig['subject_id'] && $this->checkApiKeyIsSuject($this->mConfig['apiKey'], $wealTaskConfig['subject_id'])){
			$time = Common::getTime();
			$logParams['send_type'] = 1;
			$logParams['sub_send_type'] = 4;
			$logParams['uuid'] = $this->mConfig['uuid'];
			$logRs = Client_Service_TicketTrade::getBy($logParams);
			//没有做过福利任务的体验网游
			if(!$logRs){
				//获取体验网游赠送的奖励
				$wealTaskPrize = json_decode($wealTaskConfig['award_json'],true);
				$desc = '福利任务';
				$time = Common::getTime();
				
				//获取赠送的数组,用来保存A券信息
				$prizeArr =  $this->getWealTaskAwardResult($wealTaskPrize, $desc);
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
					$this->saveWealTaskMsg($prizeArr, $wealTaskConfig['task_name'], $time);
					//保存用户做的任务进度
					$cache = Cache_Factory::getCache();
					$cacheKey = Util_CacheKey::getUserInfoKey($this->mConfig['uuid']) ;
					//保存用户做的任务进度
					$arr = json_decode($cache->hget($cacheKey,'finishTaskid'),true);
					if(!in_array(4, $arr)){
						$wealTaskProcess = $cache->hget($cacheKey,'wealTaskProcess');
						if($wealTaskProcess){
							$cache->hIncrBy($cacheKey,'wealTaskProcess',1);
						}else{
							$cache->hSet($cacheKey,'wealTaskProcess',1);
						}
						if($arr){
							array_push($arr,4);
						}else{
							$arr = array(4);
						}	
						$cache->hSet($cacheKey,'finishTaskid',json_encode($arr));
				    }
				    $this->updateStatisticReport($wealTaskConfig);
					return true;
				}else{
					return false;
				}
			}else{
				return false;
				//写入日志
				$logData= '进入福利任务联运游戏登录类，用户做过联运登录任务uuid='.$this->mConfig['uuid'];
				Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
			}
		 }else{
			//写入日志
			$logData= '进入福利任务联运游戏登录类，此游戏ID不再专题中game_api_key'.$this->mConfig['api_key'].',subject_id = '.$wealTaskConfig['subject_id'];
			Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		} 
			
	}
	
	/**
	 * 组装福利任务的数组
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
	 
	/**
	 * 保存赠送消息到消息队列中
	 */
	private function saveWealTaskMsg($msg_arr , $task_name, $time){
		if(!is_array($msg_arr) || empty($msg_arr)) return false;
		foreach ($msg_arr as $val){
			$denomination +=$val['denomination'];
		}
		$desc = '恭喜，您已完成福利任务-'.$task_name.'，获得'.$denomination.'A券奖励！请在有效期内使用!';
		$rs = $this->saveMsg($this->mConfig['uuid'], $denomination, $desc);
		return $rs;
	}
	
	/**
	 * 处理活动中的联运中游戏登录
	 * @param string $uuid
	 * @param int $send_type
	 */
	private function  activityTaskLogin($uuid, $type, $loginTime){
		if(!$uuid) return false;
		//写入日志
		$logData= '进入活动联运游戏登录类，用户的uuid='.$uuid.',========登录时间logTime='.date('Y-m-d H:i:s',$loginTime).',apiKey'.$this->mConfig['apiKey'];
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		//查找当前有效的联运登陆活动
		$params = $this->checkActivity();
		$items = Client_Service_TaskHd::getsBy($params,array('hd_start_time'=>'DESC','id'=>'DESC'));
		//记录日志
		$logData= '进入活动登录类,开启的活动items='.json_encode($items).',apiKey'.$this->mConfig['apiKey'];
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		//没有在线的活动
		if(!$items) return false;
		//取得游戏名称
		$this->mGameName = $this->getGameNameByApiKey($this->mConfig['apiKey']);
		foreach($items as $key=>$value){
			 $rs = $this->sendCondition($uuid, $value, $loginTime);
			 if($rs){
			 	$prizeConfig[] = $rs;
			 }
		}
		
		//没有配置
		if(!count($prizeConfig)){
			return false;
		}
		//获取赠送的数组
		$prizeArr =  $this->getActivityTaskAwardResult($prizeConfig);
		$logData= '进入活动登录类，组装的数组prize_arr='.json_encode($prizeArr);
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		
		$time= Common::getTime();
		//保存赠送A券记录
		$savaRs = $this->saveAcitivityTaskSendTickets($prizeArr, $time);
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
			$this->saveActivityTaskMsg($prizeArr, $time);
		}
		
	}
	
	/**
	 * 保存活动赠送消息到消息队列中
	 */
	private function saveActivityTaskMsg($msg_arr , $time){
		if(!is_array($msg_arr) || empty($msg_arr)) return false;
		foreach ($msg_arr as $val){
			$desc = '恭喜，您已完成'.$val['task_name'].'活动，获得'.$val['denomination'].'A券奖励！请在有效期内使用！';
			$rs = $this->saveMsg($val['uuid'], $val['denomination'], $desc);
		}
		return $rs;
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
		}
		$rs = $this->saveSendTickets($tmp);
		return $rs;
	}
	
	/**
	 * 组装活动的奖励数组
	 */
	private function getActivityTaskAwardResult($taskPrize){
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
						'desc'=>$val['desc'],
						'uuid'=>$val['uuid'],
                        'send_type'=>$this->mConfig['type'],
						'sub_send_type' => $val['sub_send_type'],
						'task_name'=>$val['task_name']
				);
				 
			}
		}
		$prizeArr =$this->getAwardResult($awardArr);
		return $prizeArr;
	}
	
	/**
	 * 检查联运登陆赠送条件
	 * @param string $uuid
	 * @param array $data
	 * @param int $loginTime
	 * @return array
	 */
	private function  sendCondition($uuid, $data, $loginTime){
		//全部游戏
		if($data['game_object'] == 1){
			$game_rs = Resource_Service_Games::getBy(array('api_key'=>$this->mConfig['apiKey']));
			if($game_rs){
				if($data['condition_type'] == 1 ){       //赠送条件类型为首次登陆
					return $this->checkCondition($uuid, $data, $loginTime, 1);
				} else if($data['condition_type'] == 2){ //赠送条件类型为每日登陆
					return $this->checkCondition($uuid, $data, $loginTime, 2);
				}
			}
		//专题游戏
		}elseif($data['game_object'] == 2){
			//是否在专题里面
			if($this->mConfig['apiKey'] && $this->checkApiKeyIsSuject($this->mConfig['apiKey'], $data['subject_id'] )){
				if($data['condition_type'] == 1 ){       //赠送条件类型为首次登陆
					return $this->checkCondition($uuid, $data, $loginTime, 1);
				} else if($data['condition_type'] == 2){ //赠送条件类型为每日登陆
					return $this->checkCondition($uuid, $data, $loginTime, 2);
				}
			}
		}
		return false;
	}
	

	/**
	 * 检查当前在线的登陆联运游戏的活动的条件
	 * @return array
	 */
	private function  checkActivity(){
		$params =  array();
		$params['status'] = 1;
		$params['htype'] = 2;
		$params['hd_start_time'] = array('<',Common::getTime());
		$params['hd_end_time'] = array('>',Common::getTime());
		return $params;
	}
	
	/**
	 * 判断 符合登陆联运游戏
	 * @param string $uuid
	 * @param array $hd
	 * @param int $loginTime
	 * @param boolean $flag
	 * @return array
	 */
	private function  checkCondition($uuid, $hd, $loginTime, $flag = 0){
		//写入日志
		$logData= '进入活动联运游戏登录类，用户的uuid='.$uuid.', hd='.json_decode($hd).',logTime='.$loginTime;
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		
        $search = array();
        $search['send_type'] = 3 ;
        $search['sub_send_type'] = $hd['id'];
		$search['uuid'] = $uuid;         //满足首次登陆赠送的条件
		if($flag == 2) {                 //满足每日登陆赠送的条件
			$curr_time = date('Y-m-d',Common::getTime());
			$start_time = strtotime($curr_time.' 00:00:00');
			$end_time = strtotime($curr_time.' 23:59:59');
			if($loginTime >= $start_time && $loginTime <= $end_time){     //满足：当前活动有效且今天内登陆联运游戏
				$search['consume_time'][0] = array('>=', $start_time);
				$search['consume_time'][1] = array('<=', $end_time);
			} else {
				return false;
			}
		}
		
		//满足首次登陆[$flag = 1]或者每日登陆赠送的条件[$flag = 2] 
		//查看该账号A券交易（在当天的登陆游戏赠送记录）是否存在,不存在就赠送
		$ret = Client_Service_TicketTrade::getBy($search);
		//写入日志
		$logData= '进入活动联运游戏登录类，用户是否做过此活动ret='.$ret;
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		if(!$ret) {
			$awardJson = json_decode($hd['rule_content'], true);
            if($awardJson['denomination']){
                $awardArr= array(
                    'denomination'=>$awardJson['denomination'],
                    'section_start'=>1,
                    'section_end'=> $awardJson['deadline'],
                    'desc'=> $this->mGameName.'赠送',
                    'uuid'=>$uuid,
                    'send_type'=>$this->mConfig['type'],
                    'sub_send_type' => $hd['id'],
                    'task_name'=>$hd['title']
                );
                return $awardArr;
			}
		}
		return false;
	}
	
	/**
	 * 获取游戏名称
	 */
	private function getGameNameByApiKey($apiKey){
		$game_params['api_key'] = $apiKey;
		$gameInfo = Resource_Service_Games::getBy($game_params);
		$gameName = $gameInfo['name'];
		return $gameName;
	}


	
	public function __destruct(){     //应用析构函数自动释放连接资源
		unset($this->mConfig);
		unset($this->mPath);
		unset($this->mFileName);	
		unset($this->mGameName);
		   
	}
	
	
}   
  
