<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/** 
 * 具体策略类 登录 
 */  


class Util_Activity_TicketSend extends Util_Activity_Common implements Util_Activity_Coin{  

   /**
    * 
    * @param unknown_type $config
    */
   public function __construct($config = array()){  
        $this->mConfig = $config;  
        //初始化写日志路径
        $path = Common::getConfig('siteConfig', 'logPath');
        $fileName = date('m-d').'_Ticket.log';
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
    	//1福利任务  2 日常任务 3活动任务 4 手动发送 5抽奖获取 6商城兑换 7生日礼物' 8节日活动,
    	if($type == 4){
    		return  $this->adminSendTicket();
    	}else{
    		return $this->ticketSend();
    	}
    }
    
    /**
     * 后台赠送
     */
    private function adminSendTicket(){
    	//获取赠送的数组,用来保存A券信息
    	$prizeArr =  $this->mConfig['prizeArr'];
    	$logData= '进入赠送类，操作人optname= '.$this->mConfig['optName'].',组装的数组prize_arr='.json_encode($prizeArr);
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	
    	if(!is_array($prizeArr)){
    		return false;
    	}
    
    	$time = Common::getTime();
    	//保存赠送A券记录
    	$savaRs = $this->saveAdminSendTickets($prizeArr, $time);
    	if(!$savaRs){
    		//写日志
    		$logData= '进入赠送类，保存赠送A券失败sava_rs'.$savaRs;
    		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    		return false;
    	}
    	 
    	//组装发送到支付post数组
    	$postPrizeArr = $this->postToPaymentData($prizeArr);
    	//给支付发请求
    	$paymentResult =  $this->postToPayment($postPrizeArr);
    	 
    	//写入日志
    	$logData= '进入赠送类，PSOT请求到支付组服务器返回结果paymentResult='.json_encode($paymentResult);
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	//校验支付返回的结果
    	$responseData =  $this->verifyPaymentResult($paymentResult);
    	if(!$responseData){
    		return false;
    	}
    	//更新A券的状态
    	if($this->updateSendTickets($responseData)){
    		//赠送消息入队列
    		$this->saveMutiSendMsg($prizeArr);
    		$this->saveSendMailLog($responseData, $time);
    		return true;
    	}else{
    		return false;
    	}

    }
    
    private function saveSendMailLog($responseData, $time){
        if(!count($responseData)){
            return;
        }
        $reasons = array();
        foreach ($responseData as $val){
            $reasons[] = array(
                  'id' => '',
                  'aid' => $val['aid'],
                  'reason' => $this->mConfig['reason'],
                  'operator_name' => $reason = $this->mConfig['operator_name'],
                  'create_time' => $time
            );
        }
        
        $ret = Client_Service_SendTicketReason::mutiFieldInsert($reasons);
        if($ret){
            Common::getQueue()->push('game_client_send_mail',$time);
        }
        return $ret;
    }

    /**
     * A券赠送
     * @param unknown_type $wealTaskConfig
     * @return boolean
     */
    private function ticketSend(){
    	if(!$this->mConfig['type']){
    		return false;
    	}
        
    	//检测数据的完整
    	$rs = $this->checkSendData();
    	
    	$logData= '进入赠送类，检查数据完整性的结果rs='.$rs.',uuid ='.$this->mConfig['uuid'].',denomination='.$this->mConfig['denomination'].
    	',section_start='.$this->mConfig['section_start'].',section_end='.$this->mConfig['section_end'].',desc='.$this->mConfig['desc'];
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	if(!$rs){
    		return false;
    	}
    	$time = Common::getTime();
    
    	//获取赠送的数组,用来保存A券信息
    	$prizeArr =  $this->getTaskAwardResult();
    	$logData= '进入赠送类，组装的数组prize_arr='.json_encode($prizeArr);
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
   
    	if(!is_array($prizeArr)){
    		return false;
    	}
    	if($this->mConfig['type'] == 5){
    		$desc ='积分抽奖';
    	}elseif($this->mConfig['type'] == 6){
    		$desc ='积分兑换';
    	}else{
    		$desc = $this->mConfig['desc'];
    	}
    	//保存赠送A券记录
    	$savaRs = $this->saveTaskSendTickets($prizeArr, $time, $desc);
    	if(!$savaRs){
    		//写日志
    		$logData= '进入赠送类，保存赠送A券失败sava_rs'.$savaRs;
    		Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    		return false;
    	}
    	
    	//组装发送到支付post数组
    	$postPrizeArr = $this->postToPaymentData($prizeArr);
    	//给支付发请求
    	$paymentResult =  $this->postToPayment($postPrizeArr);
    	
    	//写入日志
    	$logData= '进入赠送类，PSOT请求到支付组服务器返回结果paymentResult='.json_encode($paymentResult);
    	Common::WriteLogFile($this->mPath, $this->mFileName, $logData);
    	//校验支付返回的结果
    	$responseData =  $this->verifyPaymentResult($paymentResult);
    	if(!$responseData){
    		return false;
    	}

    	//更新A券的状态
    	if($this->updateSendTickets($responseData)){
    		//赠送消息入队列
    		if($this->mConfig['type'] == 5){
    			$desc ='抽奖活动';
    		}elseif($this->mConfig['type'] == 6){
    			$desc ='积分兑换';
    		}else{
    			$desc = $this->mConfig['desc'];
    		}
    		$this->saveTaskMsg($prizeArr, $desc);
    		return true;
    	}else{
    		return false;
    	}
    }
    
