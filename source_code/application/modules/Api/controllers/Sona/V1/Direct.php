<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-12-7 15:05:35
 * $Id: Direct.php 62100 2016-12-7 15:05:35Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Sona_V1_DirectController extends Api_BaseController {
   
    /**
     * 
     * 1.创建定向
     * direct/create
     * method: post
     * curl 'http://rock.advertiser.housead.com/v1/direct/create' -H 'Authorization: Bearer NCwxNDgxODUyNTY3LDViNzJlZmFkM2FlNjFiOTYxZDE3YmFjNmU4OTEyZjhkYzM5MWU4NzE=' -d 'advertiser_id=4' -d 'direct_name=hunter_directname2016121511' -d 'direct_config={"area":[1,2],"os":2,"network":[1,2,3],"operator":[1,2],"mobile_brand":[1,2,3],"screen_size":[1,2,3]}' -d 'outer_direct_id=4'
     * {"code":0,"msg":"","data":{"direct_id":"38","outer_direct_id":4}}
     */
    public function createAction() {
        $advertiser_id = $this->getPost('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $deliveryConfig = Common::getConfig('deliveryConfig');
        
        //必填,定向名称
        $direct_name = trim($this->getPost('direct_name'));
        if(!$direct_name){
	        $this->sonaOutput(31001, 'direct_name:parameter value should not be empty');
	    }
	    if(Common::strLength($direct_name) >= 30){
	        $this->sonaOutput(31007, 'direct_name:string length is too long');
	    }
        $params = array();
	    $params['direct_name'] = $direct_name;
	    $params['advertiser_uid'] = $advertiser_id;
	    $ret = Advertiser_Service_DirectModel::getBy($params);
	    if($ret){
	        $this->output(31013, 'direct_name:duplicate name is not allowed');
	    }
        
        //必填,定向名称(校验定向配置)
        $direct_config = html_entity_decode($this->getPost('direct_config'));
        if(!$direct_config){
	        $this->sonaOutput(31001, 'direct_config:parameter value should not be empty');
	    }
        if(Common::is_json($direct_config)){
            $direcr_config_arr = json_decode($direct_config, TRUE);
        }else{
            $this->sonaOutput(31005, 'direct_config:invalid data format');
        }
        if(!is_array($direcr_config_arr)){
            $this->sonaOutput(31005, 'direct_config:invalid data format ');
        }
        if(isset($direcr_config_arr['area'])){
            if($direcr_config_arr['area'] ){
                foreach($direcr_config_arr['area'] as $item){
                    if(!isset($deliveryConfig['provinceList'][$item])){
                        $this->sonaOutput(31017, 'direct_config area:param not in enumeration list');
                    }
                }
            }
        }else{
            $direcr_config_arr['area'] = 0;
        }
        if(isset($direcr_config_arr['os'])){
            if(is_array($direcr_config_arr['os'])){
                $this->sonaOutput(31005, 'direct_config os:invalid data format ');
            }
            if(!isset($deliveryConfig['osTypeList'][$direcr_config_arr['os']])){
                $this->sonaOutput(31017, 'direct_config os:param not in enumeration list');
            }
        }else{
            $direcr_config_arr['os'] = 0;
        }
        if(isset($direcr_config_arr['network'])){
            if($direcr_config_arr['network'] ){
                foreach($direcr_config_arr['network'] as $item){
                    if(!isset($deliveryConfig['netWorkList'][$item])){
                        $this->sonaOutput(31017, 'direct_config network:param not in enumeration list');
                    }
                }
            }
        }else{
            $direcr_config_arr['network'] = 0;
        }
        if(isset($direcr_config_arr['operator'])){
            if($direcr_config_arr['operator'] ){
                foreach($direcr_config_arr['operator'] as $item){
                    if(!isset($deliveryConfig['operatorList'][$item])){
                        $this->sonaOutput(31017, 'direct_config operator:param not in enumeration list');
                    }
                }
            }
        }else{
            $direcr_config_arr['operator'] = 0;
        }
        if(isset($direcr_config_arr['mobile_brand'])){
            if($direcr_config_arr['mobile_brand'] ){
                foreach($direcr_config_arr['mobile_brand'] as $item){
                    if(!isset($deliveryConfig['brandList'][$item])){
                        $this->sonaOutput(31017, 'direct_config mobile_brand:param not in enumeration list');
                    }
                }
            }
        }else{
            $direcr_config_arr['mobile_brand'] = 0;
        }
        if(isset($direcr_config_arr['screen_size'])){
            if($direcr_config_arr['screen_size'] ){
                foreach($direcr_config_arr['screen_size'] as $item){
                    if(!isset($deliveryConfig['screenList'][$item])){
                        $this->sonaOutput(31017, 'direct_config area:param not in enumeration list');
                    }
                }
            }
        }else{
            $direcr_config_arr['screen_size'] = 0;
        }
        
        //非必填,调用方定向id
        $outer_direct_id = intval($this->getPost('outer_direct_id'));
        if($outer_direct_id){
            if(Advertiser_Service_DirectModel::getBy(array('advertiser_uid'=>$advertiser_id, 'outer_direct_id'=>$outer_direct_id))){
                $this->sonaOutput(31027, 'outer_direct_id:client outer key mapping created before service');
            }
	    }
        
        $info = array();
        $info['direct_name'] = $direct_name;
        $db_direct_config_arr = array();
        $db_direct_config_arr['isSona'] = 1;
        if($direcr_config_arr['area'] == 0){
            $db_direct_config_arr['area_type'] = 0;
            $db_direct_config_arr['area_range'] = array();
        }else{
            $db_direct_config_arr['area_type'] = 1;
            $db_direct_config_arr['area_range'] = $direcr_config_arr['area'];
        }
        $db_direct_config_arr['os_direct_type'] = $direcr_config_arr['os'];
        if($direcr_config_arr['network'] == 0){
            $db_direct_config_arr['network_direct_type'] = 0;
            $db_direct_config_arr['network_direct_range'] = array();
        }else{
            $db_direct_config_arr['network_direct_type'] = 1;
            $db_direct_config_arr['network_direct_range'] = $direcr_config_arr['network'];
        }
        if($direcr_config_arr['operator'] == 0){
            $db_direct_config_arr['operator_direct_type'] = 0;
            $db_direct_config_arr['operator_direct_range'] = array();
        }else{
            $db_direct_config_arr['operator_direct_type'] = 1;
            $db_direct_config_arr['operator_direct_range'] = $direcr_config_arr['operator'];
        }
        if($direcr_config_arr['mobile_brand'] == 0){
            $db_direct_config_arr['brand_direct_type'] = 0;
            $db_direct_config_arr['brand_direct_range'] = array();
        }else{
            $db_direct_config_arr['brand_direct_type'] = 1;
            $db_direct_config_arr['brand_direct_range'] = $direcr_config_arr['mobile_brand'];
        }
        if($direcr_config_arr['screen_size'] == 0){
            $db_direct_config_arr['screen_direct_type'] = 0;
            $db_direct_config_arr['screen_direct_range'] = array();
        }else{
            $db_direct_config_arr['screen_direct_type'] = 1;
            $db_direct_config_arr['screen_direct_range'] = $direcr_config_arr['screen_size'];
        }
        $info['direct_config'] = json_encode($db_direct_config_arr);
        $info['advertiser_uid'] = $advertiser_id;
        $info['outer_direct_id'] = $outer_direct_id;
        $direct_id = Advertiser_Service_DirectModel::addDirect($info);
	    if (!$direct_id){
	        $this->sonaOutput(30000, 'unknown internal error, please try again later');
	    }
        
        /*操作日志start*/
        $data = array();
        $data['module'] = 'adver_delivery';
        $data['sub_module'] = 'add_direct';
        $data['content'].=$direct_id.',direct_name:'.$direct_name;
        $this->addSonaOperatelog($advertiser_id, $data);
        /*操作日志end*/
	    $this->sonaOutput(0, '',array('direct_id'=>$direct_id,'outer_direct_id'=>$outer_direct_id));
    }
    
    /**
     * 2.读取定向
     * direct/read
     * method: get
     * curl -G 'http://rock.advertiser.housead.com/v1/direct/read' -H 'Authorization: Bearer NCwxNDgxODUyNTY3LDViNzJlZmFkM2FlNjFiOTYxZDE3YmFjNmU4OTEyZjhkYzM5MWU4NzE=' -d 'advertiser_id=4' -d 'direct_id=35'
     * {"code":0,"msg":"","data":{"direct_id":"35","direct_name":"hunter_directname2016121508","direct_config":{"area":[1,2],"os":2,"network":[1,2,3],"operator":[1,2],"mobile_brand":[1,2,3],"screen_size":[1,2,3]},"outer_direct_id":"3","create_time":"1481855266","update_time":"1481855266"}}
     */
    public function readAction() {
        $advertiser_id = $this->getGet('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $direct_id = intval($this->getGet('direct_id'));
        if(empty($direct_id)){
            $this->sonaOutput(31000, 'direct_id:lack required parameters');
        }
        $directInfo = Advertiser_Service_DirectModel::getBy(array('advertiser_uid'=>$advertiser_id, 'id'=>$direct_id));
        if(empty($directInfo)){
            $this->sonaOutput(31010, 'direct_id:object operated not exist');
        }
        
        $outerDirectInfo = array();
        $outerDirectInfo['direct_id'] = $directInfo['id'];
        $outerDirectInfo['direct_name'] = $directInfo['direct_name'];
        $innerDirectConfig = json_decode($directInfo['direct_config'], true);
        $outerDirectConfig = array();
        if($innerDirectConfig['isSona']){
            if($innerDirectConfig['area_type'] == 1){
                $outerDirectConfig['area'] = $innerDirectConfig['area_range'];
            }else{
                $outerDirectConfig['area'] = 0;
            }
            $outerDirectConfig['os'] = $innerDirectConfig['os_direct_type'];
            if($innerDirectConfig['network_direct_type'] == 1){
                $outerDirectConfig['network'] = $innerDirectConfig['network_direct_range'];
            }else{
                $outerDirectConfig['network'] = 0;
            }
            if($innerDirectConfig['operator_direct_type'] == 1){
                $outerDirectConfig['operator'] = $innerDirectConfig['operator_direct_range'];
            }else{
                $outerDirectConfig['operator'] = 0;
            }
            if($innerDirectConfig['brand_direct_type'] == 1){
                $outerDirectConfig['mobile_brand'] = $innerDirectConfig['brand_direct_range'];
            }else{
                $outerDirectConfig['mobile_brand'] = 0;
            }
            if($innerDirectConfig['screen_direct_type'] == 1){
                $outerDirectConfig['screen_size'] = $innerDirectConfig['screen_direct_range'];
            }else{
                $outerDirectConfig['screen_size'] = 0;
            }
        }
        $outerDirectInfo['direct_config'] = $outerDirectConfig;
        $outerDirectInfo['outer_direct_id'] = $directInfo['outer_direct_id'];
        $outerDirectInfo['create_time'] = $directInfo['create_time'];
        $outerDirectInfo['update_time'] = $directInfo['update_time'];
        $this->sonaOutput(0, '', $outerDirectInfo);
    }
    
    /**
     * 
     * 3.更新定向
     * direct/update
     * method: post
     * curl 'http://rock.advertiser.housead.com/v1/direct/update' -H 'Authorization: Bearer NCwxNDgxODUyNTY3LDViNzJlZmFkM2FlNjFiOTYxZDE3YmFjNmU4OTEyZjhkYzM5MWU4NzE=' -d 'advertiser_id=4' -d 'direct_id=35' -d 'direct_name=hunter_directname2016121512' -d 'direct_config={"area":0,"os":2,"network":[1,2,3],"operator":[1,2],"mobile_brand":[1,2,3],"screen_size":[1,2,3]}'
     * {"code":0,"msg":"","data":{"direct_id":35}}
     */
    public function updateAction() {
        $advertiser_id = $this->getPost('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $deliveryConfig = Common::getConfig('deliveryConfig');
        
        //必填, 定向id
        $direct_id = intval($this->getPost('direct_id'));
        if(empty($direct_id)){
            $this->sonaOutput(31000, 'direct_id:lack required parameters');
        }
        $directInfo = Advertiser_Service_DirectModel::getBy(array('advertiser_uid'=>$advertiser_id, 'id'=>$direct_id));
        if(empty($directInfo)){
            $this->sonaOutput(31010, 'direct_id:object operated not exist');
        }
        
        //必填,定向名称
        $direct_name = trim($this->getPost('direct_name'));
        if(!$direct_name){
	        $this->sonaOutput(31001, 'direct_name:parameter value should not be empty');
	    }
	    if(Common::strLength($direct_name) >= 30){
	        $this->sonaOutput(31007, 'direct_name:string length is too long');
	    }
        $params = array();
	    $params['direct_name'] = $direct_name;
	    $params['advertiser_uid'] = $advertiser_id;
        $params['id'] = array('!=',$direct_id);
	    $ret = Advertiser_Service_DirectModel::getBy($params);
	    if($ret){
	        $this->output(31013, 'direct_name:duplicate name is not allowed');
	    }
        
        //必填,定向名称(校验定向配置)
        $direct_config = html_entity_decode($this->getPost('direct_config'));
        if(!$direct_config){
	        $this->sonaOutput(31001, 'direct_config:parameter value should not be empty');
	    }
        if(Common::is_json($direct_config)){
            $direcr_config_arr = json_decode($direct_config, TRUE);
        }else{
            $this->sonaOutput(31005, 'direct_config:invalid data format');
        }
        if(!is_array($direcr_config_arr)){
            $this->sonaOutput(31005, 'direct_config:invalid data format ');
        }
        if(isset($direcr_config_arr['area'])){
            if($direcr_config_arr['area'] ){
				$provinceList = Common::getConfig ( 'areaConfig', 'provinceList' );
                foreach($direcr_config_arr['area'] as $item){
                    if(!isset($provinceList[$item])){
                        $this->sonaOutput(31017, 'direct_config area:param not in enumeration list');
                    }
                }
            }
        }else{
            $direcr_config_arr['area'] = 0;
        }
        if(isset($direcr_config_arr['os'])){
            if(is_array($direcr_config_arr['os'])){
                $this->sonaOutput(31005, 'direct_config os:invalid data format ');
            }
            if(!isset($deliveryConfig['osTypeList'][$direcr_config_arr['os']])){
                $this->sonaOutput(31017, 'direct_config os:param not in enumeration list');
            }
        }else{
            $direcr_config_arr['os'] = 0;
        }
        if(isset($direcr_config_arr['network'])){
            if($direcr_config_arr['network'] ){
                foreach($direcr_config_arr['network'] as $item){
                    if(!isset($deliveryConfig['netWorkList'][$item])){
                        $this->sonaOutput(31017, 'direct_config network:param not in enumeration list');
                    }
                }
            }
        }else{
            $direcr_config_arr['network'] = 0;
        }
        if(isset($direcr_config_arr['operator'])){
            if($direcr_config_arr['operator'] ){
                foreach($direcr_config_arr['operator'] as $item){
                    if(!isset($deliveryConfig['operatorList'][$item])){
                        $this->sonaOutput(31017, 'direct_config operator:param not in enumeration list');
                    }
                }
            }
        }else{
            $direcr_config_arr['operator'] = 0;
        }
        if(isset($direcr_config_arr['mobile_brand'])){
            if($direcr_config_arr['mobile_brand'] ){
                foreach($direcr_config_arr['mobile_brand'] as $item){
                    if(!isset($deliveryConfig['brandList'][$item])){
                        $this->sonaOutput(31017, 'direct_config mobile_brand:param not in enumeration list');
                    }
                }
            }
        }else{
            $direcr_config_arr['mobile_brand'] = 0;
        }
        if(isset($direcr_config_arr['screen_size'])){
            if($direcr_config_arr['screen_size'] ){
                foreach($direcr_config_arr['screen_size'] as $item){
                    if(!isset($deliveryConfig['screenList'][$item])){
                        $this->sonaOutput(31017, 'direct_config area:param not in enumeration list');
                    }
                }
            }
        }else{
            $direcr_config_arr['screen_size'] = 0;
        }
        
        $info = array();
        $info['direct_name'] = $direct_name;
        $db_direct_config_arr = array();
        $db_direct_config_arr['isSona'] = 1;
        if($direcr_config_arr['area'] == 0){
            $db_direct_config_arr['area_type'] = 0;
            $db_direct_config_arr['area_range'] = array();
        }else{
            $db_direct_config_arr['area_type'] = 1;
            $db_direct_config_arr['area_range'] = $direcr_config_arr['area'];
        }
        $db_direct_config_arr['os_direct_type'] = $direcr_config_arr['os'];
        if($direcr_config_arr['network'] == 0){
            $db_direct_config_arr['network_direct_type'] = 0;
            $db_direct_config_arr['network_direct_range'] = array();
        }else{
            $db_direct_config_arr['network_direct_type'] = 1;
            $db_direct_config_arr['network_direct_range'] = $direcr_config_arr['network'];
        }
        if($direcr_config_arr['operator'] == 0){
            $db_direct_config_arr['operator_direct_type'] = 0;
            $db_direct_config_arr['operator_direct_range'] = array();
        }else{
            $db_direct_config_arr['operator_direct_type'] = 1;
            $db_direct_config_arr['operator_direct_range'] = $direcr_config_arr['operator'];
        }
        if($direcr_config_arr['mobile_brand'] == 0){
            $db_direct_config_arr['brand_direct_type'] = 0;
            $db_direct_config_arr['brand_direct_range'] = array();
        }else{
            $db_direct_config_arr['brand_direct_type'] = 1;
            $db_direct_config_arr['brand_direct_range'] = $direcr_config_arr['mobile_brand'];
        }
        if($direcr_config_arr['screen_size'] == 0){
            $db_direct_config_arr['screen_direct_type'] = 0;
            $db_direct_config_arr['screen_direct_range'] = array();
        }else{
            $db_direct_config_arr['screen_direct_type'] = 1;
            $db_direct_config_arr['screen_direct_range'] = $direcr_config_arr['screen_size'];
        }
        $info['direct_config'] = json_encode($db_direct_config_arr);
        $info['advertiser_uid'] = $advertiser_id;
        
        $result = Advertiser_Service_DirectModel::updateBy($info, array('id'=>$direct_id, 'advertiser_uid'=>$advertiser_id));
	    if (!$result){
	        $this->sonaOutput(30000, 'unknown internal error, please try again later');
	    }
        
        /*操作日志start*/
        $logdata = array();
        $logdata['module'] = 'adver_delivery';
        $logdata['sub_module'] = 'edit_direct';
        $logdata['content'].=$direct_id.',title:'.$direct_name;
        $compare_result = common::compare_different($directInfo, $info, array('direct_id', 'direct_name', 'direct_config'));
        $old = $compare_result['left'];
        $new = $compare_result['right'];
        if($old || $new){
            $logdata['content'].=$directInfo['id'].', old:  '.$old.'   new:  '. $new;
        }else{
            $logdata['content'].=$directInfo['id'].', 无更新';
        }
        $this->addSonaOperatelog($advertiser_id, $logdata);
        /*操作日志end*/
        
	    $this->sonaOutput(0, '',array('direct_id'=>$direct_id));
    }
    
    /**
     * 4.获取定向列表
     * direct/select
     * method: get
     * curl -G 'http://rock.advertiser.housead.com/v1/direct/select' -H 'Authorization: Bearer NCwxNDgxODUyNTY3LDViNzJlZmFkM2FlNjFiOTYxZDE3YmFjNmU4OTEyZjhkYzM5MWU4NzE=' -d 'advertiser_id=4' -d 'page=1' -d 'page_size=1'
     * {"code":0,"msg":"","data":{"list":[{"id":"38","advertiser_uid":"4","direct_name":"hunter_directname2016121511","create_time":"1481858737","update_time":"1481858737","direct_config":{"area":[1,2],"os":2,"network":[1,2,3],"operator":[1,2],"mobile_brand":[1,2,3],"screen_size":[1,2,3]},"outer_direct_id":"4"}],"page_info":{"total_num":"11","total_page":11,"page_size":1,"page":1}}}
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
        $params['advertiser_uid'] = $advertiser_id;
        $params['del'] = 0;
        list($total, $directList) = Advertiser_Service_DirectModel::getList($page, $page_size, $params);
        
        foreach($directList as $key=>$direct){
            unset($directList[$key]['area_range']);
            unset($directList[$key]['age_direct_range']);
            unset($directList[$key]['network_direct_range']);
            unset($directList[$key]['operator_direct_range']);
            unset($directList[$key]['brand_direct_range']);
            unset($directList[$key]['screen_direct_range']);
            unset($directList[$key]['interest_direct_range']);
            unset($directList[$key]['pay_ability_range']);
            unset($directList[$key]['game_frequency_range']);
            unset($directList[$key]['del']);
            
            $innerDirectConfig = json_decode($direct['direct_config'], true);
            $outerDirectConfig = array();
            if($innerDirectConfig['isSona']){
                if($innerDirectConfig['area_type'] == 1){
                    $outerDirectConfig['area'] = $innerDirectConfig['area_range'];
                }else{
                    $outerDirectConfig['area'] = 0;
                }
                $outerDirectConfig['os'] = $innerDirectConfig['os_direct_type'];
                if($innerDirectConfig['network_direct_type'] == 1){
                    $outerDirectConfig['network'] = $innerDirectConfig['network_direct_range'];
                }else{
                    $outerDirectConfig['network'] = 0;
                }
                if($innerDirectConfig['operator_direct_type'] == 1){
                    $outerDirectConfig['operator'] = $innerDirectConfig['operator_direct_range'];
                }else{
                    $outerDirectConfig['operator'] = 0;
                }
                if($innerDirectConfig['brand_direct_type'] == 1){
                    $outerDirectConfig['mobile_brand'] = $innerDirectConfig['brand_direct_range'];
                }else{
                    $outerDirectConfig['mobile_brand'] = 0;
                }
                if($innerDirectConfig['screen_direct_type'] == 1){
                    $outerDirectConfig['screen_size'] = $innerDirectConfig['screen_direct_range'];
                }else{
                    $outerDirectConfig['screen_size'] = 0;
                }
            }
            $directList[$key]['direct_config'] = $outerDirectConfig;
        }
        
        $page_info = array('total_num'=>$total, 'total_page'=>ceil($total*1.0/$page_size), 'page_size'=>$page_size, 'page'=>$page );
        
        $this->sonaOutput(0, '',array('list'=>$directList, 'page_info'=>$page_info));
    }
    
    /**
     * 5.删除定向
     * direct/delete
     * method: post
     * curl 'http://rock.advertiser.housead.com/v1/direct/delete' -H 'Authorization: Bearer NCwxNDgxODUyNTY3LDViNzJlZmFkM2FlNjFiOTYxZDE3YmFjNmU4OTEyZjhkYzM5MWU4NzE=' -d 'advertiser_id=4' -d 'direct_id=38'
     *  {"code":0,"msg":"","data":{"direct_id":38}}
     */
    public function deleteAction() {
        $advertiser_id = $this->getPost('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput('31000', 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        //必填,投放单元id
        $direct_id = intval($this->getPost('direct_id'));
        if(empty($direct_id)){
            $this->sonaOutput(31001, 'direct_id:parameter value should not be empty ');
        }
        $adInfo = Advertiser_Service_DirectModel::getBy(array('advertiser_uid'=>$advertiser_id, 'id'=>$direct_id));
        if(empty($adInfo)){
            $this->sonaOutput(31010, 'direct_id:object operated not exist');
        }
        
        $result = Advertiser_Service_DirectModel::updateBy(array('del'=>1), array('id'=>$direct_id, 'advertiser_uid'=>$advertiser_id));
	    if (!$result){
            $this->sonaOutput(30000, 'unknown internal error, please try again later');
        }
        
        /*操作日志start*/
        $logdata = array();
        $logdata['module'] = 'adver_delivery';
        $logdata['sub_module'] = 'del_direct';
        $logdata['content'] = '';
        $old = '';
        $new = '';
        $old .= 'del:0'.';';
        $new .= 'del:1'.';';
        $logdata['content'].=$adInfo['id'].', name:'.$adInfo['ad_name']. ', old:  '.$old.'   new:  '. $new;
        $this->addSonaOperatelog($advertiser_id, $logdata);
        /*操作日志end*/
        
        $this->sonaOutput(0, '',array('direct_id'=>$direct_id));
    }
}
