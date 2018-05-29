<?php
if (!defined('BASE_PATH')) {
	exit('Access Denied!');
}

/**
 *
 * Enter description here ...
 *
 * @author rock.luo
 *
 */
class Interative_ConfController extends Admin_BaseController
{

	public $actions = array(
		'listUrl' => '/Admin/Interative_Conf/index',
		'addUrl' => '/Admin/Interative_Conf/add',
		'addPostUrl' => '/Admin/Interative_Conf/addPost',
		'deleteUrl' => '/Admin/Interative_Conf/delete',
		'viewUrl' => '/Admin/Interative_Conf/view',
		'getAdsListUrl' => '/Admin/Interative_Conf/getAdsList',
		'getPosListUrl' => '/Admin/Interative_Conf/getPosList',
		'updatePosStatusUrl'=>'/Admin/Interative_Conf/updatePosStatus',
	);

	public $perpage = 10;
	public $confType = array(MobgiApi_Service_InteractiveAdConfModel::DEAFAULT_CONF_TYPE=>'全局配置',
		MobgiApi_Service_InteractiveAdConfModel::ANDRIOD_CONF_TYPE=>'安卓定向配置',
		MobgiApi_Service_InteractiveAdConfModel::IOS_CONF_TYPE=>'IOS定向配置');


	public function indexAction()
	{
		$this->getAppKeyList();

	}

	private function getAppKeyList()
	{
		$search = $params = array();
		$page = intval($this->getInput('page'));
		if ($page < 1) {
			$page = 1;
		}

		$search = $this->getInput(array(
			'platform',
			'app_name',
		));
		if (trim($search['app_name'])) {
			$appKeys = MobgiApi_Service_AdAppModel::getAppKeysByName($search['app_name']);
			if ($appKeys) {
				$params['app_key'] = array(
					'IN',
					$appKeys,
				);
			} else {
				$params['app_key'] = '0';
			}
		}
		if (isset($search['platform']) && $search['platform']) {
			$params['platform'] = $search['platform'];
		}
		$posList= MobgiApi_Service_AdDeverPosModel::getsBy(array('pos_key_type'=>'INTERATIVE_AD'));
		if($posList){
  			$appId = array_keys(common::resetKey($posList,'app_id'));
			$params['app_id'] = array(
				'IN',
				$appId,
			);
		}else{
			$params['app_id'] = 0;
		}

		$params['is_check'] = MobgiApi_Service_AdAppModel::ISCHECK_PASS;
		list($total, $appList) = MobgiApi_Service_AdAppModel::getList($page, $this->perpage, $params, array(
			'update_time' => 'DESC',
		));
		$url = $this->actions['listUrl'] . '/?' . http_build_query($search) . '&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
		foreach ($appList as $key => $value) {
			if (!stristr($value['icon'], 'http')) {
				$appList[$key]['icon'] = Common::getAttachPath() . $value['icon'];
			}
			if ($value['platform'] == Common_Service_Const::ANDRIOD_PLATFORM) {
				$appList[$key]['platform_class'] = 'android';
				$appList[$key]['platform_name'] = 'Android';
			} else {
				$appList[$key]['platform_class'] = 'ios';
				$appList[$key]['platform_name'] = 'Ios';
			}
			$appList[$key]['is_config'] = MobgiApi_Service_InteractiveAdConfRelModel::getBy(array('app_key' => $value['app_key']));
		}
		$this->assign('appList', $appList);
		$this->assign('search', $search);
		$this->assign('total', $total);
	}

	public function getQueryString()
	{
		$search = $this->getInput(array(
			'platform',
			'app_name',
			'page',
		));

		return http_build_query($search);

	}

	public function initAdConfData(){
		$info = $this->getInput(array('conf_rel_id','pos_key','app_key'));
		$this->getAppKeyList();
		$templateList = MobgiApi_Service_InteractiveAdTemplateModel::getsBy(array('id'=>array('>',0)));
		$this->assign('templateList', $templateList);

		if(!$info['conf_rel_id']){
			$data = $info;
			foreach ($this->confType as $confType=>$val){
				$adInfo[]=array(
					'conf_type'=>$confType,
					'name'=>$val,
					'status_'.$confType=>0,
					'template_id_'.$confType=>'',
					'general_list'=>array()
				);
			}
			$data['ad_Info']= $adInfo;
		}else{
			foreach ($this->confType as $confType=>$val){
				$adConf = MobgiApi_Service_InteractiveAdConfModel::getsBy(array('conf_rel_id'=>$info['conf_rel_id'],'conf_type'=>$confType));
				$list = array();
				if($adConf){
					foreach ($adConf as $va){
						$list[] = array('id'=>$va['id'],
							'ads_id'=>$va['ads_id'],
							'weight'=>$va['weight'],
							'url'=>$va['url'],
						);
					}
				}
				$adInfo[$confType]=array(
					'conf_type'=>$confType,
					'name'=>$val,
					'status_'.$confType=>$adConf?$adConf[0]['status']:0,
					'template_id_'.$confType=>$adConf?$adConf[0]['template_id']:'',
					'general_list'=>$list,
					'adsList'=>$this->initAdsIdsList(6)
				);
			}
			$data['ad_Info']= $adInfo;
			$data['conf_rel_id'] = $info['conf_rel_id'];
			$data['pos_key'] = $info['pos_key'];
			$data['app_key'] = $info['app_key'];

		}

		$posInfo = MobgiApi_Service_AdDeverPosModel::getBy(['dever_pos_key'=>$info['pos_key']]);
		$data['pos_name'] =$posInfo['dever_pos_name'];
		$data['pos_state'] =$posInfo['state'];
		$this->assign('info', $data);
		$this->assign('queryString', $this->getQueryString());
		$iaadroot = Yaf_Application::app()->getConfig()->couponroot;
		$iaAdUrl = $iaadroot.'/Coupon/Iaad/config?app_key='.$info['app_key'].'&pos_key='.$info['pos_key'];

		$this->assign('iaAdUrl', $iaAdUrl);
	}

