<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/** 
 * 具体策略类 充值
 */  
class Util_Activity_Payment extends Util_Activity_Common implements Util_Activity_Coin{  

    
   

   public function __construct($config){  
        $this->mConfig = $config;  
        //初始化写日志路径
        $path = Common::getConfig('siteConfig', 'logPath');
        $fileName = date('m-d').'_payment.log';
        $this->mPath = $path;
        $this->mFileName = $fileName;
        parent::__construct($path, $fileName);
   }  
   
   public function getCoin(){
	   $uuid = $this->mConfig['uuid'];
	   $type = $this->mConfig['type'];
	   $currentMoney = $this->mConfig['money'];
	   $gameApiKey = $this->mConfig['api_key'];
	   
	   if(!$uuid){
	   		return false;
	   }
	   if(!$currentMoney){
	   		return false;
	   }
	   //type =1 福利任务  type=2 日常任务 type=3 活动中任务
	    if($type == Util_Activity_Context::TASK_TYPE_ACTIVITY_TASK){
	   	    //活动的充值返利
	   	 	$this->activityPaymentSend($uuid, $currentMoney);
	  
	   }
	}
		 
	 /**
	  * 处理活动首次赠送
	  */
	 public function firstPaymentSend($uuid){ 	 	
	 	$currentTime = Common::getTime();
	 	$queryParams  = array();
	 	$queryParams['status'] = 1;
	 	$queryParams['htype'] = 4;          //赠送场景 充值赠送
	 	$queryParams['condition_type'] = 1; //赠送条件 首次充值	
	 	$queryParams['hd_start_time'] = array('<=', $currentTime);
	 	$queryParams['hd_end_time'] = array('>=', $currentTime);
	 	$orderBy = array('hd_start_time'=>'DESC', 'id'=>'DESC');
	 	
	 	$activityList = Client_Service_TaskHd::getsBy($queryParams, $orderBy);
	 	
	 	//没有在线的活动
	 	if(!count($activityList)){
	 		return false;
	 	}
	 	
	 	//写日志
	 	$logData= '进入活动充值赠送类,当前的活动activityResult='.json_encode($activityList);
	 	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 	
	 	$prizeConfig =   $this->firstPaymentPrizeConfig($activityList);
	    
	 	//写日志
	 	$logData= '进入活动充值赠送类,当前的活动prizeConfig='.json_encode($prizeConfig);
	 	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 	//没有配置
	 	if(!count($prizeConfig)){
	 		return false;
	 	}
	 	//获取赠送的数组
	 	$prizeArr =  $this->getAwardResultForFirstPayment($prizeConfig);
	 	$logData= '进入活动充值任务类，组装的数组prize_arr='.json_encode($prizeArr);
	 	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 	$time= Common::getTime();
	 	//保存赠送A券记录
	 	$savaRs = $this->saveAcitivityTaskSendTickets($prizeArr, $time);
	 	if(!$savaRs){
	 		//写日志
	 		$logData= '进入活动充值任务类，保存赠送A券失败sava_rs'.$savaRs;
	 		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 		return false;
	 	}
	 	 
	 	//组装发送到支付post数组
	 	$postPrizeArr  = $this->postToPaymentData($prizeArr);
	 	//给支付发请求
	 	$paymentResult =  $this->postToPayment($postPrizeArr);
	 	
	 	//写入日志
	 	$logData= '进入活动充值任务类，PSOT请求到支付组服务器返回结果paymentResult='.json_encode($paymentResult);
	 	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 	//校验支付返回的结果
	 	$responseData =  $this->verifyPaymentResult($paymentResult);
	 	if(!$responseData){
	 		return false;
	 	}
	 	//更新A券的状态
	 	if($this->updateSendTickets($responseData)){
	 		//赠送消息入队列
	 		$this->saveActivitySendMsg($prizeConfig, $time);
	 	}
	 	return true;

	 }
	 
	 
	 /**
	  * 组装活动的奖励数组
	  */
	 private function getAwardResultForFirstPayment($taskPrize, $desc){
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
                 'send_type'=>$this->mConfig['type'],
                 'sub_send_type'=> $va['sub_send_type'],
                 'task_name'=>$va['task_name'],
                 'densection'=>$va['densection'],
             );
         }
	 	$prizeArr =$this->getAwardResult($awardArr);
	 	return $prizeArr;
	 }
	 
	 public function firstPaymentPrizeConfig($activityList){
	 
	 	$prizeConfig  = array();
	 	//组装奖励配置
	 	foreach($activityList as $key=>$value){
	 		$logData= '进入活动充值赠送类, uuid='.$this->mConfig['uuid'].',赠送的对象game_object='.$value['game_object'].',game_api_key='.$this->mConfig['api_key'].',subject_id= '.$value['subject_id'] ;
	 		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
	 		$gameObject = $this->checkSendGameObject($value['game_object'], $value['subject_id'], $this->mConfig['api_key']);
	 			 		
	 		if(!$gameObject){
	 			continue;
	 		} 
	 		$isSent = $this->checkUserIsFirstPaymentSent($value['id']);
	 		if($isSent){
	 			continue;
	 		}	
	 		
	 		$result= $this->firstPaymentCondition($this->mConfig['uuid'], $value);
	 		if($result){
	 			$prizeConfig[]= $result;
	 		}
	 	}
	 	return  $prizeConfig ;
	 }
	 
	 
	 public  function checkUserIsFirstPaymentSent($activityId){
	 	$search['uuid'] = $this->mConfig['uuid'];
	 	$search['send_type'] = $this->mConfig['type'];
	 	$search['sub_send_type'] = $activityId;
	 	$currentTime = date('Y-m-d',Common::getTime());
	 	$startTime = strtotime($currentTime.' 00:00:00');
	 	$endTime = strtotime($currentTime.' 23:59:59');
	 	$search['consume_time'][0] = array('>=', $startTime);
		$search['consume_time'][1] = array('<=', $endTime);
	 	$sentInfo = Client_Service_TicketTrade::getBy($search);
	 	if($sentInfo){
	 		return true;
	 	}
	 	return false;
	 	
	 }
	 
	/**
	 * 处理活动中的返利充值
	 * @param string $uuid
	 * @param int $send_type
	 */
	public function  activityPaymentSend($uuid, $currentMoney){
		
		$logData= '进入活动充值赠送类uuid='.$uuid.',gameApiKey='.$this->mConfig['api_key'].',充值金额curr_consume='.$currentMoney ;
		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
		//首次充值
		$firstPaymentPrizeArr = $this->firstPaymentSend($uuid);
		

	}
	
	/**
	 * 保存活动赠送消息到消息队列中
	 */
	private function saveActivitySendMsg($msg_arr , $time){
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
     * 检查用户的赠送条件
     * @param unknown_type $uuid
     * @param unknown_type $hd
     */
    private function firstPaymentCondition($uuid, $hd){
        $awardJson = json_decode($hd['rule_content'], true);
        //记录日志
        $logData= '进入活动登录类,活动的配置hd='.json_encode($hd).',活动的ID='.$hd['id'].',uuid='.$uuid.'充值金额money='.$this->mConfig['money'].'赠送的金额范围'.$awardJson['denomination'];
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
                'send_type'=>$this->mConfig['type'],
                'sub_send_type' => $hd['id'],
                'task_name'=>$hd['title']

            );
            return $awardArr;
        }
        return false;


    }

    public function __destruct(){     //应用析构函数自动释放连接资源
		unset($this->mConfig);
		unset($this->mPath);
		unset($this->mFileName);

	}
}