  /**
   * 检查数据的合法性
   * @return boolean
   */
    private function checkSendData(){
    	if(!$this->mConfig['uuid']){
    		return false;
    	}
    	if(!$this->mConfig['denomination']){
    		return false;
    	}
    	if(!intval($this->mConfig['section_start'])){
    		return false;
    	}
    	if(!intval($this->mConfig['section_end'])){
    		return false;
    	}
    	if(!$this->mConfig['desc']){
    		return false;
    	}
    	if(!intval($this->mConfig['type'])){
    		return false;
    	}
    	return true;
    }
  

   /**
    * 组装福利任务的奖励数组
    * @param unknown_type $wealTaskPrize
    * @return boolean
    */
   private function getTaskAwardResult(){
       $awardArr[] = array(
           'denomination'=>$this->mConfig['denomination'],
           'section_start'=>$this->mConfig['section_start'],
           'section_end'=> $this->mConfig['section_end'],
           'desc'=>$this->mConfig['desc'],
           'uuid'=>$this->mConfig['uuid'],
           'send_type'=>$this->mConfig['type'],
           'sub_send_type'=>$this->mConfig['task_id'],
       );

       $prizeArr =$this->getAwardResult($awardArr);
	   	return $prizeArr;
   }
   
   /**
    * 保存福利任务赠送的A券
    * @param unknown_type $send_arr
    */
   private function saveTaskSendTickets($sendArr, $time, $desc ){
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
	   		$tmp[$key]['description'] = $desc;
	   	}
	   	 $rs = $this->saveSendTickets($tmp);
	   	return $rs;
   }
   
   /**
    * 保存福利任务赠送的A券
    * @param unknown_type $send_arr
    */
   private function saveAdminSendTickets($sendArr, $time ){
   	//保存赠送A券记录
   	foreach ($sendArr as $key=>$val){
   		$tmp[$key]['uuid'] = $val['uuid'];
        $tmp[$key]['ticket_type'] = $val['ticket_type'];
        $tmp[$key]['game_id'] = $val['game_id'];
        $tmp[$key]['aid'] = $val['aid'];
   		$tmp[$key]['denomination'] = $val['denomination'];
   		$tmp[$key]['status'] = 0;
   		$tmp[$key]['send_type'] = $val['send_type'];
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
    * 单个消息赠送
    */
   private function saveTaskMsg($msg_arr , $task_name){
   		$desc = $this->getTaskDesc($this->mConfig['type'], $task_name, $this->mConfig['denomination']);
   		$title = $this->getTaskTitle($this->mConfig['type']);
	   	$rs = $this->saveMsg($this->mConfig['uuid'], $this->mConfig['denomination'], $desc, $title);
   	    return $rs;
   }
   
   /**
    * A券消息标题模板
    * @param int $type
    * @return string
    */
   private function getTaskTitle($type){
   	$title = '';
   	if($type == 7){
   		$title = "祝你永远18岁！~";
   	}
   	return $title;
   }
   
   /**
    * A券消息内容模板
    * @param int $type
    * @param string $task_name
    * @param string $denomination
    * @return string
    */
   private function getTaskDesc($type, $task_name, $denomination){
   		$desc = '恭喜，您参加'.$task_name.'，获得'.$denomination.'A券奖励！请在有效期内使用！';
   		if($type == 7){
   			$desc = "生日快乐，游戏大厅送你{$denomination}A券，玩的开心！";
   		}
   		return $desc;
   }
   
   
   
   /**
    * 多个赠送消息
    */
   private function saveMutiSendMsg($msg_arr){
	  	if(!is_array($msg_arr) || empty($msg_arr)) return false;
	    foreach ($msg_arr as $val){
	        $denomination = '';
	        $denomination = round($val['denomination'],2);
	        $denomination = sprintf("%.2f", $denomination);
	    	$desc = '恭喜，金立游戏大厅赠送您'.$denomination.'A券奖励！请在有效期内使用！';
	    	$rs = $this->saveMsg($val['uuid'], $denomination, $desc);
	    }
	   	return $rs;
   }

   public function __destruct(){     //应用析构函数自动释放连接资源
	   	unset($this->mConfig);
	   	unset($this->mPath);
	   	unset($this->mFileName);
  
   }
   
}   
  
