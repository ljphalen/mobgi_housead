<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-10-26 18:02:54
 * $Id: GdtDelivery.php 62100 2016-10-26 18:02:54Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class GdtDeliveryController extends Advertiser_BaseController {
      //
//    STATUS 	advertiser_id 	app_id 	app_key 				plan_id
//    ON 	51957 		51957 	c543f7c880841ef18d3dbdf59f5c058b 	251536 代理商帐号
//    ON 	95409 		95409 	c543f7c880841ef18d3dbdf59f5c058b 	251536 子客帐号(token生成使用其代理商的帐号生成)  
//    ON    580614      580614  73df081b96a1bc2afdbc5053163d7580    2702054    
//    PS;修改代码搜索95409 516548 这二个写死的
	public $actions = array(
	    'uploadApkUrl' => '/Advertiser/GdtDelivery/uploadApk',
	    'uploadApkPostUrl' => '/Advertiser/GdtDelivery/uploadApkPost',
	    'uploadUrl' => '/Advertiser/GdtDelivery/uploadImg',
	    'uploadPostUrl' => '/Advertiser/GdtDelivery/uploadImgPost',
	    'uploadImgPost65Url' => '/Advertiser/GdtDelivery/uploadImgPost65',
	    'uploadImgsPost65Url' => '/Advertiser/GdtDelivery/uploadImgsPost65',
	    'uploadOtherUrl' => '/Advertiser/GdtDelivery/uploadOther',
	    'uploadOtherPostUrl' => '/Advertiser/GdtDelivery/uploadOtherPost',
	    'addAdStep1Url'=>'/Advertiser/GdtDelivery/addAdStep1',
	    'addAdStep1PostUrl'=>'/Advertiser/GdtDelivery/addAdStep1Post',
	    'addAdStep2Url'=>'/Advertiser/GdtDelivery/addAdStep2',
	    'addAdStep2PostUrl'=>'/Advertiser/GdtDelivery/addAdStep2Post',
	    'addAdStep3Url'=>'/Advertiser/GdtDelivery/addAdStep3',
	    'addAdStep3PostUrl'=>'/Advertiser/GdtDelivery/addAdStep3Post',
	    'addAdStep4Url'=>'/Advertiser/GdtDelivery/addAdStep4',
	    'addAdStep4PostUrl'=>'/Advertiser/GdtDelivery/addAdStep4Post',
	    'delGdtAdUrl'=>'/Advertiser/GdtDelivery/delGdtAd',

        //广点通推广计划
        'gdtCampaignListUrl' => '/Advertiser/GdtDelivery/gdtCampaignList',
        'gdtCampaignSyncUrl' => '/Advertiser/GdtDelivery/gdtCampaignSync',
        'addGdtCampaignUrl' => '/Advertiser/GdtDelivery/addGdtCampaign',
        'saveGdtCampaignPostUrl' => '/Advertiser/GdtDelivery/saveGdtCampaignPost',

	    'adManageListUrl'=>'/Advertiser/GdtDelivery/index',
        'creativeListUrl'=>'/Advertiser/GdtDelivery/creativeList',
        //广点通定向
        'gdtTargetingListUrl' => '/Advertiser/GdtDelivery/gdtTargetingList',
        'addGdtTargetingUrl' => '/Advertiser/GdtDelivery/addGdtTargeting',
        'saveTargetingPostUrl' => '/Advertiser/GdtDelivery/saveTargetingPost',
        'delGdtTargetingUrl' => '/Advertiser/GdtDelivery/delGdtTargeting',
        
	    'unitListUrl'=>'/Advertiser/GdtDelivery/unitList',
	    'batchUpdateUnitInfoUrl'=>'/Advertiser/GdtDelivery/batchUpdateUnitInfo',
	    'batchUpdateAdInfoUrl'=>'/Advertiser/GdtDelivery/batchUpdateAdInfo',
	    'batchUpdateOriginalityUrl'=>'/Advertiser/GdtDelivery/batchUpdateOriginality',
	    'addOriginalityPostUrl'=>'/Advertiser/GdtDelivery/addOriginalityPost',
	    'saveDirecConfigPostUrl'=>'/Advertiser/GdtDelivery/saveDirecConfigtPost',
	    'getDirecConfigUrl'=>'/Advertiser/GdtDelivery/getDirecConfig',
	    'updateAdNameUrl'=>'/Advertiser/GdtDelivery/updateAdName',
	    'updateOriginalityNameUrl'=>'/Advertiser/GdtDelivery/updateOriginalityName',
	    'updateUnitNameUrl'=>'/Advertiser/GdtDelivery/updateUnitName'
	    
	);
	
	public $unitStatus = array('1'=>'投放中', 2=>'暂停');
	
	public  $adStatus = array(1=>'投放中',2=>'审核中',3=>'审核未通过',4=>'已暂停',5=>'已删除',6=>'已过期');
	
	public  $modifyStatus =  array(1=>'投放中',4=>'暂停');
	
	public $perpage = 10;
	
	private $unitLimitAmount = 500;

	/**
	 * 
	 * 
	 */
	public function indexAction() {
		/*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_View')){
            $this->showMsg(100001, 'permission denied!');
        }
        /*权限校验end*/
        
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        
        if(empty($gdtconfig)){
            $this->showMsg(100001, '没有开通广点通直投');
        }
        $page = intval($this->getInput('page'));
        if(empty($page))$page=1;
        $perpage = $this->perpage;
        $gdt_query=array();
        $gdt_query['advertiser_id'] =$gdtconfig['advertiser_id'];
        $gdt_query['page'] = $page;
        $gdt_query['page_size'] = $perpage;
        $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'adgroup', 'select', $gdt_query);
        if($response['code'] == 0){
            $list = $response['data']['list'];
            $page_info = $response['data']['page_info'];
            $total = $page_info['total_num'];
            if($list){
                $gdt_bid_type  = Common::getConfig('deliveryConfig','gdt_bid_type');
                $gdt_configured_status  = Common::getConfig('deliveryConfig','gdt_configured_status');
                foreach($list as $key=>$item){
                    $tmp = common::get_hours_from_series($item['time_series']);
                    $list[$key]['start_hour'] = $tmp['start_hour'];
                    $list[$key]['end_hour'] = $tmp['end_hour'];
                    $list[$key]['bid_type_cn'] = $gdt_bid_type[$item['bid_type']];
                    $list[$key]['configured_status_cn'] = $gdt_configured_status[$item['configured_status']]['value'];
                    $list[$key]['configured_status_color'] = $gdt_configured_status[$item['configured_status']]['color'];
                    $list[$key]['system_status_cn'] = $gdt_configured_status[$item['system_status']]['value'];
                    $list[$key]['system_status_color'] = $gdt_configured_status[$item['system_status']]['color'];
                }
                
            }
            $this->assign('list', $list);
            $this->assign('pager', Common::getPages($total, $page, $perpage, $this->actions['adManageListUrl'].'/?'));
        }
	}
    
    /**
	 * 创意列表
	 */
	public function  creativeListAction(){
	    /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_creative_View')){
            $this->showMsg(100001, 'permission denied!');
        }
        /*权限校验end*/
	    
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        
        if(empty($gdtconfig)){
            $this->showMsg(100001, '没有开通广点通直投');
        }
        
        //创意类型及创意曝光策略
        $gdt_creative_template  = Common::getConfig('deliveryConfig','gdt_creative_template');
        $gdt_creative_template = Common::resetKey($gdt_creative_template, 'template_id');
        $this->assign('gdt_creative_template', $gdt_creative_template);
        
        $filters = array();
        $adgroup_id = $this->getInput('adgroup_id');
        if($adgroup_id){
            //{"field":"creative_name","operator":"CONTAINS", "value": "广告"}
            $filterItem = array();
            $filterItem['field']='adgroup_id';
            $filterItem['operator']='EQUALS';
            $filterItem['value']=$adgroup_id;
            $filters[] = $filterItem;
        }
        $page = intval($this->getInput('page'));
        if(empty($page))$page=1;
        $perpage = $this->perpage;
        $gdt_query=array();
        $gdt_query['advertiser_id'] =$gdtconfig['advertiser_id'];
        if($filters){
            $gdt_query['filter'] = $filters;
        }
        $gdt_query['page'] = $page;
        $gdt_query['page_size'] = $perpage;
        $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'creative', 'select', $gdt_query);
//        var_dump($response);die;
        if($response['code'] == 0){
            $list = $response['data']['list'];
            $page_info = $response['data']['page_info'];
            $total = $page_info['total_num'];
            $gdt_configured_status  = Common::getConfig('deliveryConfig','gdt_configured_status');
            foreach($list as $key=>$item){
                $list[$key]['configured_status_cn'] = $gdt_configured_status[$item['configured_status']]['value'];
                $list[$key]['configured_status_color'] = $gdt_configured_status[$item['configured_status']]['color'];
                $list[$key]['system_status_cn'] = $gdt_configured_status[$item['system_status']]['value'];
                $list[$key]['system_status_color'] = $gdt_configured_status[$item['system_status']]['color'];
            }
            $this->assign('list', $list);
            $this->assign('pager', Common::getPages($total, $page, $perpage, $this->actions['creativeListUrl'].'/?'));
        }
	}
	
	/**
	 *新增广告参数
	 *
	 */
	public function addAdStep1Action() {
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Add')){
            $this->showMsg(100001, 'permission denied!');
        }
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_creative_Add')){
            $this->showMsg(100002, 'permission denied!');
        }
        /*权限校验end*/

	    $info = $this->getInput(array('oid','clone','step','action'));
        /*权限校验start*/
        if(!$info['oid']){
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Add')){
                $this->showMsg(100001, 'permission denied!');
            }
        }elseif(!$info['clone']){
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Edit')){
                $this->showMsg(100001, 'permission denied!');
            }
        }else{
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Add')) {
                $this->showMsg(100001, 'permission denied!');
            }
        }
        /*权限校验end*/
        
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        if(!$gdtconfig){
            $this->output(1, '没有开通广点通直投权限');
        }

        $campaign_data = Advertiser_Service_GdtCampaignModel::getNameId(array('advertiser_uid'=>$this->userInfo['advertiser_uid']));
        $this->assign('campaign_data', $campaign_data);
        // 获取推广计划名称
