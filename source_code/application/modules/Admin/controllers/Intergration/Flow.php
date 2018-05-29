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
class Intergration_FlowController extends Admin_BaseController {
	/**
	 * @var array
	 */
	public $actions = array(
		'listUrl'           => '/Admin/Intergration_Flow/index',
		'addUrl'            => '/Admin/Intergration_Flow/add',
		'addPostUrl'        => '/Admin/Intergration_Flow/addPost',
		'deleteUrl'         => '/Admin/Intergration_Flow/delete',
		'viewUrl'           => '/Admin/Intergration_Flow/view',
		'getAdsListUrl'     => '/Admin/Intergration_Flow/getAdsList',
		'getAreaListUrl'    => '/Admin/Intergration_Flow/getAreaList',
		'getChannelListUrl' => '/Admin/Intergration_Flow/getChannelList',
		'flowListUrl'       => '/Admin/Intergration_Flow/getFlowList',
	);
	/**
	 * @var int
	 */
	public $perpage = 10;

	/**
	 * Enter description here .
	 *
	 *
	 *
	 * ..
	 */
	public function indexAction() {
		$this->getAppKeyList();

	}

	private function getAppKeyList() {
		$search = $params = array();
		$page   = intval($this->getInput('page'));
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
		$posList= MobgiApi_Service_AdDeverPosModel::getsBy(array('pos_key_type'=>array('NOT IN',array('INTERATIVE_AD'))));
		if($posList){
			$appId = array_keys(common::resetKey($posList,'app_id'));
			$params['app_id'] = array(
				'IN',
				$appId,
			);
		}else{
			$params['app_id'] = 0;
		}
		$params['is_check']    = MobgiApi_Service_AdAppModel::ISCHECK_PASS;
		list($total, $appList) = MobgiApi_Service_AdAppModel::getList($page, $this->perpage, $params, array(
				'update_time' => 'DESC',
			));
		$url = $this->actions['listUrl'].'/?'.http_build_query($search).'&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
		foreach ($appList as $key => $value) {
			if (!stristr($value['icon'], 'http')) {
				$appList[$key]['icon'] = Common::getAttachPath().$value['icon'];
			}
			if ($value['platform'] == Common_Service_Const::ANDRIOD_PLATFORM) {
				$appList[$key]['platform_class'] = 'android';
				$appList[$key]['platform_name']  = 'Android';
			} else {
				$appList[$key]['platform_class'] = 'ios';
				$appList[$key]['platform_name']  = 'Ios';
			}
			$appList[$key]['is_config'] = MobgiApi_Service_FlowConfModel::getBy(array(
					'app_key'   => $value['app_key'],
					'conf_type' => MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE))?true:false;
		}
		$this->assign('appList', $appList);
		$this->assign('search', $search);
		$this->assign('total', $total);
	}

	public function getQueryString() {
		$search = $this->getInput(array(
				'platform',
				'app_name',
				'page',
			));

		return http_build_query($search);

	}

	public function addAction() {
		$this->getAppKeyList();
		$flowId = $this->getInput('flow_id');
		if ($flowId) {
			$data = $this->getEditFlowInfo($flowId);
		} else {
			foreach (Common_Service_Const::$mAdSubType as $adSubType => $val) {
				$tmp[] = array(
					'ad_type' => $adSubType,
					'name'    => $val,
				);
			}
			$data['ad_Info'] = $tmp;
			$data['app_key'] = $this->getInput('app_key');
		}
		$this->assign('info', $data);
		$this->assign('queryString', $this->getQueryString());
	}

	public function addPostAction() {
		$info   = $this->getRequest()->getPost();
		$info   = $this->checkPostParam($info);
		$flowId = $this->updateFlowConf($info);
		$this->updateFlowAdTypeRel($info, $flowId);
		$this->updateFlowGeneralAdsRel($info, $flowId);
		$this->updateFlowPriorityAdsRel($info, $flowId);
		$this->updateFlowDspAdsRel($info, $flowId);
		$this->updateFlowAppRel($info, $flowId);
		$this->updateFlowPosRel($info, $flowId);
		$this->updateFlowPosPolicyRel($info, $flowId);
		if (!$flowId) {
			$this->output(-1, '操作失败');
		}
		$this->output(0, '操作成功');
	}

	/**
	 * @param $info
	 * @param $flowId
	 */
	public function updateFlowPosPolicyRel($info, $flowId) {
		foreach (Common_Service_Const::$mAdSubType as $adSubType => $val) {
			//编辑
			if ($info['flow_id']) {
				if ( $info['is_block_policy_'.$adSubType] && !$info['is_default_'.$adSubType]) {
					$oldData    = MobgiApi_Service_FlowPosPolicyRelModel::getsBy(array('flow_id' => $flowId, 'ad_type' => $adSubType));
					$oldPosKeys = array();
					if ($oldData) {
						$oldPosKeys = array_keys(Common::resetKey($oldData, 'pos_key'));
						//删除未提交上来的广告商
						foreach ($oldPosKeys as $oldPosKey) {
							if (!in_array($oldPosKey, $info['pos_policy_pos_key_'.$adSubType])) {
								MobgiApi_Service_FlowPosPolicyRelModel::deleteBy(array('flow_id' => $flowId, 'ad_type' => $adSubType, 'pos_key' => $oldPosKey));
							}
						}
					}
					$tmp = array();
					foreach ($info['pos_policy_pos_key_'.$adSubType] as $index => $posKey) {
						$tmp['flow_id']   = $flowId;
						$tmp['app_key']   = $info['app_key'];
						$tmp['ad_type']   = $adSubType;
						$tmp['pos_key']   = $posKey;
						$tmp['rate']      = $info['pos_policy_rate_'.$adSubType][$index];
						$tmp['status']    = $info['pos_policy_state_'.$adSubType][$index];
						$tmp['limit_num'] = $info['pos_policy_limit_num_'.$adSubType][$index];
						if (in_array($posKey, $oldPosKeys)) {
							MobgiApi_Service_FlowPosPolicyRelModel::updateBy($tmp, array('flow_id' => $flowId, 'ad_type' => $adSubType, 'pos_key' => $posKey));
						} else {
							MobgiApi_Service_FlowPosPolicyRelModel::add($tmp);
						}
					}
				} else {
					MobgiApi_Service_FlowPosPolicyRelModel::deleteBy(array('flow_id' => $flowId, 'ad_type' => $adSubType));
				}
				//添加
			} else {
				if ($info['status_'.$adSubType] && $info['is_block_policy_'.$adSubType] && !$info['is_default_'.$adSubType]) {
					$appRelData = array();
					foreach ($info['pos_policy_pos_key_'.$adSubType] as $index => $posKey) {
						$tmp['flow_id']     = $flowId;
						$tmp['app_key']     = $info['app_key'];
						$tmp['ad_type']     = $adSubType;
						$tmp['pos_key']     = $posKey;
						$tmp['rate']        = $info['pos_policy_rate_'.$adSubType][$index];
						$tmp['status']      = $info['pos_policy_state_'.$adSubType][$index];
						$tmp['limit_num']   = $info['pos_policy_limit_num_'.$adSubType][$index];
						$tmp['create_time'] = date('Y-m-d H:i:s');
						$tmp['update_time'] = date('Y-m-d H:i:s');
						$appRelData[]       = $tmp;
					}
					if ($appRelData) {
						MobgiApi_Service_FlowPosPolicyRelModel::mutiFieldInsert($appRelData);
					}
				}
			}
		}
	}

