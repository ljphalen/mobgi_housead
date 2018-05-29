<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-9-8 16:33:21
 * $Id: Adsrelconfigwhitelist.php 62100 2017-9-8 16:33:21Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');


class Systemtool_AdsrelconfigwhitelistController extends Admin_BaseController {
	public $actions = array(
	        'listUrl' => '/Admin/Systemtool_Adsrelconfigwhitelist/index',
	        'addPostUrl' => '/Admin/Systemtool_Adsrelconfigwhitelist/addPost',
	        'deleteUrl' => '/Admin/Systemtool_Adsrelconfigwhitelist/delete',
	        'viewUrl' => '/Admin/Systemtool_Adsrelconfigwhitelist/view',
	        'updateAdsposrelStateUrl' => '/Admin/Systemtool_Adsrelconfigwhitelist/updateAdsposrelState',
	        'getAdsUrl'=> '/Admin/Systemtool_Adsrelconfigwhitelist/getAdsList'
	);
	
	public $perpage = 20;
	
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {
        $search = $params = array ();
        $page = intval ( $this->getInput ( 'page' ) );
        if ($page < 1)
            $page = 1;
        $search = $this->getInput ( array (
                'app_name',
                'platform',
                'app_key' 
        ) );
        if (trim ( $search ['app_name'] )) {
            $appKeys = MobgiApi_Service_AdAppModel::getAppKeysByName ( $search ['app_name'] );
            if ($appKeys) {
                $params ['app_key'] = array (
                        'IN',
                        $appKeys 
                );
            } else {
                $params ['app_key'] = '0';
            }
        }
        if (isset ( $search ['platform'] ) && $search ['platform']) {
            $params ['platform'] = $search ['platform'];
        }
        $params ['is_check'] = MobgiApi_Service_AdAppModel::ISCHECK_PASS;
        list ( $total, $appList ) = MobgiApi_Service_AdAppModel::getList ( $page, $this->perpage, $params, array (
                'update_time' => 'DESC' 
        ) );
        
        $queryStr = $search;
        unset($queryStr['app_key']);
        $url = $this->actions ['listUrl'] . '/?' . http_build_query ( $queryStr ) . '&';
        $this->assign ( 'pager', Common::getPages ( $total, $page, $this->perpage, $url ) );
        $this->assign ( 'search', $search );
        $this->assign ( 'total', $total );
        foreach ( $appList as $key => $value ) {
            if (! stristr ( $value ['icon'], 'http' )) {
                $appList [$key] ['icon'] = Common::getAttachPath () . $value ['icon'];
            }
        
            $whereParams ['app_key'] = $value ['app_key'];
            
            // 广告商参数配置结果
            $adsAppRelRestult = MobgiApi_Service_AdsAppRelWhitelistModel::getsBy ( $whereParams, array (
                    'ads_id' => 'ASC' 
            ) );
            // 广告位配置结果
            $adsPosRelResult = MobgiApi_Service_AdsPosRelWhitelistModel::getsBy ( $whereParams, array (
                    'ads_id' => 'ASC'
            ) );
            if ((isset ( $search ['app_key'] ) && ($search ['app_key'] == $value ['app_key'])) || (! isset ( $search ['app_key'] ) && $key == 0)) {
                $data ['app_key'] = $value ['app_key'];
                $data ['platform'] = $value ['platform'];
                $data ['app_name'] = $value ['app_name'];
                // 初始化广告商参数
                $this->initAdsAppRelConf ( $adsAppRelRestult, $adsPosRelResult, $value ['app_id'] );
            }
            $appList [$key] ['is_config'] = empty ( $adsPosRelResult ) || empty ( $adsAppRelRestult ) ? false : true;
        }
        $this->assign ( 'templateList', $this->getTemplateList() );
        $this->assign ( 'appList', $appList );
        $this->assign ( 'data', $data );
        $this->assign ( 'queryString', $this->getQueryString() );
    }
    
    public function getQueryString(){
        $search = $this->getInput ( array (
                'platform',
                'app_name',
                'page'
        ) );
    
        return http_build_query ( $search );
    
    }
    
