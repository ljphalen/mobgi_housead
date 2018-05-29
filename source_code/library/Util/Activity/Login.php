<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/** 
 * 具体策略类 登录 
 */  


class Util_Activity_Login extends Util_Activity_Common implements Util_Activity_Coin{  
   /**
    * 
    * @param unknown_type $config
    */
   public function __construct($config = array()){  
        $this->mConfig = $config;  
        //初始化写日志路径
        $path = Common::getConfig('siteConfig', 'logPath');
        $fileName = date('m-d').'_login.log';
        $this->mPath = $path;
        $this->mFileName = $fileName;
        parent::__construct($path, $fileName);
        
   }  

   /**
    * 
    * @see Util_Activity_Coin::getCoin()
    */
    public function getCoin(){
    
    	$uuid = $this->mConfig['uuid'];
    	$type = $this->mConfig['type'];
    	//type =1 福利任务的登录 type=3 活动中的登录
    	
    	if($type == 1){
    		//福利任务中的客户端登录
    		$this->wealTaskLogin($uuid);
    	}elseif($type == 3){
    		//活动中的客户端登录
    		$this->activityLogin($uuid);
    	}
    }
    
    /**
     * 福利任务登录与连续登录
     * @param unknown_type $uuid
     */
    private  function wealTaskLogin($uuid){ 
    	if(!$this->mConfig['uuid'] || !$this->mConfig['type'] ||  !$this->mConfig['task_id']){
    		return false;
    	}	
    	//记录日志
    	$logData= '进入福利任务登录类UUID='.$uuid;
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	if(!$uuid) return false;

    	//处理福利任务中的首次登录
    	$wealTaskParams['id'] = 1;
    	$wealTaskParams['status'] = 1;
    	$wealTaskConfig = Client_Service_WealTaskConfig::getBy($wealTaskParams);
    	if($wealTaskConfig){
    		$this->tryToDoneWealTaskLogin($wealTaskConfig);
    	}else{
    		//写入日志
    		$logData= '进入福利任务登录类，登录任务的未开启';
    		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	}

    }
   
