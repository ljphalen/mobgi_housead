<?php
if (! defined ( 'BASE_PATH' ))
    exit ( 'Access Denied!' );

/**
 *
 * Enter description here ...
 *
 * @author rock.luo
 *        
 */
class Systemtool_AbtestflowController extends Admin_BaseController {
    public $actions = array (
            'listUrl' => '/Admin/Systemtool_Abtestflow/index',
            'addUrl' => '/Admin/Systemtool_Abtestflow/add',
            'addPostUrl' => '/Admin/Systemtool_Abtestflow/addPost',
            'deleteUrl' => '/Admin/Systemtool_Abtestflow/delete',
            'viewUrl' => '/Admin/Systemtool_Abtestflow/view',
            'getAdsListUrl' => '/Admin/Systemtool_Abtestflow/getAdsList',
  
    );
    public $perpage = 10;

    /**
     * Enter description here .
     *
     *
     *
     * ..
     */
    public function indexAction() {

        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search= $this->getInput(array('conf_name'));
        if ($search['conf_name']) {
            $params['conf_name'] = array('LIKE',$search['conf_name']);
        }
        
         
        list($total, $list) =MobgiApi_Service_AbFlowConfModel::getList($page, $this->perpage, $params);
        foreach ($list as $key=>$val){
            $list[$key]['config_num'] =  MobgiApi_Service_AbConfRelModel::getCountByFlowId(array('flow_id'=>$val['flow_id']));
        }
        $url = $this->actions['listUrl'].'/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('list', $list);

    }
    

    
    public function getQueryString(){
        $search = $this->getInput ( array (
                'platform',
                'app_name', 
                'page'
        ) );
        return http_build_query ( $search );
        
    }


    public function addAction() {
        $flowId = $this->getInput ( 'flow_id' );
        if ($flowId) {
            $data = $this->getEditFlowInfo ( $flowId );
        } else {
            foreach ( Common_Service_Const::$mAdSubType as $adSubType => $val ) {
                $tmp [] = array (
                        'ad_type' => $adSubType,
                        'name' => $val 
                );
            }
            $data ['ad_Info'] = $tmp;
        }
        $this->assign ( 'info', $data );
        $this->assign ( 'queryString', $this->getQueryString() );
    }

    public function addPostAction() {
        $info = $this->getRequest ()->getPost ();
        $info = $this->checkPostParam ( $info );  
        $flowId = $this->updateFlowConf($info);
        $this->updateFlowAdTypeRel($info, $flowId);
        $this->updateFlowGeneralAdsRel($info, $flowId);
        $this->updateFlowPriorityAdsRel($info, $flowId);
        $this->updateFlowDspAdsRel($info, $flowId);
        if(!$flowId){
            $this->output ( - 1, '操作失败' );
        }
        $this->output ( ０, '操作成功' );
    }
    
    