	public function addAction()
	{


		$this->initAdConfData();

	}

	public function addPostAction()
	{
		$info = $this->getRequest()->getPost();
		$info = $this->checkPostParam($info);
		$confRelId = $this->updateConfRel($info);
		if (!$confRelId) {
			$this->output(-1, '操作失败');
		}
		$this->updateFlowGeneralAdsRel($info, $confRelId);

		$this->output(0, '操作成功');
	}

	public function updateConfRel($info){
		if(!$info['conf_rel_id']){
			$data['app_key'] = $info['app_key'];
			$data['pos_key'] = $info['pos_key'];
			$data['operator'] = $this->userInfo['user_name'];
			$confRelId= MobgiApi_Service_InteractiveAdConfRelModel::add($data);
			return $confRelId;
		}

	return $info['conf_rel_id'];

	}



	/**
	 * @param $info
	 * @param $confRelId
	 */
	public function updateFlowGeneralAdsRel($info, $confRelId)
	{
		foreach ($this->confType as $confType => $val) {
			//编辑
			if ($info['conf_rel_id']) {
				if($info['gerneral_ads_id_' . $confType]){
					foreach ($info['gerneral_ads_id_' . $confType] as $index => $adsId) {
						$data['conf_rel_id'] = $confRelId;
						$data['conf_type'] = $confType;
						$data['ads_id'] = $adsId;
						$data['status'] = $info['status_'.$confType];
						$data['template_id'] = $info['template_id_' . $confType];
						$data['url'] = trim($info['gerneral_url_' . $confType][$index]);
						$data['weight'] = $info['gerneral_weight_' . $confType][$index];
						$data['operator'] = $this->userInfo['user_name'];
						if($info['gerneral_id_' . $confType][$index]){
							MobgiApi_Service_InteractiveAdConfModel::updateBy($data,array('id'=>$info['gerneral_id_' . $confType][$index]));
						}else{
							MobgiApi_Service_InteractiveAdConfModel::add($data);
						}
					}
				}else{
					MobgiApi_Service_InteractiveAdConfModel::deleteBy(array('conf_rel_id'=>$confRelId,'conf_type'=>$confType));
				}
				//添加
			} else {
				$data = array();
				if($info['gerneral_ads_id_' . $confType]){
					foreach ($info['gerneral_ads_id_' . $confType] as $index => $adsId) {
						$tmp['conf_rel_id'] = $confRelId;
						$tmp['conf_type'] = $confType;
						$tmp['ads_id'] = $adsId;
						$tmp['status'] = $info['status_' . $confType][$index];
						$tmp['template_id'] = $info['template_id_' . $confType][$index];
						$tmp['url'] = $info['gerneral_url_' . $confType][$index];
						$tmp['weight'] = $info['gerneral_weight_' . $confType][$index];
						$tmp['operator'] = $this->userInfo['user_name'];
						$tmp['create_time'] = date('Y-m-d H:i:s');
						$tmp['update_time'] = date('Y-m-d H:i:s');
						$data[] = $tmp;
					}
					if ($data) {
						MobgiApi_Service_InteractiveAdConfModel::mutiFieldInsert($data);
					}
				}


			}

		}
	}



	/**
	 * @param $info
	 * @return mixed
	 */
	public function checkPostParam($info)
	{
		if (!trim($info['app_key'])) {
			$this->output(-1, 'app_key为空');
		}
		if (!trim($info['pos_key'])) {
			$this->output(-1, '广告位为空为空');
		}
		$this->checkGerneralAdsConf($info);

		return $info;
	}