    public function getTemplateList(){
        $param['id'] = array('>', 0);
        $result = MobgiApi_Service_TemplateModel::getsBy($param);
        $template =array();
        foreach ($result as $val){
            $template[$val['ad_type']][$val['id']] = $val['name'];
        }
        return $template;
    }
    
    public function getAdsListAction() {
        $info = $this->getInput ( array (
                'app_key',
                'ad_type'
        ) );
        if (!$info ['ad_type'] ) {
            $this->output ( - 1, '非法操作' );
        }
        $adsList = $this->getAdsIdList($info['ad_type']);
        if($info['app_key']){
            $params['app_key'] = $info['app_key'];
            $params['ad_sub_type'] = $info['ad_type'];
            $adsAppRel = MobgiApi_Service_AdsAppRelWhitelistModel::getsBy($params);
            if($adsAppRel){
                $adsIds = array_keys(Common::resetKey($adsAppRel, 'ads_id'));
                foreach ($adsList as $index=>$adsInfo){
                    if(in_array($adsInfo['ads_id'], $adsIds)){
                        unset($adsList[$index]);
                    }
                }
            }
        }
        $data['adsList'] = $adsList;
        $appInfo = MobgiApi_Service_AdAppModel::getBy ( array (
                'app_key' => $info ['app_key']
        ) );
        $data ['blockList'] = $this->getPosListByAdSubType ( $appInfo ['app_id'], Common_Service_Const::$mAdPosType[$info['ad_type']]);
        $this->output ( 0, '操作成功', $data );
    }
    
    private function getPosListByAdSubType($appId, $adSubType) {
        $params ['pos_key_type'] = $adSubType;
        $params ['app_id'] = $appId;
        $params ['del'] = MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG;
        $result = MobgiApi_Service_AdDeverPosModel::getsBy ( $params );
        if (! $result) {
            return array ();
        }
        $outData = array ();
        foreach ( $result as $val ) {
            $outData [$val ['dever_pos_key']] = array (
                    'pos_key' => $val ['dever_pos_key'],
                    'pos_name' => $val ['dever_pos_name'],
                    'ad_sub_type'=>$val['ad_sub_type']?Common_Service_Const::$mEnbedSubType[$val['ad_sub_type']]:0,
                    'size'=>$val['size']?Common_Service_Const::$mEnbedSize[$val['size']]:0,
                    'third_party_block_id' => '',
                    'third_party_report_id' => '',
                    'ads_pos_rel_id' => 0,
                    'state' => 1,
                     
            );
        }
        return $outData;
    }
    
	
private function initAdsAppRelConf($adsAppRelResult,$adsPosRelResult, $appId){
	    
		$adsAppRelList = array();
		if($adsAppRelResult){
			$adsIdsArr    = array_keys(Common::resetKey($adsAppRelResult, 'ads_id'));
			$adTypeArr  = $this->getAdParentTypeByAdsId($adsIdsArr);
			$adTypeList = array(Common_Service_Const::INTERGRATION_AD_TYPE,Common_Service_Const::DSP_AD_TYPE);
			foreach ($adTypeList as $adType){
				foreach (  $adsAppRelResult as $index => $val ) {
					if($adTypeArr[$val['ads_id']]['ad_type'] ==  $adType){
						$adsAppRelList[$val['ad_sub_type']][$val['id']] = $val;
					}
				}
			}
		}
		$adsAppList = array();
		foreach ( Common_Service_Const::$mAdSubTypeDesc as $subType => $subTypeDesc ) {
			if( isset($adsAppRelList[$subType]) && $adsAppRelList[$subType]){
				$adsListArr = $this->getAdsIdList($subType);
				$adsListArr = Common::resetKey($adsListArr, 'ads_id');
				foreach ($adsAppRelList[$subType] as $adsAppRelId=>$relList){
					if($relList){
						$playNetwork = $relList ['play_network'];
						$lifeCycle = $relList ['life_cycle'];
						$isShowView = $relList ['is_show_view'];
						$showViewTime = $relList ['show_view_time'];
						$isUseTemplate = $relList ['is_use_template'];
						$templateShowTime = $relList ['template_show_time'];
						$templateId = $relList ['template_id'];
						break;
					}
				}
				$tmp = array();
				foreach ($adsAppRelList[$subType] as $adsAppRelId=>$relList){
				        $thirdPartyBlockId = $this->initAdsPosRelConf($adsPosRelResult, $adsAppRelResult, $appId, $relList ['ads_id'], $subType);
					   	$tmp[] = array (
					   			'ads_app_rel_id' => $adsAppRelId,
					   			'ads_id' => $relList ['ads_id'],
					   			'ads_name'=>$adsListArr[$relList ['ads_id']]['ads_name'],
					   	        'parent_name'=>$adsListArr[$relList ['ads_id']]['parent_name'],
					   			'third_party_app_key' => $relList ['third_party_app_key'],
					   			'third_party_secret' => $relList ['third_party_secret'],
					   			'third_party_report_id' => $relList ['third_party_report_id'],
					   	        'third_party_block_id'=>  empty($thirdPartyBlockId)?'':json_encode($thirdPartyBlockId) 
					   	);		
				}
				$adsAppList[$subType] = array(
				        'ad_type_desc'=>$subTypeDesc,
						'ad_type'=>$subType,
						'ad_type_name'=>Common_Service_Const::$mAdSubType[$subType],
						'play_network'=> $playNetwork,
						'life_cycle'=> $lifeCycle,
						'is_show_view'=>$isShowView,
						'show_view_time'=>$showViewTime,
				        'is_use_template'=>$isUseTemplate,
				        'template_show_time'=>$templateShowTime,
				        'template_id'=>$templateId?$templateId:0,
						'ads_list'=>$tmp
				);
			}else{
				$adsAppList[$subType] = array(
				        'ad_type_desc'=>$subTypeDesc,
						'ad_type'=>$subType,
						'ad_type_name'=>Common_Service_Const::$mAdSubType[$subType],			
						'play_network'=> 1,
						'life_cycle'=> 1800,
						'is_show_view'=>0,
						'show_view_time'=>0,
				        'is_use_template'=>0,
				        'template_show_time'=>3,
				        'template_id'=>0,
						'ads_list'=>array()
				);
			}
		}
		$this->assign('adsAppList', $adsAppList);
	}
	