    /**
     * 是否做福利任务
     * @param unknown_type $wealTaskConfig
     * @return boolean
     */
    private function tryToDoneWealTaskLogin($wealTaskConfig){
    	$uuid = $this->mConfig['uuid'];
    	$time = Common::getTime();
    	$logParams['uuid'] = $uuid;
    	$logParams['send_type'] = 1;
    	$logParams['sub_send_type'] = 1;
    	$logRs = Client_Service_TicketTrade::getBy($logParams);
    	//没有做过首次登录福利任务
    	if(!$logRs){
    		//获取赠送的奖励
    		$wealTaskPrize = json_decode($wealTaskConfig['award_json'], true);
    		$time = Common::getTime();
    		$desc ='福利任务';
    		
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
				if(!in_array(1, $arr)){
					$wealTaskProcess = $cache->hget($cacheKey,'wealTaskProcess');
					if($wealTaskProcess){
						$cache->hIncrBy($cacheKey,'wealTaskProcess',1);
					}else{
						$cache->hSet($cacheKey,'wealTaskProcess',1);
					}
					if($arr){
						array_push($arr,1);
					}else{
						$arr = array(1);
					}	
					$cache->hSet($cacheKey,'finishTaskid',json_encode($arr));
			    }
			    $this->updateStatisticReport($wealTaskConfig);
			    return true;
    		}
    	}else{
    		//写入日志
    		$logData= '进入福利任务登录类，用户已经做过这个任务uuid='.$uuid.'send_type=1,sub_send_type=1';
    		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	}
    	 
    }
    
   /**
    * 活动中的登陆客户端
    * @param unknown_type $uuid
    */
   private  function activityLogin($uuid){
	   	if(!$uuid) return false;
	   	//记录日志
	   	$logData= '进入活动登录类UUID='.$uuid.',客户端版本version='.$this->mConfig['version'];
	   	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	   	//查找当前有效的登陆客户端
	   	$params =  $search = $msg = array();
	   	$params['status'] = 1;
	   	$params['htype'] = 1;
	   	$params['condition_type'] = 1; //赠送条件类型 首次登录
	   	$params['hd_start_time'] = array('<=',Common::getTime());
	   	$params['hd_end_time'] = array('>=',Common::getTime());
	   	$items = Client_Service_TaskHd::getsBy($params,array('hd_start_time'=>'DESC','id'=>'DESC'));
	   	//没有在线的活动
	   	if(!count($items)) return false;
	   	//记录日志
	   	$logData= '进入活动登录类,开启的活动items='.json_encode($items);
	   	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	   	foreach($items as $key=>$value){
	   		//获取每个活动的奖励
	   		$rs = $this->checkCondition($uuid, $value);
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
	   		return true;
	   	}else{
	   		return false;
	   	}
 
   }
   
   
   /**
    * 
    * @param unknown_type $uuid
    * @param unknown_type $hd
    * @return boolean|multitype:number string unknown mixed
    */
   private function  checkCondition($uuid, $hd){
	   	//记录日志
	   	$logData= '进入活动登录类,活动的配置hd='.json_encode($hd).',活动的ID='.$hd['id'].',uuid='.$uuid;
	   	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
   	    $versionJson = json_decode($hd['game_version'],true);
   	    //客户端版本验证
   	    $cleintVersion = $this->checkVersion($versionJson, $this->mConfig['version']);
        if(!$cleintVersion){
        	return false;
        }
   	    $params = array();
   		$params['uuid'] = $uuid;
   		$params['send_type'] = 3;
   		$params['sub_send_type'] = $hd['id'];
   		//查看该账号A券交易（在活动中的登陆客户端赠送记录）是否存在,不存在就赠送
   		$ret = Client_Service_TicketTrade::getBy($params);

   		//记录日志
   		$logData= '进入活动登录类,此活动是否赠送过ret='.$ret;
   		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
   		if(!$ret) {
   			$currentTime = date('Y-m-d',Common::getTime());
   			$startTime = strtotime($currentTime.' 00:00:00');
   			$endTime = strtotime($currentTime.' 23:59:59');
   			$cache = Cache_Factory::getCache();
   			$cacheKey = Util_CacheKey::getUserInfoKey($uuid) ; //获取用户的uuid
   			$lastLoginTime = strtotime($cache->hGet($cacheKey,'lastLoginTime' )); //'1417919651';     //最后登录时间
   			
   			$loginLogParams['create_time'][0] = array('>=', $startTime);
   			$loginLogParams['create_time'][1] = array('<=', $endTime);
   			$loginLogParams['uuid'] = $uuid;
   			$everyLoginTotal = count(Account_Service_User::getsUserLoginLog($loginLogParams));
   			//判断该用户今天是否首次登陆
   			if($lastLoginTime >= $startTime && $lastLoginTime <= $endTime && $everyLoginTotal == 1 ){
   				$awardJson = json_decode($hd['rule_content'], true);
                if($awardJson['denomination']){
                    $awardArr= array(
                        'denomination'=>$awardJson['denomination'],
                        'section_start'=>1,
                        'section_end'=> $awardJson['deadline'],
                        'desc'=>'活动赠送',
                        'uuid'=>$uuid,
                        'send_type'=>$this->mConfig['type'],
                        'sub_send_type' => $hd['id'],
                        'task_name'=>$hd['title']

                    );
                    return $awardArr;
   				}
   			}
   		}
   		return false;
   }
   

   
   /**
    * 组装活动的奖励数组
    */
   private function getActivityTaskAwardResult($taskPrize, $desc){
	   	if(!is_array($taskPrize)){
	   		return false;
	   	}
	   	//取得的配置奖励
	   	$time = Common::gettime();
	   	foreach ($taskPrize as $val){
	   		if($val['denomination']){
	   			$awardArr[] = array(
	   					'denomination'=>$val['denomination'],
	   					'section_start'=>$val['section_start'],
	   					'section_end'=> $val['section_end'],
	   					'desc'=>$val['desc'],
	   					'uuid'=>$val['uuid'],
	   					'sub_send_type' => $val['sub_send_type'],
	   					'task_name'=>$val['task_name']
	   			);
	   	
	   		}
	   	}
	   	$prizeArr =$this->getAwardResult($awardArr);
	   	return $prizeArr;
   }
   
   /**
    * 组装福利任务的奖励数组
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
    * 保存福利任务赠送的A券
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
   
    
   public function __destruct(){     //应用析构函数自动释放连接资源
	   	unset($this->mConfig);
	   	unset($this->mPath);
	   	unset($this->mFileName);
  
   }
   
}   
  
