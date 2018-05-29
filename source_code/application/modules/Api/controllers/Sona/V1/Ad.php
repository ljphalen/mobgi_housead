<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-12-7 15:07:52
 * $Id: Ad.php 62100 2016-12-7 15:07:52Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Sona_V1_AdController extends Api_BaseController {
   
    /**
     * 
     * 1.创建广告
     * ad/create
     * method: post
     * curl 'http://rock.advertiser.housead.com/v1/ad/create' -H 'Authorization: Bearer NCwxNDgxNjk1ODAwLGMwOTAwYTZmMmQzOWY5ZjBhMjk0NGVjYmRkYmFlYzMwNjRkZjhiYzY=' -d 'advertiser_id=4' -d 'unit_id=22' -d 'ad_name=adname_hunter04' -d 'target_type=1' -d 'target_url=http://baidu.com' -d 'package_name=testpackagename' -d 'charge_type=1' -d 'price=100' -d 'start_date=2016-12-13' -d 'end_date=2016-12-30' -d 'time_series=001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000' -d 'direct_id=3' -d 'outer_ad_id=14'
     * {"code":0,"msg":"","data":{"ad_id":"47","outer_ad_id":11}}
     */
    public function createAction() {
        $advertiser_id = $this->getPost('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $deliveryConfig = Common::getConfig('deliveryConfig');
        
        //必填,投放单元id
        $unit_id = intval($this->getPost('unit_id'));
        if(empty($unit_id)){
            $this->sonaOutput(31001, 'unit_id:parameter value should not be empty');
        }
        $unitsInfo = Dedelivery_Service_UnitConfModel::getBy(array('account_id'=>$advertiser_id, 'id'=>$unit_id));
        if(empty($unitsInfo)){
            $this->sonaOutput(31010, 'unit_id:object operated not exist');
        }
        
        //必填,广告名称
        $ad_name = trim($this->getPost('ad_name'));
        if(!$ad_name){
	        $this->sonaOutput(31001, 'ad_name:parameter value should not be empty');
	    }
	    if(Common::strLength($ad_name) >= 30){
	        $this->sonaOutput(31007, 'ad_name:string length is too long');
	    }
        $params = array();
	    $params['ad_name'] = $ad_name;
	    $params['account_id'] = $advertiser_id;
	    $ret = Dedelivery_Service_AdConfListModel::getBy($params);
	    if($ret){
	        $this->output(31013, 'ad_name:duplicate name is not allowed');
	    }
        
        //必填, 广告目标类型 3网页 2IOS应用 1Android应用
        $ad_target_type = $this->getPost('target_type');
        if(empty($ad_target_type)){
            $this->sonaOutput(31001, 'ad_target_type:parameter value should not be empty');
        }
        if(!isset($deliveryConfig['adTargetType'][$ad_target_type])){
            $this->sonaOutput(31017, 'ad_target_type:param not in enumeration list');
        }
        
        //必填, 广告目标地址 
        $ad_target = trim($this->getPost('target_url'));
        if(empty($ad_target)){
            $this->sonaOutput(31001, 'target_url:parameter value should not be empty');
        }
        if(!preg_match('/^(http|https)/i', $ad_target)){
            $this->sonaOutput(31005, 'target_url:invalid data format');
        }
        
        //可能必填, IOS和android应用必填
        $package_name = trim($this->getPost('package_name'));
        if(in_array($ad_target_type, array(1, 2)) && empty($package_name)){
            $this->sonaOutput(31001, 'package_name:parameter value should not be empty');
        }
        
        //非必填, 投放状态 1投放2暂停;
        $ad_status = intval($this->getPost('ad_status'));
        if($ad_status && !isset($deliveryConfig['adStatus'][$ad_status])){
            $this->sonaOutput(31017, 'ad_status:param not in enumeration list');
        }
        
        //必填, 计费类型 1CPC 2CPM  
        $charge_type = $this->getPost('charge_type');
        if(!isset($deliveryConfig['chargeTypeList'][$charge_type])){
            $this->sonaOutput(31017, 'charge_type:param not in enumeration list');
        }
        
        //必填, 出价，单位分
        $price = intval($this->getPost('price'));
        if(!is_numeric($price) || $price <= 0){
            $this->sonaOutput(31005, 'price:invalid data format');
        }
        
        //必填, 投放日期 (todo 新增start_date, end_date格式校验)
        $start_date = $this->getPost('start_date');
        $end_date = $this->getPost('end_date');
        if( strtotime($start_date) > strtotime($end_date) ){
            $this->sonaOutput(31099, 'start_date, end_date:parameter value error');
        }
        
        //非必填, 投放时间段  48*7 位字符串，且都是 0 或 1。也就是以半个小时为最小粒度，周一至周日每天分为 48 个区段，0 为不投放，1 为投放，不传、全传 0、全传 1 均代表全时段投放
        $time_series = $this->getPost('time_series'); #todo
        if($time_series){
            if(strlen($time_series) != 336){
                $this->sonaOutput(31099, 'time_series:parameter value error');
            }
            for($i=0; $i<336; $i++){
                if(!in_array($time_series[$i], array(0, 1)) ){
                    $this->sonaOutput(31005, 'time_series:invalid data format');
                }
            }
        }else{
            $time_series='';
        }
        
        //必填, 定向id 
        $direct_id = $this->getPost('direct_id');
        if(empty($direct_id)){
            $this->sonaOutput(31001, 'direct_id:parameter value should not be empty');
        }
        $directInfo = Advertiser_Service_DirectModel::getBy(array('advertiser_uid'=>$advertiser_id, 'id'=>$direct_id));
        if(empty($directInfo)){
            $this->sonaOutput(31010, 'direct_id:object operated not exist');
        }
        
        //非必填, 调用方广告id
        $outer_ad_id = intval($this->getPost('outer_ad_id'));
        if($outer_ad_id){
            if(Dedelivery_Service_AdConfListModel::getBy(array('account_id'=>$advertiser_id, 'outer_ad_id'=>$outer_ad_id))){
                $this->sonaOutput(31027, 'outer_ad_id:client outer key mapping created before service');
            }
        }
        
        $info = array();
        $info['ad_name'] = $ad_name;
        $info['ad_target_type'] = $ad_target_type;
        $info['ad_target'] = $ad_target;
        $info['package_name'] = $package_name;
        $info['unit'] = $unit_id;
        if($ad_status){
            $info['status'] = $ad_status==1?1:4;;
        }else{
            $info['status'] = 1;
        }
        $dateTmp = array();
	    $dateTmp['start_date'] = $start_date;
	    $dateTmp['end_date'] = $end_date;
	    $info['date_range'] =json_encode($dateTmp);
	    $info['time_series'] = $time_series;
        $info['charge_type'] = $charge_type;
        $info['price'] = $price*1.0/100;
        $info['account_id'] = $advertiser_id;
        $info['direct_id'] = $direct_id;
        $info['outer_ad_id'] = $outer_ad_id;
        $adid = Dedelivery_Service_AdConfListModel::add($info);
	    if (!$adid){
	        $this->sonaOutput(30000, 'unknown internal error, please try again later');
	    }
        
        /*操作日志start*/
        $data = array();
        $data['module'] = 'adver_delivery';
        $data['sub_module'] = 'add_ad';
        $data['content'].=$adid.','.$ad_name.',unitid:'.$unit_id;
        $this->addSonaOperatelog($advertiser_id, $data);
        /*操作日志end*/
        
	    $this->sonaOutput(0, '',array('ad_id'=>$adid,'outer_ad_id'=>$outer_ad_id));
    }
    
    /**
     * 2.读取广告
     * ad/read
     * method: get
     * curl -G 'http://rock.advertiser.housead.com/v1/ad/read' -H 'Authorization: Bearer NCwxNDgxNjk1ODAwLGMwOTAwYTZmMmQzOWY5ZjBhMjk0NGVjYmRkYmFlYzMwNjRkZjhiYzY=' -d 'advertiser_id=4' -d 'ad_id=50'
     * {"code":0,"msg":"","data":{"ad_id":"50","unit_id":"22","ad_name":"adname_hunter04","target_type":"1","target_url":"http:\/\/baidu.com","package_name":"testpackagename","ad_status":1,"charge_type":"1","price":100,"start_date":"2016-12-13","end_date":"2016-12-30","time_series":"001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000","direct_id":"3","outer_ad_id":"14","create_time":1481696845,"update_time":1481696847}}
     */
    public function readAction() {
        $advertiser_id = $this->getGet('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $ad_id = intval($this->getGet('ad_id'));
        if(empty($ad_id)){
            $this->sonaOutput(31000, 'ad_id:lack required parameters');
        }
        
        $adInfo = Dedelivery_Service_AdConfListModel::getBy(array('account_id'=>$advertiser_id, 'id'=>$ad_id));
        if(empty($adInfo)){
            $this->sonaOutput(31010, 'ad_id:object operated not exist');
        }
        
        $outerAdInfo = array();
        $outerAdInfo['ad_id'] = $adInfo['id'];
        $outerAdInfo['unit_id'] = $adInfo['unit'];
        $outerAdInfo['ad_name'] = $adInfo['ad_name'];
        $outerAdInfo['target_type'] = $adInfo['ad_target_type'];
        $outerAdInfo['target_url'] = $adInfo['ad_target'];
        $outerAdInfo['package_name'] = $adInfo['package_name'];
        $outerAdInfo['ad_status'] = $adInfo['status']==1?1:2;
        $outerAdInfo['charge_type'] = $adInfo['charge_type'];
        $outerAdInfo['price'] = $adInfo['price'] * $this->OuterInnerRate;
        $dataRangeArr = json_decode($adInfo['date_range'], true);
        $outerAdInfo['start_date'] = $dataRangeArr['start_date'];
        $outerAdInfo['end_date'] = $dataRangeArr['end_date'];
        $outerAdInfo['time_series'] = $adInfo['time_series'];
        $outerAdInfo['direct_id'] = $adInfo['direct_id'];
        $outerAdInfo['outer_ad_id'] = $adInfo['outer_ad_id'];
        $outerAdInfo['create_time'] = strtotime($adInfo['create_time']);
        $outerAdInfo['update_time'] = strtotime($adInfo['update_time']);
        $this->sonaOutput(0, '', $outerAdInfo);
        
    }
    
    /**
     * 
     * 3.更新广告
     * ad/update
     * method: post
     * curl 'http://rock.advertiser.housead.com/v1/ad/update' -H 'Authorization: Bearer NCwxNDgxNjk1ODAwLGMwOTAwYTZmMmQzOWY5ZjBhMjk0NGVjYmRkYmFlYzMwNjRkZjhiYzY=' -d 'advertiser_id=4' -d 'ad_id=50' -d 'ad_name=adname_hunter04' -d 'target_type=1' -d 'target_url=http://baidu.com' -d 'package_name=testpackagename' -d 'charge_type=1' -d 'price=100' -d 'start_date=2016-12-13' -d 'end_date=2016-12-30' -d 'time_series=001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000' -d 'direct_id=3'
     * {"code":0,"msg":"","data":{"ad_id":50}}
     */
    public function updateAction() {
        $advertiser_id = $this->getPost('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $deliveryConfig = Common::getConfig('deliveryConfig');
        
        //必填,广告id
        $ad_id = intval($this->getPost('ad_id'));
        if(empty($ad_id)){
            $this->sonaOutput(31001, 'ad_id:parameter value should not be empty');
        }
        $adInfo = Dedelivery_Service_AdConfListModel::getBy(array('account_id'=>$advertiser_id, 'id'=>$ad_id));
        if(empty($adInfo)){
            $this->sonaOutput(31010, 'ad_id:object operated not exist');
        }
        
        //必填,广告名称
        $ad_name = trim($this->getPost('ad_name'));
        if(!$ad_name){
	        $this->sonaOutput(31001, 'ad_name:parameter value should not be empty');
	    }
	    if(Common::strLength($ad_name) >= 30){
	        $this->sonaOutput(31007, 'ad_name:string length is too long');
	    }
        $params = array();
	    $params['ad_name'] = $ad_name;
	    $params['account_id'] = $advertiser_id;
        $params['id'] = array('!=',$ad_id);
	    $ret = Dedelivery_Service_AdConfListModel::getBy($params);
	    if($ret){
	        $this->output(31013, 'ad_name:duplicate name is not allowed');
	    }
        
        //必填, 广告目标类型 3网页 2IOS应用 1Android应用
        $ad_target_type = $this->getPost('target_type');
        if(empty($ad_target_type)){
            $this->sonaOutput(31001, 'ad_target_type:parameter value should not be empty');
        }
        if(!isset($deliveryConfig['adTargetType'][$ad_target_type])){
            $this->sonaOutput(31017, 'ad_target_type:param not in enumeration list');
        }
        
        //必填, 广告目标地址 
        $ad_target = trim($this->getPost('target_url'));
        if(empty($ad_target)){
            $this->sonaOutput(31001, 'target_url:parameter value should not be empty');
        }
        if(!preg_match('/^(http|https)/i', $ad_target)){
            $this->sonaOutput(31005, 'target_url:invalid data format');
        }
        
        //可能必填, IOS和android应用必填 1Android应用, 2IOS应用, 3网页
        $package_name = trim($this->getPost('package_name'));
        if(in_array($ad_target_type, array(1, 2)) && empty($package_name)){
            $this->sonaOutput(31001, 'package_name:parameter value should not be empty');
        }
        
        //非必填, 投放状态 1投放2暂停;
        $ad_status = intval($this->getPost('ad_status'));
        if($ad_status && !isset($deliveryConfig['adStatus'][$ad_status])){
            $this->sonaOutput(31017, 'ad_status:param not in enumeration list');
        }
        
        //必填, 计费类型 1CPC 2CPM  
        $charge_type = $this->getPost('charge_type');
        if(!isset($deliveryConfig['chargeTypeList'][$charge_type])){
            $this->sonaOutput(31017, 'charge_type:param not in enumeration list');
        }
        
        //必填, 出价，单位分
        $price = intval($this->getPost('price'));
        if(!is_numeric($price) || $price <= 0){
            $this->sonaOutput(31005, 'price:invalid data format');
        }
        
        //必填, 投放日期 (todo 新增start_date, end_date格式校验)
        $start_date = $this->getPost('start_date');
        $end_date = $this->getPost('end_date');
        if( strtotime($start_date) > strtotime($end_date) ){
            $this->sonaOutput(31099, 'start_date, end_date:parameter value error');
        }
        
        //非必填, 投放时间段  48*7 位字符串，且都是 0 或 1。也就是以半个小时为最小粒度，周一至周日每天分为 48 个区段，0 为不投放，1 为投放，不传、全传 0、全传 1 均代表全时段投放
        $time_series = $this->getPost('time_series'); #todo
        if($time_series){
            if(strlen($time_series) != 336){
                $this->sonaOutput(31099, 'time_series:parameter value error');
            }
            for($i=0; $i<336; $i++){
                if(!in_array($time_series[$i], array(0, 1)) ){
                    $this->sonaOutput(31005, 'time_series:invalid data format');
                }
            }
        }else{
            $time_series='';
        }
        
        //必填, 定向id 
        $direct_id = $this->getPost('direct_id');
        if(empty($direct_id)){
            $this->sonaOutput(31001, 'direct_id:parameter value should not be empty');
        }
        $directInfo = Advertiser_Service_DirectModel::getBy(array('advertiser_uid'=>$advertiser_id, 'id'=>$direct_id));
        if(empty($directInfo)){
            $this->sonaOutput(31010, 'direct_id:object operated not exist');
        }
        
        $info = array();
        $info['ad_name'] = $ad_name;
        $info['ad_target_type'] = $ad_target_type;
        $info['ad_target'] = $ad_target;
        $info['package_name'] = $package_name;
        if($ad_status){
            $info['status'] = $ad_status==1?1:4;;
        }else{
            $info['status'] = 1;
        }
        $dateTmp = array();
	    $dateTmp['start_date'] = $start_date;
	    $dateTmp['end_date'] = $end_date;
	    $info['date_range'] =json_encode($dateTmp);
	    $info['time_series'] = $time_series;
        $info['charge_type'] = $charge_type;
        $info['price'] = $price*1.0/100;
        $info['account_id'] = $advertiser_id;
        $info['direct_id'] = $direct_id;
        $result = Dedelivery_Service_AdConfListModel::updateBy($info, array('id'=>$ad_id, 'account_id'=>$advertiser_id));
	    if (!$result){
	        $this->sonaOutput(30000, 'unknown internal error, please try again later');
	    }
        
        /*操作日志start*/
        $logdata = array();
        $logdata['module'] = 'adver_delivery';
        $logdata['sub_module'] = 'edit_ad';
        $logdata['content'] = '';
        $old = '';
        $new = '';
        if($adInfo['ad_name']!=$info['ad_name']){
            $old .= 'name:'.$adInfo['ad_name'].';';
            $new .= 'name:'.$info['ad_name'].';';
        }
        if($adInfo['ad_target_type'] != $info['ad_target_type']){
            $old .= 'ad_target_type:'.$adInfo['ad_target_type'].';';
            $new .= 'ad_target_type:'.$info['ad_target_type'].';';
        }
        if($adInfo['ad_target'] != $info['ad_target']){
            $old .= 'ad_target:'.$adInfo['ad_target'].';';
            $new .= 'ad_target:'.$info['ad_target'].';';
        }
        if($adInfo['package_name'] != $info['package_name'] ){
            $old .= 'package_name:'.$adInfo['package_name'].';';
            $new .= 'package_name:'.$info['package_name'].';';
        }
        if($adInfo['status'] != $info['status']){
            $old .= 'status:'.$adInfo['status'].';';
            $new .= 'status:'.$info['status'].';';
        }
        if($adInfo['date_range'] != $info['date_range']){
            $old .= 'date_range:'.$adInfo['date_range'].';';
            $new .= 'date_range:'.$info['date_range'].';';
        }
        if($adInfo['time_series'] != $info['time_series']){
            $old .= 'time_series:'.$adInfo['time_series'].';';
            $new .= 'time_series:'.$info['time_series'].';';
        }
        if($adInfo['charge_type'] != $info['charge_type']){
            $old .= 'charge_type:'.$adInfo['charge_type'].';';
            $new .= 'charge_type:'.$info['charge_type'].';';
        }
        if($adInfo['price'] != $info['price']){
            $old .= 'price:'.$adInfo['price'].';';
            $new .= 'price:'.$info['price'].';';
        }
        if($old || $new){
            $logdata['content'].=$adInfo['id'].', old:  '.$old.'   new:  '. $new;
        }else{
            $logdata['content'].=$adInfo['id'].', 无更新';
        }
        $this->addSonaOperatelog($advertiser_id, $logdata);
        /*操作日志end*/
        
	    $this->sonaOutput(0, '',array('ad_id'=>$ad_id));
    }

    /**
     * 4.获取广告列表
     * ad/select
     * method: get
     * curl -G 'http://rock.advertiser.housead.com/v1/ad/select' -H 'Authorization: Bearer NCwxNDgxNjk1ODAwLGMwOTAwYTZmMmQzOWY5ZjBhMjk0NGVjYmRkYmFlYzMwNjRkZjhiYzY=' -d 'advertiser_id=4' -d 'page=1' -d 'page_size=1'
     * {"code":0,"msg":"","data":{"list":[{"id":"50","ad_name":"adname_hunter05","ad_target_type":"1","ad_target":"http:\/\/baidu.com\/xx","package_name":"testpackagename","unit":"22","date_type":"0","date_range":"{\"start_date\":\"2016-12-13\",\"end_date\":\"2016-12-30\"}","time_type":"0","time_range":null,"time_series":"001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000","charge_type":"1","price":"1.0000","create_time":1481696845,"update_time":1481696847,"account_id":"4","status":"1","del":"0","direct_id":"3","outer_ad_id":"14","direct_config":null}],"page_info":{"total_num":"6","total_page":6,"page_size":1,"page":1}}}
     */
    public function selectAction() {
        $advertiser_id = $this->getGet('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput('31000', 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $page = intval($this->getGet('page'));
        if($page < 0){
            $this->sonaOutput(31016, 'page:parameter value is too tiny');
        }
        if(empty($page)){
            $page = 1;
        }
        
        $page_size = intval($this->getGet('page_size'));
        if($page_size > $this->maxPageSize){
            $this->sonaOutput(31015, 'page_size:parameter value is too huge');
        }
        if($page_size < 0 ){
            $this->sonaOutput(31016, 'page_size:parameter value is too tiny');
        }
        if($page_size == 0){
            $page_size = $this->perpage;
        }
        
        $params = array();
        $params['account_id'] = $advertiser_id;
        $params['del'] = 0;
        list($total, $adList) = Dedelivery_Service_AdConfListModel::getList($page, $page_size, $params);
        if($adList){
            foreach($adList as $key=>$ad){
                $adList[$key]['create_time'] = strtotime($ad['create_time']);
                $adList[$key]['update_time'] = strtotime($ad['update_time']);
            }
        }
        $page_info = array('total_num'=>$total, 'total_page'=>ceil($total*1.0/$page_size), 'page_size'=>$page_size, 'page'=>$page );
        
        $this->sonaOutput(0, '',array('list'=>$adList, 'page_info'=>$page_info));
    }
    
    /**
     * 5.删除广告
     * ad/delete
     * method: post
     * curl 'http://rock.advertiser.housead.com/v1/ad/delete' -H 'Authorization: Bearer NCwxNDgxNjk1ODAwLGMwOTAwYTZmMmQzOWY5ZjBhMjk0NGVjYmRkYmFlYzMwNjRkZjhiYzY=' -d 'advertiser_id=4' -d 'ad_id=50'
     *  {"code":0,"msg":"","data":{"ad_id":50}}
     */
    public function deleteAction() {
        $advertiser_id = $this->getPost('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput('31000', 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        //必填,投放单元id
        $ad_id = intval($this->getPost('ad_id'));
        if(empty($ad_id)){
            $this->sonaOutput(31001, 'ad_id:parameter value should not be empty ');
        }
        $adInfo = Dedelivery_Service_AdConfListModel::getBy(array('account_id'=>$advertiser_id, 'id'=>$ad_id));
        if(empty($adInfo)){
            $this->sonaOutput(31010, 'ad_id:object operated not exist');
        }
        
        $result = Dedelivery_Service_AdConfListModel::updateBy(array('del'=>1), array('id'=>$ad_id, 'account_id'=>$advertiser_id));
	    if (!$result){
	        $this->sonaOutput(30000, 'unknown internal error, please try again later');
	    }
        
        /*操作日志start*/
        $logdata = array();
        $logdata['module'] = 'adver_delivery';
        $logdata['sub_module'] = 'del_ad';
        $logdata['content'] = '';
        $old = '';
        $new = '';
        $old .= 'del:0'.';';
        $new .= 'del:1'.';';
        $logdata['content'].=$adInfo['id'].', name:'.$adInfo['ad_name']. ', old:  '.$old.'   new:  '. $new;
        $this->addSonaOperatelog($advertiser_id, $logdata);
        /*操作日志end*/
        
        $this->sonaOutput(0, '',array('ad_id'=>$ad_id));
    }



}

