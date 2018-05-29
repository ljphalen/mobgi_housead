<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-12-7 15:05:17
 * $Id: Unit.php 62100 2016-12-7 15:05:17Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Sona_V1_UnitController extends Api_BaseController {
    
    private $unitDailyLimitLeast = 5000;    //投放单元最低限额 单位:分
    private $unitDailyLimitMost = 10000000; //投放单元最高限额 单位:分
    private $unitDailyLimitConsumeTodayDiff = 5000; //投放限额与今日消费要大于5000以上 单位:分
    
    public function testAction(){
        
    }
   
    /**
     * 
     * 1.创建投放单元
     * unit/create
     * method: post
     * curl 'http://rock.advertiser.housead.com/v1/unit/create' -H 'Authorization: Bearer NCwxNDgxMjY3Mzg5LDJlOWJmMjg1ODljNzU1N2E0ZWRhMWQ2MDQyZTUwM2JjMjlhMDVkOGY=' -d 'advertiser_id=4' -d 'unit_name=hunter_test_unitname2016120908' -d 'daily_limit=6000' -d 'outer_unit_id=4'
     * {"code":0,"msg":"","data":{"unit_id":"24","outer_unit_id":4}}
     */
    public function createAction() {
        $advertiser_id = $this->getPost('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $deliveryConfig = Common::getConfig('deliveryConfig');
        
        //必填,投放单元名称
        $unit_name = trim($this->getPost('unit_name'));
        if(!$unit_name){
	        $this->sonaOutput(31001, 'unit_name:parameter value should not be empty ');
	    }
	    if(Common::strLength($unit_name) >= 30){
	        $this->sonaOutput(31007, 'unit_name:string length is too long');
	    }
        $params = array();
	    $params['name'] = $unit_name;
	    $params['account_id'] = $advertiser_id;
	    $ret = Dedelivery_Service_UnitConfModel::getBy($params);
	    if($ret){
	        $this->output(31013, 'unit_name:duplicate name is not allowed  ');
	    }
        
        //非必填
        $mode_type = $this->getPost('mode_type');
        if($mode_type && !isset($deliveryConfig['modeType'][$mode_type])){
            $this->sonaOutput(31017, 'mode_type:param not in enumeration list ');
        }
        
        //投放单元状态 非必填
        $unit_status = $this->getPost('unit_status');
        if($unit_status && !isset($deliveryConfig['unitStatus'][$unit_status])){
            $this->sonaOutput(31017, 'unit_status:param not in enumeration list ');
        }
        
        //日消耗 必填
        $daily_limit = intval($this->getPost('daily_limit'));
        if($daily_limit > $this->unitDailyLimitMost){
            $this->sonaOutput(31015, 'daily_limit:parameter value is too huge');
        }
        if($daily_limit < 0 || ($daily_limit>0 && $daily_limit<$this->unitDailyLimitLeast) ){
            $this->sonaOutput(31016, 'daily_limit:parameter value is too tiny');
        }
        $todayConsume = Advertiser_Service_AccountDayConsumeModel::getTodayConsumption($advertiser_id);
        if($daily_limit && ($daily_limit - $todayConsume * $this->OuterInnerRate <= $this->unitDailyLimitConsumeTodayDiff) ){
            $this->output(31023, 'daily_limit: daily budget out of the lower limit');
        }
        
        //调用方投放单元id
        $outer_unit_id = intval($this->getPost('outer_unit_id'));
        if($outer_unit_id){
            if(Dedelivery_Service_UnitConfModel::getBy(array('account_id'=>$advertiser_id, 'outer_unit_id'=>$outer_unit_id))){
                $this->sonaOutput(31027, 'outer_unit_id:client outer key mapping created before service ');
            }
        }
        
        $info = array();
	    $info['account_id'] =  $advertiser_id;
        $info['name'] =  $unit_name;
        if($daily_limit == 0){
            $info['limit_type'] =  0;
            $info['limit_range'] =  0;
        }else{
            $info['limit_type'] =  1;
            $info['limit_range'] =  $daily_limit;
        }
        if($mode_type){ //1匀速2普通
            $info['mode_type'] =  $mode_type;
        }else{
            $info['mode_type'] =  2;
        }
        $info['outer_unit_id'] = $outer_unit_id;
        if($unit_status){//1投放2暂停
            $info['status'] =  $unit_status;
        }else{
            $info['status'] =  1;
        }
	    $result = Dedelivery_Service_UnitConfModel::add($info);
	    if (!$result){
	        $this->sonaOutput(30000, 'unknown internal error, please try again later');
	    }
        
        /*操作日志start*/
        $data['module'] = 'adver_delivery';
        $data['sub_module'] = 'add_unit';
        $data['content'] = $result.',' .$info['name'].',' .$info['limit_type'].',' .$info['limit_range'].',' .$info['mode_type'];
        $this->addSonaOperatelog($advertiser_id, $data);
        /*操作日志end*/
        
	    $this->sonaOutput(0, '',array('unit_id'=>$result,'outer_unit_id'=>$outer_unit_id));
    }
    
    /**
     * 2.读取投放单元
     * unit/read
     * method: get
     * curl -G 'http://rock.advertiser.housead.com/v1/unit/read' -H 'Authorization: Bearer NCwxNDgxMjY3Mzg5LDJlOWJmMjg1ODljNzU1N2E0ZWRhMWQ2MDQyZTUwM2JjMjlhMDVkOGY=' -d 'advertiser_id=4' -d 'unit_id=24' 
     * {"code":0,"msg":"","data":{"unit_id":"24","unit_name":"hunter_test_unitname2016120908","unit_status":"1","daily_limit":"6000.00","outer_unit_id":"4","create_time":1481268137,"update_time":1481268137}}
     */
    public function readAction() {
        $advertiser_id = $this->getGet('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $unit_id = intval($this->getGet('unit_id'));
        if(empty($unit_id)){
            $this->sonaOutput(31000, 'unit_id:lack required parameters');
        }
        
        $unitInfo = Dedelivery_Service_UnitConfModel::getBy(array('account_id'=>$advertiser_id, 'id'=>$unit_id));
        if(empty($unitInfo)){
            $this->sonaOutput(31010, 'unit_id:object operated not exist');
        }
        
        $outerUnitInfo = array();
        $outerUnitInfo['unit_id'] = $unitInfo['id'];
        $outerUnitInfo['unit_name'] = $unitInfo['name'];
        $outerUnitInfo['unit_status'] = $unitInfo['status'];
        $outerUnitInfo['daily_limit'] = $unitInfo['limit_type']==0?'0':$unitInfo['limit_range'];
        $outerUnitInfo['outer_unit_id'] = $unitInfo['outer_unit_id'];
        $outerUnitInfo['create_time'] = strtotime($unitInfo['create_time']);
        $outerUnitInfo['update_time'] = strtotime($unitInfo['update_time']);
        $this->sonaOutput(0, '', $outerUnitInfo);
    }
    
    /**
     * 3.更新投放单元
     * unit/update
     * method: post
     * curl 'http://rock.advertiser.housead.com/v1/unit/update' -H 'Authorization: Bearer NCwxNDgxNjk1ODAwLGMwOTAwYTZmMmQzOWY5ZjBhMjk0NGVjYmRkYmFlYzMwNjRkZjhiYzY=' -d 'advertiser_id=4' -d 'unit_id=23' -d 'unit_name=hunter_test_unitame2016121309' -d 'daily_limit=6000'
     * {"code":0,"msg":"","data":{"unit_id":26}}
     */
    public function updateAction() {
        $advertiser_id = $this->getPost('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput('31000', 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $deliveryConfig = Common::getConfig('deliveryConfig');
        
        //必填,投放单元id
        $unit_id = intval($this->getPost('unit_id'));
        if(empty($unit_id)){
            $this->sonaOutput(31001, 'unit_id:parameter value should not be empty ');
        }
        $unitsInfo = Dedelivery_Service_UnitConfModel::getBy(array('account_id'=>$advertiser_id, 'id'=>$unit_id));
        if(empty($unitsInfo)){
            $this->sonaOutput(31010, 'unit_id:object operated not exist');
        }
        
        //必填,投放单元名称
        $unit_name = trim($this->getPost('unit_name'));
        if(!$unit_name){
	        $this->sonaOutput(31001, 'unit_name:parameter value should not be empty ');
	    }
	    if(Common::strLength($unit_name) >= 30){
	        $this->sonaOutput(31007, 'unit_name:string length is too long');
	    }
        $params = array();
	    $params['name'] = $unit_name;
	    $params['account_id'] = $advertiser_id;
        $params['id'] = array('!=',$unit_id);
	    $ret = Dedelivery_Service_UnitConfModel::getBy($params);
	    if($ret){
	        $this->output(31013, 'unit_name:duplicate name is not allowed  ');
	    }
        
        //非必填
        $mode_type = $this->getPost('mode_type');
        if($mode_type && !isset($deliveryConfig['modeType'][$mode_type])){
            $this->sonaOutput(31017, 'mode_type:param not in enumeration list ');
        }
        
        //投放单元状态 非必填
        $unit_status = $this->getPost('unit_status');
        if($unit_status && !isset($deliveryConfig['unitStatus'][$unit_status])){
            $this->sonaOutput(31017, 'unit_status:param not in enumeration list ');
        }
        
        //日消耗 必填
        $daily_limit = intval($this->getPost('daily_limit'));
        if($daily_limit > $this->unitDailyLimitMost){
            $this->sonaOutput(31015, 'daily_limit:parameter value is too huge');
        }
        if($daily_limit < 0 || ($daily_limit>0 && $daily_limit<$this->unitDailyLimitLeast) ){
            $this->sonaOutput(31016, 'daily_limit:parameter value is too tiny');
        }
        $todayConsume = Advertiser_Service_AccountDayConsumeModel::getTodayConsumption($advertiser_id);
        if($daily_limit && ($daily_limit - $todayConsume * $this->OuterInnerRate <= $this->unitDailyLimitConsumeTodayDiff) ){
            $this->output(1, 'daily_limit: todo ');
        }
        
        
        $info = array();
	    $info['account_id'] =  $advertiser_id;
        $info['name'] =  $unit_name;
        if($daily_limit == 0){
            $info['limit_type'] =  0;
            $info['limit_range'] =  0;
        }else{
            $info['limit_type'] =  1;
            $info['limit_range'] =  $daily_limit;
        }
        if($mode_type){ //1匀速2普通
            $info['mode_type'] =  $mode_type;
        }else{
            $info['mode_type'] =  2;
        }
        if($unit_status){//1投放2暂停
            $info['status'] =  $unit_status;
        }else{
            $info['status'] =  1;
        }
	    $result = Dedelivery_Service_UnitConfModel::updateBy($info, array('id'=>$unit_id, 'account_id'=>$advertiser_id));
	    if (!$result){
	        $this->sonaOutput(30000, 'unknown internal error, please try again later');
	    }
        
        /*操作日志start*/
        $logdata = array();
        $logdata['module'] = 'adver_delivery';
        $logdata['sub_module'] = 'edit_unit';
        $logdata['content'] = '';
        $old = '';
        $new = '';
        if($unitsInfo['name'] != $info['name']){
            $old .= 'name:'.$unitsInfo['name'].';';
            $new .= 'name:'.$info['name'].';';
        }
        if($unitsInfo['status'] != $info['status']){
            $old .= 'status:'.$unitsInfo['status'].';';
            $new .= 'status:'.$info['status'].';';
        }
        if($unitsInfo['limit_type'] != $info['limit_type']){
            $old .= 'limit_type:'.$unitsInfo['limit_type'].';';
            $new .= 'limit_type:'.$info['limit_type'].';';
        }
        if($unitsInfo['limit_range'] != $info['limit_range']){
            $old .= 'limit_range:'.$unitsInfo['limit_range'].';';
            $new .= 'limit_range:'.$info['limit_range'].';';
        }
        if($unitsInfo['mode_type'] != $info['mode_type']){
            $old .= 'mode_type:'.$unitsInfo['mode_type'].';';
            $new .= 'mode_type:'.$info['mode_type'].';';
        }
        if($old || $new){
            $logdata['content'].=$unitsInfo['id'].', old:  '.$old.'   new:  '. $new;
        }else{
            $logdata['content'].=$unitsInfo['id'].', 无更新';
        }
        $this->addSonaOperatelog($advertiser_id, $logdata);
        /*操作日志end*/
        
	    $this->sonaOutput(0, '',array('unit_id'=>$unit_id));
    }
    
    /**
     * 4.获取投放单元列表
     * unit/select
     * method: get
     * curl -G 'http://rock.advertiser.housead.com/v1/unit/select' -H 'Authorization: Bearer NCwxNDgxNjk1ODAwLGMwOTAwYTZmMmQzOWY5ZjBhMjk0NGVjYmRkYmFlYzMwNjRkZjhiYzY=' -d 'advertiser_id=4' -d 'page=1' -d 'page_size=1'
     * {"code":0,"msg":"","data":{"list":[{"id":"22","name":"hunter_test_unitname20161213","limit_type":"1","limit_range":"6000.00","mode_type":"2","create_time":1481610619,"update_time":1481610621,"account_id":"4","outer_unit_id":"4","status":"1","del":"0"}],"page_info":{"total_num":"2","total_page":2,"page_size":1,"page":1}}}
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
        list($total, $unitList) = Dedelivery_Service_UnitConfModel::getList($page, $page_size, $params);
        if($unitList){
            foreach($unitList as $key=>$unit){
                $unitList[$key]['create_time'] = strtotime($unit['create_time']);
                $unitList[$key]['update_time'] = strtotime($unit['update_time']);
            }
        }
        $page_info = array('total_num'=>$total, 'total_page'=>ceil($total*1.0/$page_size), 'page_size'=>$page_size, 'page'=>$page );
        
        $this->sonaOutput(0, '',array('list'=>$unitList, 'page_info'=>$page_info));
    }
    
    /**
     * 5.删除投放单元
     * unit/delete
     * method: post
     * curl 'http://rock.advertiser.housead.com/v1/unit/delete' -H 'Authorization: Bearer NCwxNDgxNTkzNTgwLGE4MjlkMzY2ZTI3YzA2MWQyOTg3YWVmOTc4ODkxMmE1OupdatezNjA=' -d 'advertiser_id=4' -d 'unit_id=23'
     *  {"code":0,"msg":"","data":{"unit_id":23}}
     */
    public function deleteAction() {
        $advertiser_id = $this->getPost('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput('31000', 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        //必填,投放单元id
        $unit_id = intval($this->getPost('unit_id'));
        if(empty($unit_id)){
            $this->sonaOutput(31001, 'unit_id:parameter value should not be empty ');
        }
        $unitsInfo = Dedelivery_Service_UnitConfModel::getBy(array('account_id'=>$advertiser_id, 'id'=>$unit_id));
        if(empty($unitsInfo)){
            $this->sonaOutput(31010, 'unit_id:object operated not exist');
        }
        
        $result = Dedelivery_Service_UnitConfModel::updateBy(array('del'=>1), array('id'=>$unit_id, 'account_id'=>$advertiser_id));
	    if (!$result){
	        $this->sonaOutput(30000, 'unknown internal error, please try again later');
	    }
        
        /*操作日志start*/
        $logdata = array();
        $logdata['module'] = 'adver_delivery';
        $logdata['sub_module'] = 'del_unit';
        $logdata['content'] = '';
        $old = '';
        $new = '';
        $old .= 'del:0'.';';
        $new .= 'del:1'.';';
        $logdata['content'].=$unitsInfo['id'].', name:'.$unitsInfo['name']. ', old:  '.$old.'   new:  '. $new;
        $this->addSonaOperatelog($advertiser_id, $logdata);
        /*操作日志end*/
        
        $this->sonaOutput(0, '',array('unit_id'=>$unit_id));
    }

}