	private function getAdParentTypeByAdsId($adsIds){
	    $params['del'] = Common_Service_Const::NOT_DELETE_FLAG;
	    $params['ads_id'] = array('IN', $adsIds);
	    $result =  MobgiApi_Service_AdsListModel::getsBy($params);
	    if($result){
	        return Common::resetKey($result, 'ads_id');
	    }
	    return array();
	}
	
	private function initAdsPosRelConf($adsPosRelResult, $adsAppRelResult, $appId, $adsId, $adType){
	    $oldPosInfo =$this->getPosList( $appId, Common_Service_Const::$mAdPosType[$adType]);
	    if($oldPosInfo){
	        foreach (  $oldPosInfo as $posKey => $val ) {
	            $tmpOldPosInfo[$posKey] = array(
	                    'pos_key'=>$val['dever_pos_key'],
	                    'pos_name'=>$val['dever_pos_name'],
	                    'ad_sub_type'=>$val['ad_sub_type'],
	                    'size'=>$val['size']
	            );
	        }
	    }
	    $adsPosRelList = array();
	    $currentPos = array();
	    if($adsPosRelResult){
	        foreach ($adsPosRelResult as $index => $val ) {
	            if ($val ['ad_sub_type'] == $adType && $val ['ads_id'] == $adsId) {
	                $adsPosRelList [] = array (
	                        'pos_key' => $val ['pos_key'],
	                        'pos_name' => $tmpOldPosInfo [$val ['pos_key']] ['pos_name'],
	                        'ad_sub_type'=>$tmpOldPosInfo [$val ['pos_key']] ['ad_sub_type']?Common_Service_Const::$mEnbedSubType[$tmpOldPosInfo [$val ['pos_key']] ['ad_sub_type']]:0,
	                        'size'=>$tmpOldPosInfo [$val ['pos_key']] ['size']?Common_Service_Const::$mEnbedSize[$tmpOldPosInfo [$val ['pos_key']] ['size']]:'',
	                        'third_party_block_id' => $val ['third_party_block_id'],
	                        'third_party_report_id' => $val ['third_party_report_id'],
	                        'ads_pos_rel_id' => $val ['id'],
	                        'state' => intval ( $val ['state'] ),
	                        'is_add'=>0
	                );
	            }
	        }
	    }
	    return  $adsPosRelList;
	}

