<?php
/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-10-30 15:34:57
 * $Id: Dsp.php 62100 2017-10-30 15:34:57Z hunter.fang $
 */
if (! defined ( 'BASE_PATH' ))
    exit ( 'Access Denied!' );

class Adx_V2_DspController extends Adx_Api_V2_BaseController {

	private  $mWinDspResponse = null;


    public function getTokenAction() {
        $this->mProviderId = $this->getGet ( 'providerId' );
        $timeStamp = Common::getTime ();
        $sign = sha1 ( $this->mProviderId . $timeStamp );
        $token = base64_encode ( $this->mProviderId . ',' . $timeStamp . ',' . $sign );
        echo $token;
    }

    public function configAction() {
        $this->checkAdxToken ();
        $this->checkAdPostParam ( true );
        $this->initIp ();
        $this->initAdType ();
		// 创建竞价ID
		$this->mBidId = $this->createRequestId ();

        //记录请求
		$this->sendDataToStatInterFace(Util_EventType::REQUEST_CONFIG_EVENT_TYPE);

        // 获取应用的信息
        $this->mAppInfo = MobgiApi_Service_AdAppModel::getAppInfoByAppKey ( $this->mAppKey );
        if (empty ( $this->mAppInfo )) {
            $this->output ( Util_ErrorCode::APP_STATE_CHECK, 'app state is close' );
        }
        // 获取广告位
        $this->mAppPositonList = $this->getAppPosInfo ( $this->mAppInfo );
        if (empty ( $this->mAppPositonList )) {
            $this->output ( Util_ErrorCode::POS_STATE_CHECK, 'app positon state is close' );
        }
        $this->mWhitelistConfig = $this->isWhitelist ();

        // 获取所有的dsp响应
        $dspResponses = $this->getAllDspResponses ();
        if(isset($dspResponses['ret']) && $dspResponses['ret']){
            $this->output($dspResponses['ret'], $dspResponses['msg']);
        }
        // 检查所有DSP返回的数据
        $dspResponses = $this->farmatAllDspResponses ( $dspResponses );
        if (isset ( $dspResponses ['ret'] ) && $dspResponses ['ret']) {
            $this->output ( $dspResponses ['ret'], $dspResponses ['msg'] );
        }
        // 竞价
        list ( $winDspNo, $winDspResponse ) = $this->getWinDspResponse ( $dspResponses );
        // 组装输出数据
        if ($winDspNo && $winDspResponse) {
			$this->mWinDspResponse = $winDspResponse ['data'];
        	$this->sendDataToStatInterFace(Util_EventType::REQUEST_OK_EVENT_TYPE);
            $this->output ( Util_ErrorCode::CONFIG_SUCCESS, 'ok', $winDspResponse ['data'] );
        } else {
            $this->output ( Util_ErrorCode::NO_SUCCESSFUL_BIDDING, 'There is no DSP with successful bidding', array () );
        }
    }




    public function sendDataToStatInterFace($eventType){
		if($this->mPostData['providerId'] != Util_Ssp::PROVIDER_ID_FOR_4399){
			return false;
		}
		$data = $this->getReportData($eventType);
		/*$data['ad_unit_id'] = 0;
		$data['client_ip'] = Common::getClientIP();
		$data['server_time'] = time();
		$data['ver'] = 11;
		$redis = Common::getQueue('mobgi');
		return $redis->push('RQ:ad_client', $data);*/
		$sign = Util_Ssp::getSign($data);
		$data['sign'] = $sign;
		$statUrl = Yaf_Application::app()->getConfig()->statroot.'/ssp/';
		$curl = new Util_Http_Curl($statUrl,500);
		$curl->setData($data);
		$result = $curl->send('GET');
		if ($this->isDebugMode ()) {
			$this->mDebugInfo ['reportDataUrl_'.$eventType] = $statUrl.'?'.http_build_query($data);
		}
		if($result){
			$ret = json_decode($result,true);
			if($ret['ret']){
				Util_Log::INFO(__CLASS__,'sspStatError',$data);
			}
		}

	}