//        $plan_data['advertiser_id'] = $gdtconfig['advertiser_id'];
//        $plan_data['campaign_id'] = $gdtconfig['plan_id'];
//        $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'campaign', 'read', $plan_data);
//        if($response['code']==0) {
//            // 映射数据
//            $gdtconfig['plan_name'] = $response['data']['campaign_name'];
//        }
//        $this->assign('gdtconfig', $gdtconfig);
        //标的物类型
        $gdt_product_type  = Common::getConfig('deliveryConfig','gdt_product_type');
        $this->assign('gdt_product_type', $gdt_product_type);
        
        if(!$info['oid'] || $info['step']==1){ //编辑和复制上一步，继续读取缓存数据
            $uId = $this->userInfo['advertiser_uid'];
            $cache = Advertiser_Service_GdtAdgroupModel::getCache();
            $key =Advertiser_Service_GdtAdgroupModel::getGdtAddAdStepKey(1, $uId, intval($info['oid']));
            $result = $cache->get($key);
            if(!isset($result['begin_date']) || !isset($result['end_date']) ){
                $result['begin_date'] = date('Y-m-d');
                $result['end_date'] = date('Y-m-d');
            }
            if(empty($result['end_hour'])){
                $result['end_hour'] =24;
            }
        }else{
            $gdt_query_data['advertiser_id'] = $gdtconfig['advertiser_id'];
            $gdt_query_data['adgroup_id'] = $info['oid'];
            $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'adgroup', 'read', $gdt_query_data);
            if($response['code']==0){
                // 映射数据
                $ad_data = $response['data'];
                if($info['clone']){
                    $ad_data['adgroup_name'] = $ad_data['adgroup_name']."-副本";
                }
                $hour_range = common::get_hours_from_series($response['data']['time_series']);
                $time_series = common::get_week_time_series($hour_range['start_hour'],$hour_range['end_hour']);
                if($response['data']['time_series'] != $time_series){
                    $hour_set_type = 1;
                    $hour_type = 1;
                }else{
                    $hour_set_type = 0;
                    if($hour_range['end_hour'] - $hour_range['start_hour'] != 24){
                        $hour_type = 1;
                    }else{
                        $hour_type = 0;
                    }
                }
                $result = array(
                    'oid' => $info['oid'],
                    'clone' => $info['clone'],
                    'adgroup_name' => $ad_data['adgroup_name'],
                    'campaign_id' => $ad_data['campaign_id'],
                    'begin_date' => $ad_data['begin_date'],
                    'end_date' => $ad_data['end_date'],
                    'hour_type' => $hour_type,
                    'start_hour' => $hour_range['start_hour'],
                    'end_hour' => $hour_range['end_hour'],
                    'end_date' => $ad_data['end_date'],
                    'hour_set_type' => $hour_set_type,
                    'time_series' => $ad_data['time_series'],
                    'product_type' => $ad_data['product_type'],
                    'destination_appstoreid' => $ad_data['product_refs_id'],
                    'destination_android_yybid' => $ad_data['product_refs_id'],
                );
                if($gdt_product_type[$ad_data['product_type']] == 'IOS'){
                    $result['destination_android_yybid'] = '';
                }else{
                    $result['destination_appstoreid'] = '';
                }
            }else{
                $this->output(1, '获取失败:'. $response['message']);
            }
        }
	    $this->assign('result', $result);
	}
	
	/**
	 *post广告参数
	 *
	 */
	public function addAdStep1PostAction() {
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Add')){
            $this->showMsg(100001, 'permission denied!');
        }
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_creative_Add')){
            $this->showMsg(100002, 'permission denied!');
        }
        /*权限校验end*/
        $info = $this->getInput(array('adgroup_name', 'campaign_id', 'campaign_id_e', 'begin_date', 'end_date', 'hour_type', 'start_hour', 'end_hour', 'hour_set_type', 'time_series',  'product_type', 'destination_url', 'oid','clone', 'action', 'product_type', 'destination_appstoreid', 'destination_android_yybid','product_type_e','destination_appstoreid_e','destination_android_yybid_e'));
        $info = $this->checkAdStep1Param($info);
        $uId = $this->userInfo['advertiser_uid'];
        $params['adgroup_name'] = trim($info['adgroup_name']);
        $params['advertiser_uid'] = $uId;
        $result = Advertiser_Service_GdtAdgroupModel::getBy($params);
        if(!$info['oid'] || $info['clone']){ // 新建 或 复制
            if($result){
                if($result['adgroup_id'] == ''){//如果属于未创建成功的，直接删除
                    Advertiser_Service_GdtAdgroupModel::deleteAdgroup($result['id']);
                }else{
                    $this->output(1, '广告名称已经存在，操作失败');
                }
            }
        }elseif($info['oid'] && !$info['clone']){ // 编辑
            if($result){
                if($result['adgroup_id'] != '' && $result['adgroup_id'] !=$info['oid']){//找到不是本身的同名广告
                    $this->output(1, '广告名称已经存在，操作失败');
                }
            }
        }
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        if(!$gdtconfig){
            $this->output(1, '没有开通广点通直投权限');
        }
        //同步标的物,若本地记录已同步了,则无须再同步.
        $gdt_product_data = array();
        if($info['product_type'] == 'PRODUCT_TYPE_APP_IOS'){
            $gdt_product_data['product_refs_id'] = $info['destination_appstoreid'];
            $info['destination_url'] = 'http://itunes.apple.com/cn';
        }else{
            $gdt_product_data['product_refs_id'] = $info['destination_android_yybid'];
            $info['destination_url'] = 'http://app.qq.com';
        }
        if(!Advertiser_Service_GdtProductModel::getBy(array('product_refs_id'=>$gdt_product_data['product_refs_id'], 'product_type'=>$info['product_type']))){
            $gdt_product_data['advertiser_id'] = $gdtconfig['advertiser_id'];
            $gdt_product_data['product_type'] = $info['product_type'];
            $gdt_product_data['product_name'] = 'test_product_name';
            $product_response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'product', 'create', $gdt_product_data);
            $logdata = array();
            $logdata['advertiser_uid'] =  $this->userInfo['advertiser_uid'];
            $logdata['product_refs_id']=$product_response['data']['product_refs_id'];
            $logdata['product_type'] =  $info['product_type'];
            $logdata['config'] = $gdt_product_data;
            $logdata['sync_status'] =  'success';
            $logdata['sync_response'] = $product_response;
            if($product_response['code']==0){
                $logdata['sync_status']='success';
                $logdata['sync_response']=  $product_response;
                Advertiser_Service_GdtProductModel::addProduct($logdata);
            }else{
                $this->output(1, $product_response['message']);
            }
        }
        $info['product_refs_id'] = $gdt_product_data['product_refs_id'];#写入缓存
        $cache = Advertiser_Service_GdtAdgroupModel::getCache(); 
        $key =Advertiser_Service_GdtAdgroupModel::getGdtAddAdStepKey(1, $uId, intval($info['oid']));
        $result = $cache->set($key, $info, Advertiser_Service_GdtAdgroupModel::CACHE_EPIRE);
        if (!$result){
            $this->output(1, '操作失败');
        }
          $this->output(0, '操作成功');
	}
	
	public function addAdStep2Action() {
        $info = $this->getInput(array('oid','clone','step'));
        /*权限校验start*/
        if(!$info['oid']){
            /*权限校验start*/
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Add')){
                $this->showMsg(100001, 'permission denied!');
            }
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_creative_Add')){
                $this->showMsg(100002, 'permission denied!');
            }
            /*权限校验end*/
        }elseif(!$info['clone']){
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Edit')){
                $this->showMsg(100001, 'permission denied!');
            }
        }else{
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Add')){
                $this->showMsg(100001, 'permission denied!');
            }
        }
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        if(!$gdtconfig){
            $this->output(1, '没有开通广点通直投权限');
        }
        /*权限校验end*/
        $uId = $this->userInfo['advertiser_uid'];
        $cache = Advertiser_Service_GdtAdgroupModel::getCache(); 
        $key =Advertiser_Service_GdtAdgroupModel::getGdtAddAdStepKey(1, $uId, intval($info['oid']));
        $cacheData = $cache->get($key);
	    if(!$cacheData){
	        $this->output(1, '操作失败');
	    }
        //创意类型
        $gdt_creative_template  = Common::getConfig('deliveryConfig','gdt_creative_template');
        $this->assign('gdt_creative_template', $gdt_creative_template);
        
	    $creative_selection_type  = Common::getConfig('deliveryConfig','gdt_creative_selection_type');
	    $this->assign('creative_selection_type', $creative_selection_type);
	    
	    if(!$info['oid'] || $info['step']==2){//编辑和复制上一步，继续读取缓存数据
	        $key =Advertiser_Service_GdtAdgroupModel::getGdtAddAdStepKey(2, $uId, intval($info['oid']));
	        $result = $cache->get($key);
            if($info['step']!=2){
                Advertiser_Service_GdtImageModel::getImageUrls($result);
            }
	    }else{
            $gdt_query_data['advertiser_id'] = $gdtconfig['advertiser_id'];
            $gdt_query_data['adgroup_id'] = $info['oid'];
            $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'adgroup', 'read', $gdt_query_data);
            if($response['code']==0){
                // 映射数据
                $copy_data = $response['data'];
                $creative_data = Advertiser_Service_GdtCreativeModel::getCreativebyAdgroupid($this->userInfo['advertiser_uid'],$info['oid'],$info['clone']);
                $result = array(
                    'oid' => $info['oid'],
                    'clone' => $info['clone'],
                    'template_id' => $creative_data[0]['template_id'],
                    'creative_selection_type' => $copy_data['creative_selection_type'],
                    'creative_arr' => $creative_data,
                    'product_type' => $copy_data['product_type'],
                );
            }else{
                $this->output(1, '获取失败:'. $response['message']);
            }
	    }
	    $this->assign('result', $result);
	}
	
	public function addAdStep2PostAction() {
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Add')){
            $this->showMsg(100001, 'permission denied!');
        }
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_creative_Add')){
            $this->showMsg(100002, 'permission denied!');
        }
        /*权限校验end*/
        
	    $info = $this->getInput(array('template_id', 'creative_selection_type', 'creative_arr', 'creative_name','creative_desc',
            'template65_img', 'template65_ourimageid',
            'template271_img1', 'template271_ourimageid1', 'template271_img2','template271_ourimageid2','template271_img3', 'template271_ourimageid3',
            'template351_img1','template351_ourimageid1','template351_img2','template351_ourimageid2','template351_video','template351_ourvideoid',
            'oid','clone','template_id_e'));
        if($info['oid'] && !$info['clone']){
            $info['template_id'] = $info['template_id_e'];
            unset($info['template_id_e']);
        }
//        $this->output(1, '获取失败:'. $info['template65_img']);
	    $info = $this->checkStep2Param($info);
        $uId = $this->userInfo['advertiser_uid'];
        if(!$info['oid'] || $info['clone']){
            $params['creative_name'] = $info['creative_name'];
            $params['advertiser_uid'] = $uId;
            $result = Advertiser_Service_GdtCreativeModel::getBy($params);
            if($result){
                $this->output(1, '创意名称已经存在，操作失败');
            }
        }
        $cache = Advertiser_Service_GdtAdgroupModel::getCache(); 
        $key =Advertiser_Service_GdtAdgroupModel::getGdtAddAdStepKey(2, $uId, intval($info['oid']));
	    $result = $cache->set($key, $info, Advertiser_Service_GdtAdgroupModel::CACHE_EPIRE);	  
	    $this->output(0, '操作成功');
	    
	}

	public function addAdStep3Action() {
	    $info = $this->getInput(array('oid','clone','step'));
        /*权限校验start*/
        if(!$info['oid']){
            /*权限校验start*/
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Add')){
                $this->showMsg(100001, 'permission denied!');
            }
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_creative_Add')){
                $this->showMsg(100002, 'permission denied!');
            }
            /*权限校验end*/
        }elseif(!$info['clone']){
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Edit')){
                $this->showMsg(100001, 'permission denied!');
            }
        }else{
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Edit')){
                $this->showMsg(100001, 'permission denied!');
            }
        }
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        if(!$gdtconfig){
            $this->output(1, '没有开通广点通直投权限');
        }
        /*权限校验end*/ 
	    $uId = $this->userInfo['advertiser_uid'];
        $cache = Advertiser_Service_GdtAdgroupModel::getCache(); 
        $key =Advertiser_Service_GdtAdgroupModel::getGdtAddAdStepKey(2, $uId, intval($info['oid']));
	    $step2CacheData = $cache->get($key);
	    if(!$step2CacheData){
	        $this->output(1, '操作失败');
	    }
        if(!$info['oid'] || $info['step']==3) {//编辑和复制上一步，继续读取缓存数据
            $key = Advertiser_Service_GdtAdgroupModel::getGdtAddAdStepKey(3, $uId, intval($info['oid']));
            $result = $cache->get($key);
        }else{
            $gdt_query_data['advertiser_id'] = $gdtconfig['advertiser_id'];
            $gdt_query_data['adgroup_id'] = $info['oid'];
            $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'adgroup', 'read', $gdt_query_data);
            if($response['code']==0){
                // 映射数据
                $copy_data = $response['data'];
                $result = array(
                    'oid' => $info['oid'],
                    'clone' => $info['clone'],
                    'targeting_id' => $copy_data['targeting_id'],
                    'bid_type' => $copy_data['bid_type'],
                    'bid_amount' => $copy_data['bid_amount'],
                );
            }else{
                $this->output(1, '获取失败:'. $response['message']);
            }
        }
	    
	    $params['advertiser_uid'] = $this->userInfo['advertiser_uid'];
	    $targetinglist = Advertiser_Service_GdtTargetingModel::getsBy($params);
	    $targetinglist = Common::resetKey($targetinglist, 'targeting_id');
	    $this->assign('targetinglist', $targetinglist);
	    $this->assign('result', $result);
        
        $gdt_bid_type  = Common::getConfig('deliveryConfig','gdt_bid_type');
	    $this->assign('gdt_bid_type', $gdt_bid_type);
	}
	
	
	public function addAdStep3PostAction() {
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Add')){
            $this->showMsg(100001, 'permission denied!');
        }
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_creative_Add')){
            $this->showMsg(100002, 'permission denied!');
        }
        /*权限校验end*/

	    $info = $this->getInput(array('oid','clone','targeting_id','bid_type','bid_amount'));
	    $info = $this->checkStep3Param($info);
	    $uId = $this->userInfo['advertiser_uid'];
        $cache = Advertiser_Service_GdtAdgroupModel::getCache(); 
        $key =Advertiser_Service_GdtAdgroupModel::getGdtAddAdStepKey(3, $uId, intval($info['oid']));
	    $result = $cache->set($key, $info, Advertiser_Service_GdtAdgroupModel::CACHE_EPIRE);
	    $this->output(0, '操作成功');
	}
	
	public function addAdStep4Action() {
	    /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Add')){
            $this->showMsg(100001, 'permission denied!');
        }
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_creative_Add')){
            $this->showMsg(100002, 'permission denied!');
        }
        /*权限校验end*/
        
	    $info = $this->getInput(array('oid','clone'));
        /*权限校验start*/
        if(!$info['oid']){
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Add')){
                $this->showMsg(100001, 'permission denied!');
            }
        }elseif(!$info['clone']){
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Edit')){
                $this->showMsg(100001, 'permission denied!');
            }
        }else{
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Edit')){
                $this->showMsg(100001, 'permission denied!');
            }
        }
        /*权限校验end*/
        
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        if(!$gdtconfig){
            $this->output(1, '没有开通广点通直投权限');
        }
        $this->assign('gdtconfig', $gdtconfig);
        $this->assign('result', $info);
        
        //创意类型及创意曝光策略
        $gdt_creative_template  = Common::getConfig('deliveryConfig','gdt_creative_template');
        $gdt_creative_template = Common::resetKey($gdt_creative_template, 'template_id');
        $this->assign('gdt_creative_template', $gdt_creative_template);
	    $creative_selection_type  = Common::getConfig('deliveryConfig','gdt_creative_selection_type');
	    $this->assign('creative_selection_type', $creative_selection_type);

        $params['advertiser_uid'] = $this->userInfo['advertiser_uid'];
	    $targetinglist = Advertiser_Service_GdtTargetingModel::getsBy($params);
	    $targetinglist = Common::resetKey($targetinglist, 'targeting_id');
        $this->assign('targetinglist', $targetinglist);
        $gdt_bid_type  = Common::getConfig('deliveryConfig','gdt_bid_type');
	    $this->assign('gdt_bid_type', $gdt_bid_type);
        
	    $uId = $this->userInfo['advertiser_uid'];
	    
	    $cache = Advertiser_Service_GdtAdgroupModel::getCache(); 
        $key =Advertiser_Service_GdtAdgroupModel::getGdtAddAdStepKey(1, $uId, intval($info['oid']));
	    $step1Reslut = $cache->get($key);
	     $key =Advertiser_Service_GdtAdgroupModel::getGdtAddAdStepKey(2, $uId, intval($info['oid']));
	    $step2Reslut = $cache->get($key);
	    $key =Advertiser_Service_GdtAdgroupModel::getGdtAddAdStepKey(3, $uId, intval($info['oid']));
	    $step3Reslut = $cache->get($key);
	    $this->assign('step1Reslut', $step1Reslut);
	    $this->assign('step2Reslut', $step2Reslut);
	    $this->assign('step3Reslut', $step3Reslut);