	private function getAdsListByadsAppRelResult($subType,$adsParamsResult){
	    if(empty($adsParamsResult)){
	        return array();
	    }
	    $adsListConf = array();
	    foreach ($adsParamsResult as $val){
	        if($val['ad_sub_type'] == $subType ){
	            $adsListConf[] = $val;
	        }
	    }
	
	    return $adsListConf;
	}
	
	
	private function getPosList($appId,  $type){
	    $adPosInfo = MobgiApi_Service_AdDeverPosModel::getsBy(array('app_id'=>$appId,'del'=>MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG) );
	    $posInfo = array();
	    if($adPosInfo){
	        foreach ($adPosInfo as $val){
	            if($val['pos_key_type'] == $type){
	                $posInfo[$val['dever_pos_key']] = $val;
	            }
	        }
	    }
	    return $posInfo;
	}
	
	private function addAdsToAdsConfList($adsList,  $adsConfList, $type){
	    if(empty($adsConfList)){
	        return array();
	    }
	    if( !is_array($adsList) ){
	        return   $adsConfList;
	    }
	    foreach ($adsConfList as $key=>$val){
	        if(!array_key_exists($key, $adsList)){
	            unset($adsConfList[$key]);
	        }
	        if($type == 'ads_list'){
	            $adsConfList[$key] = $adsList[$key];
	        }
	    }
	    return $adsConfList;
	}
	
	
	/**
	 *
	 * @param unknown $intergration_sub_type
	 * @param number $adsType
	 */
	private  function getAdsIdList($adSubType, $adType = null ){
	    //获取广告商列表
	    if(!$adType){
	        $adType = array(Common_Service_Const::INTERGRATION_AD_TYPE,Common_Service_Const::DSP_AD_TYPE);
	    }else{
	        $adType = array($adType);
	    }
	    $params['ad_type'] = array('IN', $adType);
	    $params['del'] = Common_Service_Const::NOT_DELETE_FLAG;
	    $result =  MobgiApi_Service_AdsListModel::getsBy($params,array('ads_id'=>'ASC'));
	    $adsList = array();
	    if($result){
	        $adsParentTypeName = $this->getAdsParentTypeName ();
	        foreach ($result as $val){
	            $adSubTypeArr = json_decode($val['ad_sub_type'], true);
	            if(in_array($adSubType, $adSubTypeArr)){
	                $adsList[] = array('ads_id'=>$val['ads_id'],
	                        'ads_name'=>$val['name'],
	                        'parent_name'=>$adsParentTypeName[$val['ads_id']]);
	            }
	        }
	    }
	    return $adsList;
	}
	
	private function getAdsSubTypeName(){
	    //获取广告商列表
	    $adType = array(Common_Service_Const::INTERGRATION_AD_TYPE,Common_Service_Const::DSP_AD_TYPE);
	    $params['del'] = Common_Service_Const::NOT_DELETE_FLAG;
	    $result =  MobgiApi_Service_AdsListModel::getsBy($params);
	    $typeName = array();
	    foreach ($result as $val){
	        $typeName[$val['ads_id']] = $val['name'];
	    }
	    return $typeName;
	}
	
	private function getAdsParentTypeName(){
	    //获取广告商列表
	    $adType = array(Common_Service_Const::INTERGRATION_AD_TYPE,Common_Service_Const::DSP_AD_TYPE);
	    $params['del'] = Common_Service_Const::NOT_DELETE_FLAG;
	    $result =  MobgiApi_Service_AdsListModel::getsBy($params);
	    $typeName = array();
	    foreach ($result as $val){
	        $typeName[$val['ads_id']] = Common_Service_Const::$mAdType[$val['ad_type']];
	    }
	    return $typeName;
	}
	
	
	public function addPostAction() {
	    $info = $this->getRequest ()->getPost ();
	    // 广告商参数
	    $this->checkPostAdsParam ( $info );
	    $this->checkPostAdsPosition ( $info );
	    $this->saveAdsAppRelData ( $info );
	    $this->saveAdsPositionData($info);
	    $this->output(0, '配置成功');
	}
	