    public function updateFlowDspAdsRel($info, $flowId){
        foreach ( Common_Service_Const::$mAdSubType as $adSubType => $val ) {
            //编辑
            if($info['flow_id']){
                if($info ['is_use_dsp_' . $adSubType] && !$info ['is_default_' . $adSubType] ){
                    $oldData = MobgiApi_Service_AbFlowAdsRelModel::getsBy(array('flow_id'=>$flowId,'ad_type'=>$adSubType,'conf_type'=>MobgiApi_Service_AbFlowAdsRelModel::DSP_ADS));
                    $oldAdsIds = array();
                    if($oldData){
                        $oldAdsIds = array_keys(Common::resetKey($oldData, 'ads_id'));
                        foreach ($oldAdsIds as $oldAdsId){
                            if(!in_array($oldAdsId, $info['dsp_ads_id_'.$adSubType])){
                                MobgiApi_Service_AbFlowAdsRelModel::deleteBy(array('flow_id'=>$flowId,'ad_type'=>$adSubType,'conf_type'=>MobgiApi_Service_AbFlowAdsRelModel::DSP_ADS,'ads_id'=>$oldAdsId));
                            }
                        }
                    }
                    $tmp = array();
                    if($info['dsp_ads_id_'.$adSubType]){
						foreach ($info['dsp_ads_id_'.$adSubType] as $index=>$adsId){
							$tmp ['flow_id'] = $flowId;
							$tmp['ad_type'] = $adSubType;
							$tmp['conf_type'] = MobgiApi_Service_AbFlowAdsRelModel::DSP_ADS;
							$tmp['ads_id'] = $adsId;
							$tmp['position'] = $info['dsp_position_'.$adSubType][$index];
							if(in_array($adsId, $oldAdsIds)){
								MobgiApi_Service_AbFlowAdsRelModel::updateBy($tmp, array('flow_id'=>$flowId,'ad_type'=>$adSubType,'conf_type'=>MobgiApi_Service_AbFlowAdsRelModel::DSP_ADS,'ads_id'=>$adsId));
							}else{
								MobgiApi_Service_AbFlowAdsRelModel::add($tmp);
							}
						}
					}
                }else{
                    MobgiApi_Service_AbFlowAdsRelModel::deleteBy(array('flow_id'=>$flowId,'ad_type'=>$adSubType,'conf_type'=>MobgiApi_Service_AbFlowAdsRelModel::DSP_ADS));
                }
                //添加
            }else{
                if($info ['status_' . $adSubType] && $info ['is_use_dsp_' . $adSubType] && !$info ['is_default_' . $adSubType]){
                    $data = array();
                    foreach ($info['dsp_ads_id_'.$adSubType] as $index=>$adsId){
                        $tmp ['flow_id'] = $flowId;
                        $tmp['ad_type'] = $adSubType;
                        $tmp['conf_type'] = MobgiApi_Service_AbFlowAdsRelModel::DSP_ADS;
                        $tmp['ads_id'] = $adsId;
                        $tmp['position'] = $info['dsp_position_'.$adSubType][$index];
                        $tmp ['create_time'] = date('Y-m-d H:i:s');
                        $tmp ['update_time'] = date('Y-m-d H:i:s');
                        $data[] = $tmp;
                    }
                    if($data){
                        MobgiApi_Service_AbFlowAdsRelModel::mutiFieldInsert($data);
                    }
                }
            }
    
        }
    }
    