	/**
	 * @param $info
	 * @param $flowId
	 */
	public function updateFlowPosRel($info, $flowId) {
		foreach (Common_Service_Const::$mAdSubType as $adSubType => $val) {
			//编辑
			if ($info['flow_id']) {
				if ( $info['is_app_rel_'.$adSubType] && !$info['is_default_'.$adSubType]) {
					$oldData   = MobgiApi_Service_FlowPosRelModel::getsBy(array('flow_id' => $flowId, 'ad_type' => $adSubType));
					$oldAdsIds = array();
					if ($oldData) {
						$oldAdsIds = array_keys(Common::resetKey($oldData, 'ads_id'));
						//删除未提交上来的广告商
						foreach ($oldAdsIds as $oldAdsId) {
							if (!in_array($oldAdsId, $info['app_key_rel_ads_id_'.$adSubType])) {
								MobgiApi_Service_FlowPosRelModel::deleteBy(array('flow_id' => $flowId, 'ad_type' => $adSubType, 'ads_id' => $oldAdsId));
							}
						}
					}
					$tmp = array();
					foreach ($info['app_key_rel_ads_id_'.$adSubType] as $adsId) {
						$jsonInfo = json_decode($info['app_key_rel_pos_set_'.$adSubType][$adsId], true);
						foreach ($jsonInfo as $index => $posInfo) {
							$tmp['flow_id']              = $flowId;
							$tmp['app_key']              = $info['app_key'];
							$tmp['ad_type']              = $adSubType;
							$tmp['ads_id']               = $adsId;
							$tmp['pos_key']              = $posInfo['pos_key'];
							$tmp['third_party_block_id'] = trim($posInfo['third_party_block_id']);
							$params                      = array(
								'flow_id' => $flowId,
								'ad_type' => $adSubType,
								'pos_key' => $posInfo['pos_key'],
								'ads_id'  => $adsId,
							);
							$result = MobgiApi_Service_FlowPosRelModel::getBy($params);
							if ($result) {
								MobgiApi_Service_FlowPosRelModel::updateBy($tmp, $params);
							} else {
								MobgiApi_Service_FlowPosRelModel::add($tmp);
							}
						}
					}
				} else {
					MobgiApi_Service_FlowPosRelModel::deleteBy(array('flow_id' => $flowId, 'ad_type' => $adSubType));
				}
				//添加
			} else {
				if ($info['status_'.$adSubType] && $info['is_app_rel_'.$adSubType] && !$info['is_default_'.$adSubType]) {
					$appRelData = array();
					foreach ($info['app_key_rel_ads_id_'.$adSubType] as $adsId) {
						$jsonInfo = json_decode($info['app_key_rel_pos_set_'.$adSubType][$adsId], true);
						foreach ($jsonInfo as $index => $posInfo) {
							$tmp['flow_id']              = $flowId;
							$tmp['app_key']              = $info['app_key'];
							$tmp['ad_type']              = $adSubType;
							$tmp['ads_id']               = $adsId;
							$tmp['pos_key']              = $posInfo['pos_key'];
							$tmp['third_party_block_id'] = trim($posInfo['third_party_block_id']);
							$tmp['create_time']          = date('Y-m-d H:i:s');
							$tmp['update_time']          = date('Y-m-d H:i:s');
							$appRelData[]                = $tmp;
						}
					}
					if ($appRelData) {
						MobgiApi_Service_FlowPosRelModel::mutiFieldInsert($appRelData);
					}
				}
			}
		}

	}

	/**
	 * @param $info
	 * @param $flowId
	 */
	public function updateFlowAppRel($info, $flowId) {
		foreach (Common_Service_Const::$mAdSubType as $adSubType => $val) {
			//编辑
			if ($info['flow_id']) {
				if ( $info['is_app_rel_'.$adSubType] && !$info['is_default_'.$adSubType]) {
					$oldData   = MobgiApi_Service_FlowAppRelModel::getsBy(array('flow_id' => $flowId, 'ad_type' => $adSubType));
					$oldAdsIds = array();
					if ($oldData) {
						$oldAdsIds = array_keys(Common::resetKey($oldData, 'ads_id'));
						//删除未提交上来的广告商
						foreach ($oldAdsIds as $oldAdsId) {
							if (!in_array($oldAdsId, $info['app_key_rel_ads_id_'.$adSubType])) {
								MobgiApi_Service_FlowAppRelModel::deleteBy(array('flow_id' => $flowId, 'ad_type' => $adSubType, 'ads_id' => $oldAdsId));
							}
						}
					}
					$tmp = array();
					foreach ($info['app_key_rel_ads_id_'.$adSubType] as $index => $adsId) {
						$tmp['flow_id']             = $flowId;
						$tmp['app_key']             = $info['app_key'];
						$tmp['ad_type']             = $adSubType;
						$tmp['ads_id']              = $adsId;
						$tmp['third_party_app_key'] = $info['third_party_app_key_'.$adSubType][$index];
						$tmp['third_party_secret']  = $info['third_party_secret_'.$adSubType][$index];
						if (in_array($adsId, $oldAdsIds)) {
							MobgiApi_Service_FlowAppRelModel::updateBy($tmp, array('flow_id' => $flowId, 'ad_type' => $adSubType, 'ads_id' => $adsId));
						} else {
							MobgiApi_Service_FlowAppRelModel::add($tmp);
						}
					}
				} else {
					MobgiApi_Service_FlowAppRelModel::deleteBy(array('flow_id' => $flowId, 'ad_type' => $adSubType));
				}
				//添加
			} else {
				if ($info['status_'.$adSubType] && !$info['is_default_'.$adSubType]) {
					$appRelData = array();
					foreach ($info['app_key_rel_ads_id_'.$adSubType] as $index => $adsId) {
						$tmp['flow_id']             = $flowId;
						$tmp['app_key']             = $info['app_key'];
						$tmp['ad_type']             = $adSubType;
						$tmp['ads_id']              = $adsId;
						$tmp['third_party_app_key'] = $info['third_party_app_key_'.$adSubType][$index];
						$tmp['third_party_secret']  = $info['third_party_secret_'.$adSubType][$index];
						$tmp['create_time']         = date('Y-m-d H:i:s');
						$tmp['update_time']         = date('Y-m-d H:i:s');
						$appRelData[]               = $tmp;
					}
					if ($appRelData) {
						MobgiApi_Service_FlowAppRelModel::mutiFieldInsert($appRelData);
					}
				}
			}
		}
	}

	/**
	 * @param $info
	 * @param $flowId
	 */
	public function updateFlowDspAdsRel($info, $flowId) {
		foreach (Common_Service_Const::$mAdSubType as $adSubType => $val) {
			//编辑
			if ($info['flow_id']) {
				if ( $info['is_use_dsp_'.$adSubType] && !$info['is_default_'.$adSubType]) {
					$oldData   = MobgiApi_Service_FlowAdsRelModel::getsBy(array('flow_id' => $flowId, 'ad_type' => $adSubType, 'conf_type' => MobgiApi_Service_FlowAdsRelModel::DSP_ADS));
					$oldAdsIds = array();
					if ($oldData) {
						$oldAdsIds = array_keys(Common::resetKey($oldData, 'ads_id'));
						foreach ($oldAdsIds as $oldAdsId) {
							if (!in_array($oldAdsId, $info['dsp_ads_id_'.$adSubType])) {
								MobgiApi_Service_FlowAdsRelModel::deleteBy(array('flow_id' => $flowId, 'ad_type' => $adSubType, 'conf_type' => MobgiApi_Service_FlowAdsRelModel::DSP_ADS, 'ads_id' => $oldAdsId));
							}
						}
					}
					$tmp = array();
					foreach ($info['dsp_ads_id_'.$adSubType] as $index => $adsId) {
						$tmp['flow_id']   = $flowId;
						$tmp['app_key']   = $info['app_key'];
						$tmp['ad_type']   = $adSubType;
						$tmp['conf_type'] = MobgiApi_Service_FlowAdsRelModel::DSP_ADS;
						$tmp['ads_id']    = $adsId;
						$tmp['position']  = $info['dsp_position_'.$adSubType][$index];
						if (in_array($adsId, $oldAdsIds)) {
							MobgiApi_Service_FlowAdsRelModel::updateBy($tmp, array('flow_id' => $flowId, 'ad_type' => $adSubType, 'conf_type' => MobgiApi_Service_FlowAdsRelModel::DSP_ADS, 'ads_id' => $adsId));
						} else {
							MobgiApi_Service_FlowAdsRelModel::add($tmp);
						}
					}
				} else {
					MobgiApi_Service_FlowAdsRelModel::deleteBy(array('flow_id' => $flowId, 'ad_type' => $adSubType, 'conf_type' => MobgiApi_Service_FlowAdsRelModel::DSP_ADS));
				}
				//添加
			} else {
				if ($info['status_'.$adSubType] && $info['is_use_dsp_'.$adSubType] && !$info['is_default_'.$adSubType]) {
					$data = array();
					foreach ($info['dsp_ads_id_'.$adSubType] as $index => $adsId) {
						$tmp['flow_id']     = $flowId;
						$tmp['app_key']     = $info['app_key'];
						$tmp['ad_type']     = $adSubType;
						$tmp['conf_type']   = MobgiApi_Service_FlowAdsRelModel::DSP_ADS;
						$tmp['ads_id']      = $adsId;
						$tmp['position']    = $info['dsp_position_'.$adSubType][$index];
						$tmp['create_time'] = date('Y-m-d H:i:s');
						$tmp['update_time'] = date('Y-m-d H:i:s');
						$data[]             = $tmp;
					}
					if ($data) {
						MobgiApi_Service_FlowAdsRelModel::mutiFieldInsert($data);
					}
				}
			}

		}
	}