	private function saveAdsPositionData($info){
	    $data['app_name'] = $info['app_name'];
	    $data['platform'] = $info['platform'];
	    $data['app_key'] = $info['app_key'];
	    foreach (Common_Service_Const::$mAdSubTypeDesc as $adType=>$adTypeDesc){
	        foreach ( $info [$adTypeDesc.'_ads_id'] as  $adsId ) {
	            $jsonInfo = json_decode ( $info ['app_key_rel_pos_set_' . $adType] [$adsId], true );
	            if(empty($jsonInfo)){
	                continue;
	            }
	            $posKey = array_keys(Common::resetKey($jsonInfo, 'pos_key'));
	            $params['dever_pos_key'] = array('IN', $posKey);
	            $blockName = MobgiApi_Service_AdDeverPosModel::getsBy($params);
	            $blockName = Common::resetKey($jsonInfo, 'dever_pos_key');
	            foreach ($jsonInfo as $block){
	                $data ['app_name'] = $info ['app_name'];
	                $data ['app_key'] = $info ['app_key'];
	                $data ['platform'] = $info ['platform'];
	                $data ['ad_sub_type'] = $adType;
	                $data ['ads_id'] = $adsId;
	                $data ['third_party_block_id'] = $block['third_party_block_id'];
	                $data ['third_party_report_id'] = $block['third_party_report_id'];
	                $data['pos_id'] = $blockName[$block['pos_key']]['id'];
	                $data['state'] = $block['state'];
	                $data['pos_key'] = $block['pos_key'];
	                $id = $block ['ads_pos_rel_id'];
	                if($id){
	                    MobgiApi_Service_AdsPosRelWhitelistModel::updateByID($data, $id);
	                }else{
	                    MobgiApi_Service_AdsPosRelWhitelistModel::add($data);
	                }
	            }
	        }
	    }
	}
	
	private function saveAdsAppRelData($post) {
	    foreach (Common_Service_Const::$mAdSubTypeDesc as $adSubType=>$adSubTypeDesc){
	        if(!$post[$adSubTypeDesc.'_ads_id']){
	            continue;
	        }
	        foreach ( $post [$adSubTypeDesc . '_ads_id'] as $key => $val ) {
	            $data ['app_name'] = $post ['app_name'];
	            $data ['app_key'] = $post ['app_key'];
	            $data ['platform'] = $post ['platform'];
	            $data ['ad_sub_type'] = $adSubType;
	            $data ['ads_id'] = $val;
	            $data ['play_network'] = $post [$adSubTypeDesc.'_play_network'];
	            $data ['life_cycle'] = $post [$adSubTypeDesc.'_life_cycle'];
	            $data ['is_show_view'] = $post [$adSubTypeDesc.'_is_show_view'];
	            $data ['show_view_time'] = $post [$adSubTypeDesc.'_show_view_time'];
	            $data ['is_use_template'] = $post [$adSubTypeDesc.'_is_use_template'];
	            $data ['template_show_time'] = $post [$adSubTypeDesc.'_template_show_time'];
	            $data ['template_id'] = $post [$adSubTypeDesc.'_template_id'];
	            $data ['third_party_app_key'] = $post [$adSubTypeDesc.'_third_party_app_key'][$key];
	            $data ['third_party_secret'] = $post [$adSubTypeDesc.'_third_party_secret'][$key];
	            $data ['third_party_report_id'] = $post [$adSubTypeDesc.'_third_party_report_id'][$key];
	            $id = $post [$adSubTypeDesc.'_ads_app_rel_id'][$key];
	            if($id){
	                MobgiApi_Service_AdsAppRelWhitelistModel::updateByID($data, $id);
	            }else{
	                MobgiApi_Service_AdsAppRelWhitelistModel::add($data);
	            }
	        }
	    }
	}
	
	
	private function checkPostAdsPosition($info){
	    foreach (Common_Service_Const::$mAdSubTypeDesc as $adType=>$adTypeDesc){
	        foreach ( $info [$adTypeDesc.'_ads_id'] as  $adsId ) {
	            $jsonInfo = json_decode ( $info ['app_key_rel_pos_set_' . $adType] [$adsId], true );
	            foreach ($jsonInfo as $block){
	                if(!Common::validthirdPartyInput($block['third_party_block_id'])){
	                    $this->output ( - 1, Common_Service_Const::$mAdSubType[$adType] . '配置中第三方 blockid的"'.$adsId.'"第三方广告位"'.$block['pos_name'].'"含有非法字符' );
	                }
	                if(!Common::validthirdPartyInput($block['third_party_report_id'])){
	                    $this->output ( - 1, Common_Service_Const::$mAdSubType[$adType] . '配置中第三方 blockid的"'.$adsId.'"第三方report"'.$block['pos_name'].'"含有非法字符' );
	                }
	            }
	        }
	    }
	    foreach (Common_Service_Const::$mAdSubTypeDesc as $adType=>$adTypeDesc){
	        foreach ( $info [$adTypeDesc.'_ads_id'] as  $adsId ) {
	            $jsonInfo = json_decode ( $info ['app_key_rel_pos_set_' . $adType] [$adsId], true );
	            if(!$jsonInfo){
	                continue;
	            }
	            foreach ($jsonInfo as $block){
	                $params ['app_key'] = $info ['app_key'];
	                $params ['ad_sub_type'] = $adType;
	                $params ['ads_id'] = $adsId;
	                $params['pos_key'] = $block['pos_key'];
	                $result = MobgiApi_Service_AdsPosRelWhitelistModel::getBy($params);
	                $id = $block['ads_pos_rel_id'];
	                if($result && !$id ){
	                    $this->output(-1,'广告位”'.$params['pos_key'].'"中的'.$params ['ads_id'].'已经存在,请检查配置'.$result['id'].':'.$id);
	                }
	            }
	        }
	    }
	
	}
	