//        var_dump($step1Reslut,$step2Reslut,$step3Reslut);
	}
	
	public function addAdStep4PostAction() {
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Add')){
            $this->showMsg(100001, 'permission denied!');
        }
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_creative_Add')){
            $this->showMsg(100002, 'permission denied!');
        }
        /*权限校验end*/
	 
	    $info = $this->getInput(array('oid','clone'));
        /*权限校验start*/
        if(!$info['oid']){
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Add')){
                $this->output(1, 'permission denied!');
            }
        }elseif(!$info['clone']){
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Edit')){
                $this->output(1, 'permission denied!');
            }
        }else{
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Edit')){
                $this->output(1, 'permission denied!');
            }
        }
        /*权限校验end*/
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        if(!$gdtconfig){
            $this->output(1, '没有开通广点通直投权限');
        }
	    $uId = $this->userInfo['advertiser_uid'];
        $cache = Advertiser_Service_GdtAdgroupModel::getCache(); 
        $key1 =Advertiser_Service_GdtAdgroupModel::getGdtAddAdStepKey(1, $uId, intval($info['oid']));
        $step1Reslut = $cache->get($key1);
        $key2 =Advertiser_Service_GdtAdgroupModel::getGdtAddAdStepKey(2, $uId, intval($info['oid']));
        $step2Reslut = $cache->get($key2);
        $key3 =Advertiser_Service_GdtAdgroupModel::getGdtAddAdStepKey(3, $uId, intval($info['oid']));
        $step3Reslut = $cache->get($key3);
	    if(!$step1Reslut || !$step2Reslut || !$step3Reslut){
	        $this->output(1, '操作失败，请返回，重新创建');
	    }

        $gdt_creative_template  = Common::getConfig('deliveryConfig','gdt_creative_template');
        $gdt_creative_template = Common::resetKey($gdt_creative_template, 'template_id');

        $creativeCheckParam = array();
        $creativeCheckParam['creative_name'] = $step2Reslut['creative_name'];
        $creativeCheckParam['advertiser_uid'] = $uId;
        if(!$info['oid'] || $info['clone']){
            if(Advertiser_Service_GdtAdgroupModel::getAdgroupByName($step1Reslut['adgroup_name'],$uId)){
                $this->output(-1, '广告名称已经被使用');
            }
            $result = Advertiser_Service_GdtCreativeModel::getBy($creativeCheckParam);
            if($result && $step2Reslut['template_id'] != 65){
                $this->output(1, '创意名称已经被使用');
            }
            $ourAdgroupId = Advertiser_Service_GdtAdgroupModel::addAdgroup(array('adgroup_name'=>$step1Reslut['adgroup_name'], 'advertiser_uid'=>$uId,'local_config'=>  array_merge($step1Reslut, $step2Reslut, $step3Reslut)));
            if (!$ourAdgroupId) $this->output(-1, '创建广告失败');
        }elseif($info['oid'] && !$info['clone']){
            // 编辑下判断创意名称是否重复
            $result = Advertiser_Service_GdtCreativeModel::getsBy($creativeCheckParam);
            if(!empty($result) && $step2Reslut['template_id'] != 65){
                if(count($result)>1){
                    $this->output(1, '创意名称已经被使用（异常存在多个同名创意）');
                }elseif($result[0]['adgroup_id']!=$info['oid']){
                    $this->output(1, '创意名称已经被使用');
                }
            }

        }
        $gdt_query_data = array();
        $gdt_query_data['advertiser_id'] = $gdtconfig['advertiser_id'];
        $gdt_query_data['campaign_id'] = $step1Reslut['campaign_id'];
        $gdt_query_data['targeting_id'] = $step3Reslut['targeting_id'];
        $gdt_query_data['adgroup_name'] = $step1Reslut['adgroup_name'];
        $gdt_query_data['bid_type'] = $step3Reslut['bid_type'];
        $gdt_query_data['bid_amount'] = $step3Reslut['bid_amount'];
        $gdt_query_data['begin_date'] = $step1Reslut['begin_date'];
        $gdt_query_data['end_date'] = $step1Reslut['end_date'];
        $gdt_query_data['site_set'] = array($gdt_creative_template[$step2Reslut['template_id']]['site_set']);
        if($step1Reslut['hour_type']==0){
            $gdt_query_data['time_series'] = common::get_week_time_series(0, 24);
        }else{
            if($step1Reslut['hour_set_type']==0){
                $gdt_query_data['time_series'] = common::get_week_time_series($step1Reslut['start_hour'], $step1Reslut['end_hour']);
            }else{
                // 时间区段不满336位，自动填充0
                $gdt_query_data['time_series'] = common::update_time_series_add_zero($step1Reslut['time_series']);
            }
        }
        $gdt_query_data['product_type'] = $step1Reslut['product_type'];
        $gdt_query_data['product_refs_id'] = $step1Reslut['product_refs_id'];
        $gdt_query_data['creative_selection_type'] = $step2Reslut['creative_selection_type'];
        $gdt_query_data['combination_type'] = 'creative_combination_type';
        $gdt_query_data['outer_adgroup_id'] = $ourAdgroupId;

        if($info['oid'] && !$info['clone']){
            $ad_update_data = array();
            $ad_update_data['advertiser_id'] = $gdtconfig['advertiser_id'];
            $ad_update_data['adgroup_id'] = $info['oid'];
            $response = Advertiser_Service_GdtdirectconfigModel::curl($uId, 'adgroup', 'read', $ad_update_data);
            if($response['code']==0){
                // 获取数据后，和本次修改的数据进行匹配，看哪些做了修改
                $ad_data = $response['data'];
                if($ad_data['adgroup_name']!=$gdt_query_data['adgroup_name']){
                    if(Advertiser_Service_GdtAdgroupModel::getAdgroupByName($gdt_query_data['adgroup_name'],$uId)){
                        $this->output(-1, '广告名称已经被使用');
                    }
                    $ad_update_data['adgroup_name']=$gdt_query_data['adgroup_name'];
                }
                if($ad_data['begin_date']!=$gdt_query_data['begin_date']) $ad_update_data['begin_date']=$gdt_query_data['begin_date'];
                if($ad_data['end_date']!=$gdt_query_data['end_date']) $ad_update_data['end_date']=$gdt_query_data['end_date'];
                if($ad_data['time_series']!=$gdt_query_data['time_series']) $ad_update_data['time_series']=$gdt_query_data['time_series'];
                if($ad_data['targeting_id']!=$gdt_query_data['targeting_id']) $ad_update_data['targeting_id']=$gdt_query_data['targeting_id'];
                if($ad_data['bid_type']!=$gdt_query_data['bid_type']) $ad_update_data['bid_type']=$gdt_query_data['bid_type'];
                if($ad_data['bid_amount']!=$gdt_query_data['bid_amount']) $ad_update_data['bid_amount']=$gdt_query_data['bid_amount'];
//                if($ad_data['site_set']){}
                if($ad_data['creative_selection_type']!=$gdt_query_data['creative_selection_type']) $ad_update_data['creative_selection_type']=$gdt_query_data['creative_selection_type'];
//                if($ad_data['creative_template_id']){}
                $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'adgroup', 'update', $ad_update_data);
                if($response['code']==0){
                    $logdata = array();
                    $logdata['sync_status']='success';
                    $logdata['sync_response']=  $response;
                    $logdata['adgroup_name']=  $gdt_query_data['adgroup_name'];
                    $logdata['local_config']=  array_merge($step1Reslut, $step2Reslut, $step3Reslut);
                    Advertiser_Service_GdtAdgroupModel::updateAdgroupByParams($logdata, array('adgroup_id'=>$info['oid']));
//                    $this->output(0, '更新成功');
                }
            }else{
                $this->output(1, '获取失败:'. $response['message']);
            }
        }else{
            $response = Advertiser_Service_GdtdirectconfigModel::curl($uId, 'adgroup', 'create', $gdt_query_data);
            $logdata = array();
            $logdata['adgroup_id']=$response['data']['adgroup_id'];
            $logdata['config'] = $gdt_query_data;
            if($response['code']==0){
                $logdata['sync_status']='success';
                $logdata['sync_response']=  $response;
                Advertiser_Service_GdtAdgroupModel::updateAdgroup($logdata, $ourAdgroupId);
            }else{
                Advertiser_Service_GdtAdgroupModel::deleteAdgroup($ourAdgroupId);
            }
        }

        //创建广告成功
        if($response['code']==0){
            //同步创意
            $gdt_creative_data = array();
            $gdt_creative_data['advertiser_id'] = $gdtconfig['advertiser_id'];
            $gdt_creative_data['campaign_id'] = $gdtconfig['plan_id'];
            $gdt_creative_data['campaign_id'] = $step1Reslut['campaign_id'];
            $gdt_creative_data['adgroup_id'] = $response['data']['adgroup_id'];//228548
            $gdt_creative_data['creative_name'] = $step2Reslut['creative_name'];
            $gdt_creative_data['creative_template_id'] = $step2Reslut['template_id'];
            $gdt_creative_data['destination_url'] = $step1Reslut['destination_url'];
            // 创意类型 65 需要批量处理
            if($step2Reslut['template_id']==65){
                $gdt_creative_data['creative_arr'] = $step2Reslut['creative_arr'];
                $success_num = $this->craete_multiple_creative_65($gdt_creative_data);
                Advertiser_Service_GdtAdgroupModel::deleteAdGroupKey($key1, $key2, $key3);//清理缓存
                if($success_num == 0){
                    //创意没有一个成功，并且是新建广告，则删除广点通创建的广告,删除本地的广告和本地创意
                    Advertiser_Service_GdtdirectconfigModel::curl($uId, 'adgroup', 'delete', array('advertiser_id'=>$gdtconfig['advertiser_id'],'adgroup_id'=>$gdt_creative_data['adgroup_id']));
                    Advertiser_Service_GdtAdgroupModel::deleteAdgroup($ourAdgroupId);
                    $this->output(-1, '没有一个创意创建成功，请核查素材');
                }
                $this->output(0, '操作成功，新增创意'.$success_num.'个',$gdt_creative_data['adgroup_id']);
            }else if($info['oid'] && !$info['clone']){
                $gdt_creative_data['creative_elements'] = $this->get_creative_elements($step1Reslut, $step2Reslut);
                // 查找出要进行修改的创意id
                $creative_data = Advertiser_Service_GdtCreativeModel::getCreativebyAdgroupid($info['oid']);
                $creative_config = $creative_data['config'];
                $creative_update_data = array();
                $creative_update_data['advertiser_id'] = $gdtconfig['advertiser_id'];
                $creative_update_data['creative_id'] = $creative_data['creative_id'];
                if($creative_data['creative_name']!=$gdt_creative_data['creative_name']) $creative_update_data['creative_name']=$gdt_creative_data['creative_name'];
                if($creative_config['creative_elements']!==$gdt_creative_data['creative_elements']) $creative_update_data['creative_elements']=$gdt_creative_data['creative_elements'];
                if($creative_config['destination_url']!==$gdt_creative_data['destination_url']) $creative_update_data['destination_url']=$gdt_creative_data['destination_url'];

                $creative_response = Advertiser_Service_GdtdirectconfigModel::curl($uId, 'creative', 'update', $creative_update_data);
                Advertiser_Service_GdtCreativeModel::updateCreative($logdata, $creative_data['id']);
                $logdata = array();
                $logdata['creative_id']=$creative_response['data']['creative_id'];
                $logdata['config'] = $gdt_creative_data;
                if($creative_response['code']==0){
                    $logdata['sync_status']='success';
                    $logdata['sync_response']=  $creative_response;
                    Advertiser_Service_GdtCreativeModel::updateCreative($logdata, $creative_data['id']);
                    Advertiser_Service_GdtAdgroupModel::deleteAdGroupKey($key1, $key2, $key3);//清理缓存
                    $this->output(0, '操作成功',$gdt_creative_data['adgroup_id']);
                }else{
                    $this->output($creative_response['code'], $creative_response['message']);
                }
            }else{
                $gdt_creative_data['creative_elements'] = $this->get_creative_elements($step1Reslut, $step2Reslut);
                $our_creative_id = Advertiser_Service_GdtCreativeModel::addCreative(array('creative_name'=>$step2Reslut['creative_name'], 'advertiser_uid'=>$uId, 'adgroup_id'=>$gdt_creative_data['adgroup_id']));
                if (!$our_creative_id) $this->output(-1, '操作失败');
                if($our_creative_id){
                    $gdt_creative_data['outer_creative_id'] = $our_creative_id;
                }
                $creative_response = Advertiser_Service_GdtdirectconfigModel::curl($uId, 'creative', 'create', $gdt_creative_data);
                $logdata = array();
                $logdata['creative_id']=$creative_response['data']['creative_id'];
                $logdata['config'] = $gdt_creative_data;
                if($creative_response['code']==0){
                    $logdata['sync_status']='success';
                    $logdata['sync_response']=  $creative_response;
                    Advertiser_Service_GdtCreativeModel::updateCreative($logdata, $our_creative_id);
                    Advertiser_Service_GdtAdgroupModel::deleteAdGroupKey($key1, $key2, $key3);//清理缓存
                    $this->output(0, '操作成功',$gdt_creative_data['adgroup_id']);
                }else{
                    $logdata['sync_status']='failed';
                    $logdata['sync_response']=  $creative_response;
                    $logdata['del']=  1;
                    Advertiser_Service_GdtCreativeModel::updateCreative($logdata, $our_creative_id);
                    //创意同步失败,则删除广点通创建的广告,删除本地的广告和本地创意
                    Advertiser_Service_GdtdirectconfigModel::curl($uId, 'adgroup', 'delete', array('advertiser_id'=>$gdtconfig['advertiser_id'],'adgroup_id'=>$gdt_creative_data['adgroup_id']));
                    Advertiser_Service_GdtAdgroupModel::deleteAdgroup($ourAdgroupId);
                    Advertiser_Service_GdtCreativeModel::deleteCreative($our_creative_id);
                    $this->output($creative_response['code'], $creative_response['message']);
                }
            }
        }else{
            $this->output(1, '广点通同步失败:'. $response['message']);
        }
	}


    /**
     * 删除定向
     */
    public function delGdtAdAction(){
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Adgroup_Del')){
            $this->output(1, 'permission denied!');
        }
        /*权限校验end*/

        $adgroup_id=$this->getInput('adgroup_id');
        if(empty($adgroup_id)){
            $this->output(1, '参数错误');
        }
        $adgroupInfo = Advertiser_Service_GdtAdgroupModel::getAdgroup($adgroup_id);
        if($adgroupInfo['advertiser_uid']!=$this->userInfo['advertiser_uid']){
            $this->output(-1, '只能删除自己创建的广告');
        }

        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        if(!$gdtconfig){
            $this->output(1, '没有开通广点通直投权限');
        }

        $gdt_info = array();
        $gdt_info['adgroup_id']=$adgroup_id;
        $gdt_info['advertiser_id'] = $gdtconfig['advertiser_id'];
        $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'adgroup', 'delete', $gdt_info);

        $logdata = array();
        $logdata['adgroup_id']=$response['data']['adgroup_id'];
        $logdata['config'] = json_encode($gdt_info);
        if($response['code']==0){
            $logdata['del']=  1;
            Advertiser_Service_GdtAdgroupModel::updateAdgroup($logdata, $adgroupInfo['id']);
            $this->output(0, '操作成功');
        }else{
            $this->output(-1, '操作失败');
        }
    }

    private function craete_multiple_creative_65($gdt_creative_data){
        $creative_arr = $gdt_creative_data['creative_arr'];
        unset($gdt_creative_data['creative_arr']);
        $uId = $this->userInfo['advertiser_uid'];
        $success_num = 0;
        foreach($creative_arr as $key => $creative_val) {
            if($creative_val['is_edit']){// 暂时不提供修改功能
                $success_num ++;
                continue;
            }
            $gdt_creative_data['creative_name'] = $creative_val['creative_name'];
            $gdt_creative_data['creative_elements'] = $this->get_creative_elements_65($creative_val);
            $our_creative_id = Advertiser_Service_GdtCreativeModel::addCreative(array('creative_name' => $creative_val['creative_name'],'advertiser_uid' => $uId, 'adgroup_id' => $gdt_creative_data['adgroup_id']));
            if (!$our_creative_id){
                continue;
            }
            if ($our_creative_id) {
                $gdt_creative_data['outer_creative_id'] = $our_creative_id;
            }
//            var_dump($gdt_creative_data);
            $creative_response = Advertiser_Service_GdtdirectconfigModel::curl($uId, 'creative', 'create', $gdt_creative_data);
//            var_dump($creative_response);
            $logdata = array();
            $logdata['creative_id'] = $creative_response['data']['creative_id'];
            $logdata['config'] = $gdt_creative_data;
            if ($creative_response['code'] == 0) {
                $logdata['sync_status'] = 'success';
                $logdata['sync_response'] = $creative_response;
                Advertiser_Service_GdtCreativeModel::updateCreative($logdata, $our_creative_id);
                $success_num ++;
            } else {
                Advertiser_Service_GdtCreativeModel::deleteCreative($our_creative_id);
                continue;
            }
        }
        return $success_num;
    }

    private function get_creative_elements_65($creative_val){
        $ourimageid = $creative_val['template65_ourimageid'];
        //todo 线上使用需要先上传512*512格式, 400K以内的图片作为商标
        $corporate= array();
        $corporate['corporate_name'] = '乐逗游戏';
            $corporate['corporate_img'] = '580614:2b3637da4aa9848aeec5381b8d5991a2';
        $element = array();
        if($ourimageid){
            $imageInfo = Advertiser_Service_GdtImageModel::getImage($ourimageid);
            $element['image'] = $imageInfo['image_id'];
        }else{
            $element['image'] = $creative_val['template65_img'];
        }
        $element['title'] = $creative_val['creative_desc'];
        $element['corporate'] = $corporate;
        $json_str = json_encode($element);
        return $json_str;
    }

    private function get_creative_elements($step1Reslut,$step2Reslut){
        $template_id = $step2Reslut['template_id'];
        if($template_id == 65){
            $ourimageid = $step2Reslut['template65_ourimageid'];
            //todo 线上使用需要先上传512*512格式, 400K以内的图片作为商标
            $corporate= array();
            $corporate['corporate_name'] = '乐逗游戏';
            $corporate['corporate_img'] = '580614:2b3637da4aa9848aeec5381b8d5991a2';
            $element = array();
            if($ourimageid){
                $imageInfo = Advertiser_Service_GdtImageModel::getImage($ourimageid);
                $element['image'] = $imageInfo['image_id'];
            }else{
                $element['image'] = $step2Reslut['template65_img'];
            }
            $element['title'] = $step2Reslut['creative_desc'];
            $element['corporate'] = $corporate;
            $json_str = json_encode($element);
            return $json_str;
        }else if($template_id == 271){
            $ourimageid1 = $step2Reslut['template271_ourimageid1'];
            $ourimageid2 = $step2Reslut['template271_ourimageid2'];
            $ourimageid3 = $step2Reslut['template271_ourimageid3'];
            $element = array();
            $element_story= array();
            if($ourimageid1 != '' && $ourimageid2 != '' && $ourimageid3 != ''){
                $imageInfo1 = Advertiser_Service_GdtImageModel::getImage($ourimageid1);
                $imageInfo2 = Advertiser_Service_GdtImageModel::getImage($ourimageid2);
                $imageInfo3 = Advertiser_Service_GdtImageModel::getImage($ourimageid3);
                $element_story[0] = array('image'=>$imageInfo1['image_id'], 'url'=>$step1Reslut['destination_url']);
                $element_story[1] = array('image'=>$imageInfo2['image_id'], 'url'=>$step1Reslut['destination_url']);
                $element_story[2] = array('image'=>$imageInfo3['image_id'], 'url'=>$step1Reslut['destination_url']);
            }else{
                $element_story[0] = array('image'=>$step2Reslut['template271_img1'], 'url'=>$step1Reslut['destination_url']);
                $element_story[1] = array('image'=>$step2Reslut['template271_img2'], 'url'=>$step1Reslut['destination_url']);
                $element_story[2] = array('image'=>$step2Reslut['template271_img3'], 'url'=>$step1Reslut['destination_url']);
            }
//            $element['element_story'] = json_encode($element_story);
            $element['element_story'] = $element_story;
            $element['title'] = $step2Reslut['creative_name'];
            $element['sortable'] = 0;//自动优化
            $json_str = json_encode($element);
            return $json_str;
        }else if($template_id == 351){
            $ourimageid1 = $step2Reslut['template351_ourimageid1'];
            $ourimageid2 = $step2Reslut['template351_ourimageid2'];
            $ourvideoid = $step2Reslut['template351_ourvideoid'];
            $element = array();
            if($ourimageid1 != '' && $ourimageid2 != '' && $ourvideoid != ''){
                $imageInfo1 = Advertiser_Service_GdtImageModel::getImage($ourimageid1);
                $imageInfo2 = Advertiser_Service_GdtImageModel::getImage($ourimageid2);
                $videoinfo = Advertiser_Service_GdtMediaModel::getMedia($ourvideoid);
                $element['image'] = $imageInfo1['image_id'];
                $element['image2'] = $imageInfo2['image_id'];
                $element['video'] = $videoinfo['media_id'];
            }else{
                $element['image'] = $step2Reslut['template351_img1'];
                $element['image2'] = $step2Reslut['template351_img2'];
                $element['video'] = $step2Reslut['template351_video'];
            }
            $element['title'] = $step2Reslut['creative_name'];
            $element['description'] = $step2Reslut['creative_desc'];
            $json_str = json_encode($element);
            return $json_str;
        }
    }
	
	private function checkStep3Param($info){
//        'targeting_id','bid_type','bid_amount'
        if(empty($info['targeting_id'])){
            $this->output(1, '请选择定向');
        }
        
        if(empty($info['bid_type'])){
            $this->output(1, '请选择计费类型');
        }
        
	    if(!is_numeric($info['bid_amount']) || intval($info['bid_amount']) <= 0){
	         $this->output(1, '出价为大于零数字类型');
	    }
        
        if($info['bid_type'] =='BID_TYPE_CPC'){
            if(intval($info['bid_amount'])<10 || intval($info['bid_amount']) > 10000 ){
                $this->output(1, 'CPC计费出价在10分到10000分之间');
            }
        }
        
	    $info['bid_amount']  = intval($info['bid_amount']);
	    return $info;
	}
	
    private function checkStep2Param($info){

        if(empty($info['template_id'])){
	        $this->output(1, '请选择创意类型');
	    }
        
        if(empty($info['creative_selection_type'])){
	        $this->output(1, '请选择创意曝光策略');
	    }

        if($info['template_id'] == 65){
            if(!trim($info['creative_arr'])){
                $this->output(1, '请添加创意');
            }
            //将htmlentities()函数转义过的字符串转成html标签
            $info['creative_arr'] = html_entity_decode($info['creative_arr']);
            $info['creative_arr'] = json_decode($info['creative_arr'], true);
            $creative_name_arr = array();
            foreach($info['creative_arr'] as $key =>$value){
                if(!trim($value['creative_name'])){
                    $this->output(1, '第'.($key+1).'个创意的标题为空');
                }
                if(!trim($value['creative_desc'])){
                    $this->output(1, '第'.($key+1).'个创意的描述为空');
                }
                $creative_name_arr[$key] = $value['creative_name'];
                if(!$value['template65_img']){
                    $this->output(1, '第'.($key+1).'个创意没有上传');
                }
                // 查询创意名是否重复
                if(!$value['is_edit']){
                    $result = Advertiser_Service_GdtCreativeModel::getBy(array('creative_name'=>$value['creative_name'],'advertiser_uid'=>$this->userInfo['advertiser_uid'],'del'=>0));
                    if($result){
                        $this->output(1,  '第'.($key+1).'个创意名称已经被使用');
                    }
                }
            }
            // 检验提交表单是否有重复的创意名
            if(count($info['creative_arr']) != count(array_unique($creative_name_arr))){
                $this->output(1,  '提交的创意中存在有同名创意');
            }
        }else{
            if(!trim($info['creative_name'])){
                $this->output(1, '创意标题不能为空');
            }
            $info['creative_name']  = trim($info['creative_name']);
            if(Common::strLength($info['creative_name'])>=20){
                $this->output(1, '创意标题最多20字符');
            }
            $info['creative_desc']  = trim($info['creative_desc']);
            if(Common::strLength($info['creative_desc'])>=30){
                $this->output(1, '创意描述最多30字符');
            }
            if($info['template_id'] == 271 && (!$info['template271_img1'] || !$info['template271_img2']|| !$info['template271_img3']) ){
                $this->output(1, '创意没有上传');
            }

            if($info['template_id'] == 351 && (!$info['template351_img1'] ||!$info['template351_img2'] || !$info['template351_video']) ){
                $this->output(1, '创意没有上传');
            }
        }
        return $info;
    }

    private function checkAdStep1Param($info){
        if(!$info['product_type']){
            $info['product_type'] = $info['product_type_e'];
            unset($info['product_type_e']);
        }
        if(!$info['destination_appstoreid']){
            $info['destination_appstoreid'] = $info['destination_appstoreid_e'];
            unset($info['destination_appstoreid_e']);
        }
        if(!$info['destination_android_yybid']){
            $info['destination_android_yybid'] = $info['destination_android_yybid_e'];
            unset($info['destination_android_yybid_e']);
        }
        if(!$info['campaign_id']){
            $info['campaign_id'] = $info['campaign_id_e'];
            unset($info['campaign_id_e']);
        }
        if(!$info['oid'] || $info['clone']) {
            if (!$info['campaign_id']) {
                $this->output(1, '请选择推广计划');
            }
        }
        if(!trim($info['adgroup_name'])){
            $this->output(1, '广告名称不能为空');
        }
        if(Common::strLength($info['adgroup_name']) >= 30){
            $this->output(1, '广告名称不能太长');
        }
        $info['adgroup_name']  = trim($info['adgroup_name']);
        
        if( strtotime($info['begin_date']) > strtotime($info['end_date']) ){
            $this->output(1, '开始时间不能小于结束时间');
        }
        
        if($info['hour_type'] != '0' &&  $info['hour_type'] != '1'){
            $this->output(1, '设置投放时段没有选中');
        }
        if($info['hour_type'] == 1){
            if(($info['hour_set_type'] == 0) && ($info['start_hour'] >=$info['end_hour'])){
                $this->output(1, '开始时间不能早于结束时间');
            }
            if(($info['hour_set_type'] == 1) && $info['time_series'] == ''){
                $this->output(1, '请选择详细时间区段');
            }
        }

        if(!$info['oid'] || $info['clone']){ // 编辑时候无需验证
            $gdt_product_type  = Common::getConfig('deliveryConfig','gdt_product_type');

            if( !($gdt_product_type[$info['product_type']]) ){
                $this->output(1, '广告目标类型错误');
            }
            if($info['product_type'] == 'PRODUCT_TYPE_APP_IOS' && empty($info['destination_appstoreid'])){
                $this->output(1, '请输入appstoreid');
            }
            if($info['product_type'] == 'PRODUCT_TYPE_APP_IOS' && strlen($info['destination_appstoreid'])>128){
                $this->output(1, '标的物id长度必须小于 128 个英文字符');
            }
            if($info['product_type'] == 'PRODUCT_TYPE_APP_ANDROID_OPEN_PLATFORM' && empty($info['destination_android_yybid'])){
                $this->output(1, '请输入应用宝id');
            }
            if($info['product_type'] == 'PRODUCT_TYPE_APP_ANDROID_OPEN_PLATFORM' && strlen($info['destination_android_yybid'])>128){
                $this->output(1, '标的物id长度必须小于 128 个英文字符');
            }
        }
//        if($info['product_type']!='PRODUCT_TYPE_LINK'){
//            $this->output(1, '广告目标类型错误');
//        }
//        if(!trim($info['destination_url'])){
//            $this->output(1, '链接地址不能为空');
//        }
//        if(!preg_match('/^(http|https)/i', $info['destination_url'])){
//            $this->output(1, '链接地址不规范,以http,https开头');
//        }
        
        return  $info;
    }
	

	
	/**
	 *
	 * Enter description here ...
	 */
	public function uploadApkAction() {
	    $apkId = $this->getInput('apkId');
	    $this->assign('apkId', $apkId);
	    $this->getView()->display('common/uploadApk.phtml');
	    exit;
	}
	
	/**
	 *
	 * Enter description here ...
	 */
	public function uploadApkPostAction() {
	    $ret = Common::uploadApk('apk', 'gdtdelivery');
	    $this->assign('code' , $ret['data']);
	    $this->assign('msg' , $ret['msg']);
	    $this->assign('data', $ret['data']);
	    $apkId = $this->getInput('apkId');
	    $this->assign('apkId', $apkId);
	    $this->getView()->display('common/uploadApk.phtml');
	    exit;
	}
	

	public function uploadImgAction() {
	    $imgId = $this->getInput('imgId');
        $template_id = $this->getInput('template_id');
        $type = $this->getInput('type');
	    $this->assign('imgId', $imgId);
        $this->assign('template_id', $template_id);
        $this->assign('type', $type);
	    $this->getView()->display('common/gdtupload.phtml');
	    exit;
	}
	

	public function uploadImgPostAction() {
        $imgId = $this->getInput('imgId');
        $template_id =  $this->getInput('template_id');
        $type = $this->getInput('type');
        $image_name = $this->getUploadImageName();
        $resourcecheck = $this->checkGdtResource($template_id, $type);
        if($resourcecheck['code']==0){
            $ret = Common::upload('img', 'gdtdelivery', array(), false);
            $code = $ret['code'];
            //上传成功
            if($ret['code']==0){
                $attachUrl = Common::getAttachUrl();
                $url = $attachUrl. $ret['data'];
                $ourImageId = Advertiser_Service_GdtImageModel::addImage(array('image_name'=>$image_name, 'advertiser_uid'=>$this->userInfo['advertiser_uid']));
                if (!$ourImageId){
                    $code = '-1';
                    $msg = '操作失败';
                }else{
                    $gdtresponse = $this->uploadGdtImgPost($url, $image_name, $ourImageId);
                    if($gdtresponse['code'] != 0){
                        $url = '';
                    }
                    $code=$gdtresponse['code'];
                    $msg = $gdtresponse['message'];
                }
            }
        }else{
            $code=$resourcecheck['code'];
            $msg = $resourcecheck['message'];
        }
	    $this->assign('code' , $code);
	    $this->assign('msg' , $msg);
	    $this->assign('data', $url);
        $this->assign('gdtresponse', $gdtresponse);
	    $this->assign('imgId', $imgId);
        $this->assign('template_id', $template_id);
        $this->assign('type', $type);
        $this->assign('our_image_id', $ourImageId);
	    $this->getView()->display('common/gdtupload.phtml');
	    exit;
	}

    /*
     * 上传单个图片
     */
    public function uploadImgPost65Action() {
        // 关闭自动渲染，手动寻找
        Yaf_Dispatcher::getInstance()->autoRender(false);
        $success_num = 0;
        $creative_arr = array();
        $file_detail = $_FILES['file'];
        $pos = strrpos($file_detail['name'], '.');
        $image_name = substr($file_detail['name'], 0, $pos);
        $check_status = Advertiser_Service_CheckUploadResourceModel::checkGdtImage(65, 'image',$file_detail);
        if($check_status){
            // 校验成功，开始上传到本地，校验上传是否成功
            $ret = Advertiser_Service_CheckUploadResourceModel::upload($file_detail, 'gdtdelivery', array(), false);
            // 上传成功，开始插入数据库，并且上传图片到广点通
            if($ret['code']==0){
                $attachUrl = Common::getAttachUrl();
                $url = $attachUrl. $ret['data'];
                $ourImageId = Advertiser_Service_GdtImageModel::addImage(array('image_name'=>$image_name, 'advertiser_uid'=>$this->userInfo['advertiser_uid']));
                if ($ourImageId){
                    $gdtresponse = $this->uploadGdtImgPost($url, $image_name, $ourImageId);
                    if($gdtresponse['code'] == 0){
                        $creative_arr = array(
                            'image_id' => $gdtresponse['data']['image_id'],
                            'our_imageid' => $ourImageId,
                            'img_url' => $url
                        );
                        $success_num ++;
                    }
                }
            }
        }
        if($success_num == 0){
            die(json_encode(array('code'=>'-1','msg'=>'上传失败，请检查图片大小及类型')));
        }else{
            die(json_encode(array('code'=>'0','msg'=>'上传成功','image_id'=> $creative_arr['image_id'],'our_imageid'=> $creative_arr['our_imageid'],"img_url" => $creative_arr['img_url'])));
        }
    }

    /*
     * 上传多个图片
     */
    public function uploadImgsPost65Action() {
        // 关闭自动渲染，手动寻找
        Yaf_Dispatcher::getInstance()->autoRender(false);
        $total_num = count($_FILES['filelist']['name']); // 总共上传的文件个数
        if($total_num == 0){
            die(json_encode(array('code'=>'-1','msg'=>'上传的文件个数为0')));
        }
        $success_num = 0;
        $creative_arr = array();
        for($i=0;$i<$total_num;$i++){
            $file_detail = array(
                'name' => $_FILES['filelist']['name'][$i],
                'tmp_name' => $_FILES['filelist']['tmp_name'][$i],
                'error' => $_FILES['filelist']["error"][$i],
                'type' => $_FILES['filelist']['type'][$i],
                'size' => $_FILES['filelist']['size'][$i],
            );
            $pos = strrpos($file_detail['name'], '.');
            $image_name = substr($file_detail['name'], 0, $pos);
            $check_status = Advertiser_Service_CheckUploadResourceModel::checkGdtImage(65, 'image',$file_detail);
            if($check_status){
                // 校验成功，开始上传到本地，校验上传是否成功
                $ret = Advertiser_Service_CheckUploadResourceModel::upload($file_detail, 'gdtdelivery', array(), false);
                // 上传成功，开始插入数据库，并且上传图片到广点通
                if($ret['code']==0){
                    $attachUrl = Common::getAttachUrl();
                    $url = $attachUrl. $ret['data'];
                    $ourImageId = Advertiser_Service_GdtImageModel::addImage(array('image_name'=>$image_name, 'advertiser_uid'=>$this->userInfo['advertiser_uid']));
                    if ($ourImageId){
                        $gdtresponse = $this->uploadGdtImgPost($url, $image_name, $ourImageId);
                        if($gdtresponse['code'] == 0){
                            $creative_arr[$success_num] = array(
                                'image_id' => $gdtresponse['data']['image_id'],
                                'our_imageid' => $ourImageId,
                                'img_url' => $url
                            );
                            $success_num ++;
                        }
                    }
                }
            }
        }
        if($success_num == 0){
            die(json_encode(array('code'=>'-1','msg'=>'上传失败，请检查图片大小及类型')));
        }else{
            die(json_encode(array('code'=>'0','msg'=> '上传'.$total_num.'个，成功'.$success_num.'个','creative_arr'=> $creative_arr)));
        }
    }

    /**
     * 上传的图片名称
     * @return type
     */
    private function getUploadImageName(){
        $name = $_FILES['img']['name'];
        $pos = strrpos($name, '.');
        $realname = substr($name, 0, $pos);
        return $realname;
    }
    
    /**
     * 上传视频的名称
     * @return type
     */
    private function getUploadOtherName(){
        $name = $_FILES['other']['name'];
        $pos = strrpos($name, '.');
        $realname = substr($name, 0, $pos);
        return $realname;
    }
    
    /**
     * 上传到广通之前对上传的图片，视频先做图片大小，长，宽，后缀名校验．　视频大小，后缀名校验．
     * @param type $template_id
     * @param string $type
     * @return string
     */
    private function checkGdtResource($template_id, $type){
        $response = array();
        $response['code'] = 0;
        $response['message'] = 0;
        if(empty($type)){
            $type = 'image';
        }
        $creative_template_refs  = Common::getConfig('deliveryConfig','creative_template_refs');
        $template_ref= $creative_template_refs[$template_id];
        if( ($type=='image'||$type=='image2') && ($template_id == 65 || $template_id==271 || $template_id==351) ){
            //检测文件格式
            $ext = strtolower(substr(strrchr($_FILES['img']['name'], '.'), 1));
            if(!in_array($ext, $template_ref[$type]['file_format'])){
                $response['code'] = -1;
                $response['message'] = '图片格式必须为：'.implode(' ', $template_ref[$type]['file_format']);
                return $response;
            }
            //检测宽高
            $imagesizeInfo =  getimagesize($_FILES['img']['tmp_name']);
            $imagewidth = $imagesizeInfo[0];
            $imageheight = $imagesizeInfo[1];
            if($imagewidth != $template_ref[$type]['width']){
                $response['code'] = -1;
                $response['message'] = '图片宽度必须是'.$template_ref[$type]['width'];
                return $response;
            }
            if($imageheight != $template_ref[$type]['height']){
                $response['code'] = -2;
                $response['message'] = '图片高度必须是'.$template_ref[$type]['height'];
                return $response;
            }
            //检测大小
            $size = filesize($_FILES['img']['tmp_name']);
            $sizekb = $size/1024.0;
            if($sizekb>$template_ref[$type]['file_size_KB_limit']){
                $response['code'] = -3;
                $response['message'] = '图片大小必须 '.$template_ref[$type]['file_size_KB_limit']."k 以内";
                return $response;
            }
        }
        if($type =='video' && $template_id==351){
            //检测文件格式
            $ext = strtolower(substr(strrchr($_FILES['other']['name'], '.'), 1));
            if(!in_array($ext, $template_ref[$type]['file_format'])){
                $response['code'] = -1;
                $response['message'] = '上传格式必须为：'.implode(' ', $template_ref[$type]['file_format']);
                return $response;
            }
            //检测大小
            $size = filesize($_FILES['other']['tmp_name']);
            $sizemb = $size/1024.0/1024.0;
            $limitMb = $template_ref[$type]['file_size_KB_limit']/1024.0;
            if($sizemb>$limitMb){
                $response['code'] = -3;
                $response['message'] = '视频大小必须 '.$limitMb."M 以内";
                return $response;
            }
        }
        return $response;
    }
    
    /**
     * 同步图片到广点通．
     * @param type $url
     * @param type $image_name
     * @param type $ourImageId
     * @return type
     */
    private function uploadGdtImgPost($url, $image_name, $ourImageId=''){
//        $realfileurl = Common::getAttachPath(). $url;
//        $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201610/580763dc2ed6e.jpg';
//        $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581b13f129bca.jpg';//1000x560
//        $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581b147af23a1.jpg';//1000x560
//        $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581b149ada100.jpg';//1000x560
//        $realfileurl = 'https://dl2.gxpan.cn/ad/delivery/201611/583c22d5466a6.png';//公司商标
//        $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581c353801f5b.jpg';//商标图片格式，512x512
//        $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581c81d00ab62.jpg';//视频小图　300x300 image_id  51957:7574f2199739b24348ae41d879bef71a our_image_id:54
//        $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581c8213c1025.jpg';//视频封面　640x360 image_id 51957:defc71170a514c1902a5c2251b7b0e60  our_image_id:55
        if(Util_Environment::isOnline()){
            $realfileurl = $url;
        }else{
            $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581c8213c1025.jpg';//640x360
            $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581b147af23a1.jpg';//1000x560
//            $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581c353801f5b.jpg';//512x512
        }
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        $gdt_query=array();
        $gdt_query['advertiser_id'] =$gdtconfig['advertiser_id'];
        $gdt_query['image_url'] = $realfileurl;
        if($ourImageId){
            $gdt_query['outer_image_id'] = $ourImageId;
        }
        $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'image', 'create_by_url', $gdt_query);
        $logdata = array();
        $logdata['image_id']=$response['data']['image_id'];
        $logdata['config'] = $gdt_query;
        if($response['code']==0){
            $logdata['sync_status']='success';
            $logdata['sync_response']=  $response;
            Advertiser_Service_GdtImageModel::updateImage($logdata, $ourImageId);
        }else{
            Advertiser_Service_GdtImageModel::deleteImage($ourImageId);
        }
        return $response;
    }
    
    /**
     * 同步视频文件到广点通．
     * @param type $url
     * @param type $media_name
     * @return type
     */
    private function uploadGdtMediaPost($realfile, $media_name, $ourMediaId=''){
//        var_dump($realfile);
//        $tmpfile =$_FILES['other']['tmp_name'];
//        $filecontent = file_get_contents($realfile);
//        $media_signature = md5($filecontent);
//        var_dump($media_signature);die;#a0f5fcb408d8e48c440c22a6a2bf3d7e
//        var_dump($realfile);die;
//        $realfile = realpath($realfile);#a0f5fcb408d8e48c440c22a6a2bf3d7e
        $media_signature = md5_file($realfile);#a0f5fcb408d8e48c440c22a6a2bf3d7e
//        var_dump($media_signature);die;
        $media_description = $media_name;
        
//        $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201610/580763dc2ed6e.jpg';
//        $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581b13f129bca.jpg';
//        $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581b147af23a1.jpg';
//        $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581b149ada100.jpg';
//        $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581c353801f5b.jpg';//商标图片格式，512x512
//        $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581c81d00ab62.jpg';//视频小图　300x300 image_id  51957:7574f2199739b24348ae41d879bef71a
//        $realfileurl = 'http://dl2.gxpan.cn/ad/delivery/201611/581c8213c1025.jpg';//视频封面　640x360 image_id 51957:defc71170a514c1902a5c2251b7b0e60
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        $gdt_query=array();
        $gdt_query['media_signature'] = $media_signature;
        $gdt_query['media_file'] =$realfile;//需要放到最后,避名造成不必要的影响
        $gdt_query['media_description'] = $media_description;
        $gdt_query['advertiser_id'] =$gdtconfig['advertiser_id'];
        
        
//        $gdt_query['media_file'] = '@'.$realfile;
//        var_dump($gdt_query);die;
        $response = Advertiser_Service_GdtdirectconfigModel::normal_curl($this->userInfo['advertiser_uid'], 'media', 'create', $gdt_query);
        $logdata = array();
        $logdata['media_id']=$response['data']['media_id'];
        if($response['code']==0){
            unset($gdt_query['media_file']);
            $logdata['config'] = $gdt_query;
            $logdata['sync_status']='success';
            $logdata['sync_response']=  $response;
            Advertiser_Service_GdtMediaModel::updateMedia($logdata, $ourMediaId);
        }else{
//            unset($gdt_query['media_file']);
            $logdata['config'] = $gdt_query;
            $logdata['sync_status']='failed';
            $logdata['sync_response']=  $response;
            Advertiser_Service_GdtMediaModel::updateMedia($logdata, $ourMediaId);
//            Advertiser_Service_GdtMediaModel::deleteMedia($ourMediaId);
        }
        return $response;
    }
	
	public function uploadOtherAction() {
	    $otherId = $this->getInput('otherId');
        $template_id = $this->getInput('template_id');
        $type = $this->getInput('type');
	    $this->assign('otherId', $otherId);
        $this->assign('template_id', $template_id);
        $this->assign('type', $type);
	    $this->getView()->display('common/gdtuploadOther.phtml');
	    exit;
	}
	
	
	public function uploadOtherPostAction() {
	    $otherId = $this->getInput('otherId');
        $template_id =  $this->getInput('template_id');
        $type = $this->getInput('type');
        $media_name = $this->getUploadOtherName();
        $resourcecheck = $this->checkGdtResource($template_id, $type);
        if($resourcecheck['code']==0){
            $ret = Common::uploadOther('other', 'gdtdelivery');
            $code = $ret['code'];
            //上传成功
//            if($ret['code']==0){
                $attachPath = Common::getConfig('siteConfig', 'attachPath');
                $realfile = $attachPath . $ret['data'];
                $ourMediaId = Advertiser_Service_GdtMediaModel::addMedia(array('media_name'=>$media_name, 'advertiser_uid'=>$this->userInfo['advertiser_uid']));
                if (!$ourMediaId){
                    $code = '-1';
                    $msg = '操作失败';
                }else{
                    $gdtresponse = $this->uploadGdtMediaPost($realfile, $media_name, $ourMediaId);
//                    var_dump($gdtresponse);die;
                    if($gdtresponse['code'] != 0){
                        $url = '';
                    }
                    $code=$gdtresponse['code'];
                    $msg = $gdtresponse['message'];
                }
//            }
        }else{
            $code=$resourcecheck['code'];
            $msg = $resourcecheck['message'];
        }
        
	    $this->assign('code' , $code);
	    $this->assign('msg' , $msg);
	    $this->assign('data', $ret['data']);
	    $this->assign('otherId', $otherId);
        $this->assign('template_id', $template_id);
        $this->assign('our_media_id', $ourMediaId);
        $this->assign('type', $type);
	    $this->getView()->display('common/gdtuploadOther.phtml');
	    exit;
	}
    
    /**
     * 定向管理
     */
    public function gdtTargetingListAction(){
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Targeting_View')){
            $this->showMsg(100001, 'permission denied!');
        }
        /*权限校验end*/
        
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        $this->assign('gdtconfig', $gdtconfig);
        $config = Common::getConfig('deliveryConfig');
        foreach ($config as $key=>$val){
            $this->assign($key, $val);
        }
        //housead定向列表
        if(empty($gdtconfig)){
            
        }
        //广点通定向列表
        else{
            $page = intval($this->getInput('page'));
            if(empty($page))$page=1;
            $perpage = $this->perpage;
            $gdt_query=array();
            $gdt_query['advertiser_id'] =$gdtconfig['advertiser_id'];
            $gdt_query['page'] = $page;
            $gdt_query['page_size'] = $perpage;
            $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'targeting', 'select', $gdt_query);
            if($response['code'] == 0){
                //1.获取app行为定向，按app分类
            
                //next 3 line for debug 
                $treejson = '{"data":{"list":[{"id":34359739236,"name":"\u6e38\u620f","parent_id":0},{"id":34359739246,"name":"\u7b56\u7565","parent_id":34359739236},{"id":34359739247,"name":"\u517b\u6210","parent_id":34359739236},{"id":34359739248,"name":"\u7ecf\u8425\u5efa\u8bbe","parent_id":34359739236},{"id":34359739249,"name":"\u89d2\u8272\u626e\u6f14","parent_id":34359739236},{"id":34359739250,"name":"PK\u7ade\u6280","parent_id":34359739236},{"id":34359739251,"name":"\u4f11\u95f2\u76ca\u667a","parent_id":34359739236},{"id":34359739252,"name":"\u97f3\u4e50\u8282\u594f","parent_id":34359739236},{"id":34359739253,"name":"\u68cb\u724c\u5361\u724c","parent_id":34359739236},{"id":34359739254,"name":"\u4f53\u80b2","parent_id":34359739236},{"id":34359739255,"name":"\u5c04\u51fb","parent_id":34359739236},{"id":34359739237,"name":"\u975e\u6e38\u620f","parent_id":0},{"id":34359739266,"name":"\u751f\u6d3b","parent_id":34359739237},{"id":34359739846,"name":"\u751f\u6d3b-\u8d44\u8baf","parent_id":34359739266},{"id":34359739847,"name":"\u751f\u6d3b-\u5f69\u7968","parent_id":34359739266},{"id":34359739848,"name":"\u751f\u6d3b-\u5bfc\u8d2d","parent_id":34359739266},{"id":34359739849,"name":"\u751f\u6d3b-\u6bcd\u5a74","parent_id":34359739266},{"id":34359739850,"name":"\u751f\u6d3b-\u7f8e\u98df","parent_id":34359739266},{"id":34359739851,"name":"\u751f\u6d3b-\u54c1\u724c","parent_id":34359739266},{"id":34359739852,"name":"\u751f\u6d3b-\u5728\u7ebf\u7968\u52a1","parent_id":34359739266},{"id":34359739853,"name":"\u751f\u6d3b-\u9884\u4ed8\u7f34\u8d39","parent_id":34359739266},{"id":34359739854,"name":"\u751f\u6d3b-\u9605\u8bfb","parent_id":34359739266},{"id":34359739855,"name":"\u751f\u6d3b-\u65c5\u6e38","parent_id":34359739266},{"id":34359739856,"name":"\u751f\u6d3b-\u5065\u5eb7","parent_id":34359739266},{"id":34359739267,"name":"\u793e\u4ea4","parent_id":34359739237},{"id":34359739866,"name":"\u793e\u4ea4-\u4ea4\u53cb","parent_id":34359739267},{"id":34359739867,"name":"\u793e\u4ea4-\u597d\u53cb\u4e92\u52a8","parent_id":34359739267},{"id":34359739868,"name":"\u793e\u4ea4-\u901a\u8baf","parent_id":34359739267},{"id":34359739268,"name":"\u5de5\u5177","parent_id":34359739237},{"id":34359739886,"name":"\u5de5\u5177-\u5b66\u4e60\u6559\u80b2","parent_id":34359739268},{"id":34359739887,"name":"\u5de5\u5177-\u4fdd\u9669\u7406\u8d22","parent_id":34359739268},{"id":34359739888,"name":"\u5de5\u5177-\u56fe\u7247\u5904\u7406","parent_id":34359739268},{"id":34359739889,"name":"\u5de5\u5177-\u9762\u8bd5\u62db\u8058","parent_id":34359739268},{"id":34359739890,"name":"\u5de5\u5177-\u62cd\u7167","parent_id":34359739268},{"id":34359739891,"name":"\u5de5\u5177-\u5b89\u5168","parent_id":34359739268},{"id":34359739892,"name":"\u5de5\u5177-\u7cfb\u7edf","parent_id":34359739268},{"id":34359739893,"name":"\u5de5\u5177-\u529e\u516c","parent_id":34359739268},{"id":34359739894,"name":"\u5de5\u5177-\u5bfc\u822a","parent_id":34359739268},{"id":34359739269,"name":"\u5a31\u4e50","parent_id":34359739237},{"id":34359739906,"name":"\u5a31\u4e50-\u6d4b\u8bd5\u5360\u535c","parent_id":34359739269},{"id":34359739907,"name":"\u5a31\u4e50-\u661f\u5ea7","parent_id":34359739269},{"id":34359739908,"name":"\u5a31\u4e50-\u5f71\u97f3","parent_id":34359739269},{"id":34359739909,"name":"\u5a31\u4e50-\u59d3\u540d\u827a\u672f","parent_id":34359739269},{"id":34359739910,"name":"\u5a31\u4e50-\u8da3\u5473\u8bbe\u8ba1","parent_id":34359739269}]},"code":0,"message":""}';        
                $treearr = json_decode($treejson, true);
                $category_list = $treearr['data']['list'];

    //            //gdt远程获取app分类数据
    //            $remoteResponse = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'utility', 'get_app_category_list', array());
    //            $category_list = $remoteResponse['data']['list'];
                $apps=common::resetKey($category_list, 'id');

                //2.获取移动媒体分类

                //next 3 line for debug 
                $mediatreejson = '{"data":{"list":[{"id":1,"name":"\u529e\u516c","parent_id":0},{"id":2,"name":"\u7cfb\u7edf","parent_id":0},{"id":3,"name":"\u7f8e\u5316","parent_id":0},{"id":4,"name":"\u751f\u6d3b\u5b9e\u7528","parent_id":0},{"id":5,"name":"\u8d2d\u7269","parent_id":0},{"id":6,"name":"\u5065\u5eb7","parent_id":0},{"id":7,"name":"\u6559\u80b2","parent_id":0},{"id":8,"name":"\u6c7d\u8f66","parent_id":0},{"id":9,"name":"\u5546\u4e1a","parent_id":0},{"id":10,"name":"\u51fa\u884c","parent_id":0},{"id":11,"name":"\u7f8e\u98df","parent_id":0},{"id":12,"name":"\u5a31\u4e50","parent_id":0},{"id":13,"name":"\u97f3\u4e50","parent_id":0},{"id":14,"name":"\u89c6\u9891","parent_id":0},{"id":15,"name":"\u5e7f\u64ad","parent_id":0},{"id":16,"name":"\u793e\u4ea4","parent_id":0},{"id":17,"name":"\u6e38\u620f","parent_id":0},{"id":18,"name":"\u9605\u8bfb","parent_id":0}]},"code":0,"message":""}';
                $mediatreearr = json_decode($mediatreejson, true);
                $media_list = $mediatreearr['data']['list'];

    //            //gdt远程获取media分类数据
    //            $mediaResponse = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'utility', 'get_union_media_category_list', array());
    //            $media_list = $mediaResponse['data']['list'];
                $medias = common::resetKey($media_list, 'id');
                $this->assign('apps', $apps);
                $this->assign('medias', $medias);
                
                $list = $response['data']['list'];
                $page_info = $response['data']['page_info'];
                $total = $page_info['total_num'];
                $this->assign('list', $list);
                $this->assign('pager', Common::getPages($total, $page, $perpage, $this->actions['gdtTargetingListUrl'].'/?'));
            }
        }
    }
    
    /**
     * 检查广点通添加定向参数
     * @param type $info
     * @return type
     */
    private function checkGdtAddParam($info){
        $gdt_info = array();
        if($info['targeting_id']){
            $gdt_info['targeting_id'] = $info['targeting_id'];
        }
        if(empty($info['targeting_name'])){
	        $this->output(1, '请填写定向名称');
	    }
        if(strlen($info['targeting_name'])>120){
            $this->output(1, '名称限制在120个英文字符内');
        }
        $gdt_info['targeting_name'] = $info['targeting_name'];
        if(!empty($info['description'])){
            $gdt_info['description'] = $info['description'];
        }

        $targeting_setting = array();
        if(!isset($info['gdt_age_type'])){
            $this->output(1, '年龄没有选中');
        }
        if($info['gdt_age_type'] == '1' && !isset($info['gdt_age'])){
            $this->output(1, '年龄定向没有选中');
        }
        if($info['gdt_age']){
            $age_arr = explode('~', $info['gdt_age']);
            if(count($age_arr)!=2 && !is_integer($age_arr[0]) && !is_integer($age_arr[1])){
                $this->output(1, '年龄定向格式错误');
            }
        if($age_arr[0]>$age_arr[1] || $age_arr[0]<5 || $age_arr[1]>60 ){
                $this->output(1, '正确的年龄定向范围:5~60');
            }
            $targeting_setting['age'] = array($info['gdt_age']);
        }
        if(!isset($info['gdt_temperature_type'])){
            $this->output(1, '温度没有选中');
        }
        if($info['gdt_temperature_type'] == '1' && !isset($info['gdt_temperature'])){
            $this->output(1, '温度定向没有选中');
        }
        if($info['gdt_temperature']){
            $temperature_arr = explode('~', $info['gdt_temperature']);
            if(count($temperature_arr)!=2 && !is_integer($temperature_arr[0]) && !is_integer($temperature_arr[1])){
                $this->output(1, '温度定向格式错误');
            }
            if($temperature_arr[0]>$temperature_arr[1] || $temperature_arr[0]<-50 || $temperature_arr[1]>50 ){
                $this->output(1, '正确的温度定向范围:-50~50');
            }
            $temperature_arr[0] += 273;
            $temperature_arr[1] += 273;
            $info['gdt_temperature'] = $temperature_arr[0]."~".$temperature_arr[1];
            $targeting_setting['temperature'] = array($info['gdt_temperature']);
        }
        if(!isset($info['gdt_gender_type'])){
            $this->output(1, '性别没有选中');
        }
        if($info['gdt_gender_type'] != '0'){
            $targeting_setting['gender']=array($info['gdt_gender_type']);
        }
        if(!isset($info['gdt_app_install_status_type'])){
            $this->output(1, '应用用户没有选中');
        }
        if($info['gdt_app_install_status_type'] != '0'){
            $targeting_setting['app_install_status']=$info['gdt_app_install_status_type'];
        }
        // gdt_region_type 没有处理
        $check_arr  = array(
            'online_scenario'=>'上网场景',
            'education'=>'用户学历',
            'relationship_status'=>'婚恋状态',
            'living_status'=>'工作状态',
            'paying_user_type'=>'付费用户',
            'dressing_index'=>'穿衣指数',
            'uv_index'=>'紫外线指数',
            'makeup_index'=>'化妆指数',
            'climate'=>'天气气象',
//            'temperature'=>'温度',
        );
        foreach($check_arr as $key => $value){
            if(!isset($info['gdt_'.$key.'_type'])){
                $this->output(1, $value.'没有选中');
            }
            if($info['gdt_'.$key.'_type'] == '1' && !isset($info['gdt_'.$key.'_range'])){
                $this->output(1, $value.'定向没有选中');
            }
            if($info['gdt_'.$key.'_type'] == '1'){
                $targeting_setting[$key]=$info['gdt_'.$key.'_range'];
            }
        }
        if($info['gdt_keyword']){
            $keyword_arr = explode(',', html_entity_decode($info['gdt_keyword']));
            if(count($keyword_arr)>500){
                $this->output(1, '最多允许设置 500 个关键词');
            }
            foreach($keyword_arr as $keyword){
                if(strlen($keyword)>30){
                    $this->output(1, '每个关键词最长 30 个字节');  
                }
            }
            $targeting_setting['keyword'] = json_encode(array('words'=>$keyword_arr));
        }
        
	    if(!isset($info['gdt_app_behavior_type'])){
	        $this->output(1, 'app行为没有选中');
	    }
	    if($info['gdt_app_behavior_type'] == '1' && !isset($info['gdt_app_behavior_range'])){
	        $this->output(1, 'app行为定向没有选中');
	    }
        if( intval($info['gdt_time_window'])!= $info['gdt_time_window'] ||  $info['gdt_time_window']<1 || $info['gdt_time_window']>365 ){
            $this->output(1, 'app行为对象有效期的取值范围：1~365');
        }
        if($info['gdt_app_behavior_type'] == '1'){
            if(count($info['gdt_act_id_list'])==0){
                $this->output(1, '请选择用户行为');
            }
            $targeting_setting['app_behavior']=  json_encode(array(
                'object_type'=>'APP_CLASS',
                'object_id_list'=>$info['gdt_app_behavior_range'],
                'time_window'=>$info['gdt_time_window'],
                'act_id_list'=>$info['gdt_act_id_list'],
            ));
        }
        
        
	    if(!isset($info['gdt_device_price_type'])){
	        $this->output(1, '设备价格没有选中');
	    }
	    if($info['gdt_device_price_type'] == '1' && !isset($info['gdt_device_price_range'])){
	        $this->output(1, '设备价格定向没有选中');
	    }
        if($info['gdt_device_price_type'] == '1'){
            $targeting_setting['device_price']=$info['gdt_device_price_range'];
        }
        
        
	    if(!isset($info['gdt_user_os_type'])){
	        $this->output(1, '操作系统没有选中');
	    }
	    if($info['gdt_user_os_type'] == '1' && !isset($info['gdt_user_os_range'])){
	        $this->output(1, '操作系统的定向没有选中');
	    }
        if($info['gdt_user_os_type'] == '1'){
            $targeting_setting['user_os']=$info['gdt_user_os_range'];
        }
        
        if(!isset($info['gdt_network_type_type'])){
	        $this->output(1, '联网方式没有选中');
	    }
	    if($info['gdt_network_type_type'] == '1' && !isset($info['gdt_network_type_range'])){
	        $this->output(1, '联网方式的定向没有选中');
	    }
        if($info['gdt_network_type_type'] == '1'){
            $targeting_setting['network_type']=$info['gdt_network_type_range'];
        }
        
        if(!isset($info['gdt_network_operator_type'])){
	        $this->output(1, '移动运营商没有选中');
	    }
	    if($info['gdt_network_operator_type'] == '1' && !isset($info['gdt_network_operator_range'])){
	        $this->output(1, '移动运营商的定向没有选中');
	    }
        if($info['gdt_network_operator_type'] == '1'){
            $targeting_setting['network_operator']=$info['gdt_network_operator_range'];
        }
        
        if(!isset($info['gdt_business_interest_type'])){
	        $this->output(1, '商业兴趣分类没有选中');
	    }
	    if($info['gdt_business_interest_type'] == '1' && !isset($info['gdt_business_interest_range'])){
	        $this->output(1, '商业兴趣分类的定向没有选中');
	    }
        if($info['gdt_business_interest_type'] == '1'){
            $targeting_setting['business_interest']=$info['gdt_business_interest_range'];
        }
        if(!isset($info['gdt_region_type'])){
	        $this->output(1, '投放地区分类没有选中');
	    }
	    if($info['gdt_region_type'] == '1' && !isset($info['gdt_region_range'])){
	        $this->output(1, '投放地区分类的定向没有选中');
	    }
        if($info['gdt_region_type'] == '1'){
            $targeting_setting['region']=$info['gdt_region_range'];
        }
        if(!isset($info['gdt_union_media_category_type'])){
	        $this->output(1, '移动媒体分类没有选中');
	    }
	    if($info['gdt_union_media_category_type'] == '1' && !isset($info['gdt_union_media_category_range'])){
	        $this->output(1, '移动媒体分类的定向没有选中');
	    }
        if($info['gdt_union_media_category_type'] == '1'){
            $targeting_setting['media_category_union']=$info['gdt_union_media_category_range'];
        }
        $gdt_info['targeting_setting'] = $targeting_setting;
	    return $gdt_info;
	}
    
    private function findChild(&$arr,$id){
        $childs=array();
        if(empty($arr)){
            return NULL;
        }
        foreach ($arr as $k => $v){
            if($v['parent_id']== $id){
                $childs[]=$v;
            }
        }
        return $childs;
    }

    private function build_tree($rows,$root_id){
        $childs=$this->findChild($rows,$root_id);
        if(empty($childs)){
            return null;
        }
        foreach ($childs as $k => $v){
            $rescurTree=$this->build_tree($rows,$v['id']);
            if( null != $rescurTree){
                $childs[$k]['childs']=$rescurTree;
            }
        }
        return $childs;
     }

    private function build_tree_overseas($rows,$root_id){
        $childs=$this->findChild($rows,$root_id);
        if(empty($childs)){
            return null;
        }
        foreach ($childs as $k => $v){
            $v['id'] = $v['id'] / 1000;
            $childs_2=array();
            if(empty($rows)){
                return NULL;
            }
            foreach ($rows as $k2 => $v2){
                $child_id = $v2['id'] / 1000;
                if(intval($child_id) == $v['id']){
                    $childs_2[]=$v2;
                }
            }
            $rescurTree=$childs_2;
            if( null != $rescurTree){
                $childs[$k]['childs']=$rescurTree;
            }
        }
        return $childs;
     }
     
     public function addGdtTargetingAction(){
        $targeting_id=$this->getInput('targeting_id');
        
        /*权限校验start*/
        if($targeting_id){
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Targeting_View')){
                $this->showMsg(100001, 'permission denied!');
            }
        }else{
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Targeting_Add')){
                $this->showMsg(100001, 'permission denied!');
            }
        }
        /*权限校验end*/
        
        $config = Common::getConfig('deliveryConfig');
	    foreach ($config as $key=>$val){
	        $this->assign($key, $val);
	    }
        
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        if(empty($gdtconfig)){
            $this->showMsg(100001, '没有开通广点通直投权限!');
        }
        if($targeting_id){
            $targetingInfo = Advertiser_Service_GdtTargetingModel::getTargeting($targeting_id);
//            if($targetingInfo['advertiser_uid']!=$this->userInfo['advertiser_uid']){
//                $this->output(-1, '只能查看自己的定向');
//            }
            $gdt_querydata[] = array();
            $gdt_querydata['targeting_id'] = $targeting_id;
            $gdt_querydata['advertiser_id'] = $gdtconfig['advertiser_id'];
            //for debug
//                $response = json_decode('{"data":{"targeting_id":122851,"targeting_name":"xxxxxxxxxxxxxx_\u526f\u672c","description":"","targeting_setting":{"age":["5~6"],"app_behavior":{"object_type":"APP_CLASS","object_id_list":["34359739847","34359739849"],"time_window":300,"act_id_list":["ACTIVE","PAID"]},"network_operator":["CMCC"],"user_os":["IOS"],"network_type":["WIFI"],"device_price":["INEXPENSIVE"],"media_category_union":[8,9],"keyword":{"words":["xx","33","44","afa"]}},"outer_targeting_id":8,"ui_visibility":"INVISIBLE","created_time":1477360161,"last_modified_time":1477360161},"code":0,"message":""}', true);
//                print_r($response);
            $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'targeting', 'read', $gdt_querydata);
            $this->assign('gdt_targeting_info', $response['data']);
        }
        
        //1.获取app行为定向，按app分类

        //next 3 line for debug 