	/**
	 * @param $info
	 * @param $flowId
	 */
	public function updateFlowPriorityAdsRel($info, $flowId) {
		foreach (Common_Service_Const::$mAdSubType as $adSubType => $val) {
			//编辑
			if ($info['flow_id']) {
				if ( $info['is_priority_'.$adSubType] && !$info['is_default_'.$adSubType]) {
					$oldData   = MobgiApi_Service_FlowAdsRelModel::getsBy(array('flow_id' => $flowId, 'ad_type' => $adSubType, 'conf_type' => MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS));
					$oldAdsIds = array();
					if ($oldData) {
						$oldAdsIds = array_keys(Common::resetKey($oldData, 'ads_id'));
						foreach ($oldAdsIds as $oldAdsId) {
							if (!in_array($oldAdsId, $info['priority_ads_id_'.$adSubType])) {
								MobgiApi_Service_FlowAdsRelModel::deleteBy(array('flow_id' => $flowId, 'ad_type' => $adSubType, 'conf_type' => MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS, 'ads_id' => $oldAdsId));
							}
						}
					}
					$tmp = array();
					foreach ($info['priority_ads_id_'.$adSubType] as $index => $adsId) {
						$tmp['flow_id']   = $flowId;
						$tmp['app_key']   = $info['app_key'];
						$tmp['ad_type']   = $adSubType;
						$tmp['conf_type'] = MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS;
						$tmp['ads_id']    = $adsId;
						$tmp['position']  = $info['priority_position_'.$adSubType][$index];
						$tmp['limit_num'] = $info['priority_limit_num_'.$adSubType][$index];
						if (in_array($adsId, $oldAdsIds)) {
							MobgiApi_Service_FlowAdsRelModel::updateBy($tmp, array('flow_id' => $flowId, 'ad_type' => $adSubType, 'conf_type' => MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS, 'ads_id' => $adsId));
						} else {
							MobgiApi_Service_FlowAdsRelModel::add($tmp);
						}
					}
				} else {
					MobgiApi_Service_FlowAdsRelModel::deleteBy(array('flow_id' => $flowId, 'ad_type' => $adSubType, 'conf_type' => MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS));
				}
				//添加
			} else {
				if ($info['status_'.$adSubType] && $info['is_priority_'.$adSubType] && !$info['is_default_'.$adSubType]) {
					$data = array();
					foreach ($info['priority_ads_id_'.$adSubType] as $index => $adsId) {
						$tmp['flow_id']     = $flowId;
						$tmp['app_key']     = $info['app_key'];
						$tmp['ad_type']     = $adSubType;
						$tmp['conf_type']   = 2;
						$tmp['ads_id']      = $adsId;
						$tmp['position']    = $info['priority_position_'.$adSubType][$index];
						$tmp['limit_num']   = $info['priority_limit_num_'.$adSubType][$index];
						$tmp['create_time'] = date('Y-m-d H:i:s');
						$tmp['update_time'] = date('Y-m-d H:i:s');
						$data[]             = $tmp;
					}
					if ($data) {
						MobgiApi_Service_FlowAdsRelModel::mutiFieldInsert($data);
					}
				}
			}
		}

	}

	/**
	 * @param $info
	 * @param $flowId
	 */
	public function updateFlowGeneralAdsRel($info, $flowId) {
		foreach (Common_Service_Const::$mAdSubType as $adSubType => $val) {
			//编辑
			if ($info['flow_id']) {
				if (!$info['is_default_'.$adSubType]) {
					$oldData   = MobgiApi_Service_FlowAdsRelModel::getsBy(array('flow_id' => $flowId, 'ad_type' => $adSubType, 'conf_type' => 1));
					$oldAdsIds = array();
					if ($oldData) {
						$oldAdsIds = array_keys(Common::resetKey($oldData, 'ads_id'));
						foreach ($oldAdsIds as $oldAdsId) {
							if (!in_array($oldAdsId, $info['gerneral_ads_id_'.$adSubType])) {
								MobgiApi_Service_FlowAdsRelModel::deleteBy(array('flow_id' => $flowId, 'ad_type' => $adSubType, 'conf_type' => 1, 'ads_id' => $oldAdsId));
							}
						}
					}
					$tmp = array();
					if($info['gerneral_ads_id_'.$adSubType]){
						foreach ($info['gerneral_ads_id_'.$adSubType] as $index => $adsId) {
							$tmp['flow_id']   = $flowId;
							$tmp['app_key']   = $info['app_key'];
							$tmp['ad_type']   = $adSubType;
							$tmp['conf_type'] = 1;
							$tmp['ads_id']    = $adsId;
							$tmp['position']  = $info['gerneral_position_'.$adSubType][$index];
							$tmp['limit_num'] = $info['gerneral_limit_num_'.$adSubType][$index];
							$tmp['weight']    = $info['gerneral_weight_'.$adSubType][$index];
							if (in_array($adsId, $oldAdsIds)) {
								MobgiApi_Service_FlowAdsRelModel::updateBy($tmp, array('flow_id' => $flowId, 'ad_type' => $adSubType, 'conf_type' => 1, 'ads_id' => $adsId));
							} else {
								MobgiApi_Service_FlowAdsRelModel::add($tmp);
							}
						}
					}
				}else {
					MobgiApi_Service_FlowAdsRelModel::deleteBy(array('flow_id' => $flowId, 'ad_type' => $adSubType, 'conf_type' => 1));
				}
				//添加
			} else {
				if ($info['status_'.$adSubType] && !$info['is_default_'.$adSubType]) {
					$data = array();
					foreach ($info['gerneral_ads_id_'.$adSubType] as $index => $adsId) {
						$tmp['flow_id']     = $flowId;
						$tmp['app_key']     = $info['app_key'];
						$tmp['ad_type']     = $adSubType;
						$tmp['conf_type']   = 1;
						$tmp['ads_id']      = $adsId;
						$tmp['position']    = $info['gerneral_position_'.$adSubType][$index];
						$tmp['limit_num']   = $info['gerneral_limit_num_'.$adSubType][$index];
						$tmp['weight']      = $info['gerneral_weight_'.$adSubType][$index];
						$tmp['create_time'] = date('Y-m-d H:i:s');
						$tmp['update_time'] = date('Y-m-d H:i:s');
						$data[]             = $tmp;
					}
					if ($data) {
						MobgiApi_Service_FlowAdsRelModel::mutiFieldInsert($data);
					}
				}
			}

		}
	}

	/**
	 * @param $info
	 * @param $flowId
	 */
	public function updateFlowAdTypeRel($info, $flowId) {
        foreach ( Common_Service_Const::$mAdSubType as $adSubType => $val ) {
            $data ['flow_id'] = $info ['flow_id'] ? $info ['flow_id'] : $flowId;
            $data ['app_key'] = $info ['app_key'];
            $data ['ad_type'] = $adSubType;
            $data ['status'] = $info ['status_' . $adSubType];
            $data ['is_priority'] = (! $info ['is_default_' . $adSubType]) ? $info ['is_priority_' . $adSubType] : 0;
            $data ['is_use_dsp'] = (! $info ['is_default_' . $adSubType]) ? $info ['is_use_dsp_' . $adSubType] : 0;
            $data ['price'] = $info ['is_use_dsp_' . $adSubType] ? $info ['price_' . $adSubType] : 0;
            $data ['is_delay'] = (! $info ['is_default_' . $adSubType]) ? $info ['is_delay_' . $adSubType] : 0;
            $data ['time'] = $info ['is_delay_' . $adSubType] ? $info ['time_' . $adSubType] : 0;
            $data ['is_app_rel'] = (! $info ['is_default_' . $adSubType]) ? $info ['is_app_rel_' . $adSubType] : 0;
            $data ['is_block_policy'] = (! $info ['is_default_' . $adSubType]) ? $info ['is_block_policy_' . $adSubType] : 0;
            $data ['is_default'] = ($info['conf_type'] == MobgiApi_Service_FlowConfModel::CUSTOME_CONF_TYPE)?$info ['is_default_' . $adSubType] : 0;
            if ($info ['flow_id']) {
                $params ['flow_id'] = $info ['flow_id'];
                $params ['app_key'] = $info ['app_key'];
                $params ['ad_type'] = $adSubType;
                MobgiApi_Service_FlowAdTypeRelModel::updateBy ( $data, $params );
            } else {
                MobgiApi_Service_FlowAdTypeRelModel::add ( $data );
            }
        }
    }