	private function checkPostAdsParam($info){
	    if (empty($info['app_name']) ||  empty($info['platform']) || empty($info['app_key']) ){
	        $this->output(-1,'非法操作');
	    }
	     
	    //检测开屏配置:悬浮窗关闭按钮
	    if(!isset($info['splash_is_show_view'])){
	        $this->output(-1,'聚合请选择开屏配置:悬浮窗关闭按钮');
	    }
	    if($info['splash_is_show_view'] == '1' && intval($info['splash_show_view_time']) <= 0 ){
	        $this->output(-1,'聚合开屏配置:悬浮窗关闭按钮出现时间不合法');
	    }
	     
	    if($info['video_is_use_template'] == '1' && intval($info['video_template_show_time']) <= 0 ){
	        $this->output(-1,'视频中的展示时长时间不合法');
	    }
	    if($info['video_is_use_template'] == '1' && !$info['video_template_id'] ){
	        $this->output(-1,'视频中的模板不合法');
	    }
		if($info['pic_is_use_template'] == '1' && !$info['pic_template_id'] ){
			$this->output(-1,'插页中的模板不合法');
		}
		if($info['splash_is_use_template'] == '1' && intval($info['splash_template_show_time']) <= 0 ){
			$this->output(-1,'开屏中的展示时长时间不合法');
		}
		if($info['splash_is_use_template'] == '1' && !$info['splash_template_id'] ){
			$this->output(-1,'开屏中的模板不合法');
		}
	
	    foreach (Common_Service_Const::$mAdSubTypeDesc as $adType=>$adTypeDesc){
	        if($info[$adTypeDesc.'_life_cycle'] < 0){
	            $this->output(-1,Common_Service_Const::$mAdSubType[$adType].'广告生命周期设置错误');
	        }
	        	
	        foreach ( $info [$adTypeDesc . '_third_party_app_key'] as $key => $val ){
	            if(!Common::validthirdPartyInput($val)){
	                $this->output(-1,Common_Service_Const::$mAdSubType[$adType].'广告商参数中"'. $info [$adTypeDesc . '_ads_id'][$key] .'"的第三方appkey有非法字符，请检查');
	            }
	        }
	        foreach ( $info [$adTypeDesc . '_third_party_secret'] as $key => $val ) {
	            if(!Common::validthirdPartyInput($val)){
	                $this->output(-1,Common_Service_Const::$mAdSubType[$adType].'广告商参数中"'. $info [$adTypeDesc . '_ads_id'][$key] .'"的密钥有非法字符，请检查');
	            }
	            	
	        }
	        foreach ( $info [$adTypeDesc . '_third_party_report_id'] as $key => $val ) {
	            if(!Common::validthirdPartyInput($val)){
	                $this->output(-1,Common_Service_Const::$mAdSubType[$adType].'广告商参数中"'. $info [$adTypeDesc . '_ads_id'][$key] .'"的第三方的reportId有非法字符，请检查');
	            }
	        }
	
	    }
	    foreach (Common_Service_Const::$mAdSubTypeDesc as $adType=>$adTypeDesc){
	        if(!$info[$adTypeDesc.'_ads_id']){
	            continue;
	        }
	        foreach ( $info [$adTypeDesc . '_ads_id'] as $key => $val ) {
	            $params ['app_key'] = $info ['app_key'];
	            $params ['ad_sub_type'] = $adType;
	            $params ['ads_id'] = $val;
	            $result = MobgiApi_Service_AdsAppRelWhitelistModel::getBy($params);
	            $id = $info [$adTypeDesc.'_ads_app_rel_id'][$key];
	            if($result && !$id ){
	                $this->output(-1,'应用”'.$params['app_key'].'"中的'.$params ['ads_id'].'已经保存,请检查配置'.$result['id'].':'.$id);
	            }
	        }
	    }
	     
	}
	
