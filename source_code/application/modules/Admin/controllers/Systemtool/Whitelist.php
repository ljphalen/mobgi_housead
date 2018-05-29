<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-8-21 16:16:24
 * $Id: Whitelist.php 62100 2017-8-21 16:16:24Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Systemtool_WhitelistController extends Admin_BaseController {
	
	public $actions = array(
        'searchUrl' => '/Admin/Systemtool_Whitelist/search',

	);


    public function searchAction(){
        //事件类型
        $eventtype_name_config =  Common::getConfig('intergrationConfig','statEventtypeName');
        $ver_name_config =  Common::getConfig('intergrationConfig','statVerName');
        
        $params = $this->getInput(array('ver', 'platform', 'imei_idfa', 'event_type', 'app_version', 'sdk_version', 'ad_type', 'pos_key', 'app_key', 'screatedate', 'ecreatedate', 'page'));
		$page = $params['page'];
        
        $whereArr = array ();
        $pageParams = array();
		if (isset ( $params ['platform'] ) && $params ['platform']) {
			$whereArr ['platform'] = $params ['platform'];;
			$pageParams ['platform'] = trim ( $params ['platform'] );
		}
		if (isset ( $params ['imei_idfa'] ) && $params ['imei_idfa']) {
			$pageParams ['imei_idfa'] = $params ['imei_idfa'];
//            if(empty($params['platform'])){
//                $this->showMsg(-1, '请选择平台');
//            }
            $whereArr ['imei'] = $params ['imei_idfa'];
            $pageParams ['platform'] = $params ['platform'];
		}
        
        if (isset ( $params ['event_type'] ) && $params ['event_type']) {
            $whereArr ['event_type'] = $params ['event_type'];
			$pageParams ['event_type'] = $params ['event_type'];
		}
        
        if (isset ( $params ['app_version'] ) && $params ['app_version']) {
			$whereArr ['app_version'] = trim ($params ['app_version']);
			$pageParams ['app_version'] = trim ( $params ['app_version'] );
		}
        
        if (isset ( $params ['sdk_version'] ) && $params ['sdk_version']) {
			$whereArr ['sdk_version'] = trim ($params ['sdk_version']);
			$pageParams ['sdk_version'] = trim ( $params ['sdk_version'] );
		}
        
        if (isset ( $params ['ad_type'] ) && $params ['ad_type']) {
			$whereArr ['ad_type'] = trim ($params ['ad_type']);
			$pageParams ['ad_type'] = trim ( $params ['ad_type'] );
		}
        
        if (isset ( $params ['pos_key'] ) && $params ['pos_key']) {
			$whereArr ['pos_key'] = trim ($params ['pos_key']);
			$pageParams ['pos_key'] = trim ( $params ['pos_key'] );
		}
        
        if (isset ( $params ['app_key'] ) && $params ['app_key']) {
			$whereArr ['app_key'] = trim ($params ['app_key']);
			$pageParams ['app_key'] = trim ( $params ['app_key'] );
		}
        
        //搜索时间
        if (isset($params['screatedate']) && $params['screatedate'] && isset($params['ecreatedate']) && $params['ecreatedate'] ) {
            $pageParams ['screatedate'] = $params ['screatedate'];
            $pageParams ['ecreatedate'] = $params ['ecreatedate'];
            $screatetime = strtotime($params['screatedate']);
            $ecreatetime = strtotime($params['ecreatedate']." 23:59:59");
            $whereArr['server_time'] = array(array('>=', $screatetime), array('<=', $ecreatetime));
        }
        
        if(empty($params['platform']) && empty($params['imei_idfa']) && empty($params['screatedate'])&& empty($params['ecreatedate']) ){
            $params['screatedate'] = date("Y-m-d");
            $params['ecreatedate'] = date("Y-m-d");
            $result = array();
            $total = 0;
        }else{
            list($total, $result) = BhStat_Service_AdClientWhitelistModel::getList($page, $this->perpage, $whereArr, array('id'=>'DESC'));
            $url = $this->actions['searchUrl'].'/?' . http_build_query($pageParams) . '&';
            $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
            $originalityType =Common::getConfig('deliveryConfig', 'originalityType');
            if($result){
                $imei_idfa_device_name_arr = array();
               // list($whitelistnum ,$whitelist) = MobgiApi_Service_IntergrationWhitelistModel::getAll();
               /* if($whitelist){
                    foreach($whitelist as $item){
                        $imei_idfa_device_name_arr[$item['imei_idfa']] = $item['device_name'];
                    }
                }*/
                foreach($result as $key=>$item){
                    $result[$key]['eventtype_name'] = $eventtype_name_config[$item['event_type']];
                    $result[$key]['server_time_format'] = date('Y-m-d H:i:s', $item['server_time']);
                    //$result[$key]['device_name'] = $imei_idfa_device_name_arr[$item['imei']];
                    $result[$key]['report_vername'] = $ver_name_config[$item['ver']];
                    if($item['platform']==1){
                        $result[$key]['platform_name'] = 'Android';
                    }else if($item['platform']==2){
                        $result[$key]['platform_name'] = 'IOS';
                    }else{
                        $result[$key]['platform_name'] = 'platform:'. $item['platform'];
                    }
                    if(isset($originalityType[$item['ad_type']])){
                        $result[$key]['ad_type_name'] = $originalityType[$item['ad_type']];
                    }else{
                        $result[$key]['ad_type_name'] = 'ad_type：'. $item['ad_type'];
                    }
                }
            }
        }
        $this->assign('result', $result);
        $this->assign('total', $total);
        $this->assign('params', $params);
        $this->assign('eventtype_name_config', $eventtype_name_config);
        $this->assign('ver_name_config', $ver_name_config);
        
    }


    public function checkpackageAction(){
        $result = MobgiData_Service_MobgiModel::getDao('ReportPackageInfo')->getList();
        $this->assign('data',$result);
    }

    
}