    public function updateFlowPriorityAdsRel($info, $flowId){
        foreach ( Common_Service_Const::$mAdSubType as $adSubType => $val ) {
            //编辑
            if($info['flow_id']){
                if( $info ['is_priority_' . $adSubType] &&  !$info ['is_default_' . $adSubType] ){
                    $oldData = MobgiApi_Service_AbFlowAdsRelModel::getsBy(array('flow_id'=>$flowId,'ad_type'=>$adSubType,'conf_type'=>MobgiApi_Service_AbFlowAdsRelModel::PRIORITY_ADS));
                    $oldAdsIds = array();
                    if($oldData){
                        $oldAdsIds = array_keys(Common::resetKey($oldData, 'ads_id'));
                        foreach ($oldAdsIds as $oldAdsId){
                            if(!in_array($oldAdsId, $info['priority_ads_id_'.$adSubType])){
                                MobgiApi_Service_AbFlowAdsRelModel::deleteBy(array('flow_id'=>$flowId,'ad_type'=>$adSubType,'conf_type'=>MobgiApi_Service_AbFlowAdsRelModel::PRIORITY_ADS,'ads_id'=>$oldAdsId));
                            }
                        }
                    }
                    $tmp = array();
                    if($info['priority_ads_id_'.$adSubType]){
						foreach ($info['priority_ads_id_'.$adSubType] as $index=>$adsId){
							$tmp ['flow_id'] = $flowId;
							$tmp['ad_type'] = $adSubType;
							$tmp['conf_type'] = MobgiApi_Service_AbFlowAdsRelModel::PRIORITY_ADS;
							$tmp['ads_id'] = $adsId;
							$tmp['position'] = $info['priority_position_'.$adSubType][$index];
							$tmp['limit_num'] = $info['priority_limit_num_'.$adSubType][$index];
							if(in_array($adsId, $oldAdsIds)){
								MobgiApi_Service_AbFlowAdsRelModel::updateBy($tmp, array('flow_id'=>$flowId,'ad_type'=>$adSubType,'conf_type'=>MobgiApi_Service_AbFlowAdsRelModel::PRIORITY_ADS,'ads_id'=>$adsId));
							}else{
								MobgiApi_Service_AbFlowAdsRelModel::add($tmp);
							}
						}
					}
                }else{
                    MobgiApi_Service_AbFlowAdsRelModel::deleteBy(array('flow_id'=>$flowId,'ad_type'=>$adSubType,'conf_type'=>MobgiApi_Service_AbFlowAdsRelModel::PRIORITY_ADS));
                }
                //添加
            }else{
                if($info ['status_' . $adSubType] && $info ['is_priority_' . $adSubType] && !$info ['is_default_' . $adSubType]){
                    $data = array();
                    foreach ($info['priority_ads_id_'.$adSubType] as $index=>$adsId){
                        $tmp ['flow_id'] = $flowId;
                        $tmp['ad_type'] = $adSubType;
                        $tmp['conf_type'] = 2;
                        $tmp['ads_id'] = $adsId;
                        $tmp['position'] = $info['priority_position_'.$adSubType][$index];
                        $tmp['limit_num'] = $info['priority_limit_num_'.$adSubType][$index];
                        $tmp ['create_time'] = date('Y-m-d H:i:s');
                        $tmp ['update_time'] = date('Y-m-d H:i:s');
                        $data[] = $tmp;
                    }
                    if($data){
                        MobgiApi_Service_AbFlowAdsRelModel::mutiFieldInsert($data);
                    }
                }
            }
        }
    
    }
    
    
    public function updateFlowGeneralAdsRel($info, $flowId){
        
        foreach ( Common_Service_Const::$mAdSubType as $adSubType => $val ) {
            //编辑
            if($info['flow_id']){
                if( !$info ['is_default_' . $adSubType]){
                    $oldData = MobgiApi_Service_AbFlowAdsRelModel::getsBy(array('flow_id'=>$flowId,'ad_type'=>$adSubType,'conf_type'=>1));
                    $oldAdsIds = array();
                    if($oldData){
                        $oldAdsIds = array_keys(Common::resetKey($oldData, 'ads_id'));
                        foreach ($oldAdsIds as $oldAdsId){
                            if(!in_array($oldAdsId, $info['gerneral_ads_id_'.$adSubType])){
                                MobgiApi_Service_AbFlowAdsRelModel::deleteBy(array('flow_id'=>$flowId,'ad_type'=>$adSubType,'conf_type'=>1,'ads_id'=>$oldAdsId));
                            }
                        }
                    }
                    $tmp = array();
                    if($info['gerneral_ads_id_'.$adSubType]){
						foreach ($info['gerneral_ads_id_'.$adSubType] as $index=>$adsId){
							$tmp ['flow_id'] = $flowId;
							$tmp['ad_type'] = $adSubType;
							$tmp['conf_type'] = 1;
							$tmp['ads_id'] = $adsId;
							$tmp['position'] = $info['gerneral_position_'.$adSubType][$index];
							$tmp['limit_num'] = $info['gerneral_limit_num_'.$adSubType][$index];
							$tmp['weight'] = $info['gerneral_weight_'.$adSubType][$index];
							if(in_array($adsId, $oldAdsIds)){
								MobgiApi_Service_AbFlowAdsRelModel::updateBy($tmp, array('flow_id'=>$flowId,'ad_type'=>$adSubType,'conf_type'=>1,'ads_id'=>$adsId));
							}else{
								MobgiApi_Service_AbFlowAdsRelModel::add($tmp);
							}
						}
					}
                }else{
                    MobgiApi_Service_AbFlowAdsRelModel::deleteBy(array('flow_id'=>$flowId,'ad_type'=>$adSubType,'conf_type'=>1));
                }
            //添加
            }else{
                if($info ['status_' . $adSubType] && !$info ['is_default_' . $adSubType]){
                    $data = array();
                    foreach ($info['gerneral_ads_id_'.$adSubType] as $index=>$adsId){
                        $tmp ['flow_id'] = $flowId;
                        $tmp['ad_type'] = $adSubType;
                        $tmp['conf_type'] = MobgiApi_Service_AbFlowAdsRelModel::GERNERAL_ADS;
                        $tmp['ads_id'] = $adsId;
                        $tmp['position'] = $info['gerneral_position_'.$adSubType][$index];
                        $tmp['limit_num'] = $info['gerneral_limit_num_'.$adSubType][$index];
                        $tmp['weight'] = $info['gerneral_weight_'.$adSubType][$index];
                        $tmp ['create_time'] = date('Y-m-d H:i:s');
                        $tmp ['update_time'] = date('Y-m-d H:i:s');
                        $data[] = $tmp;
                    }
                    if($data){
                        MobgiApi_Service_AbFlowAdsRelModel::mutiFieldInsert($data);
                    }
                }
            }
        
        }
    }
    