//        $treejson = '{"data":{"list":[{"id":34359739236,"name":"\u6e38\u620f","parent_id":0},{"id":34359739246,"name":"\u7b56\u7565","parent_id":34359739236},{"id":34359739247,"name":"\u517b\u6210","parent_id":34359739236},{"id":34359739248,"name":"\u7ecf\u8425\u5efa\u8bbe","parent_id":34359739236},{"id":34359739249,"name":"\u89d2\u8272\u626e\u6f14","parent_id":34359739236},{"id":34359739250,"name":"PK\u7ade\u6280","parent_id":34359739236},{"id":34359739251,"name":"\u4f11\u95f2\u76ca\u667a","parent_id":34359739236},{"id":34359739252,"name":"\u97f3\u4e50\u8282\u594f","parent_id":34359739236},{"id":34359739253,"name":"\u68cb\u724c\u5361\u724c","parent_id":34359739236},{"id":34359739254,"name":"\u4f53\u80b2","parent_id":34359739236},{"id":34359739255,"name":"\u5c04\u51fb","parent_id":34359739236},{"id":34359739237,"name":"\u975e\u6e38\u620f","parent_id":0},{"id":34359739266,"name":"\u751f\u6d3b","parent_id":34359739237},{"id":34359739846,"name":"\u751f\u6d3b-\u8d44\u8baf","parent_id":34359739266},{"id":34359739847,"name":"\u751f\u6d3b-\u5f69\u7968","parent_id":34359739266},{"id":34359739848,"name":"\u751f\u6d3b-\u5bfc\u8d2d","parent_id":34359739266},{"id":34359739849,"name":"\u751f\u6d3b-\u6bcd\u5a74","parent_id":34359739266},{"id":34359739850,"name":"\u751f\u6d3b-\u7f8e\u98df","parent_id":34359739266},{"id":34359739851,"name":"\u751f\u6d3b-\u54c1\u724c","parent_id":34359739266},{"id":34359739852,"name":"\u751f\u6d3b-\u5728\u7ebf\u7968\u52a1","parent_id":34359739266},{"id":34359739853,"name":"\u751f\u6d3b-\u9884\u4ed8\u7f34\u8d39","parent_id":34359739266},{"id":34359739854,"name":"\u751f\u6d3b-\u9605\u8bfb","parent_id":34359739266},{"id":34359739855,"name":"\u751f\u6d3b-\u65c5\u6e38","parent_id":34359739266},{"id":34359739856,"name":"\u751f\u6d3b-\u5065\u5eb7","parent_id":34359739266},{"id":34359739267,"name":"\u793e\u4ea4","parent_id":34359739237},{"id":34359739866,"name":"\u793e\u4ea4-\u4ea4\u53cb","parent_id":34359739267},{"id":34359739867,"name":"\u793e\u4ea4-\u597d\u53cb\u4e92\u52a8","parent_id":34359739267},{"id":34359739868,"name":"\u793e\u4ea4-\u901a\u8baf","parent_id":34359739267},{"id":34359739268,"name":"\u5de5\u5177","parent_id":34359739237},{"id":34359739886,"name":"\u5de5\u5177-\u5b66\u4e60\u6559\u80b2","parent_id":34359739268},{"id":34359739887,"name":"\u5de5\u5177-\u4fdd\u9669\u7406\u8d22","parent_id":34359739268},{"id":34359739888,"name":"\u5de5\u5177-\u56fe\u7247\u5904\u7406","parent_id":34359739268},{"id":34359739889,"name":"\u5de5\u5177-\u9762\u8bd5\u62db\u8058","parent_id":34359739268},{"id":34359739890,"name":"\u5de5\u5177-\u62cd\u7167","parent_id":34359739268},{"id":34359739891,"name":"\u5de5\u5177-\u5b89\u5168","parent_id":34359739268},{"id":34359739892,"name":"\u5de5\u5177-\u7cfb\u7edf","parent_id":34359739268},{"id":34359739893,"name":"\u5de5\u5177-\u529e\u516c","parent_id":34359739268},{"id":34359739894,"name":"\u5de5\u5177-\u5bfc\u822a","parent_id":34359739268},{"id":34359739269,"name":"\u5a31\u4e50","parent_id":34359739237},{"id":34359739906,"name":"\u5a31\u4e50-\u6d4b\u8bd5\u5360\u535c","parent_id":34359739269},{"id":34359739907,"name":"\u5a31\u4e50-\u661f\u5ea7","parent_id":34359739269},{"id":34359739908,"name":"\u5a31\u4e50-\u5f71\u97f3","parent_id":34359739269},{"id":34359739909,"name":"\u5a31\u4e50-\u59d3\u540d\u827a\u672f","parent_id":34359739269},{"id":34359739910,"name":"\u5a31\u4e50-\u8da3\u5473\u8bbe\u8ba1","parent_id":34359739269}]},"code":0,"message":""}';
//        $treearr = json_decode($treejson, true);
//        $category_list = $treearr['data']['list'];

        //gdt远程获取app分类数据
        $remoteResponse = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'utility', 'get_app_category_list', array());
        $category_list = $remoteResponse['data']['list'];
        $apps=$this->build_tree($category_list,0);

        //2.获取移动媒体分类

        //next 3 line for debug 