	/**
	 * 获取应用所属平台
	 * @param unknown $platform
	 * @return string
	 */
	private  function getPlatformCn($platform)
	{
	    if($platform=='' || $platform=='0'){
	        return "(T)";
	    }else if($platform == "1"){
	        return '(A)';
	    }else if($platform == '2'){
	        return '(I)';
	    }
	}
	
	
	private function checkUrl($url){
	    if(!preg_match('/http|https:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',$url)){
	        return false;
	    }
	    return true;
	}
	
	
	/**
	 *
	 * Enter description here ...
	 */
	public function deleteAction() {
	    $adsId = $this->getInput('ads_id');
	    $appKey = $this->getInput('app_key');
	    if(empty($adsId)){
	        $this->output ( - 1, '广告商id非法' );
	    }
	    if(empty($appKey)){
	        $this->output ( - 1, 'appKey非法' );
	    }
/* 	    $params['app_key'] = $appKey;
	    $params['ad_type'] =  $this->getInput('ad_type');
	    $result = MobgiApi_Service_FlowAdsRelModel::getsBy($params);
	    if(!empty($result)){
	        foreach ($result as $va){
	            if($va['ads_id'] == $adsId){
	                $this->output ( - 1, '流量配置中配置此广告商' );
	            }
	        }
	    } */
	    $params = array('app_key'=>$appKey,'ad_sub_type'=>$this->getInput('ad_type'),'ads_id'=>$adsId);
	    //删除应用关联
	    if(MobgiApi_Service_AdsAppRelWhitelistModel::getBy($params)){
	        $ret = MobgiApi_Service_AdsAppRelWhitelistModel::deleteBy($params);
	        if(!$ret){
	            $this->output ( - 1, '删除失败1' );
	        }
	    }
	    //删除广告位关联
	    if(MobgiApi_Service_AdsPosRelWhitelistModel::getBy($params)){
	        $ret = MobgiApi_Service_AdsPosRelWhitelistModel::deleteBy($params);
	        if(!$ret){
	            $this->output ( - 1, '删除失败' );
	        }
	    }
	    $this->output ( 0, '删除成功' );
	}
	
	
	
	/**
	 * 更新广告商广告位的开关状态
	 */
	public function updateAdsposrelStateAction(){
	    $id = $this->getInput('ads_pos_rel_id');
	    $state = $this->getInput('state');
	    $ret = MobgiApi_Service_AdsPosRelWhitelistModel::getByID($id);
	    if (!$ret) $this->output(-1, '应用非法');
	    $data['state'] = $state;
	    $ret= MobgiApi_Service_AdsPosRelWhitelistModel::updateByID($data, $id);
	    if (!$ret) $this->output(-1, '操作失败');
	    $this->output(0, '操作成功');
	}
	
	
    
}