    public function updateFlowAdTypeRel($info, $flowId){
        foreach ( Common_Service_Const::$mAdSubType as $adSubType => $val ) {
            $data['flow_id'] = $info['flow_id']?$info['flow_id']:$flowId;
            $data['ad_type'] = $adSubType;
            $data['status'] = $info ['status_' . $adSubType];
            $data['is_priority'] = ( !$info ['is_default_' . $adSubType])?$info ['is_priority_' . $adSubType]:0;
            $data['is_use_dsp'] =  ( !$info ['is_default_' . $adSubType])?$info ['is_use_dsp_' . $adSubType]:0;
            $data['price'] = $info ['is_use_dsp_' . $adSubType]?$info ['price_' . $adSubType]:0;
            $data['is_delay'] = (!$info ['is_default_' . $adSubType])?$info ['is_delay_' . $adSubType]:0;
            $data['time'] = $info ['is_delay_' . $adSubType]?$info ['time_' . $adSubType]:0;
            $data['is_default'] = $info ['is_default_' . $adSubType]?$info ['is_default_' . $adSubType]:0;
            if($info['flow_id']){
                $params['flow_id'] = $info ['flow_id'];
                $params['ad_type'] = $adSubType;
                MobgiApi_Service_AbFlowAdTypeRelModel::updateBy($data, $params);
            }else{
                MobgiApi_Service_AbFlowAdTypeRelModel::add($data);
            }
        }
    }
    
    public function updateFlowConf($info){
        $data['conf_name'] = $info['conf_name'];
        $data['operator_id'] = $this->userInfo['user_id'];
        if($info['flow_id']){
            MobgiApi_Service_AbFlowConfModel::updateByID($data, $info['flow_id']);
            $flowId = $info['flow_id'];
        }else{
            $flowId = MobgiApi_Service_AbFlowConfModel::add($data);
         }
        return $flowId;
    }
    
    

    private  function checkPostParam($info) {
        if (! trim ( $info ['conf_name'] )) {
            $this->output ( - 1, '配置名称为空' );
        }
        $params['conf_name'] = trim ( $info ['conf_name'] );
        if($info['flow_id']){
            $params['flow_id'] = array('<>', $info['flow_id']);
        }
        $ret = MobgiApi_Service_AbFlowConfModel::getBy($params);
        if($ret){
            $this->output ( - 1, '配置名称已经存在' );
        }
        $this->checkPriorityAdsConf($info) ;
        $this->checkGerneralAdsConf($info);
        $this->checkDspAdsConf($info);
        $this->checkOtherConf ( $info );
        return $info;
    }
    