//        $mediatreejson = '{"data":{"list":[{"id":1,"name":"\u529e\u516c","parent_id":0},{"id":2,"name":"\u7cfb\u7edf","parent_id":0},{"id":3,"name":"\u7f8e\u5316","parent_id":0},{"id":4,"name":"\u751f\u6d3b\u5b9e\u7528","parent_id":0},{"id":5,"name":"\u8d2d\u7269","parent_id":0},{"id":6,"name":"\u5065\u5eb7","parent_id":0},{"id":7,"name":"\u6559\u80b2","parent_id":0},{"id":8,"name":"\u6c7d\u8f66","parent_id":0},{"id":9,"name":"\u5546\u4e1a","parent_id":0},{"id":10,"name":"\u51fa\u884c","parent_id":0},{"id":11,"name":"\u7f8e\u98df","parent_id":0},{"id":12,"name":"\u5a31\u4e50","parent_id":0},{"id":13,"name":"\u97f3\u4e50","parent_id":0},{"id":14,"name":"\u89c6\u9891","parent_id":0},{"id":15,"name":"\u5e7f\u64ad","parent_id":0},{"id":16,"name":"\u793e\u4ea4","parent_id":0},{"id":17,"name":"\u6e38\u620f","parent_id":0},{"id":18,"name":"\u9605\u8bfb","parent_id":0}]},"code":0,"message":""}';
//        $mediatreearr = json_decode($mediatreejson, true);
//        $media_list = $mediatreearr['data']['list'];

        //gdt远程获取media分类数据
        $mediaResponse = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'utility', 'get_union_media_category_list', array());
        $media_list = $mediaResponse['data']['list'];
        $medias = $this->build_tree($media_list,0);
        //gdt远程获取region
        $remoteResponse = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'utility', 'get_region_list', array());
        $region_list = $remoteResponse['data']['list'];
        $region = array();
        $region['inside'] = $this->build_tree($region_list,0);
        $region['overseas'] = $this->build_tree_overseas($region_list,9000);