	/**
	 * @param $info
	 * @return mixed
	 */
	public function updateFlowConf($info) {
		$data['app_key']           = $info['app_key'];
		$data['conf_type']         = $info['conf_type'];
		$data['conf_name']         = $info['conf_name'];
		$data['user_conf_type']    = $info['user_conf_type'];
		$data['game_conf_type']    = $info['game_conf_type'];
		$data['channel_conf_type'] = $info['channel_conf_type'];
		$data['area_conf_type']    = $info['area_conf_type'];
		$data['sys_conf_type']     = $info['sys_conf_type'];
		$data['operator_id']       = $this->userInfo['user_id'];
		$data['user_conf']         = '';
		if ($info['user_conf_type']) {
			$data['user_conf'] = json_encode($info['user_conf']);
		}
		$data['game_conf'] = '';
		if ($info['game_conf_type']) {
			$data['game_conf'] = json_encode($info['game_conf']);
		}
		$data['channel_conf'] = '';
		if ($info['channel_conf_type']) {
			$data['channel_conf'] = json_encode($info['channel_id']);
		}
		$data['area_conf'] = '';
		if ($info['area_conf_type']) {
			$data['area_conf'] = json_encode($info['area_id']);
		}
		$data['sys_conf'] = '';
		if ($info['sys_conf_type']) {
			$tmp                       = array();
			$tmp['sys_conf_condition'] = $info['sys_conf_condition'];
			$tmp['sys_conf_content']   = $info['sys_conf_content'];
			$data['sys_conf']          = json_encode($tmp);
		}
		$data['conf_num'] = 0;
		if ($info['conf_type'] == MobgiApi_Service_FlowConfModel::CUSTOME_CONF_TYPE) {
			$confNum = array();
			if ($info['user_conf_type']) {
				$confNum[] = 'user_conf_type';
			}
			if ($info['game_conf_type']) {
				$confNum[] = 'game_conf_type';
			}
			if ($info['channel_conf_type']) {
				$confNum[] = 'channel_conf_type';
			}
			if ($info['area_conf_type']) {
				$confNum[] = 'area_conf_type';
			}
			if ($info['sys_conf_type']) {
				$confNum[] = 'sys_conf_type';
			}
			$data['conf_num'] = count($confNum);
		}
		if ($info['flow_id']) {
			MobgiApi_Service_FlowConfModel::updateByID($data, $info['flow_id']);
			$flowId = $info['flow_id'];
		} else {
			$flowId = MobgiApi_Service_FlowConfModel::add($data);
		}
		return $flowId;
	}

	/**
	 * @param $info
	 * @return mixed
	 */
	public function checkPostParam($info) {
		if (!trim($info['app_key'])) {
			$this->output(-1, 'app_key为空');
		}
		if (!trim($info['conf_name'])) {
			$this->output(-1, '配置名称为空');
		}
		if ($info['user_conf_type'] == '') {
			$this->output(-1, '用户行为没有选择');
		}

		if ($info['channel_conf_type'] == '') {
			$this->output(-1, '渠道定向没有选择');
		}
		if ($info['area_conf_type'] == '') {
			$this->output(-1, '区域定向没有选择');
		}
		if ($info['game_conf_type'] == '') {
			$this->output(-1, '应用版本没有选择');
		}
		if ($info['conf_type'] == MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE) {
			foreach (Common_Service_Const::$mAdSubType as $adSubType => $adTypeName) {
				if ($info['is_default_'.$adSubType]) {
					$this->output(-1, $adTypeName.'中的不能选择使用全局配置按钮');
				}
			}
		}

		$this->checkDeafaultConfig($info);
		if ($info['conf_type'] == MobgiApi_Service_FlowConfModel::CUSTOME_CONF_TYPE) {
			$this->checkConditionIsExits($info);
		}
		$this->checkPriorityAdsConf($info);
		$this->checkGerneralAdsConf($info);
		$this->checkDspAdsConf($info);
		$this->checkOtherConf($info);
		return $info;
	}

	/**
	 * @param $info
	 */
	private function checkPriorityAdsConf($info) {
        foreach ( Common_Service_Const::$mAdSubType as $adSubType => $adTypeName ) {
            if ($info ['is_priority_' . $adSubType] && ! $info ['is_default_' . $adSubType]) {
                if (empty ( $info ['priority_ads_id_' . $adSubType] )) {
                    $this->output ( - 1, $adTypeName . '中的优先广告商为空' );
                }
                if (count ( $info ['priority_ads_id_' . $adSubType] ) != count ( array_unique ( $info ['priority_ads_id_' . $adSubType] ) )) {
                    $this->output ( - 1, $adTypeName . '中的优先广告商位置重复' );
                }
                foreach ( $info ['priority_ads_id_' . $adSubType] as $postion => $va ) {
                    if (! $va) {
                        $this->output ( - 1, $adTypeName . '中的优先广告商位置:"' . ($postion + 1) . '"为空' );
                    }
                    if ($va == 'Mobgi' && empty ( $info ['dsp_ads_id_' . $adSubType] )) {
                        $this->output ( - 1, $adTypeName . '中的优先广告商配置了mobgi，请配置dsp广告商' );
                    }
                }
                foreach ( $info ['priority_limit_num_' . $adSubType] as $postion => $va ) {
                    if (! is_numeric ( $va )) {
                        $this->output ( - 1, $adTypeName . '中的优先广告商次数限制必须为数字' );
                    }
                }
            }
        }
    }