    private function checkPriorityAdsConf($info){
        foreach ( Common_Service_Const::$mAdSubType as $adSubType => $adTypeName ) {
            if ($info ['is_priority_' . $adSubType] && !$info ['is_default_' . $adSubType]) {
                    if(empty ( $info ['priority_ads_id_' . $adSubType] )){
                        $this->output ( - 1, $adTypeName . '中的优先广告商为空' );
                    }
                    if(count($info ['priority_ads_id_' . $adSubType]) != count(array_unique($info ['priority_ads_id_' . $adSubType]))){
                        $this->output ( - 1, $adTypeName . '中的优先广告商位置重复' );
                    }
                    foreach ($info ['priority_ads_id_' . $adSubType] as $postion=> $va){
                        if(!$va){
                            $this->output ( - 1, $adTypeName . '中的优先广告商位置:"'.($postion+1).'"为空' );
                        }
                        if($va == 'Mobgi' && empty($info ['dsp_ads_id_' . $adSubType])){
                            $this->output ( - 1,  $adTypeName.'中的优先广告商配置了mobgi，请配置dsp广告商' );
                        }
                    }
                    foreach ($info ['priority_limit_num_' . $adSubType] as $postion=> $va){
                        if(!is_numeric($va)){
                            $this->output ( - 1, $adTypeName . '中的优先广告商次数限制必须为数字' );
                        }
                    }
            }
        }
    }
    
    private function checkGerneralAdsConf($info) {
        foreach ( Common_Service_Const::$mAdSubType as $adSubType => $adTypeName ) {
            if ($info ['status_' . $adSubType] && ! $info ['is_default_' . $adSubType]) {
                if (empty ( $info ['gerneral_ads_id_' . $adSubType] )) {
                    $this->output ( - 1, $adTypeName . '中的一般广告商为空' );
                }
            }
            if (! empty ( $info ['gerneral_ads_id_' . $adSubType] ) && ! $info ['is_default_' . $adSubType]) {
                if (count ( $info ['gerneral_ads_id_' . $adSubType] ) != count ( array_unique ( $info ['gerneral_ads_id_' . $adSubType] ) )) {
                    $this->output ( - 1, $adTypeName . '中的一般广告商位置重复' );
                }
                foreach ( $info ['gerneral_ads_id_' . $adSubType] as $postion => $va ) {
                    if (! $va) {
                        $this->output ( - 1, $adTypeName . '中的一般广告商位置:"' . ($postion + 1) . '"为空' );
                    }
                    if ($va == 'Mobgi' && empty ( $info ['dsp_ads_id_' . $adSubType] )) {
                        $this->output ( - 1, $adTypeName . '中一般广告商配置了mobgi，请配置dsp广告商' );
                    }
                }
                foreach ( $info ['gerneral_ads_id_' . $adSubType] as $postion => $va ) {
                    if (! $va) {
                        $this->output ( - 1, $adTypeName . '中的一般广告商位置:"' . ($postion + 1) . '"为空' );
                    }
                }
                foreach ( $info ['gerneral_weight_' . $adSubType] as $postion => $va ) {
                    if (! is_numeric ( $va )) {
                        $this->output ( - 1, $adTypeName . '中的一般广告商权重必须为数字' );
                    }
                    if ($va > 1 || $va <= 0) {
                        $this->output ( - 1, $adTypeName . '中的一般广告商权重范围０－１之间数字' );
                    }
                }
                foreach ( $info ['gerneral_limit_num_' . $adSubType] as $postion => $va ) {
                    if (! is_numeric ( $va )) {
                        $this->output ( - 1, $adTypeName . '中的一般广告商次数限制必须为数字' . $va );
                    }
                }
                if (strval ( array_sum ( $info ['gerneral_weight_' . $adSubType] ) ) != '1') {
                    $this->output ( - 1, $adTypeName . '中的一般广告商的权重不为１,计算结果为：' . array_sum ( $info ['gerneral_weight_' . $adSubType] ) );
                }
            }
        }
    }
    
