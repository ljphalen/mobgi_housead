<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Baseinfo_AppController extends Admin_BaseController {
	
	public $actions = array(
		'listUrl' => '/Admin/Baseinfo_App/index',
		'checkListUrl' => '/Admin/Baseinfo_App/checklist',
		'checkUrl' => '/Admin/Baseinfo_App/check',
		'checkPostUrl' => '/Admin/Baseinfo_App/checkPost',
		'addAppUrl' => '/Admin/Baseinfo_App/addApp',
		'addAppPostUrl' => '/Admin/Baseinfo_App/addAppPost',
		'deleteUrl' => '/Admin/Baseinfo_App/delete',
		'addPosUrl' => '/Admin/Baseinfo_App/addPos',
		'addPosPostUrl' => '/Admin/Baseinfo_App/addPosPost',
		'delUrl' => '/Admin/Baseinfo_App/delete',
		'uploadUrl' => '/Admin/Baseinfo_App/uploadImg',
		'uploadPostUrl' => '/Admin/Baseinfo_App/uploadImgPost',
	    'exportExcelUrl'=> '/Admin/Baseinfo_App/exportexcel',
		'viewUrl' => '/Admin/Baseinfo_App/view',
		'updateAppStateUrl'=>'/Admin/Baseinfo_App/updateAppState',
	);
	
	public $perpage = 20;
	
	public $mCheckState = array (
			- 1 => '未通过',
			1 => '通过',
			2 => '申请中',
			3 => '编辑后再申请'
	);
  
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {
	    $params = array();
	    $page = intval($this->getInput('page'));
	    if ($page < 1) $page = 1;
	    $search= $this->getInput(array('dev_id','is_check','platform','state','app_name','app_key'));
	    if ($search['dev_id']) {
	        $params['dev_id'] =  $search['dev_id'];
	    }
	    if ($search['is_check']) {
	    	$params['is_check'] =  $search['is_check'];
	    }else{
	    	$params['is_check'] =  MobgiApi_Service_AdAppModel::ISCHECK_PASS;
	    }
	    if ($search['platform']) {
	    	$params['platform'] =  $search['platform'];
	    }
	    if ($search['state']) {
	    	$params['state'] =  $search['state']-1;
	    }
		if (trim($search['app_name'])) {
			$appKeys = MobgiApi_Service_AdAppModel::getAppKeysByName($search['app_name']);
			if($appKeys){
				$params['app_key'] = array('IN',$appKeys);
			}else{
				$params['app_key'] = '0';
			}
		}

	    list($total, $appList) =MobgiApi_Service_AdAppModel::getList($page, $this->perpage, $params, array('update_time'=>'DESC','app_id' => 'DESC'));
	    $url = $this->actions['listUrl'].'/?' . http_build_query($search) . '&';
	    $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
	   $appList = $this->fillDataToAppList($appList);
	    $this->assign('search', $search);
	    $this->assign('total', $total);
	    $this->assign('appList', $appList);
	    $this->assign('userList', $this->getUserList());
	}
	
	private function getUserList(){
		$userParam['is_check'] = Admin_Service_UserModel::ISCHECK_PASS;
		$userParam['user_type'] = array('IN', array(Admin_Service_UserModel::DEVERLOPER_USER, Admin_Service_UserModel::OPERATOR_USER));
		$userList = Admin_Service_UserModel::getsBy($userParam);
		return $userList;
	}
	
	public function checklistAction() {
		$params = array();
		$page = intval($this->getInput('page'));
		if ($page < 1) $page = 1;
		$search= $this->getInput(array('dev_id','is_check','platform','state','app_name','app_key'));
	
		$params['is_check'] = array('in',array(MobgiApi_Service_AdAppModel::ISCHECKING,MobgiApi_Service_AdAppModel::ISCHECK_NOT_PASS)) ;
		if ($search['platform']) {
			$params['platform'] =  $search['platform'];
		}
	 	if ($search['app_name']) {
	    	$appKeys = MobgiApi_Service_AdAppModel::getAppKeysByName($search['app_name']);
	 		if($appKeys){
				$params['app_key'] = array('IN',$appKeys);
			}else{
				$params['app_key'] = '0';
			}
	    }
		list($total, $appList) =MobgiApi_Service_AdAppModel::getList($page, $this->perpage, $params, array('is_check'=>'DESC','app_id' => 'DESC'));
		$url = $this->actions['checkListUrl'].'/?' . http_build_query($search) . '&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
		$appList = $this->fillDataToAppList($appList);
		$this->assign('search', $search);
		$this->assign('total', $total);
		$this->assign('appList', $appList);
	}
	
	public function checkAction(){
		$appId = intval ( $this->getGet ( 'app_id'));
		$appInfo = MobgiApi_Service_AdAppModel::getByID ( $appId );
		if (! $appInfo) {
			$this->output ( - 1, '非法操作' );
		}
		$appInfo ['appcate_name'] = $this->getAppTypeName($appInfo['appcate_id']);
		$appInfo ['developer'] = $this->getEmailById($appInfo['dev_id']);
		$this->assign ( 'appInfo', $appInfo );
		$this->assign ( 'appPosList', $this->getPostList($appInfo['dev_id'], $appId) );
		$this->assign ( 'adPosType', Common_Service_Const::$mAdPosTypeName );
	}
	
	private function getPostList($devId, $appId){
		$params ['dev_id'] = $devId;
		$params ['app_id'] = $appId;
		$params ['del'] = MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG;
		$appPosList = MobgiApi_Service_AdDeverPosModel::getsBy ( $params );
		return $appPosList;
	}
	
	private function getEmailById($userId){
		$userParam['user_id'] = $userId;
		$userInfo = Admin_Service_UserModel::getUser($userId);
		return $userInfo['email'];
	}
	
	private function getAppTypeName($appcateId){
		$appTypeConfig = $this->appConfig ();
		foreach ( $appTypeConfig as $val ) {
			$appType = $val [$appcateId];
			if ($appType) {
				break;
			}
		}
		return $appType;
	}
	
	public function checkPostAction(){
		$info= $this->getInput(array('app_id','msg','is_check'));
		if (! $info['app_id']) {
			$this->output ( - 1, '非法操作' );
		}
		$data['is_check'] = $info['is_check'];
		$data['check_msg'] = $info['msg'];
		if($info['is_check'] == MobgiApi_Service_AdAppModel::ISCHECK_PASS){
			$data['state'] = MobgiApi_Service_AdAppModel::OPEN_STATUS;
		}else{
			$data['state'] = MobgiApi_Service_AdAppModel::CLOSE_STATUS;
		}
		$ret = MobgiApi_Service_AdAppModel::updateByID($data, $info['app_id']);
		if (! $ret) {
			$this->output ( - 1, '操作失败' );
		}
		$appInfo = MobgiApi_Service_AdAppModel::getBy(array('app_id'=>$info['app_id']));
		if($info['is_check'] == MobgiApi_Service_AdAppModel::ISCHECK_PASS){
			$this->addUserRelApp($appInfo);
			$this->addAdsAppRel($appInfo);
			$this->addAdsPosRel($appInfo);
		}	
		$this->output ( 0, '操作成功' );
	}
	
	public function fillDataToAppList($appList){
		if(empty($appList)){
			return array();
		}
		foreach ( $appList as $key => $val ) {
			$posParams ['del'] = MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG;
			$posParams ['app_id'] =$val ['app_id'];
			$posInfo = MobgiApi_Service_AdDeverPosModel::getsBy ( $posParams );
			$open = 0;
			$close = 0;
			if ($posInfo) {
				foreach ( $posInfo as $pos ) {
				if ($pos ['state']) {
						$open ++;
					} else {
						$close ++;
					}
				}
			}
			if(!stristr($val['icon'],'http')){
				$appList[$key]['icon'] = Common::getAttachPath().$val['icon'];
			}
			$appList [$key] ['pos_open'] = $open;
			$appList [$key] ['pos_close'] = $close;
		}
	    return $appList;
	}
	

	
	public function addAppAction() {
		$appId = intval ( $this->getGet ( 'app_id'));
		$this->assign('navTitle', '添加');
		if ($appId) {
			$this->assign('navTitle', '编辑');
			$appInfo = MobgiApi_Service_AdAppModel::getByID ( $appId );
			if (! $appInfo) {
				$this->output ( - 1, '非法操作' );
			}
			$appInfo ['appcate_name'] = $this->getAppTypeName($appInfo['appcate_id']);
			$this->assign ( 'appInfo', $appInfo );
		}
		$this->assign('userList', $this->getUserList());
		$this->assign('appType', MobgiApi_Service_AdAppModel::APP_TYPE);
	}
	
	public function addAppPostAction() {
		$info = $this->getPost ( array (
				'app_name',
				'apk_url',
				'app_key',
				'package_name',
				'platform',
				'appcate_id',
				'keyword',
				'app_desc',
				'app_id',
				'icon',
				'dev_id',
		        'out_game_id',
		        'app_type',
		        'is_track',
		        'delivery_type',
		        'appstore_id',
		        'consumer_key'
		) );
		$this->checkAppInfo ( $info );
		$info ['state'] = MobgiApi_Service_AdAppModel::OPEN_STATUS;
		$info ['is_check'] = MobgiApi_Service_AdAppModel::ISCHECK_PASS;
		if( $info['is_track'] == MobgiApi_Service_AdAppModel::CLOSE_STATUS){
			unset($info['delivery_type']);
			unset($info['appstore_id']);
			unset($info['consumer_key']);
		}
		if ($info ['app_id']) {
			$result = MobgiApi_Service_AdAppModel::updateByID ( $info, $info ['app_id'] );
		} else {
			$info ['create_time'] = Common::getTime ();
			$result = MobgiApi_Service_AdAppModel::add ( $info );
			$info['app_id'] = $result;
		}
		$this->addUserRelApp($info);
		$this->addAdsAppRel($info);
		$this->addAdsPosRel($info);
		if (! $result) {
			$this->output ( - 1, '操作失败' );
		}
		if( $info['is_track'] == MobgiApi_Service_AdAppModel::OPEN_STATUS){
			$appId = $info['app_id'] ? $info['app_id'] : $result;
			$this->syncMonitorApp($info, $appId);
		}
		if ($info ['app_id']) {
			$this->output ( 0, '操作成功', array ('app_id' => $info ['app_id'] ) );
		} else {
			$this->output ( 0, '操作成功', array ('app_id' => $result ) );
		}
	}	

	# 同步加入监测功能的应用到spm
	private function syncMonitorApp($info, $appId){
		$appId = intval($appId);
		$monitorApp = MobgiSpm_Service_DeliveryModel::getAppById($appId);
		$app = MobgiSpm_Service_DeliveryModel::getDeliveryAppByAppstoreId($info['appstore_id'], $appId);
		if($app){
			$this->output ( - 1, '同步到投放监控失败，对应appstore_id已经被占用（'.$app['app_name'].')' );
		}
		$platformType = array('1' => 'android', '2' => 'ios');
		# 将 apk_url 去除 html编码
		if(!stristr($info['icon'],'http')){ //绝对路径
			$info['icon'] = Common::getAttachPath().$info['icon'];
		}
		$data = array(
				'app_name' => $info['app_name'],
				'bundleid' => $info['package_name'],
				'consumer_key' => $info['consumer_key'],
				'appstore_id' => $info['appstore_id'],
				'appstore_url' => html_entity_decode($info ['apk_url']),
				'icon' => html_entity_decode($info['icon']),
				'ledou_gameid' => $info['out_game_id'],
				'delivery_type' => $info['delivery_type'],
				'platform' => $platformType[$info['platform']],
				'operator' => $this->userInfo['user_name'],
		);
		if($monitorApp){
			# update
			$params['app_id'] = $appId;
			MobgiSpm_Service_DeliveryModel::updateApp($data, $params);
		}else{
			# insert
			$data['app_id'] = $appId;
			MobgiSpm_Service_DeliveryModel::addApp($data);
		}
		# 同步自投应用到infobright应用表
		if($data['delivery_type'] == 1){
			$this->syncBhMonitorApp($data, $appId); # 暂时关闭，等infobright数据库迁移完毕后开启
		}
	}

	# 同步加入监测功能的应用到Inforbright 的 spm
	private function syncBhMonitorApp($data, $appId){
		$monitorApp = MobgiSpm_Service_BrightHouseModel::getAppById($appId);
		$bhData = array(
				'app_name' => $data['app_name'],
				'bundleid' => $data['bundleid'],
				'consumer_key' => $data['consumer_key'],
				'appstore_id' => $data['appstore_id'],
				'appstore_url' => $data['appstore_url'],
				'del' => 0,
				'operator' => $data['operator'],
		);
		if($monitorApp){
			# update
			$params['app_id'] = $appId;
			MobgiSpm_Service_BrightHouseModel::updateApp($bhData, $params);
		}else{
			# insert
			$bhData['app_id'] = $appId;
			MobgiSpm_Service_BrightHouseModel::addApp($bhData);
		}
	}

	private function addFlowPosRel($info){
	    //查询出有几个广告商
	    $param['app_key'] = $info['app_key'];
	    $adsAppRel = MobgiApi_Service_FlowAppRelModel::getsBy($param);
	    if(!$adsAppRel){
	        return false;
	    }
	    $adsList = array();
	    foreach ($adsAppRel as $val){
	        $adsList[$val['ad_type']][$val['ads_id']] = $val['ads_id'];
	    }
	    if(!$adsList){
	        return false;
	    }
	
	    var_dump($tmpPosList);die;
	}
	
	private function addAdsPosRel($appInfo){
		//查询出有几个广告商
		$param['app_key'] = $appInfo['app_key'];
		$adsAppRel = MobgiApi_Service_AdsAppRelModel::getsBy($param);
		if(!$adsAppRel){
			return false;
		}
		$adsList = array();
		foreach ($adsAppRel as $val){
			$adsList[$val['ad_sub_type']][$val['ads_id']] = $val['ads_id'];
		}
		if(!$adsList){
			return false;
		}
   		$posList = $this->getPosListByAppId($appInfo['app_id']);
   		$posList = Common::resetKey($posList, 'dever_pos_key');
   		$adPosTypeRel = array(
   				 'VIDEO_INTERGRATION'=>Common_Service_Const::VIDEO_AD_SUB_TYPE,
   				 'PIC_INTERGRATION' => Common_Service_Const::PIC_AD_SUB_TYPE,
   				'CUSTOME_INTERGRATION' => Common_Service_Const::CUSTOME_AD_SUB_TYPE,
   				'SPLASH_INTERGRATION' => Common_Service_Const::SPLASH_AD_SUB_TYPE,
   				'ENBED_INTERGRATION' => Common_Service_Const::ENBED_AD_SUB_TYPE,
			    'INTERATIVE_AD'=>Common_Service_Const::INTERATIVE_AD_SUB_TYPE
   		);
   		$tmpPosList =  array();
   		foreach ($posList as $val){
   			$tmpPosList[$adPosTypeRel[$val['pos_key_type']]][$val['dever_pos_key']] = $adsList[$adPosTypeRel[$val['pos_key_type']]];
   		}
   		if(!$tmpPosList){
   			return false;
   		}
   		$data = array();
		foreach ($tmpPosList as $adType=>$adTypeValue){
			foreach ( $adTypeValue as  $posKey=>$posKeyValue ) {
				foreach ( $posKeyValue as  $adsId ) {
					$param['app_key'] = $appInfo['app_key'];
					$param['pos_key'] = $posKey;
					$param['ads_id'] = $adsId;
					$param['ad_sub_type'] = $adType;
					$ret = MobgiApi_Service_AdsPosRelModel::getBy($param);
					if(!$ret) {
						$tmp['app_key'] = $appInfo['app_key'];
						$tmp['platform'] = $appInfo['platform'];
						$tmp['app_name'] = $appInfo['app_name'];
						$tmp['pos_key'] = $posKey;
						$tmp['pos_id'] = $posList[$posKey]['id'];
						$tmp['ads_id'] = $adsId;
						$tmp['ad_sub_type'] = $adType;
						$tmp['third_party_block_id'] = in_array($adsId, array('Mobgi','Housead_DSP'))?$posKey:'';
						$tmp['create_time'] = Common::getTime();
						$tmp['update_time'] = Common::getTime();
						$data[] = $tmp;
					}
				}
			}
		}
		if (!empty($data)){
			MobgiApi_Service_AdsPosRelModel::mutiFieldInsert($data);
		}
	}
	
	private function getPosListByAppId($appId){
		$params ['app_id'] = $appId;
		$params['pos_key_type'] = array('IN', Common_Service_Const::$mAdPosType);
		$params ['del'] = MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG;
		$appPosList = MobgiApi_Service_AdDeverPosModel::getsBy ( $params );
		return $appPosList;
	}
	
	private function addAdsAppRel($appInfo){
		//Mobgi Housead_DSP
		//默认加上Mobgi Housead_DSP广告商
		$adsList = array('Mobgi','Housead_DSP');
		$data = array();

		foreach ( Common_Service_Const::$mAdSubTypeDesc as $subType => $subTypeDesc ) {
            $result = MobgiApi_Service_AdsAppRelModel::getBy ( array('app_key'=>$appInfo['app_key'],'ad_sub_type'=>$subType) );
            $lifeCycle = 1800;
            $playNetwork = 1;
            $isShowView = 0;
            $showViewTime = 0;
            if ($result) {
                $lifeCycle = $result ['life_cycle'];
                $playNetwork = $result ['play_network'];
                if ($subType == Common_Service_Const::SPLASH_AD_SUB_TYPE) {
                    $isShowView = $result ['is_show_view'];
                    $showViewTime = $result ['show_view_time'];
                }
            }
            foreach ( $adsList as $adsId ) {
                $param ['app_key'] = $appInfo ['app_key'];
                $param ['ads_id'] = $adsId;
                $param ['ad_sub_type'] = $subType;
                $ret = MobgiApi_Service_AdsAppRelModel::getBy ( $param );
                if (! $ret) {
                    $tmp ['app_key'] = $appInfo ['app_key'];
                    $tmp ['platform'] = $appInfo ['platform'];
                    $tmp ['app_name'] = $appInfo ['app_name'];
                    $tmp ['third_party_app_key'] = $appInfo ['app_key'];
                    $tmp ['ads_id'] = $adsId;
                    $tmp ['ad_sub_type'] = $subType;
                    $tmp ['life_cycle'] = $lifeCycle;
                    $tmp ['play_network'] = $playNetwork;
                    $tmp ['is_show_view'] = $isShowView;
                    $tmp ['show_view_time'] = $showViewTime;
                    $tmp ['create_time'] = Common::getTime ();
                    $tmp ['update_time'] = Common::getTime ();
                    $data [] = $tmp;
                }
            }
        }
		if (!empty($data)){
			MobgiApi_Service_AdsAppRelModel::mutiFieldInsert($data);
		}
	}
	
	private  function addUserRelApp($info){
		//默认添加应用权限
		$userAppRelData ['user_id'] = $info ['dev_id'];
		$userAppRelData ['app_key'] = $info ['app_key'];
		$ret = Admin_Service_UserAppRelModel::getBy($userAppRelData);
		if(!$ret){
			Admin_Service_UserAppRelModel::add ( $userAppRelData );
		}
		
	}
	
	
	
	public function addPosAction() {
		$info =$this->getGet ( array('app_id', 'search_pos_key_type', 'search_pos_name','opt','search_pos_key') ) ;
		if (! $info['app_id']) {
			$this->output ( - 1, '非法操作' );
		}
		$appInfo = MobgiApi_Service_AdAppModel::getByID ( $info['app_id'] );
		if (! $appInfo) {
			$this->output ( - 1, '非法操作' );
		}
		//$params ['dev_id'] = $appInfo['dev_id'];
		$params ['app_id'] = $info['app_id'];
		$params ['del'] = MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG;
		if($info['opt'] == 'searchPos'){
			if($info['search_pos_key_type']){
				$params['pos_key_type'] = $info['search_pos_key_type'];
			}
			if($info['search_pos_name']){
				$params['dever_pos_name'] = array('LIKE',trim($info['search_pos_name']));
			}
			if($info['search_pos_key']){
				$params['dever_pos_key'] = array('LIKE',trim($info['search_pos_key']));
			}
		}
		
		$appPosList = MobgiApi_Service_AdDeverPosModel::getsBy ( $params );
        $customSubType =Common_Service_Const::$mCustomeSubType;
        $enbedSubType = Common_Service_Const::$mEnbedSubType;
        $mEnbedSize = Common_Service_Const::$mEnbedSize;
        if($appPosList){
            foreach($appPosList as $key=>$value){
                if($value['pos_key_type'] == 'CUSTOME_INTERGRATION'){
                    $appPosList[$key]['ad_sub_type_str'] = $customSubType[$value['ad_sub_type']];
                }else if($value['pos_key_type'] == 'ENBED_INTERGRATION'){
                    $appPosList[$key]['ad_sub_type_str'] = $enbedSubType[$value['ad_sub_type']];
                }
            }
        }
		$this->assign ( 'appPosList', $appPosList );
		$this->assign ( 'adPosType', Common_Service_Const::$mAdPosTypeName );
		$this->assign ( 'customSubType', $customSubType );
        $this->assign ( 'enbedSubType', $enbedSubType );
        $this->assign ( 'enbedSize', $mEnbedSize );
		$this->assign ( 'appInfo', $appInfo );
		$this->assign ( 'search', $info );
	}
	
	public function exportExcelAction(){
		$info =$this->getGet ( array('app_id', 'search_pos_key_type', 'search_pos_name','opt','search_pos_key') ) ;
		$appInfo = MobgiApi_Service_AdAppModel::getByID($info['app_id']);
		//广告位信息
		$adPosType =Common_Service_Const::$mAdPosTypeName;
		$posParams['app_id'] = $info["app_id"];
		$posParams['del'] = MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG;
		if ($info['search_pos_key_type']) {
			$posParams['pos_key_type'] = $info['search_pos_key_type'];
		}
		if ($info['search_pos_name']) {
			$posParams['dever_pos_name'] = array('LIKE',$info['search_pos_name']);
		}
		if($info['search_pos_key']){
			$posParams['dever_pos_key'] = array('LIKE',$info['search_pos_key']);
		}
		$appPosInfo = MobgiApi_Service_AdDeverPosModel::getsBy($posParams);
		if($appPosInfo){
			foreach($appPosInfo as $key=>$value){
				$appPosInfo[$key]['pos_key_name'] = $adPosType[$value['pos_key_type']]?$adPosType[$value['pos_key_type']]:'未知类型';
				if($value['pos_key_type'] == 'CUSTOME_INTERGRATION' ){
					$appPosInfo["pos"][$key]['pos_key_name'] .=  ' ' . Common_Service_Const::$mCustomeSubType[$value['ad_sub_type']];
				}
			}
		}
		//按广告位进行排序
		if($appPosInfo){
			$sort_pos_key_type = array();
			foreach($appPosInfo as $item){
				$sort_pos_key_type[] = $item['pos_key_type'];
			}
			array_multisort($sort_pos_key_type, SORT_DESC, $appPosInfo);
		}
		Yaf_loader::import("Util/PHPExcel/PHPExcel.php");
		$objPHPExcel = new PHPExcel();
		/*以下是一些设置 ，什么作者  标题啊之类的*/
		$objPHPExcel->getProperties()->setCreator("backend")
		->setLastModifiedBy("backend")
		->setTitle($appInfo['app_name']."广告位")
		->setSubject($appInfo['app_name']."广告位")
		->setDescription($appInfo['app_name']."广告位")
		->setKeywords("excel")
		->setCategory("result file");
		/*以下就是对处理Excel里的数据，横着取数据*/
		$first_line = array();
		
		$all_field =  array(
				"app_name"=>"应用名称",
				"app_key"=>"app_key",
				"dever_pos_name" => "广告位名称",
				"pos_key_name" => "广告位形式",
				"dever_pos_key" => "广告位ID",
		);
		$num = 1;
		$char = 'A';
		foreach($all_field as $field_key => $field_val){
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$num, $field_val);
			$char ++;
		}
		foreach($appPosInfo as $data_key => $data_val){
			$num ++;
			$char = 'A';
			foreach($all_field as $field_key => $field_val){
				if(in_array($field_key, array('app_name', 'app_key'))){
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$num, $appInfo[$field_key]);
					$objPHPExcel->getActiveSheet()->mergeCells('A2:A'.$num);
					$objPHPExcel->getActiveSheet()->mergeCells('B2:B'.$num);
					$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
					$objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
					$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中
					$objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中
				}else{
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$num, $data_val[$field_key]);
				}
				$char ++;
			}
		}
		// 开始组合头
		$xml_name = $appInfo['app_name']."广告位";
		$objPHPExcel->getActiveSheet()->setTitle('User');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$xml_name.'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	
	public function modifyPosKeyAction(){
		$pos_key=$this->getPost('pos_key');
		$pos_id = $this->getPost('pos_key_id');
		if(!trim($pos_key)){
			$this->output(1,'修改的广告位不能为空');
		}
		
		if(Util_Environment::isOnline()){
			$this->output(1,'正式站的广告位不能修改');
		}
		$params['dever_pos_key'] = $pos_key;
		$params['del'] = 1;
		$posInfo = MobgiApi_Service_AdDeverPosModel::getBy($params);
		if($posInfo['id'] == $pos_id){
			$this->output(1,'广告位未改变');
		}
		if($posInfo){
			$this->output(1,'广告位已存在');
		}
		$oldPosInfo = MobgiApi_Service_AdDeverPosModel::getBy(array('id'=>$pos_id));
		//更新广告位key
		$data['dever_pos_key'] = $pos_key;
		MobgiApi_Service_AdDeverPosModel::updateByID($data, $pos_id);
		//删除插图与视图广告位
		//$this->updateIntergrationAd($oldPosInfo['dever_pos_key'], $this->post["app_key"]);
		//$this->updateFiterConf($oldPosInfo['dever_pos_key'], $this->post["app_key"]);
		$this->output(0,'广告位操作成功');
		 
	}
	
	public function addPosPostAction() {
		$info = $this->getPost ( array (
				'app_key',
				'app_id',
				'dever_pos_key',
				'pos_id',
				'dever_pos_name',
				'pos_key_type',
				'pos_desc',
				'ad_sub_type',
                'size',
				'state',
				'rate',
				'limit_num'
		) );
		$this->checkAppPos ( $info );
		if ($info ['dever_pos_key']) {
			$appInfo = MobgiApi_Service_AdAppModel::getByID ( $info['app_id'] );
			foreach ( $info ['dever_pos_key'] as $key => $val ) {
				$data ['app_id'] = $info ['app_id'];
				$data ['dev_id'] = $appInfo ['dev_id'];
				$data ['dever_pos_name'] = $info ['dever_pos_name'] [$key];
				$data ['pos_key_type'] = $info ['pos_key_type'] [$key];
				$data ['dever_pos_key'] = $val;
				$data ['pos_desc'] = $info ['pos_desc'] [$key];
				$data ['ad_sub_type'] = $info ['ad_sub_type'] [$key];
                $data ['size'] = $info ['size'] [$key];
				$data ['state'] = $info ['state'] [$key];
				$data ['rate'] = $info ['rate'] [$key];
				$data ['limit_num'] = intval($info ['limit_num'] [$key]);
				if ($info ['pos_id'] [$key]) {
					MobgiApi_Service_AdDeverPosModel::updateByID ( $data, $info ['pos_id'] [$key] );
				} else {
					MobgiApi_Service_AdDeverPosModel::add ( $data );
				}
			}
			$this->addAdsAppRel($appInfo);
			$this->addAdsPosRel($appInfo);
		   //$this->addFlowPosRel($appInfo);
		}
		$this->output ( 0, '操作成功' );
	
	}
	
	private function checkAppPos($info) {
		if (empty ( $info ['dever_pos_key'] )) {
			return false;
		}
		
		foreach ( $info ['dever_pos_key'] as $key => $val ) {
			if(!is_numeric($info ['rate'] [$key]) || $info ['rate'] [$key]>1 ||$info ['rate'] [$key]< 0 ){
				$this->output ( 1, '广告位概率在０－１之间' );
			}
		}
		foreach ( $info ['limit_num'] as $key => $val ) {
			if(!is_numeric($info ['limit_num'] [$key]) ){
				$this->output ( 1, '限制次数是大于等于零数字' );
			}
		}
		foreach ( $info ['dever_pos_key'] as $key => $val ) {
			if ($info ['pos_id'] [$key]) {
				$params ['id'] = array ('<>',$info ['pos_id'] [$key] );
			}
			$params ['dever_pos_key'] = $val;
			$params ['del'] = MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG;
			$params['pos_key_type'] = array('IN', Common_Service_Const::$mAdPosType);
			$result = MobgiApi_Service_AdDeverPosModel::getBy ( $params );
			if ($result) {
				$this->output ( - 1, '广告位id:' . $val . '已经存在' );
			}
		}
		
		foreach ( $info ['dever_pos_name'] as $key => $val ) {
			if ( Common::strLength($val) >50) {
				$this->output ( - 1, '广告位名称:"'.$val.'"长度不符合' );
			}	
			if ($info ['pos_id'] [$key]) {
				$params ['id'] = array (
						'<>',
						$info ['pos_id'] [$key] 
				);
			}
			$params ['dever_pos_name'] = $val;
			$params ['dev_id'] = $this->userInfo ['dev_id'];
			$params ['app_id'] = $info ['app_id'];
			$params ['pos_key_type'] = $info ['pos_key_type'] [$key];
			$params ['del'] = MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG;
			$result = MobgiApi_Service_AdDeverPosModel::getBy ( $params );
			if ($result) {
				$this->output ( - 1, '广告位名称:' . $val . '已经存在' );
			}
		}
		
		foreach ( $info ['pos_desc'] as $key => $val ) {
			if ( Common::strLength($val) >50) {
				$this->output ( - 1, '广告位描述:"'.$val.'"长度不符合' );
			}
		}
	
	}
	
	private function checkAppInfo($info) {
		if (empty ( trim ( $info ['app_name'] ) )) {
			$this->output ( - 1, '应用名称不能为空' );
		}
		if (!$info ['dev_id'] ) {
			$this->output ( - 1, '选择开发者' );
		}
		if ( Common::strLength($info ['app_name']) >20) {
			$this->output ( - 1, '应用名称长度不能大于20' );
		}
		if (empty ( trim ( $info ['app_key'] ) )) {
			$this->output ( - 1, 'app_key不能为空' );
		}
		if (empty ( trim ( $info ['package_name'] ) )) {
			$this->output ( - 1, '应用包名不能为空' );
		}
		if (empty ( trim ( $info ['platform'] ) )) {
			$this->output ( - 1, '应用平台非法' );
		}
		if (empty ( trim ( $info ['appcate_id'] ) )) {
			$this->output ( - 1, '应用分类不能为空' );
		}
		if (empty ( trim ( $info ['app_desc'] ) )) {
			$this->output ( - 1, '应用描述不能为空' );
		}
		if ( Common::strLength($info ['app_desc']) >200) {
			$this->output ( - 1, '应用描述长度不能大于200' );
		}
		if (empty ( trim ( $info ['keyword'] ) )) {
			$this->output ( - 1, '关键字不能为空' );
		}
		if ( Common::strLength($info ['keyword']) >50) {
			$this->output ( - 1, '关键字不能大于50' );
		}
		if (empty ( trim ( $info ['icon'] ) )) {
			$this->output ( - 1, 'icon图片不能为空' );
		}
		
		if ($info ['app_id']) {
			$params ['app_id'] = array (
					'<>',
					$info ['app_id'] 
			);
		}
		$params ['app_key'] = trim ( $info ['app_key'] );
		$result = MobgiApi_Service_AdAppModel::getBy ( $params );
		if ($result) {
			$this->output ( - 1, 'app_key已经存在' . $info ['app_id'] );
		}
		unset ( $params );
		if ($info ['app_id']) {
			$params ['app_id'] = array (
					'<>',
					$info ['app_id'] 
			);
		}
		$params ['app_name'] = trim ( $info ['app_name'] );
		$params ['dev_id'] = $this->userInfo ['dev_id'];
		$result = MobgiApi_Service_AdAppModel::getBy ( $params );
		if ($result) {
			$this->output ( - 1, '应用的名称已经存在' );
		}
		if ($info ['is_track'] == '1') {
			if ( !in_array($info['delivery_type'] , array(1,2,3))) {
				$this->output ( - 1, '投放类型异常' );
			}
			if (empty ( trim ( $info ['appstore_id'] ) )) {
				$this->output ( - 1, 'appstoreId不能为空' );
			}
			if (empty ( trim ( $info ['consumer_key'] ) )) {
				$this->output ( - 1, 'consumerKey不能为空' );
			}
		}
	
	}
	
	public function createAppKeyAction() {
		$appKey = $this->createAppKey ();
		$this->output ( 0, 'ok', $appKey );
	}
	
	public function createAppKey() {
		$str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()";
		$str = str_shuffle ( $str ); // 随机打乱字符
		$str = substr ( $str, 0, 32 ); // 取打乱后的前32个字符作为基本字符
		$md5str = md5 ( strrev ( sha1 ( md5 ( $str ) ) ) . time () );
		$serialnum = str_replace ( array (
				'O',
				'0',
				'I',
				'1' 
		), '', strtoupper ( $md5str ) ); // 去掉容易产生误会的字符
		$serialnum = substr ( $serialnum, 0, 20 );
		if (empty ( $serialnum ) || strlen ( $serialnum ) > 20 || strpbrk ( $serialnum, "0OI1" )) { // 如果生成的序列号少于20个字符或者字符中存在0，O，I，1则重新生成
			$this->createAppKey ();
		}
		return $serialnum;
	}
	
	public function createPosKeyAction() {
		$appKey = $this->getGet ( 'app_key' );
		if (empty ( $appKey )) {
			$appKey = 0;
		}
		//$posKey = substr ( base64_encode ( microtime () ), 0, 23 ) . "-" . substr ( base64_encode ( ($appKey) ), 0, 8 );
		$time = explode(' ', microtime());
		$time2= substr($time[0], 2, 3);
		$posKey = date('YmdHis',$time[1]).$time2.rand(10000,99999);
		$this->output ( 0, 'ok', $posKey );
	}
	
	/**
	 * 返回AppType
	 */
	public function appTypeAction() {
		$appType = $this->appConfig ();
		echo json_encode ( $appType );
	}
	
	/**
	 * app类型
	 *
	 * @return array
	 */
	private function appConfig() {
		$appType = array (
				'教育/阅读' => array (
						1 => '儿童教育',
						2 => '漫画',
						3 => '商业杂志',
						4 => '成功励志',
						5 => '言情都市',
						6 => '工具书',
						7 => '新闻时事',
						8 => '其他读物' 
				),
				'财务/效率' => array (
						9 => '金融',
						10 => '理财',
						11 => '效率' 
				),
				'娱乐/社交' => array (
						12 => '社交',
						13 => '音乐',
						14 => '视频',
						15 => '娱乐' 
				),
				'生活/工具' => array (
						16 => '健康',
						17 => '生活',
						18 => '实用工具',
						19 => '天气' 
				),
				'游戏' => array (
						20 => '休闲益智',
						21 => '经营策略',
						22 => '动作竞技',
						23 => '棋牌游戏',
						24 => '飞行射击',
						25 => '体育竞技',
						26 => '网络游戏',
						27 => '社交游戏',
						28 => '其他游戏' 
				),
				'其他类别' => array (
						29 => '其他应用' 
				) 
		);
		return $appType;
	}
	
	public function uploadImgAction() {
		$imgId = $this->getInput ( 'imgId' );
		$this->assign ( 'imgId', $imgId );
		$this->getView ()->display ( 'common/upload.phtml' );
		exit ();
	}
	
	public function uploadImgPostAction() {
		$ret = Common::upload ( 'img', 'icon', array (
				'allowFileType' => array (
						'gif',
						'jpeg',
						'jpg',
						'png',
						'bmp' 
				) 
		) );
		$imgId = $this->getInput ( 'imgId' );
		$this->assign ( 'code', $ret ['data'] );
		$this->assign ( 'msg', $ret ['msg'] );
		$this->assign ( 'data', $ret ['data'] );
		$this->assign ( 'imgId', $imgId );
		$this->getView ()->display ( 'common/upload.phtml' );
		exit ();
	}
	
	public function updateAppStateAction(){
		$appId = $this->getInput('app_id');
		$state = $this->getInput('state');
		$ret = MobgiApi_Service_AdAppModel::getByID($appId);
		if (!$ret) $this->output(-1, '应用非法');
		$data['state'] = $state;
		$ret= MobgiApi_Service_AdAppModel::updateByID($data, $appId);
		if (!$ret) $this->output(-1, '操作失败');
		$this->output(0, '操作成功');
	}
	

	/**
	 * 
	 * Enter description here ...
	 */
	public function deleteAction() {
		$id = $this->getInput('id');
		$result = Admin_Service_MenuConfigModel::deleteById($id);
		if (!$result) $this->output(-1, '操作失败');
		$this->output(0, '操作成功');
	}
	
	/**
	 *
	 * Enter description here ...
	 */
	public function viewAction() {
		$appId = intval ( $this->getGet ( 'app_id'));
		$appInfo = MobgiApi_Service_AdAppModel::getByID ( $appId );
		if (! $appInfo) {
			$this->output ( - 1, '非法操作' );
		}
		$appInfo ['appcate_name'] = $this->getAppTypeName($appInfo['appcate_id']);
		$appInfo ['developer'] = $this->getEmailById($appInfo['dev_id']);
		$this->assign ( 'appInfo', $appInfo );
		$this->assign ( 'appPosList', $this->getPostList($appInfo['dev_id'], $appId) );
		$this->assign ( 'adPosType', Common_Service_Const::$mAdPosTypeName );
		
	}
  
}