	/**
	 * @param $info
	 */
	private function checkGerneralAdsConf($info) {
        foreach ( Common_Service_Const::$mAdSubType as $adSubType => $adTypeName ) {
            if ($info ['status_' . $adSubType] && ! $info ['is_default_' . $adSubType]) {
                if (empty ( $info ['gerneral_ads_id_' . $adSubType] )) {
                    $this->output ( - 1, $adTypeName . '中的一般广告商为空' );
                }
            }

            if (! empty ( $info ['gerneral_ads_id_' . $adSubType] ) ) {
                if (count ( $info ['gerneral_ads_id_' . $adSubType] ) != count ( array_unique ( $info ['gerneral_ads_id_' . $adSubType] ) )) {
                    $this->output ( - 1, $adTypeName . '中的一般广告商位置重复' );
                }
                foreach ( $info ['gerneral_ads_id_' . $adSubType] as $postion => $va ) {
                    if (! $va) {
                        $this->output ( - 1, $adTypeName . '中的一般广告商位置:"' . ($postion + 1) . '"为空' );
                    }
                    if ($va == 'Mobgi' && empty ( $info ['dsp_ads_id_' . $adSubType] )) {
                        $this->output ( - 1, $adTypeName . '中的一般广告商配置了mobgi，请配置dsp广告商' );
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
	/**
	 * @param $info
	 */
	private function checkDspAdsConf($info) {
		foreach (Common_Service_Const::$mAdSubType as $adSubType => $adTypeName) {
			if ($info['is_use_dsp_'.$adSubType] && !$info['is_default_'.$adSubType]) {
					if (empty($info['dsp_ads_id_'.$adSubType])) {
						$this->output(-1, $adTypeName.'中的DSP广告商为空');
					}
					if (count($info['dsp_ads_id_'.$adSubType]) != count(array_unique($info['dsp_ads_id_'.$adSubType]))) {
						$this->output(-1, $adTypeName.'中的DSP广告商位置重复');
					}
					foreach ($info['dsp_ads_id_'.$adSubType] as $postion => $va) {
						if (!$va) {
							$this->output(-1, $adTypeName.'中的DSP广告商位置:"'.($postion+1).'"为空');
						}
					}
				}
		}
	}

	/**
	 * @param $info
	 */
	private function checkOtherConf($info) {
		foreach (Common_Service_Const::$mAdSubType as $adSubType => $adTypeName) {
			if ( !$info['is_default_'.$adSubType]) {
				if ($info['is_delay_'.$adSubType] && (!is_numeric($info['time_'.$adSubType]) || $info['time_'.$adSubType] <= 0)) {
					$this->output(-1, $adTypeName.'中的尝鲜延迟加载必须大于零整数');
				}
				if ($info['is_app_rel_'.$adSubType]) {
					if (empty($info['app_key_rel_ads_id_'.$adSubType])) {
						$this->output(-1, $adTypeName.'中定制APPKey没有配置');
					}
					foreach ($info['third_party_app_key_'.$adSubType] as $index => $val) {
						if (!Common::validthirdPartyInput($val)) {
							$this->output(-1, $adTypeName.'中"'.$info['app_key_rel_ads_id_'.$adSubType][$index].'"第三方appkey含有非法字符');
						}
					}
					foreach ($info['third_party_secret_'.$adSubType] as $index => $val) {
						if (!Common::validthirdPartyInput($val)) {
							$this->output(-1, $adTypeName.'中"'.$info['app_key_rel_ads_id_'.$adSubType][$index].'"第三方密钥含有非法字符');
						}
					}
					foreach ($info['app_key_rel_pos_set_'.$adSubType] as $adsId => $val) {
						$blockInfo = json_decode($val, true);
						if (!$blockInfo) {
							$this->output(-1, $adTypeName.'定制APPKey中"'.$adsId.'"第三方广告位未配置'.$val);
						}
						foreach ($blockInfo as $block) {
							if (!Common::validthirdPartyInput($block['third_party_block_id'])) {
								$this->output(-1, $adTypeName.'定制APPKey中"'.$adsId.'"第三方广告位"'.$block['pos_name'].'"含有非法字符');
							}
						}
					}
				}
				if ($info['is_block_policy_'.$adSubType]) {
					if (empty($info['pos_policy_pos_key_'.$adSubType])) {
						$this->output(-1, $adTypeName.'中广告位策略没有配置');
					}
				}
			}
		}
	}

	/**
	 * @param $info
	 */
	private function checkConditionIsExits($info) {
		$conditionArr = array();
		if ($info['channel_conf_type']) {
			$conditionArr[] = 'channel_conf_type';
		}
		if ($info['area_conf_type']) {
			$conditionArr[] = 'area_conf_type';
		}
		if ($info['game_conf_type']) {
			$conditionArr[] = 'game_conf_type';
		}
		if ($info['user_conf_type']) {
			$conditionArr[] = 'user_conf_type';
		}
		if ($info['sys_conf_type']) {
			$conditionArr[]                         = 'sys_conf_type';
			$info['sys_conf']['sys_conf_condition'] = $info['sys_conf_condition'];
			$info['sys_conf']['sys_conf_content']   = $info['sys_conf_content'];
		}
		if ($info['conf_type'] == MobgiApi_Service_FlowConfModel::CUSTOME_CONF_TYPE) {
			if (!count($conditionArr)) {
				$this->output(-1, '配置条件不能为空');
			}
		}
		if ($info['user_conf_type']) {
			if (empty($info['user_conf'])) {
				$this->output(-1, '用户行为配置条件不能为空');
			}
		}
		if ($info['channel_conf_type']) {
			if (empty($info['channel_id'])) {
				$this->output(-1, '渠道定向配置条件不能为空');
			}
		}
		if ($info['area_conf_type']) {
			if (empty($info['area_id'])) {
				$this->output(-1, '区域定向配置条件不能为空');
			}
		}
		if ($info['game_conf_type']) {
			if (empty($info['game_conf'])) {
				$this->output(-1, '游戏版本定向配置条件不能为空');
			}
			if (count(array_unique($info['game_conf'])) < count($info['game_conf'])) {
				$this->output(-1, '游戏版本的值重复');
			}
		}
		if ($info['sys_conf_type']) {
			$pattern = '/^\d+\.\d$/';
			foreach ($info['sys_conf_content'] as $version) {
				if (!preg_match($pattern, $version, $match)) {
					$this->output(-1, '系统版本格式不正确列如：2.1');
				}
			}
			if (empty($info['sys_conf_content'])) {
				$this->output(-1, '系统版本定向配置条件不能为空');
			}
			if (in_array($info['sys_conf_condition'], array(1, 2)) && count($info['sys_conf_content']) > 1) {
				$this->output(-1, '高于或者低于系统版本的条件不能配置多个值');
			}
			if (count(array_unique($info['sys_conf_content'])) < count($info['sys_conf_content'])) {
				$this->output(-1, '系统版本的值重复');
			}
		}

		$params['conf_type'] = $info['conf_type'];
		$params['conf_num']  = count($conditionArr);
		$params['app_key']   = $info['app_key'];
		if ($info['flow_id']) {
			$params['id'] = array('<>', $info['flow_id']);
		}
		$flowConf          = MobgiApi_Service_FlowConfModel::getsBy($params);
		$conditionRelField = array(
			'channel_conf_type' => 'channel_conf',
			'area_conf_type'    => 'area_conf',
			'game_conf_type'    => 'game_conf',
			'user_conf_type'    => 'user_conf',
			'sys_conf_type'     => 'sys_conf',
		);
		$conditionRelPostData = array(
			'channel_conf_type' => 'channel_id',
			'area_conf_type'    => 'area_id',
			'game_conf_type'    => 'game_conf',
			'user_conf_type'    => 'user_conf',
			'sys_conf_type'     => 'sys_conf',
		);
		foreach ($flowConf as $key => $conf) {
			$flag = array();
			foreach ($conditionArr as $condition) {
				if ($conf[$condition]) {
					$jsonArr = json_decode($conf[$conditionRelField[$condition]], true);
					if ($this->checkConfIsExist($jsonArr, $info[$conditionRelPostData[$condition]], $condition)) {
						$flag[] = 1;
					}
				}
			}
			if (count($conditionArr) == count($flag)) {
				$this->output(-1, '配置条件重复，请检查,重复配置为：'.$conf['conf_name']);
			}
		}
		foreach ($flowConf as $key => $conf) {
			if ($conf['sys_conf_type'] && $info['sys_conf_type']) {
				$jsonArr = json_decode($conf[$conditionRelField['sys_conf_type']], true);
				if (($jsonArr['sys_conf_condition'] == 1) && ($info['sys_conf_condition'] == 1)) {
					$this->output(-1, '系统版本"高于"条件只能配置一条，与配置冲突：：'.$conf['conf_name']);
				}
				if (($jsonArr['sys_conf_condition'] == 2) && ($info['sys_conf_condition'] == 2)) {
					$this->output(-1, '系统版本"低于"条件只能配置一条，与配置冲突：：'.$conf['conf_name']);
				}

				if (($info['sys_conf_condition'] == 1) && (in_array($jsonArr['sys_conf_condition'], array(2, 3))) && version_compare($info['sys_conf_content'][0], $jsonArr['sys_conf_content'][0], '<')) {
					$this->output(-1, '系统版本与配置冲突：：'.$conf['conf_name']);
				}
				if (($info['sys_conf_condition'] == 2) && (in_array($jsonArr['sys_conf_condition'], array(1, 3))) && version_compare($info['sys_conf_content'][0], $jsonArr['sys_conf_content'][0], '>')) {
					$this->output(-1, '系统版本与配置冲突2：：'.$conf['conf_name']);
				}
				if (($info['sys_conf_condition'] == 3) && ($jsonArr['sys_conf_condition'] == 1) && version_compare($info['sys_conf_content'][0], $jsonArr['sys_conf_content'][0], '>')) {
					$this->output(-1, '系统版本与配置冲突3：：'.$conf['conf_name']);
				}
				if (($info['sys_conf_condition'] == 3) && ($jsonArr['sys_conf_condition'] == 2) && version_compare($info['sys_conf_content'][0], $jsonArr['sys_conf_content'][0], '<')) {
					$this->output(-1, '系统版本与配置冲突4：：'.$conf['conf_name']);
				}
			}
		}

	}

	/**
	 * @param $targetConf
	 * @param $sourceConf
	 * @param $type
	 */
	public function checkConfIsExist($targetConf, $sourceConf, $type) {
		if (!is_array($sourceConf) || !is_array($targetConf)) {
			return false;
		}
		if ($type == 'sys_conf_type') {
			foreach ($sourceConf['sys_conf_content'] as $confId) {
				if (in_array($confId, $targetConf['sys_conf_content']) && $sourceConf['sys_conf_condition'] == $targetConf['sys_conf_condition']) {
					return true;
				}
			}
			return false;
		} else {
			foreach ($sourceConf as $confId) {
				if (in_array($confId, $targetConf)) {
					return true;
				}
			}
			return false;
		}
	}

	/**
	 * @param $info
	 */
	public function checkDeafaultConfig($info) {
		$params['conf_type'] = MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE;
		$params['app_key']   = $info['app_key'];
		if ($info['flow_id']) {
			$params['id'] = array('<>', $info['flow_id']);
		}
		$restult = MobgiApi_Service_FlowConfModel::getBy($params);
		if (!$restult && $info['conf_type'] == MobgiApi_Service_FlowConfModel::CUSTOME_CONF_TYPE) {
			$this->output(-1, '请先配置全局配置');
		}
		if ($info['conf_type'] == MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE) {
			if ($restult) {
				$this->output(-1, '全局配置只能已经存在，全局配置有且仅有一个');
			}
		}
		if ($info['conf_type'] == MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE && ($info['user_conf_type'] != '0')) {
			$this->output(-1, '全局配置不能用户行为定向条件');
		}
		if ($info['conf_type'] == MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE && ($info['channel_conf_type'] != '0')) {
			$this->output(-1, '全局配置不能渠道定向条件');
		}
		if ($info['conf_type'] == MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE && ($info['area_conf_type'] != '0')) {
			$this->output(-1, '全局配置不能区域定向条件');
		}
		if ($info['conf_type'] == MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE && ($info['game_conf_type'] != '0')) {
			$this->output(-1, '全局配置不能应用版本定向条件');
		}
		if ($info['conf_type'] == MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE && ($info['sys_conf_type'] != '0')) {
			$this->output(-1, '全局配置不能系統版本版本定向条件');
		}
	}

	/**
	 * Enter description here .
	 *
	 *
	 *
	 * ..
	 */
	public function viewAction() {
		$this->getAppKeyList();
		$flowId = $this->getInput('flow_id');
		if ($flowId) {
			$data = $this->getEditFlowInfo($flowId);
		}
		$this->assign('info', $data);
		$this->assign('act', 'view');
		$this->assign('queryString', $this->getQueryString());
		$this->getView()->display('intergration/flow/add.phtml');
		exit();
	}

	/**
	 * @param $flowId
	 * @return mixed
	 */
	private function getEditFlowInfo($flowId) {
		$flowConf = MobgiApi_Service_FlowConfModel::getByID($flowId);
		if (!$flowConf) {
			return array();
		}
		$data = array(
			'flow_id'           => $flowId,
			'app_key'           => $flowConf['app_key'],
			'conf_type'         => $flowConf['conf_type'],
			'conf_name'         => $flowConf['conf_name'],
			'user_conf_type'    => $flowConf['user_conf_type'],
			'channel_conf_type' => $flowConf['channel_conf_type'],
			'area_conf_type'    => $flowConf['area_conf_type'],
			'game_conf_type'    => $flowConf['game_conf_type'],
			'sys_conf_type'     => $flowConf['sys_conf_type'],
		);
		$appInfo = MobgiApi_Service_AdAppModel::getBy(array(
				'app_key' => $flowConf['app_key'],
			));
		$data['icon']     = $appInfo['icon'];
		$data['app_key']  = $appInfo['app_key'];
		$data['app_name'] = $appInfo['app_name'];
		$data['platform'] = $appInfo['platform'];
		if (!stristr($appInfo['icon'], 'http')) {
			$data['icon'] = Common::getAttachPath().$appInfo['icon'];
		} else {
			$data['icon'] = $appInfo['icon'];
		}
		if ($appInfo['platform'] == Common_Service_Const::ANDRIOD_PLATFORM) {
			$data['platform_class'] = 'android';
		} else {
			$data['platform_class'] = 'ios';
		}
		if ($flowConf['user_conf_type']) {
			$data['user_conf'] = json_decode($flowConf['user_conf'], true);
		}
		if ($flowConf['channel_conf_type']) {
			$data['channel_conf'] = $this->parseChannelConf($flowConf['channel_conf']);
		}
		if ($flowConf['area_conf_type']) {
			$data['area_conf'] = $this->parseAreaConf($flowConf['area_conf']);
		}
		if ($flowConf['game_conf_type']) {
			$data['game_conf'] = json_decode($flowConf['game_conf'], true);
		}
		if ($flowConf['sys_conf_type']) {
			$tmp                        = json_decode($flowConf['sys_conf'], true);
			$data['sys_conf_condition'] = $tmp['sys_conf_condition'];
			$data['sys_conf_content']   = $tmp['sys_conf_content'];
		}
		$data['ad_Info'] = $this->getAdInfo($flowId, $appInfo);
		return $data;
	}

	public function getFlowListAction() {
		$appKey = $this->getInput('app_key');
		if (!$appKey) {
			$this->output(-1, '非法请求');
		}
		$params['app_key'] = $appKey;
		$flowList          = MobgiApi_Service_FlowConfModel::getsBy($params);
		$outData           = array();
		$appInfo           = MobgiApi_Service_AdAppModel::getBy(array(
				'app_key' => $appKey,
			));
		if (empty($appInfo)) {
			$this->output(-1, 'ok');
		}
		$outData['app_key']  = $appInfo['app_key'];
		$outData['app_name'] = $appInfo['app_name'];
		$outData['platform'] = $appInfo['platform'];
		if (!stristr($appInfo['icon'], 'http')) {
			$outData['icon'] = Common::getAttachPath().$appInfo['icon'];
		} else {
			$outData['icon'] = $appInfo['icon'];
		}
		if ($appInfo['platform'] == Common_Service_Const::ANDRIOD_PLATFORM) {
			$outData['platform_class'] = 'android';
		} else {
			$outData['platform_class'] = 'ios';
		}
		$list = array();
		if ($flowList) {
			foreach ($flowList as $key => $val) {
				$userInfo = Admin_Service_UserModel::getBy(array(
						'user_id' => $val['operator_id'],
					));
				$list[] = array(
					'id'             => $val['id'],
					'conf_type'      => $val['conf_type'],
					'conf_type_name' => $val['conf_type'] == 2?'定向配置':'全局配置',
					'conf_name'      => $val['conf_name'],
					'operator'       => $userInfo['user_name'],
					'update_time'    => $val['update_time'],
				);
			}
		}
		$outData['list'] = $list;
		$this->output(0, 'ok', $outData);
	}

	public function deleteAction() {
		$flowId = $this->getInput('flow_id');
		if (!$flowId) {
			$this->output(-1, '非法请求');
		}
		$flowConf = MobgiApi_Service_FlowConfModel::getByID($flowId);
		if (!$flowConf) {
			$this->output(-1, '非法请求');
		}
		$result = MobgiApi_Service_FlowConfModel::deleteById($flowId);
		if (!$result) {
			$this->output(-1, '删除失败');
		}
		MobgiApi_Service_FlowAdTypeRelModel::deleteBy(array(
				'flow_id' => $flowId,
			));
		MobgiApi_Service_FlowAdsRelModel::deleteBy(array(
				'flow_id' => $flowId,
			));
		MobgiApi_Service_FlowAppRelModel::deleteBy(array(
				'flow_id' => $flowId,
			));
		MobgiApi_Service_FlowPosRelModel::deleteBy(array(
				'flow_id' => $flowId,
			));
		MobgiApi_Service_FlowPosPolicyRelModel::deleteBy(array(
				'flow_id' => $flowId,
			));
		$this->output(0, '删除成功');
	}

	public function deleteFlowPosRelAction() {
		$id = $this->getInput('flow_pos_rel_id');
		if (!$id) {
			$this->output(0, '删除成功');
		}
		$info = MobgiApi_Service_FlowPosRelModel::getByID($id);
		MobgiApi_Service_FlowPosRelModel::deleteBy(array(
				'id' => $id,
			));
		if ($info['position'] > 1) {
			$prams['flow_id']  = $info['flow_id'];
			$prams['app_key']  = $info['app_key'];
			$prams['ad_type']  = $info['ad_type'];
			$prams['ads_id']   = $info['ads_id'];
			$prams['position'] = array('>', $info['position']);
			$result            = MobgiApi_Service_FlowPosRelModel::getsBy($prams);
			if ($result) {
				foreach ($result as $val) {
					$data['position'] = $val['position']-1;
					MobgiApi_Service_FlowPosRelModel::updateByID($data, $val['id']);
				}
			}
		}
		$this->output(0, '删除成功');
	}

	/**
	 * @param $flowId
	 * @param $appInfo
	 * @return mixed
	 */
	private function getAdInfo($flowId, $appInfo) {
		$adTypeRelList = MobgiApi_Service_FlowAdTypeRelModel::getsBy(array(
				'flow_id' => $flowId,
			), array(
				'ad_type' => 'ASC',
			));
		if ($adTypeRelList) {
			foreach ($adTypeRelList as $adTypeRelInfo) {
				list($dspAdsList, $intergrationAdsList)                   = $this->initAdsIdsList($adTypeRelInfo['app_key'], $adTypeRelInfo['ad_type']);
				$adInfo[$adTypeRelInfo['ad_type']]['dspAdsList']          = $dspAdsList;
				$adInfo[$adTypeRelInfo['ad_type']]['intergrationAdsList'] = $intergrationAdsList;
				$adInfo[$adTypeRelInfo['ad_type']]['status']              = $adTypeRelInfo['status'];
				$adInfo[$adTypeRelInfo['ad_type']]['name']                = Common_Service_Const::$mAdSubType[$adTypeRelInfo['ad_type']];
				$adInfo[$adTypeRelInfo['ad_type']]['ad_type']             = $adTypeRelInfo['ad_type'];
				$adInfo = $this->fillAdtypeDataToAdInfo($adInfo, $adTypeRelInfo);
				$adInfo = $this->fillPriorityDataToAdInfo($flowId, $adInfo, $adTypeRelInfo);
				$adInfo = $this->fillGerneralConfToAdInfo($flowId, $adInfo, $adTypeRelInfo);
				$adInfo = $this->fillDspConfToAdInfo($flowId, $adInfo, $adTypeRelInfo);
				$adInfo = $this->fillAppRelDataToAdInfo($flowId, $appInfo, $adInfo, $adTypeRelInfo);
				$adInfo = $this->fillPosPolicyToAdInfo($flowId, $appInfo, $adInfo, $adTypeRelInfo);
			}
		}
		return $adInfo;
	}

	/**
	 * @param $flowId
	 * @param $appInfo
	 * @param $adInfo
	 * @param $adTypeRelInfo
	 * @return mixed
	 */
	private function fillPosPolicyToAdInfo($flowId, $appInfo, $adInfo, $adTypeRelInfo) {
		if ($adTypeRelInfo['is_block_policy']) {
			$flowPosPoclicyRel = MobgiApi_Service_FlowPosPolicyRelModel::getsBy(array(
					'ad_type' => $adTypeRelInfo['ad_type'],
					'flow_id' => $flowId,
				));
			if (empty($flowPosPoclicyRel)) {
				$adInfo[$adTypeRelInfo['ad_type']]['pos_policy_rel_conf'] = array();
				return $adInfo;
			}
			$blockNameList = $this->getPosListByAdSubType($appInfo['app_id'], Common_Service_Const::$mAdPosType[$adTypeRelInfo['ad_type']]);
			$blockNameList = Common::resetKey($blockNameList, 'pos_key');
			$posPolicyConf = array();
			foreach ($flowPosPoclicyRel as $val) {
				$posPolicyConf[] = array(
					'pos_key'   => $val['pos_key'],
					'pos_name'  => $blockNameList[$val['pos_key']]['pos_name'],
					'rate'      => $val['rate'],
					'status'    => $val['status'],
					'limit_num' => $val['limit_num'],
				);
			}
			$adInfo[$adTypeRelInfo['ad_type']]['pos_policy_rel_conf'] = $posPolicyConf;
		}
		return $adInfo;
	}

	/**
	 * @return mixed
	 */
	private function getAdsNameList() {
		$params['ad_type'] = array(
			'IN',
			array(
				1,
				3,
			),
		);
		$adsList     = MobgiApi_Service_AdsListModel::getsBy($params);
		$adsNameList = Common::resetKey($adsList, 'ads_id');
		return $adsNameList;
	}

	/**
	 * @param $flowId
	 * @param $appInfo
	 * @param $adInfo
	 * @param $adTypeRelInfo
	 * @return mixed
	 */
	private function fillAppRelDataToAdInfo($flowId, $appInfo, $adInfo, $adTypeRelInfo) {
		if ($adTypeRelInfo['is_app_rel']) {
			$adsNameList      = $this->getAdsNameList();
			$oldBlockNameList = $this->getPosListByAdSubType($appInfo['app_id'], Common_Service_Const::$mAdPosType[$adTypeRelInfo['ad_type']]);
			$oldBlockNameList = Common::resetKey($oldBlockNameList, 'pos_key');
			$flowPosRel       = MobgiApi_Service_FlowPosRelModel::getsBy(array(
					'ad_type' => $adTypeRelInfo['ad_type'],
					'flow_id' => $flowId,
				));
			$posConf = array();
			foreach ($flowPosRel as $appRelInfo) {
				$posConf[$appRelInfo['ads_id']][$appRelInfo['pos_key']]['third_party_block_id'] = $appRelInfo['third_party_block_id'];
				$posConf[$appRelInfo['ads_id']][$appRelInfo['pos_key']]['pos_key']              = $appRelInfo['pos_key'];
				$posConf[$appRelInfo['ads_id']][$appRelInfo['pos_key']]['pos_name']             = $oldBlockNameList[$appRelInfo['pos_key']]['pos_name'];
			}

			$flowAppRel = MobgiApi_Service_FlowAppRelModel::getsBy(array(
					'ad_type'        => $adTypeRelInfo['ad_type'],
					'flow_id'        => $flowId,
				), array('ads_id' => 'ASC'));
			if (empty($flowAppRel)) {
				$adInfo[$adTypeRelInfo['ad_type']]['app_rel_conf'] = array();
				return $adInfo;
			}
			$appConf = array();
			foreach ($flowAppRel as $appRelInfo) {
				$tmp = array();
				foreach ($posConf[$appRelInfo['ads_id']] as $posRelInfo) {
					$tmp[] = $posRelInfo;
				}
				$appConf[] = array(
					'ads_id'               => $appRelInfo['ads_id'],
					'name'                 => $adsNameList[$appRelInfo['ads_id']]['name'],
					'third_party_secret'   => $appRelInfo['third_party_secret'],
					'third_party_app_key'  => $appRelInfo['third_party_app_key'],
					'third_party_block_id' => empty($tmp)?'':json_encode($tmp),
				);
			}
			$adInfo[$adTypeRelInfo['ad_type']]['app_rel_conf'] = $appConf;
		}
		return $adInfo;
	}

	/**
	 * @param $flowId
	 * @param $adInfo
	 * @param $adTypeRelInfo
	 * @return mixed
	 */
	private function fillDspConfToAdInfo($flowId, $adInfo, $adTypeRelInfo) {
		$flowAdsRel = MobgiApi_Service_FlowAdsRelModel::getsBy(array(
				'ad_type'   => $adTypeRelInfo['ad_type'],
				'flow_id'   => $flowId,
				'conf_type' => 3,
			));
		if (!$flowAdsRel) {
			$adInfo[$adTypeRelInfo['ad_type']]['dsp_list'] = array();
			return $adInfo;
		}
		$adsNameList = $this->getAdsNameList();
		$dspConf     = array();
		foreach ($flowAdsRel as $flowAdsInfo) {
			$dspConf[] = array(
				'ads_id'   => $flowAdsInfo['ads_id'],
				'name'     => $adsNameList[$flowAdsInfo['ads_id']]['name'],
				'position' => $flowAdsInfo['position'],
			);
		}
		$adInfo[$adTypeRelInfo['ad_type']]['dsp_list'] = $this->multiArraySort($dspConf, 'position');
		return $adInfo;
	}

	/**
	 * @param $flowId
	 * @param $adInfo
	 * @param $adTypeRelInfo
	 * @return mixed
	 */
	private function fillGerneralConfToAdInfo($flowId, $adInfo, $adTypeRelInfo) {
		$flowAdsRel = MobgiApi_Service_FlowAdsRelModel::getsBy(array(
				'ad_type'   => $adTypeRelInfo['ad_type'],
				'flow_id'   => $flowId,
				'conf_type' => 1,
			));
		if (!$flowAdsRel) {
			$adInfo[$adTypeRelInfo['ad_type']]['general_list'] = array();
			return $adInfo;
		}
		$adsNameList = $this->getAdsNameList();
		$generalConf = array();
		foreach ($flowAdsRel as $flowAdsInfo) {
			$generalConf[] = array(
				'ads_id'    => $flowAdsInfo['ads_id'],
				'name'      => $adsNameList[$flowAdsInfo['ads_id']]['name'],
				'limit_num' => $flowAdsInfo['limit_num'],
				'position'  => $flowAdsInfo['position'],
				'weight'    => $flowAdsInfo['weight'],
			);
		}
		$adInfo[$adTypeRelInfo['ad_type']]['general_list'] = $this->multiArraySort($generalConf, 'position');
		return $adInfo;
	}

	/**
	 * @param $multiArr
	 * @param $sortKey
	 * @param $sort
	 * @return mixed
	 */
	private function multiArraySort($multiArr, $sortKey, $sort = SORT_ASC) {
		if (!is_array($multiArr)) {
			return array();
		}
		$sortArr = array();
		foreach ($multiArr as $rowArr) {
			if (is_array($rowArr)) {
				$sortArr[] = $rowArr[$sortKey];
			}
		}
		array_multisort($sortArr, $sort, $multiArr);
		return $multiArr;
	}

	/**
	 * @param $flowId
	 * @param $adInfo
	 * @param $adTypeRelInfo
	 * @return mixed
	 */
	private function fillPriorityDataToAdInfo($flowId, $adInfo, $adTypeRelInfo) {
		if ($adTypeRelInfo['is_priority']) {
			$flowAdsRel = MobgiApi_Service_FlowAdsRelModel::getsBy(array(
					'ad_type'   => $adTypeRelInfo['ad_type'],
					'flow_id'   => $flowId,
					'conf_type' => 2,
				));
			if (!$flowAdsRel) {
				$adInfo[$adTypeRelInfo['ad_type']]['priority_list'] = array();
				return $adInfo;
			}
			$adsNameList  = $this->getAdsNameList();
			$priorityConf = array();
			foreach ($flowAdsRel as $flowAdsInfo) {
				$priorityConf[] = array(
					'ads_id'    => $flowAdsInfo['ads_id'],
					'name'      => $adsNameList[$flowAdsInfo['ads_id']]['name'],
					'limit_num' => $flowAdsInfo['limit_num'],
					'position'  => $flowAdsInfo['position'],
				);
			}
			$adInfo[$adTypeRelInfo['ad_type']]['priority_list'] = $this->multiArraySort($priorityConf, 'position');
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
	        $adInfo[$adTypeRelInfo['ad_type']]['is_priority']     = $adTypeRelInfo['is_priority'];
	        $adInfo[$adTypeRelInfo['ad_type']]['is_delay']        = $adTypeRelInfo['is_delay'];
	        $adInfo[$adTypeRelInfo['ad_type']]['time']            = $adTypeRelInfo['time'];
	        $adInfo[$adTypeRelInfo['ad_type']]['is_use_dsp']      = $adTypeRelInfo['is_use_dsp'];
	        $adInfo[$adTypeRelInfo['ad_type']]['price']           = $adTypeRelInfo['price'];
	        $adInfo[$adTypeRelInfo['ad_type']]['is_app_rel']      = $adTypeRelInfo['is_app_rel'];
	        $adInfo[$adTypeRelInfo['ad_type']]['is_block_policy'] = $adTypeRelInfo['is_block_policy'];
	        $adInfo[$adTypeRelInfo['ad_type']]['is_default']      = $adTypeRelInfo['is_default'];
	    }
		return $adInfo;
	}

	/**
	 * @param $channel_conf
	 * @return mixed
	 */
	public function parseChannelConf($channel_conf) {
		$channelIds  = json_decode($channel_conf, true);
		$channelList = MobgiApi_Service_ChannelModel::getsBy(array(
				'channel_id' => array(
					'IN',
					$channelIds,
				),
			));
		$tmp = array();
		if ($channelList) {
			foreach ($channelList as $val) {
				$tmp[] = array(
					'id'        => $val['channel_id'],
					'name'      => $val['channel_name'],
					'level'     => 2,
					'parent_id' => $val['group_id'],
				);
			}
		}
		return $tmp;
	}

	/**
	 * @param $area_conf
	 * @return mixed
	 */
	public function parseAreaConf($area_conf) {
		$areaConf     = json_decode($area_conf, true);
		$provinceList = Common::getConfig('areaConfig', 'provinceList');
		$countryArea  = Common::getConfig('areaConfig', 'countryArea');
		$countryList  = array();
		foreach ($countryArea as $parentId => $val) {
			foreach ($val as $ke              => $va) {
				$countryList[$ke] = array(
					'id'        => $ke,
					'name'      => $va,
					'parent_id' => $parentId,
					'level'     => 2,
				);
			}
		}
		$tmp = array();
		foreach ($areaConf as $key => $areId) {
			if (in_array($areId, array_keys($provinceList))) {
				$tmp[] = array(
					'id'        => $areId,
					'name'      => $provinceList[$areId],
					'level'     => 3,
					'parent_id' => 'CN',
				);
			} elseif (in_array($areId, array_keys($countryList))) {
				$tmp[] = array(
					'id'        => $areId,
					'name'      => $countryList[$areId]['name'],
					'level'     => 2,
					'parent_id' => $countryList[$areId]['parent_id'],
				);
			}

		}
		return $tmp;
	}

	public function getChannelListAction() {
		$channelGroupList = MobgiApi_Service_ChannelModel::getsBy(array(
				'group_id' => 0,
			));
		$idsArr         = array_keys(Common::resetKey($channelGroupList, 'channel_id'));
		$subChannelList = MobgiApi_Service_ChannelModel::getsBy(array(
				'group_id' => array(
					'IN',
					$idsArr,
				),
			));
		$outData = array();
		foreach ($channelGroupList as $channel) {
			$tmp = array();
			foreach ($subChannelList as $subChanenl) {
				if ($channel['channel_id'] == $subChanenl['group_id']) {
					$tmp[] = array(
						'id'        => $subChanenl['channel_id'],
						'name'      => $subChanenl['channel_name'],
						'parent_id' => $channel['channel_id'],
						'level'     => 2,
						'item'      => array(),
					);
				}
			}
			$outData[] = array(
				'id'        => strval($channel['channel_id']),
				'name'      => $channel['channel_name'],
				'parent_id' => '0',
				'level'     => 1,
				'item'      => $tmp,
			);
		}
		$callback = $this->getInput('callback');
		echo $callback.'('.json_encode($outData).')';
		die();
	}

	public function getAreaListAction() {
		$stateArea    = Common::getConfig('areaConfig', 'stateArea');
		$countryArea  = Common::getConfig('areaConfig', 'countryArea');
		$provinceList = Common::getConfig('areaConfig', 'provinceList');
		$outData      = array();
		foreach ($countryArea as $key => $state) {
			$tmp = array();
			foreach ($state as $ke => $country) {
				$tmp2 = array();
				if ($ke == 'CN') {
					foreach ($provinceList as $k => $province) {
						$tmp2[] = array(
							'id'        => strval($k),
							'name'      => $province,
							'parent_id' => strval($ke),
							'level'     => 3,
							'item'      => array(),
						);
					}
				}
				$tmp[] = array(
					'id'        => $ke,
					'name'      => $country,
					'parent_id' => strval($key),
					'level'     => 2,
					'item'      => $tmp2,
				);
			}
			$outData[] = array(
				'id'        => strval($key),
				'name'      => $stateArea[$key],
				'parent_id' => '0',
				'level'     => 1,
				'item'      => $tmp,
			);
		}
		$callback = $this->getInput('callback');
		echo $callback.'('.json_encode($outData).')';
		die();
	}

	public function getAdsListAction() {
		$info = $this->getInput(array(
				'app_key',
				'ad_type',
			));
		if (!$info['app_key'] || !$info['ad_type']) {
			$this->output(-1, '非法操作');
		}
		list($dspAdsList, $intergrationAdsList) = $this->initAdsIdsList($info['app_key'], $info['ad_type']);
		$data['dspAdsList']                     = $dspAdsList;
		$data['intergrationAdsList']            = $intergrationAdsList;
		$appInfo                                = MobgiApi_Service_AdAppModel::getBy(array(
				'app_key' => $info['app_key'],
			));
		$data['blockList'] = $this->getPosListByAdSubType($appInfo['app_id'], Common_Service_Const::$mAdPosType[$info['ad_type']]);
		$this->output(0, '操作成功', $data);
	}

	/**
	 * @param $appId
	 * @param $adSubType
	 * @return mixed
	 */
	private function getPosListByAdSubType($appId, $adSubType) {
		$params['pos_key_type'] = $adSubType;
		$params['app_id']       = $appId;
		$params['del']          = MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG;
		$result                 = MobgiApi_Service_AdDeverPosModel::getsBy($params);
		if (!$result) {
			return array();
		}
		$outData = array();

		foreach ($result as $val) {
			$outData[$val['dever_pos_key']] = array(
				'pos_key'              => $val['dever_pos_key'],
				'pos_name'             => $val['dever_pos_name'],
				'state'                => $val['state'],
				'limit_num'            => $val['limit_num'],
				'rate'                 => $val['rate'],
				'third_party_block_id' => '',
			);
		}
		return $outData;
	}

	/**
	 * @param $appKey
	 * @param $adSubType
	 */
	private function initAdsIdsList($appKey, $adSubType) {
		$dspAdsList          = array();
		$intergrationAdsList = array();
		// 广告商参数
		$params['ad_sub_type'] = $adSubType;
		$params['app_key']     = $appKey;
		$adsAppRelRestut       = MobgiApi_Service_AdsAppRelModel::getsBy($params, array('ads_id' => 'ASC'));
		if (!$adsAppRelRestut) {
			return array(
				$dspAdsList,
				$intergrationAdsList,
			);
		}
		$adsIds = array_keys(Common::resetKey($adsAppRelRestut, 'ads_id'));
		unset($params);
		$params['ad_type'] = array(
			'IN',
			array(
				1,
				3,
			),
		);
		$params['ads_id'] = array(
			'IN',
			$adsIds,
		);
		$adsList = MobgiApi_Service_AdsListModel::getsBy($params, array('ads_id' => 'ASC'));
		if (!$adsList) {
			return array(
				$dspAdsList,
				$intergrationAdsList,
			);
		}
		foreach ($adsList as $val) {
			if ($val['ad_type'] == 3) {
				$dspAdsList[$val['ads_id']] = $val['name'];
			} else {
				$intergrationAdsList[$val['ads_id']] = $val['name'];
			}
		}
		return array(
			$dspAdsList,
			$intergrationAdsList,
		);
	}
}