	public function getReportData($eventType){
    	if($eventType == Util_EventType::REQUEST_OK_EVENT_TYPE){
			$data['dspId'] = $this->mWinDspResponse['dspId'];
			$data['bidId'] = $this->mWinDspResponse['bidId'];
			$data['outBidId'] =  $this->mWinDspResponse['outBidId'];
			$data['adId'] = $this->mWinDspResponse['adInfo'][0]['basicInfo']['adId'];
			$data['originalityId'] = $this->mWinDspResponse['adInfo'][0]['basicInfo']['originalityId'];
			$data['price'] = $this->mWinDspResponse['adInfo'][0]['basicInfo']['price'];
			$data['chargeType'] = $this->mWinDspResponse['adInfo'][0]['basicInfo']['chargeType'];
			$data['currency'] = $this->mWinDspResponse['adInfo'][0]['basicInfo']['currency'];
		}else{
			$data['dspId']=-1;
			$data['bidId'] =$this->mBidId;
			$data['outBidId']=-1;
			$data['adId'] =-1;
			$data['originalityId']=-1;
			$data['price']=-1;
			$data['chargeType']=-1;
			$data['currency']=-1;
		}
		$data['providerId'] = $this->mPostData['providerId'];
		$data['appKey'] = $this->mPostData['app']['appKey'];
		$data['blockId'] = $this->mPostData['imp'][0]['blockId'];
		$data['adType'] = $this->mAdType;
		$data['eventType'] = $eventType;
		$data['brand'] =  $this->mPostData['device']['brand']?$this->mPostData['device']['brand']:-1;
		$data['model'] =  $this->mPostData['device']['model']?$this->mPostData['device']['model']:-1;
		$data['imei'] =  $this->mPostData['device']['deviceId']?$this->mPostData['device']['deviceId']:-1;
		$data['uuid'] =  $this->mPostData['device']['deviceId']?$this->mPostData['device']['deviceId']:-1;
		$data['netType'] =  $this->mPostData['device']['net'];
		$data['operator'] =  $this->mPostData['device']['operator'];
		$data['platform'] =  $this->mPostData['device']['platform'];
		$data['resolution'] =  $this->mPostData['device']['resolution'];
		$data['appVersion'] =  $this->mPostData['app']['version'];
		$data['sdkVersion'] =  -1;
		$data['clientTime'] =  common::getTime();
		return $data;
	}


    /**
     *
     * @param type $dspResponses            
     * @return array
     */
    private function farmatAllDspResponses($dspResponses) {
        $errorMsg = array();
        $outData = array ();
        $dspList = array_keys ( $dspResponses );
        foreach ( $dspList as $dspNo ) {
            $tmp = $this->dspInstances [$dspNo]->formatResponses ( $dspResponses [$dspNo], $dspNo, 'V2' );
            if (! empty ( $tmp['data'] )) {
                $outData [$dspNo] = $tmp;
            }else{
                $errorMsg[$dspNo] = $tmp;
            }
        }
        if ($this->isDebugMode ()) {
            $this->mDebugInfo ['formatDspResponses'] = $outData;
        }
        if(empty($outData)){
            if(in_array(Common_Service_Const::HOUSEAD_DSP_ID, $dspList)){
                return array('ret'=>$errorMsg[Common_Service_Const::HOUSEAD_DSP_ID]['ret'],'msg'=>$errorMsg[Common_Service_Const::HOUSEAD_DSP_ID]['msg'], 'data'=>array());
            }else{
                return array('ret'=>Util_ErrorCode::DSP_RETURN_DATA_EMPTY,'msg'=>'dsp return data is empty', 'data'=>array());
            }
        }
        return $outData;
    }



    /**
     * 竞价获取赢得竞价的dsp响应
     *
     * @param type $dspResponses            
     * @return type
     */
    private function getWinDspResponse($dspResponses) {
        $winDspNo = '';
        $winDspResponse = array ();
        $bidPrice = $this->mBasePrice;
        if (! $dspResponses) {
            return array (
                    $winDspNo,
                    $winDspResponse 
            );
        }
		$bidPriceDspArr = array();
        if ($this->mAdType == Common_Service_Const::CUSTOME_AD_SUB_TYPE && isset ( $dspResponses [Common_Service_Const::HOUSEAD_DSP_ID] )) {
            $winDspNo = Common_Service_Const::HOUSEAD_DSP_ID;
            $winDspResponse = $dspResponses [Common_Service_Const::HOUSEAD_DSP_ID];
            $winDspResponse ['data'] ['dspId'] = $winDspNo;
        } else {
            foreach ( $dspResponses as $dspNo => $dspResponse ) {
            	if($dspResponse ['data'] ['adInfo'] [0] ['basicInfo'] ['bidPrice']){
					$bidPriceDspArr[$dspNo]= $dspResponse ['data'] ['adInfo'] [0] ['basicInfo'] ['bidPrice'];
				}
            }
			$winDspNo = $this->getWinBidPriceDspNo($bidPriceDspArr);
            if($winDspNo){
				$winDspResponse =$dspResponses[$winDspNo];
			}

        }

        if ($winDspNo && $winDspResponse) {
            $this->sendDspEvent ( $winDspNo, 'win' );
			$this->sendDspEvent ( $winDspNo, 'notice',$winDspResponse['data']['outBidId'] );
        }
        if ($this->isDebugMode ()) {
            $this->mDebugInfo ['bidPirceDspList'] = $bidPriceDspArr;
            $this->mDebugInfo ['winDspNo'] = $winDspNo;
        }
        return array (
                $winDspNo,
                $winDspResponse 
        );
    }



}