    private function checkDspAdsConf($info){
        foreach ( Common_Service_Const::$mAdSubType as $adSubType => $adTypeName ) {
            if ($info ['is_use_dsp_' . $adSubType]  && !$info ['is_default_' . $adSubType]) {
                if (empty ( $info ['dsp_ads_id_' . $adSubType] )) {
                    $this->output ( - 1, $adTypeName . '中的DSP广告商为空' );
                }
                if (count ( $info ['dsp_ads_id_' . $adSubType] ) != count ( array_unique ( $info ['dsp_ads_id_' . $adSubType] ) )) {
                    $this->output ( - 1, $adTypeName . '中的DSP广告商位置重复' );
                }
                foreach ( $info ['dsp_ads_id_' . $adSubType] as $postion => $va ) {
                    if (! $va) {
                        $this->output ( - 1, $adTypeName . '中的DSP广告商位置:"' . ($postion + 1) . '"为空' );
                    }
                }
            }
        }
    }

    private function checkOtherConf($info) {
        foreach ( Common_Service_Const::$mAdSubType as $adSubType => $adTypeName ) {
            if ($info ['status_' . $adSubType] && !$info ['is_default_' . $adSubType]) {
                if ($info ['is_delay_' . $adSubType] && (! is_numeric ( $info ['time_' . $adSubType] ) || $info ['time_' . $adSubType] <= 0)) {
                    $this->output ( - 1, $adTypeName . '中的尝鲜延迟加载必须大于零整数' );
                }
            }
        }
    }


    public function viewAction() {
        $flowId = $this->getInput ( 'flow_id' );
        if ($flowId) {
            $data = $this->getEditFlowInfo ( $flowId );
        }
        $this->assign ( 'info', $data );
        $this->assign ( 'act', 'view' );
        $this->assign ( 'queryString', $this->getQueryString () );
    }

    private  function getEditFlowInfo($flowId) {
        $flowConf = MobgiApi_Service_AbFlowConfModel::getByID ( $flowId );
        if (! $flowConf) {
            return array();
        }
        $data = array (
                'flow_id' => $flowId,
                'conf_name' => $flowConf ['conf_name'],
        );
        $data ['ad_Info'] = $this->getAdInfo ( $flowId );
        return $data;
    }

   

    public function deleteAction() {
        $flowId = $this->getInput ( 'flow_id' );
        if (! $flowId) {
            $this->output ( - 1, '非法请求' );
        }
        $flowConf = MobgiApi_Service_AbFlowConfModel::getByID ( $flowId );
        if (! $flowConf) {
            $this->output ( - 1, '非法请求' );
        }
        $ret = MobgiApi_Service_AbConfRelModel::getBy(array('flow_id'=>$flowId));
        if($ret){
            $this->output ( -1, '此配置已经使用' );
        }
         $ret =MobgiApi_Service_AbFlowConfModel::deleteById($flowId);
         if(!$ret){
             $this->output ( -1, '删除失败' );
         }
         MobgiApi_Service_AbFlowAdsRelModel::deleteBy(array('flow_id'=>$flowId));
         MobgiApi_Service_AbFlowAdTypeRelModel::deleteBy(array('flow_id'=>$flowId));
        $this->output ( 0, '删除成功' );
    }
    
   
    