//         var_dump($region['outside']);
//        $region = $region_list;

        //gdt远程获取business_interest
        $remoteResponse = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'utility', 'get_business_interest_list', array());
        $business_interest_list = $remoteResponse['data']['list'];
        $business_interest = $this->build_tree($business_interest_list,0);
//        $business_interest = $business_interest_list;

        $this->assign('gdtconfig', $gdtconfig);
        $this->assign('apps', $apps);
        $this->assign('medias', $medias);
        $this->assign('region', $region);
        $this->assign('business_interest', $business_interest);
        $this->assign('indexUrl', $this->actions['indexUrl']);
        $this->assign('targeting_id', $targeting_id);
        $this->assign('result', $targetingInfo);
        
    }
    
    public function saveTargetingPostAction() {
        
        $gdt_info = $this->getInput(array(
            'targeting_name','description','targeting_id','gdt_age_type','gdt_age',
            'gdt_gender_type','gdt_online_scenario_type','gdt_online_scenario_range','gdt_education_type','gdt_education_range',
            'gdt_region_type','gdt_region_range', 'gdt_relationship_status_type','gdt_relationship_status_range',
            'gdt_living_status_type','gdt_living_status_range','gdt_business_interest_type','gdt_business_interest_range',
            'gdt_paying_user_type_type','gdt_paying_user_type_range','gdt_app_install_status_type','gdt_resident_community_price_type','gdt_resident_community_price',
            'gdt_dressing_index_type', 'gdt_dressing_index_range', 'gdt_uv_index_type', 'gdt_uv_index_range', 'gdt_makeup_index_type','gdt_makeup_index_range',
            'gdt_climate_type', 'gdt_climate_range', 'gdt_temperature_type', 'gdt_temperature',
            'gdt_keyword', 'gdt_app_behavior_type', 'gdt_app_behavior_range', 'gdt_act_id_list', 'gdt_time_window',
            'gdt_device_price_type', 'gdt_device_price_range', 'gdt_user_os_type', 'gdt_user_os_range', 'gdt_network_type_type', 'gdt_network_type_range',
            'gdt_network_operator_type', 'gdt_network_operator_range', 'gdt_union_media_category_type', 'gdt_union_media_category_range', 
        ));
        
        /*权限校验start*/
        if($gdt_info['targeting_id']){
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Targeting_Edit')){
                $this->output(1, 'permission denied!');
            }
        }else{
            if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Targeting_Add')){
                $this->output(1, 'permission denied!');
            }
        }
        /*权限校验end*/
        
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        if($gdtconfig){
            $gdt_info = $this->checkGdtAddParam($gdt_info);
        }else{
            $this->showMsg(100001, '没有开通广点通直投权限!');
        }
        if(empty($gdt_info['targeting_id'])){
            if(Advertiser_Service_GdtTargetingModel::getTargetingByName($gdt_info['targeting_name'])){
                $this->output(-1, '定向名称已经被使用');
            }
            $info['advertiser_uid'] = $this->userInfo['advertiser_uid'];
            $ourTargetingId = Advertiser_Service_GdtTargetingModel::addTargeting(array('targeting_name'=>$gdt_info['targeting_name'], 'advertiser_uid'=>$this->userInfo['advertiser_uid']));
            if (!$ourTargetingId) $this->output(-1, '操作失败');
            
            $gdt_info['outer_targeting_id']=$ourTargetingId;
            $gdt_info['advertiser_id'] = $gdtconfig['advertiser_id'];
            $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'targeting', 'create', $gdt_info);
            $logdata = array();
            $logdata['targeting_id']=$response['data']['targeting_id'];
            $logdata['config'] = json_encode($gdt_info);
            if($response['code']==0){
                $logdata['sync_status']='success';
                $logdata['sync_response']=  json_encode($response);
                Advertiser_Service_GdtTargetingModel::updateTargeting($logdata, $ourTargetingId);
            }else{
                Advertiser_Service_GdtTargetingModel::deleteTargeting($ourTargetingId);
            }
                
            if($response['code']==0){
                $this->output(0, '操作成功');
            }else{
                $this->output(1, '广点通同步失败:'. $response['message']);
            }
        }else{
            $targetingInfo = Advertiser_Service_GdtTargetingModel::getTargeting($gdt_info['targeting_id']);
            if($targetingInfo['advertiser_uid']!=$this->userInfo['advertiser_uid']){
                $this->output(-1, '只能查看自己的定向');
            }
            
            $gdt_info['advertiser_id'] = $gdtconfig['advertiser_id'];
            $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'targeting', 'update', $gdt_info);
            if($response['code']==0){
                $logdata = array();
                $logdata['targeting_id']=$response['data']['targeting_id'];
                $logdata['config'] = json_encode($gdt_info);
                $logdata['sync_status']='success';
                $logdata['sync_response']=  json_encode($response);
                Advertiser_Service_GdtTargetingModel::updateTargeting($logdata, $targetingInfo['id']);
                $this->output(0, '更新成功');
            }else{
                $this->output(0, '广点通同步失败:'.$response['message']);
            }
        }
    }
    
    /**
     * 删除定向
     */
    public function delGdtTargetingAction(){
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_Targeting_Del')){
            $this->output(1, 'permission denied!');
        }
        /*权限校验end*/
        
        $targeting_id=$this->getInput('targeting_id');
        if(empty($targeting_id)){
	        $this->output(1, '参数错误');
	    }
        $targetingInfo = Advertiser_Service_GdtTargetingModel::getTargeting($targeting_id);
        if($targetingInfo['advertiser_uid']!=$this->userInfo['advertiser_uid']){
            $this->output(-1, '只能删除自己创建的定向');
        }
        
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        if(!$gdtconfig){
            $this->output(1, '没有开通广点通直投权限');
        }
        
        $gdt_info = array();
        $gdt_info['targeting_id']=$targeting_id;
        $gdt_info['advertiser_id'] = $gdtconfig['advertiser_id'];
        $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'targeting', 'delete', $gdt_info);
        
        $logdata = array();
        $logdata['targeting_id']=$response['data']['targeting_id'];
        $logdata['config'] = json_encode($gdt_info);
        if($response['code']==0){
            $logdata['del']=  1;
            Advertiser_Service_GdtTargetingModel::updateTargeting($logdata, $targetingInfo['id']);
            $this->output(0, '操作成功');
        }else{
            $this->output(-1, '操作失败');
        }
    }

    /*
     * 广点通推广计划列表
     */
    public function gdtCampaignListAction(){
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_campaign_View')){
            $this->showMsg(100001, 'permission denied!');
        }
        /*权限校验end*/

        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);

        if(empty($gdtconfig)){
            $this->showMsg(100001, '没有开通广点通直投');
        }
        $page = intval($this->getInput('page'));
        if(empty($page))$page=1;
        $perpage = $this->perpage;
        $gdt_query=array();
        $gdt_query['advertiser_id'] =$gdtconfig['advertiser_id'];
        $gdt_query['page'] = $page;
        $gdt_query['page_size'] = $perpage;
        $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'campaign', 'select', $gdt_query);
        if($response['code'] == 0){
            $list = $response['data']['list'];
            $page_info = $response['data']['page_info'];
            $total = $page_info['total_num'];
            if($list){
                $gdt_configured_status  = Common::getConfig('deliveryConfig','gdt_configured_status');
                $gdt_campaign_speed_mode_type  = Common::getConfig('deliveryConfig','gdt_campaign_speed_mode_type');
                foreach($list as $key=>$item){
                    $list[$key]['speed_mode_cn'] = $gdt_campaign_speed_mode_type[$item['speed_mode']];
                    $list[$key]['configured_status_cn'] = $gdt_configured_status[$item['configured_status']]['value'];
                    $list[$key]['configured_status_color'] = $gdt_configured_status[$item['configured_status']]['color'];
                }
            }
            $this->assign('list', $list);
            $this->assign('pager', Common::getPages($total, $page, $perpage, $this->actions['gdtCampaignListUrl'].'/?'));
        }
    }

    /*
     * 同步推广计划到本地
     */
    public function gdtCampaignSyncAction(){
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_campaign_Add')){
            $this->output(1, 'permission denied!');
        }
        /*权限校验end*/
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);

        if(empty($gdtconfig)){
            $this->output(1, '没有开通广点通直投!');
        }
        $info = $this->getInput(array('campaign_id'));
        // 读取广点通推广计划信息
        $gdt_query_data = array();
        $gdt_query_data['advertiser_id'] = $gdtconfig['advertiser_id'];
        $gdt_query_data['campaign_id'] = $info['campaign_id'];
        if(Advertiser_Service_GdtCampaignModel::getBy(array('advertiser_uid'=>$this->userInfo['advertiser_uid'],'campaign_id'=>$info['campaign_id']))){
            $this->output(1, '已经存在，无需同步!');
        }
        $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'campaign', 'read', $gdt_query_data);
        if($response['code']==0){
            $data = $response['data'];
            $local_config = array();
            $local_config['campaign_name'] = $data['campaign_name'];
            $local_config['daily_budget'] = $data['daily_budget'];
            $local_config['speed_mode'] = $data['speed_mode'];
            $insert_data = array(
                'campaign_name'=>$local_config['campaign_name'],
                'campaign_id'=>$info['campaign_id'],
                'advertiser_uid'=>$this->userInfo['advertiser_uid'],
                'local_config'=>$local_config,
                'sync_status'=>'success',
                'config'=>$data
            );
            $ourCampaignId = Advertiser_Service_GdtCampaignModel::addCampaign($insert_data);
            if (!$ourCampaignId) $this->output(-1, '操作失败');
            $this->output(0, '同步成功！'. $response['message']);
            // 将本地推广计划id 更新到广点通
//            $gdt_update_data = array();
//            $gdt_update_data['advertiser_id'] = $gdtconfig['advertiser_id'];
//            $gdt_update_data['campaign_id'] = $info['campaign_id'];
//            $gdt_update_data['campaign_id'] = $info['campaign_id'];
//            $gdt_update_data['outer_campaign_id'] = $ourCampaignId;
//            $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'campaign', 'update', $gdt_update_data);
//            if($response['code']==0){
//                $this->output(0, '同步成功！'. $response['message']);
//            }else{
//                $this->output(1, '同步失败:'. $response['message']);
//            }
        }else{
            $this->output(1, '获取推广计划失败:'. $response['message']);
        }
    }

    /*
     * 新增广点通推广计划
     */
    public function addGdtCampaignAction(){
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_campaign_Add')){
            $this->showMsg(100001, 'permission denied!');
        }
        /*权限校验end*/
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);

        if(empty($gdtconfig)){
            $this->showMsg(100001, '没有开通广点通直投');
        }
        //标的物类型
        $gdt_campaign_speed_mode_type = Common::getConfig('deliveryConfig','gdt_campaign_speed_mode_type');
        $this->assign('gdt_campaign_speed_mode_type', $gdt_campaign_speed_mode_type);
        // 关闭自动渲染，手动寻找
        Yaf_Dispatcher::getInstance()->autoRender(false);
        $this->getView()->display('gdtdelivery/addgdtcampaign.phtml');
    }

    /*
     * 广点通推广计划保存
     */
    public function saveGdtCampaignPostAction(){
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Gdt_campaign_Add')){
            $this->output(1, 'permission denied!');
        }
        /*权限校验end*/
        $gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
        if(empty($gdtconfig)){
            $this->output(1, '没有开通广点通直投!');
        }
        $info = $this->getInput(array('campaign_name','daily_budget','speed_mode'));
        if($info['campaign_name']=='')
            $this->output(1, '请填写推广计划名称');
        if($info['daily_budget']=='')
            $this->output(1, '请设定投放限额');
        if(intval($info['daily_budget']) <= 0)
            $this->output(1, '投放限额为大于零数字类型');
        $info['daily_budget']  = intval($info['daily_budget']);
        if($info['speed_mode']=='')
            $this->output(1, '请设置投放方式');
        $campaign_data = Advertiser_Service_GdtCampaignModel::getBy(array('advertiser_uid'=>$this->userInfo['advertiser_uid'],'campaign_name'=>$info['campaign_name']));
        if($campaign_data){
            if($campaign_data['campaign_id']){
                $this->output(-1, '广告名称已经被使用'.$campaign_data['campaign_id']);
            }else{// 创建失败的记录直接删除
                Advertiser_Service_GdtCampaignModel::deleteCampaign($campaign_data['id']);
            }
        }
        $ourCampaignId = Advertiser_Service_GdtCampaignModel::addCampaign(array('campaign_name'=>$info['campaign_name'], 'advertiser_uid'=>$this->userInfo['advertiser_uid'],'local_config'=>$info));
        if (!$ourCampaignId) $this->output(-1, '操作失败');
        $gdt_query_data = array();
        $gdt_query_data['advertiser_id'] = $gdtconfig['advertiser_id'];
        $gdt_query_data['campaign_name'] = $info['campaign_name'];
        $gdt_query_data['campaign_type'] = 'CAMPAIGN_TYPE_NORMAL';// 计划类型只有一个——普通展示广告
        $gdt_query_data['daily_budget'] = $info['daily_budget'];
        $gdt_query_data['speed_mode'] = $info['speed_mode'];
        $gdt_query_data['outer_campaign_id'] = $ourCampaignId;
        $response = Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'campaign', 'create', $gdt_query_data);
        $logdata = array();
        $logdata['campaign_id']=$response['data']['campaign_id'];
        $logdata['config'] = $gdt_query_data;
        if($response['code']==0){
            $logdata['sync_status']='success';
            $logdata['sync_response']=  $response;
            Advertiser_Service_GdtCampaignModel::updateCampaign($logdata, $ourCampaignId);
            $this->output(0, '创建成功');
        }else{
            Advertiser_Service_GdtCampaignModel::deleteCampaign($ourCampaignId);
            $this->output(1, '创建失败:'. $response['message']);
        }
    }

    
}