	/**
	 * @param $info
	 */
	private function checkGerneralAdsConf($info)
	{

		foreach ($this->confType as $confType => $name) {

			if ($info ['status_' . $confType]&& !empty ($info ['gerneral_ads_id_' . $confType]) && !$info ['template_id_' . $confType]) {
				$this->output(-1, $name . '中的模板没有选择' );
			}

			if (!empty ($info ['gerneral_ads_id_' . $confType])) {

				foreach ($info ['gerneral_ads_id_' . $confType] as $postion => $va) {
					if (!$va) {
						$this->output(-1, $name . '中的广告商位置:"' . ($postion + 1) . '"为空');
					}

				}
				foreach ($info ['gerneral_weight_' . $confType] as $postion => $va) {
					if (!is_numeric($va)) {
						$this->output(-1, $name . '中的广告商权重必须为数字');
					}
					if ($va > 1 || $va <= 0) {
						$this->output(-1, $name . '中的广告商权重范围０－１之间数字');
					}
				}
				foreach ($info ['gerneral_url_' . $confType] as $postion => $va) {
					if (!common::checkUrl($va)) {
						$this->output(-1, $name . '中的广告商url不合法' . $va);
					}
				}
				if (strval(array_sum($info ['gerneral_weight_' . $confType])) != '1') {
					$this->output(-1, $name . '中的广告商的权重不为１,计算结果为：' . array_sum($info ['gerneral_weight_' . $confType]));
				}
			}
		}
	}




	public function viewAction()
	{
		$this->initAdConfData();
		$this->assign('act', 'view');
		$this->getView()->display('interative/conf/add.phtml');
		exit();
	}


	public function updatePosStatusAction(){
		$pos_key = $this->getInput('pos_key');
		if (!$pos_key) {
			$this->output(-1, '非法请求');
		}
		$data['state']=  intval($this->getInput('status'));
		$result = MobgiApi_Service_AdDeverPosModel::updateBy($data,['dever_pos_key'=>$pos_key]);
		if (!$result) {
			$this->output(-1, '更新失败');
		}
		$this->output(0, '操作成功');

	}

	public function getPosListAction()
	{
		$appKey = $this->getInput('app_key');
		if (!$appKey) {
			$this->output(-1, '非法请求');
		}
		$params['app_key'] = $appKey;
		$outData = array();
		$appInfo = MobgiApi_Service_AdAppModel::getBy(array(
			'app_key' => $appKey,
		));
		if (empty($appInfo)) {
			$this->output(-1, 'ok');
		}
		unset($params);
		$params ['app_id'] = $appInfo['app_id'];
		$params['pos_key_type'] = array('IN', Common_Service_Const::$mAdPosType);
		$params ['del'] = MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG;
		$appPosList = MobgiApi_Service_AdDeverPosModel::getsBy ( $params );

		$outData['app_key'] = $appInfo['app_key'];
		$outData['app_name'] = $appInfo['app_name'];
		$outData['platform'] = $appInfo['platform'];
		if (!stristr($appInfo['icon'], 'http')) {
			$outData['icon'] = Common::getAttachPath() . $appInfo['icon'];
		} else {
			$outData['icon'] = $appInfo['icon'];
		}
		if ($appInfo['platform'] == Common_Service_Const::ANDRIOD_PLATFORM) {
			$outData['platform_class'] = 'android';
		} else {
			$outData['platform_class'] = 'ios';
		}
		$list = array();
		if ($appPosList) {
			foreach ($appPosList as $key => $val) {
				$adConf = MobgiApi_Service_InteractiveAdConfRelModel::getBy(array('app_key'=>$appKey,'pos_key'=>$val['dever_pos_key']));
				$list[] = array(
					'conf_rel_id'=>$adConf?$adConf['id']:0,
					'app_key'=>$appKey,
					'pos_key' => $val['dever_pos_key'],
					'pos_name' => $val['dever_pos_name'],
					'state' => ($val['state']== 1)? '开启':'关闭',
					'operator' => $adConf?$adConf['operator']:'无',
					'update_time' => $adConf?date('Y-m-d H:i:s',$val['update_time']):'无',
				);
			}
		}
		$outData['list'] = $list;
		$this->output(0, 'ok', $outData);
	}

	public function deleteAction()
	{
		$flowId = $this->getInput('flow_id');
		if (!$flowId) {
			$this->output(-1, '非法请求');
		}

		$this->output(0, '删除成功');
	}







	/**
	 * @return mixed
	 */
	private function getAdsNameList()
	{
		$params['ad_type'] = array(
			'IN',
			array(
				1,
				3,
			),
		);
		$adsList = MobgiApi_Service_AdsListModel::getsBy($params);
		$adsNameList = Common::resetKey($adsList, 'ads_id');
		return $adsNameList;
	}





	public function getAdsListAction()
	{
		$info = $this->getInput(array(
			'ad_type',
		));
		if (!$info['ad_type']) {
			$this->output(-1, '非法操作');
		}
		$adsList= $this->initAdsIdsList($info['ad_type']);
		$data['adsList'] = $adsList;
		$this->output(0, '操作成功', $data);
	}


	/**
	 * @param $adSubType
	 * @return array|multitype
	 */
	private function initAdsIdsList($adSubType)
	{
		$adsList = array();
		$params['ad_type'] = array(
			'IN',
			array(
				1,
			),
		);
		$result = MobgiApi_Service_AdsListModel::getsBy($params, array('ads_id' => 'ASC'));
		if (!$result) {
			return $adsList;

		}
		foreach ($result as $val) {
			$ad_sub_type = json_decode($val['ad_sub_type'], true);
			if(in_array($adSubType,$ad_sub_type)){
				$adsList[$val['ads_id']] = $val['name'];
			}

		}
		return $adsList;
	}
}