    private function getAdInfo($flowId) {
        $adTypeRelList = MobgiApi_Service_AbFlowAdTypeRelModel::getsBy ( array (
                'flow_id' => $flowId 
        ), array (
                'ad_type' => 'ASC' 
        ) );
        if ($adTypeRelList) {
            foreach ( $adTypeRelList as $adTypeRelInfo ) {
                list($dspAdsList, $intergrationAdsList )= $this->initAdsIdsList( $adTypeRelInfo ['ad_type']);
                $adInfo [$adTypeRelInfo ['ad_type']]['dspAdsList'] = $dspAdsList;
                $adInfo [$adTypeRelInfo ['ad_type']]['intergrationAdsList'] = $intergrationAdsList;
                $adInfo [$adTypeRelInfo ['ad_type']] ['status'] = $adTypeRelInfo ['status'];
                $adInfo [$adTypeRelInfo ['ad_type']] ['name'] = Common_Service_Const::$mAdSubType [$adTypeRelInfo ['ad_type']];
                $adInfo [$adTypeRelInfo ['ad_type']] ['ad_type'] = $adTypeRelInfo ['ad_type'];
                $adInfo = $this->fillAdtypeDataToAdInfo ( $adInfo, $adTypeRelInfo );
                $adInfo = $this->fillPriorityDataToAdInfo ( $flowId, $adInfo, $adTypeRelInfo );
                $adInfo = $this->fillGerneralConfToAdInfo ( $flowId, $adInfo, $adTypeRelInfo );
                $adInfo = $this->fillDspConfToAdInfo ( $flowId, $adInfo, $adTypeRelInfo );
            
            }
        }
        return $adInfo;
    }


    private function getAdsNameList() {
        $params ['ad_type'] = array (
                'IN',
                array (
                        1,
                        3 
                ) 
        );
        $adsList = MobgiApi_Service_AdsListModel::getsBy ( $params );
        $adsNameList = Common::resetKey ( $adsList, 'ads_id' );
        return $adsNameList;
    }

   

    private function fillDspConfToAdInfo($flowId, $adInfo, $adTypeRelInfo) {
        $flowAdsRel = MobgiApi_Service_AbFlowAdsRelModel::getsBy ( array (
                'ad_type' => $adTypeRelInfo ['ad_type'],
                'flow_id' => $flowId,
                'conf_type' => 3 
        ) );
        if (! $flowAdsRel) {
            $adInfo [$adTypeRelInfo ['ad_type']] ['dsp_list'] = array ();
            return $adInfo;
        }
     
        $adsNameList = $this->getAdsNameList ();
        $dspConf = array ();
        foreach ( $flowAdsRel as $flowAdsInfo ) {
            $dspConf [] = array (
                    'ads_id' => $flowAdsInfo ['ads_id'],
                    'name' => $adsNameList [$flowAdsInfo ['ads_id']] ['name'],
                    'position' => $flowAdsInfo ['position'] 
            );
        }  
        $adInfo [$adTypeRelInfo ['ad_type']] ['dsp_list'] = $this->multiArraySort($dspConf, 'position');
        return $adInfo;
    }

    private function fillGerneralConfToAdInfo($flowId, $adInfo, $adTypeRelInfo) {
        $flowAdsRel = MobgiApi_Service_AbFlowAdsRelModel::getsBy ( array (
                'ad_type' => $adTypeRelInfo ['ad_type'],
                'flow_id' => $flowId,
                'conf_type' => 1 
        ) );
        if (! $flowAdsRel) {
            $adInfo [$adTypeRelInfo ['ad_type']] ['general_list'] = array ();
            return $adInfo;
        }
        $adsNameList = $this->getAdsNameList ();
        $generalConf = array ();
        foreach ( $flowAdsRel as $flowAdsInfo ) {
            $generalConf [] = array (
                    'ads_id' => $flowAdsInfo ['ads_id'],
                    'name' => $adsNameList [$flowAdsInfo ['ads_id']] ['name'],
                    'limit_num' => $flowAdsInfo ['limit_num'],
                    'position' => $flowAdsInfo ['position'],
                    'weight' => $flowAdsInfo ['weight'] 
            );
        }
        $adInfo [$adTypeRelInfo ['ad_type']] ['general_list'] = $this->multiArraySort($generalConf, 'position');
        return $adInfo;
    }
    
    private function multiArraySort($multiArr,$sortKey,$sort=SORT_ASC){
        if (!is_array ( $multiArr )) {
            return array();
        }
        $sortArr = array();
        foreach ( $multiArr as $rowArr ) {
            if (is_array ( $rowArr )) {
                $sortArr [] = $rowArr [$sortKey];
            }
        }
        array_multisort ( $sortArr, $sort, $multiArr );
        return $multiArr;
    }

    private function fillPriorityDataToAdInfo($flowId, $adInfo, $adTypeRelInfo) {
        if ($adTypeRelInfo ['is_priority']) {
            $flowAdsRel = MobgiApi_Service_AbFlowAdsRelModel::getsBy ( array (
                    'ad_type' => $adTypeRelInfo ['ad_type'],
                    'flow_id' => $flowId,
                    'conf_type' => 2 
            ) );
            if (! $flowAdsRel) {
                $adInfo [$adTypeRelInfo ['ad_type']] ['priority_list'] = array ();
                return $adInfo;
            }
            $adsNameList = $this->getAdsNameList ();
            $priorityConf = array ();
            foreach ( $flowAdsRel as $flowAdsInfo ) {
                $priorityConf [] = array (
                        'ads_id' => $flowAdsInfo ['ads_id'],
                        'name' => $adsNameList [$flowAdsInfo ['ads_id']] ['name'],
                        'limit_num' => $flowAdsInfo ['limit_num'],
                        'position' => $flowAdsInfo ['position'] 
                );
            }
            $adInfo [$adTypeRelInfo ['ad_type']] ['priority_list'] = $this->multiArraySort($priorityConf, 'position');
        }
        return $adInfo;
    }

    /**
     *
     * @param
     *            adInfo
     */
    private function fillAdtypeDataToAdInfo($adInfo, $adTypeRelInfo) {
        if($adTypeRelInfo){
            $adInfo [$adTypeRelInfo ['ad_type']] ['is_priority'] = $adTypeRelInfo ['is_priority'];
            $adInfo [$adTypeRelInfo ['ad_type']] ['is_delay'] = $adTypeRelInfo ['is_delay'];
            $adInfo [$adTypeRelInfo ['ad_type']] ['time'] = $adTypeRelInfo ['time'];
            $adInfo [$adTypeRelInfo ['ad_type']] ['is_use_dsp'] = $adTypeRelInfo ['is_use_dsp'];
            $adInfo [$adTypeRelInfo ['ad_type']] ['price'] = $adTypeRelInfo ['price'];
            $adInfo [$adTypeRelInfo ['ad_type']] ['is_default'] = $adTypeRelInfo ['is_default'];
        }
        return $adInfo;
    }
    public function getAdsListAction() {
        $info = $this->getInput ( array (
                'ad_type'
        ) );
        if (!$info ['ad_type'] ) {
            $this->output ( - 1, '非法操作' );
        }
        list ( $dspAdsList, $intergrationAdsList ) = $this->initAdsIdsList ($info['ad_type'] );
        $data['dspAdsList'] = $dspAdsList;
        $data['intergrationAdsList'] = $intergrationAdsList;
        $this->output ( 0, '操作成功', $data );
    }
    

    private function initAdsIdsList( $adSubType) {
        $dspAdsList = array ();
        $intergrationAdsList = array ();
        $params ['ad_type'] = array ('IN',array (1,3 )  );
        $adsList = MobgiApi_Service_AdsListModel::getsBy ( $params,array('ads_id'=>'ASC'));
        if (! $adsList) {
            return array ($dspAdsList,$intergrationAdsList);
        }
        foreach ( $adsList as $val ) {
            if ($val ['ad_type'] == 3) {
                $dspAdsList [$val ['ads_id'] ] =$val ['name'];
            } else {
                $adTypeArr = json_decode($val['ad_sub_type'], true);
                if(in_array($adSubType, $adTypeArr)){
                    $intergrationAdsList [$val ['ads_id']] = $val ['name'];
                }
            }
        }
        return array (
                $dspAdsList,
                $intergrationAdsList 
        );
    }
}
