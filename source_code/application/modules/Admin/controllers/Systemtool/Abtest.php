<?php
if (!defined('BASE_PATH'))
	exit ('Access Denied!');

/**
 *
 * Enter description here ...
 *
 * @author rock.luo
 *
 */
class Systemtool_AbtestController extends Admin_BaseController
{
	public $actions = array(
		'listUrl' => '/Admin/Systemtool_Abtest/index',
		'addUrl' => '/Admin/Systemtool_Abtest/add',
		'addPostUrl' => '/Admin/Systemtool_Abtest/addPost',
		'deleteUrl' => '/Admin/Systemtool_Abtest/delete',
		'delConfRelUrl' => '/Admin/Systemtool_Abtest/delConfRel',
		'viewUrl' => '/Admin/Systemtool_Abtest/view',
		'getAdsListUrl' => '/Admin/Systemtool_Abtest/getAdsList',
		'getAreaListUrl' => '/Admin/Intergration_Flow/getAreaList',
		'getChannelListUrl' => '/Admin/Intergration_Flow/getChannelList',
		'getFlowListUrl' => '/Admin/Systemtool_Abtest/getFlowList',
		'updateStatusUrl' => '/Admin/Systemtool_Abtest/updateStatus',
	);
	public $perpage = 10;

	/**
	 * Enter description here .
	 *
	 *
	 *
	 * ..
	 */
	public function indexAction()
	{
		$search = $params = array();
		$page = intval($this->getInput('page'));
		if ($page < 1)
			$page = 1;
		$search = $this->getInput(array(
			'conf_name',
			'conf_type',
			'app_key',
			'device_id'
		));
		if (isset ($search ['device_id']) && $search ['device_id']) {
			$params ['content'] = array('LIKE', $search ['device_id']);
		}
		if (isset ($search ['conf_name']) && $search ['conf_name']) {
			$params ['conf_name'] = array('LIKE', $search ['conf_name']);
		}
		if (isset ($search ['conf_type']) && $search ['conf_type']) {
			$params ['conf_type'] = $search ['conf_type'];
		}
		if (isset ($search ['app_key']) && $search ['app_key']) {
			$params ['app_key'] = $search ['app_key'];
		}
		list ($total, $list) = MobgiApi_Service_AbConfModel::getList($page, $this->perpage, $params, array(
			'update_time' => 'DESC'
		));

		$conRelResult = array();
		if ($list) {
			$confIds = array_keys(Common::resetKey($list, 'conf_id'));
			$conRelResult = MobgiApi_Service_AbConfRelModel::getsBy(array('conf_id' => array('IN', $confIds)));
			$flowConfIds = array_keys(Common::resetKey($conRelResult, 'flow_id'));
			$flowConf = MobgiApi_Service_AbFlowConfModel::getsBy(array('flow_id' => array('IN', $flowConfIds)));
			$flowConf = Common::resetKey($flowConf, 'flow_id');
			$conRel = array();
			foreach ($conRelResult as $val) {
				$conRel[$val['conf_id']][$val['flow_id']] = $val;
			}
			foreach ($list as $key => $val) {
				$flowConfName = '';
				foreach ($conRel[$val['conf_id']] as $va) {
					$flowConfName = $flowConfName . $flowConf[$va['flow_id']]['conf_name'] . '&nbsp' . $va['weight'] . '<br>';
				}
				$list[$key]['flow_conf'] = $flowConfName;
				if ($val['conf_type'] == MobgiApi_Service_AbConfModel::WHILELIST_CONF_TYPE) {
					$content = json_decode($val['content'], true);
					$deviceIds = '';
					foreach ($content as $va) {
						$deviceIds = $deviceIds . $va . '<br/>';
					}
					$list[$key]['content'] = $deviceIds;
				}
			}
		}

		$url = $this->actions ['listUrl'] . '/?' . http_build_query($search) . '&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
		$this->assign('list', $list);
		$this->assign('search', $search);
		$this->assign('total', $total);
		$this->assign('configType', array(MobgiApi_Service_AbConfModel::WHILELIST_CONF_TYPE => '白名单', MobgiApi_Service_AbConfModel::ABTEST_CONF_TYPE => 'AbTest'));
		$this->assign('appList', $this->getAppList());
	}


	private function getAppList()
	{
		$params['is_check'] = MobgiApi_Service_AdAppModel::ISCHECK_PASS;
		$appList = MobgiApi_Service_AdAppModel::getsBy($params);
		$data = array();
		foreach ($appList as $key => $val) {
			$data[$val['app_key']] = $val;
			$data[$val['app_key']]['platform_cn'] = $val['platform'] == 1 ? 'Andriod' : 'Ios';
		}
		return $data;
	}


	public function addAction()
	{
		$confId = $this->getInput('conf_id');
		if ($confId) {
			$data = $this->getEditFlowInfo($confId);
		} else {
			$data['start_time'] = date('Y-m-d');
			$data['end_time'] = date('Y-m-d');
		}
		$this->assign('configType', array(MobgiApi_Service_AbConfModel::WHILELIST_CONF_TYPE => '白名单', MobgiApi_Service_AbConfModel::ABTEST_CONF_TYPE => 'AbTest'));
		$this->assign('appList', $this->getAppList());
		$this->assign('info', $data);
	}

	public function addPostAction()
	{
		$info = $this->getRequest()->getPost();
		$info = $this->checkPostParam($info);
		$confId = $this->updateAbConf($info);
		$this->updateAbConfRel($info, $confId);
		if (!$confId) {
			$this->output(-1, '操作失败');
		}
		$this->output(０, '操作成功');
	}

	public function updateAbConfRel($info, $confId)
	{
		foreach ($info['conf_rel_id'] as $key => $confRelId) {
			$data['conf_id'] = $confId;
			$data['flow_id'] = $info['flow_id'][$key];
			$data['weight'] = $info['weight'][$key];
			$data['operator_id'] = $this->userInfo['user_id'];
			if ($confRelId) {
				MobgiApi_Service_AbConfRelModel::updateByID($data, $confRelId);
			} else {
				MobgiApi_Service_AbConfRelModel::add($data);
			}
		}
	}

	public function updateAbConf($info)
	{
		$data['conf_type'] = $info['conf_type'];
		$data['conf_name'] = $info['conf_name'];
		$data['rate'] = $info['rate'];
		$data['status'] = $info['status'];
		$data['start_time'] = $info['start_time'];
		$data['end_time'] = $info['end_time'];
		$data['operator_id'] = $this->userInfo['user_id'];
		$content = array();
		if ($info['conf_type'] == MobgiApi_Service_AbConfModel::ABTEST_CONF_TYPE) {
			$data['app_key'] = $info['app_key'];
			$content['game_conf_type'] = $info['game_conf_type'];
			$content['channel_conf_type'] = $info['channel_conf_type'];
			$content['area_conf_type'] = $info['area_conf_type'];
			$content['sys_conf_type'] = $info['sys_conf_type'];
			if ($info['game_conf_type']) {
				$content['game_conf'] = $info['game_conf'];
			}
			if ($info ['channel_conf_type']) {
				$content ['channel_conf'] = $info ['channel_id'];
			}
			if ($info['area_conf_type']) {
				$content['area_conf'] = $info['area_id'];
			}
			if ($info['sys_conf_type']) {
				$content['sys_conf']['sys_conf_condition'] = $info['sys_conf_condition'];
				$content['sys_conf']['sys_conf_content'] = $info['sys_conf_content'];
			}
			$data['content'] = json_encode($content);
		} else {
			$data['dev_mode'] = $info['dev_mode'];
			$data['is_report'] = $info['is_report'];
			$tmpDevice =array();
			foreach ($info['device'] as $val){
				$tmpDevice[]= trim($val);
			}
			$data['content'] = json_encode($tmpDevice);
		}

		if ($info['conf_id']) {
			MobgiApi_Service_AbConfModel::updateByID($data, $info['conf_id']);
			$confId = $info['conf_id'];
		} else {
			$confId = MobgiApi_Service_AbConfModel::add($data);
		}
		return $confId;
	}


	public function checkPostParam($info)
	{
		if (!trim($info ['conf_name'])) {
			$this->output(-1, '配置名称为空');
		}
		if (!$info ['conf_type']) {
			$this->output(-1, '测试类型为空');
		}
		$this->checkCondition($info);
		return $info;
	}


	private function checkCondition($info)
	{

		if (strtotime($info['start_time']) > strtotime($info['end_time'])) {
			$this->output(-1, '时间不合法' . $info['end_time']);
		}
		if ($info ['conf_type'] == MobgiApi_Service_AbConfModel::ABTEST_CONF_TYPE) {
			if (empty($info['app_key'])) {
				$this->output(-1, '应用不能为空');
			}
			if ($info ['user_conf_type']) {
				if (empty($info['user_conf'])) {
					$this->output(-1, '用户行为配置条件不能为空');
				}
			}
			if ($info ['channel_conf_type']) {
				if (empty($info['channel_id'])) {
					$this->output(-1, '渠道定向配置条件不能为空');
				}
			}
			if ($info ['area_conf_type']) {
				if (empty($info['area_id'])) {
					$this->output(-1, '区域定向配置条件不能为空');
				}
			}
			if ($info ['game_conf_type']) {
				if (empty($info['game_conf'])) {
					$this->output(-1, '游戏版本定向配置条件不能为空');
				}
				if (count(array_unique($info['game_conf'])) < count($info['game_conf'])) {
					$this->output(-1, '游戏版本的值重复');
				}
			}
			if ($info ['sys_conf_type']) {
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
			if (!is_numeric($info ['rate']) || $info ['rate'] < 1 || $info ['rate'] > 100) {
				$this->output(-1, '抽样比例不合法');
			}
		} else {
			if (empty ($info ['device'])) {
				$this->output(-1, '设备号为空');
			}
			$parms['conf_type'] = MobgiApi_Service_AbConfModel::WHILELIST_CONF_TYPE;
			if ($info['conf_id']) {
				$parms['conf_id'] = array('<>', $info['conf_id']);
			}
			$relust = MobgiApi_Service_AbConfModel::getsBy($parms);
			$deviceArr = array();
			if ($relust) {
				foreach ($relust as $va) {
					$content = json_decode($va['content']);
					foreach ($content as $device) {
						$deviceArr[$device] = $va['conf_name'];
					}
				}
			}

			foreach ($info ['device'] as $postion => $va) {
				if (empty($va)) {
					$this->output(-1, '位置:"' . ($postion + 1) . '"设备号为空');
				}
				if (array_key_exists($va, $deviceArr)) {
					$this->output(-1, '设备号已经在名称为：“' . $deviceArr[$va] . '”配置过');
				}
			}
		}
		if (empty ($info ['flow_id'])) {
			$this->output(-1, '流量配置为空');
		}
		if (count($info ['flow_id']) != count(array_unique($info ['flow_id']))) {
			$this->output(-1, '流量配置位置重复');
		}
		foreach ($info ['flow_id'] as $postion => $va) {
			if (!$va) {
				$this->output(-1, '流量配置:"' . ($postion + 1) . '"为空');
			}
		}
		foreach ($info ['weight'] as $postion => $va) {
			if (!is_numeric($va)) {
				$this->output(-1, '位置:"' . ($postion + 1) . '"中的一般广告商权重必须为数字');
			}
			if ($va > 1 || $va <= 0) {
				$this->output(-1, '位置:"' . ($postion + 1) . '"中的一般广告商权重范围０－１之间数字');
			}
		}
		if (strval(array_sum($info ['weight'])) != '1') {
			$this->output(-1, '中的一般广告商的权重不为１,计算结果为：' . array_sum($info ['weight']));
		}
	}


	public function viewAction()
	{
		$confId = $this->getInput('conf_id');
		if ($confId) {
			$data = $this->getEditFlowInfo($confId);
		} else {
			$data['start_time'] = date('Y-m-d');
			$data['end_time'] = date('Y-m-d');
		}
		$this->assign('configType', array(MobgiApi_Service_AbConfModel::WHILELIST_CONF_TYPE => '白名单', MobgiApi_Service_AbConfModel::ABTEST_CONF_TYPE => 'AbTest'));
		$this->assign('appList', $this->getAppList());
		$this->assign('info', $data);
	}

	private function getEditFlowInfo($confId)
	{
		$testConf = MobgiApi_Service_AbConfModel::getByID($confId);
		if (!$testConf) {
			return array();
		}
		$data = array(
			'conf_id' => $confId,
			'app_key' => $testConf ['app_key'],
			'conf_type' => $testConf ['conf_type'],
			'conf_name' => $testConf ['conf_name'],
			'status' => $testConf ['status'],
			'start_time' => ($testConf ['start_time'] == '0000-00-00') ? date('Y-m-d') : $testConf ['start_time'],
			'end_time' => ($testConf ['end_time'] == '0000-00-00') ? date('Y-m-d') : $testConf ['end_time'],
		);
		$content = json_decode($testConf['content'], true);
		if ($testConf['conf_type'] == MobgiApi_Service_AbConfModel::ABTEST_CONF_TYPE) {
			$data['channel_conf_type'] = $content['channel_conf_type'];
			$data['area_conf_type'] = $content['area_conf_type'];
			$data['game_conf_type'] = $content['game_conf_type'];
			$data['sys_conf_type'] = $content['sys_conf_type'];
			if ($content ['channel_conf_type']) {
				$data ['channel_conf'] = $this->parseChannelConf($content ['channel_conf']);
			}
			if ($content ['area_conf_type']) {
				$data ['area_conf'] = $this->parseAreaConf($content ['area_conf']);
			}
			if ($content ['game_conf_type']) {
				$data ['game_conf'] = $content ['game_conf'];
			}
			if ($content ['sys_conf_type']) {
				$data ['sys_conf_condition'] = $content['sys_conf']['sys_conf_condition'];
				$data ['sys_conf_content'] = $content['sys_conf']['sys_conf_content'];
			}
			$data['rate'] = $testConf['rate'];
		} else {
			$data['is_report'] = $testConf['is_report'];
			$data['dev_mode'] = $testConf['dev_mode'];
			$tmp = array();
			foreach ($content as $val) {
				$tmp[] = array('device' => trim($val));
			}
			$data['device_list'] = $tmp;
		}
		$confRel = MobgiApi_Service_AbConfRelModel::getsBy(array('conf_id' => $confId));
		$conRelList = array();
		if($confRel){
			foreach ($confRel as $val) {
				$conRelList[] = array(
					'flow_id' => $val['flow_id'],
					'conf_rel_id' => $val['id'],
					'weight' => $val['weight'],
				);
			}
		}
		$data['conf_rel_list'] = $conRelList;
		$data['flow_list'] = $this->getFlowList();
		return $data;
	}


	public function deleteAction()
	{
		$configId = $this->getInput('conf_id');
		if (!$configId) {
			$this->output(-1, '非法请求');
		}
		$ret = MobgiApi_Service_AbConfModel::getByID($configId);
		if (!$ret) {
			$this->output(-1, '非法请求');
		}
		if ($ret['status']) {
			$this->output(-1, '测试在运行中，不能删除');
		}
		$result = MobgiApi_Service_AbConfModel::deleteById($configId);
		if (!$result) {
			$this->output(-1, '删除失败');
		}
		MobgiApi_Service_AbConfRelModel::deleteBy(array(
			'conf_id' => $configId
		));

		$this->output(0, '删除成功');
	}

	public function deleteFlowPosRelAction()
	{
		$id = $this->getInput('flow_pos_rel_id');
		if (!$id) {
			$this->output(0, '删除成功');
		}
		$info = MobgiApi_Service_FlowPosRelModel::getByID($id);
		MobgiApi_Service_FlowPosRelModel::deleteBy(array(
			'id' => $id
		));
		if ($info['position'] > 1) {
			$prams['flow_id'] = $info['flow_id'];
			$prams['app_key'] = $info['app_key'];
			$prams['ad_type'] = $info['ad_type'];
			$prams['ads_id'] = $info['ads_id'];
			$prams['position'] = array('>', $info['position']);
			$result = MobgiApi_Service_FlowPosRelModel::getsBy($prams);
			if ($result) {
				foreach ($result as $val) {
					$data['position'] = $val['position'] - 1;
					MobgiApi_Service_FlowPosRelModel::updateByID($data, $val['id']);
				}
			}
		}
		$this->output(0, '删除成功');
	}

	private function getAdsNameList()
	{
		$params ['ad_type'] = array(
			'IN',
			array(
				1,
				3
			)
		);
		$adsList = MobgiApi_Service_AdsListModel::getsBy($params);
		$adsNameList = Common::resetKey($adsList, 'ads_id');
		return $adsNameList;
	}


	public function parseChannelConf($channelIds)
	{
		$channelList = MobgiApi_Service_ChannelModel::getsBy(array(
			'channel_id' => array(
				'IN',
				$channelIds
			)
		));
		$tmp = array();
		if ($channelList) {
			foreach ($channelList as $val) {
				$tmp [] = array(
					'id' => $val ['channel_id'],
					'name' => $val ['channel_name'],
					'level' => 2,
					'parent_id' => $val['group_id'],
				);
			}
		}
		return $tmp;
	}

	public function parseAreaConf($areaConf)
	{
		$tmp = array();
		$provinceList = Common::getConfig('areaConfig', 'provinceList');
		$countryArea = Common::getConfig('areaConfig', 'countryArea');
		$countryList = array();
		foreach ($countryArea as $parentId => $val) {
			foreach ($val as $ke => $va) {
				$countryList [$ke] = array(
					'id' => $ke,
					'name' => $va,
					'parent_id' => $parentId,
					'level' => 2
				);
			}
		}
		$tmp = array();
		foreach ($areaConf as $key => $areId) {
			if (in_array($areId, array_keys($provinceList))) {
				$tmp [] = array(
					'id' => $areId,
					'name' => $provinceList[$areId],
					'level' => 3,
					'parent_id' => 'CN'
				);
			} elseif (in_array($areId, array_keys($countryList))) {
				$tmp [] = array(
					'id' => $areId,
					'name' => $countryList[$areId]['name'],
					'level' => 2,
					'parent_id' => $countryList[$areId]['parent_id']
				);
			}

		}
		return $tmp;
	}

	public function getChannelListAction()
	{
		$channelGroupList = MobgiApi_Service_ChannelModel::getsBy(array(
			'group_id' => 0
		));
		$idsArr = array_keys(Common::resetKey($channelGroupList, 'channel_id'));
		$subChannelList = MobgiApi_Service_ChannelModel::getsBy(array(
			'group_id' => array(
				'IN',
				$idsArr
			)
		));
		$outData = array();
		foreach ($channelGroupList as $channel) {
			$tmp = array();
			foreach ($subChannelList as $subChanenl) {
				if ($channel ['channel_id'] == $subChanenl ['group_id']) {
					$tmp [] = array(
						'id' => $subChanenl ['channel_id'],
						'name' => $subChanenl ['channel_name'],
						'parent_id' => $channel ['channel_id'],
						'level' => 2,
						'item' => array()
					);
				}
			}
			$outData [] = array(
				'id' => strval($channel ['channel_id']),
				'name' => $channel ['channel_name'],
				'parent_id' => '0',
				'level' => 1,
				'item' => $tmp
			);
		}
		$callback = $this->getInput('callback');
		echo $callback . '(' . json_encode($outData) . ')';
		die ();
	}

	public function getAreaListAction()
	{
		$stateArea = Common::getConfig('areaConfig', 'stateArea');
		$countryArea = Common::getConfig('areaConfig', 'countryArea');
		$provinceList = Common::getConfig('areaConfig', 'provinceList');
		$outData = array();
		foreach ($countryArea as $key => $state) {
			$tmp = array();
			foreach ($state as $ke => $country) {
				$tmp2 = array();
				if ($ke == 'CN') {
					foreach ($provinceList as $k => $province) {
						$tmp2 [] = array(
							'id' => strval($k),
							'name' => $province,
							'parent_id' => strval($ke),
							'level' => 3,
							'item' => array()
						);
					}
				}
				$tmp [] = array(
					'id' => $ke,
					'name' => $country,
					'parent_id' => strval($key),
					'level' => 2,
					'item' => $tmp2
				);
			}
			$outData [] = array(
				'id' => strval($key),
				'name' => $stateArea [$key],
				'parent_id' => '0',
				'level' => 1,
				'item' => $tmp
			);
		}
		$callback = $this->getInput('callback');
		echo $callback . '(' . json_encode($outData) . ')';
		die ();
	}

	private function getFlowList()
	{
		$params['flow_id'] = array('>', 0);
		$flowList = MobgiApi_Service_AbFlowConfModel::getsBy($params);
		if (!$flowList) {
			return array();
		}
		$data = array();
		foreach ($flowList as $val) {
			$data[$val['flow_id']] = $val['conf_name'];
		}
		return $data;
	}

	public function getFlowListAction()
	{
		$data = $this->getFlowList();
		$this->output(0, 'ok', $data);
	}

	public function updateStatusAction()
	{
		$configId = $this->getInput('conf_id');
		$filed = $this->getInput('filed');
		$testConf = MobgiApi_Service_AbConfModel::getByID($configId);
		if (!$testConf) {
			$this->output(-1, '非法请求');
		}
		$data[$filed] = $this->getInput('status');
		$ret = MobgiApi_Service_AbConfModel::updateByID($data, $configId);
		if (!$ret) {
			$this->output(-1, '修改失败');
		}
		$this->output(0, '修改成功');
	}

	public function delConfRelAction()
	{
		$info = $this->getInput(array('conf_rel_id'));
		if (!$info['conf_rel_id']) {
			$this->output(-1, '非法请求');
		}
		$ret = MobgiApi_Service_AbConfRelModel::getByID($info['conf_rel_id']);
		if (!$ret) {
			$this->output(-1, '非法请求');
		}
		$ret = MobgiApi_Service_AbConfRelModel::deleteById($info['conf_rel_id']);
		if (!$ret) {
			$this->output(-1, '非法请求');
		}
		$this->output(0, '删除成功');
	}

	public function getAdsListAction()
	{
		$info = $this->getInput(array(
			'app_key',
			'ad_type'
		));
		if (!$info ['app_key'] || !$info ['ad_type']) {
			$this->output(-1, '非法操作');
		}
		list ($dspAdsList, $intergrationAdsList) = $this->initAdsIdsList($info ['app_key'], $info['ad_type']);
		$data['dspAdsList'] = $dspAdsList;
		$data['intergrationAdsList'] = $intergrationAdsList;
		$appInfo = MobgiApi_Service_AdAppModel::getBy(array(
			'app_key' => $info ['app_key']
		));
		$data ['blockList'] = $this->getPosListByAdSubType($appInfo ['app_id'], Common_Service_Const:: $mAdPosType[$info['ad_type']]);
		$this->output(0, '操作成功', $data);
	}


	private function getPosListByAdSubType($appId, $adSubType)
	{
		$params ['pos_key_type'] = $adSubType;
		$params ['app_id'] = $appId;
		$params ['del'] = MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG;
		$result = MobgiApi_Service_AdDeverPosModel::getsBy($params);
		if (!$result) {
			return array();
		}
		$outData = array();

		foreach ($result as $val) {
			$outData [$val ['dever_pos_key']] = array(
				'pos_key' => $val ['dever_pos_key'],
				'pos_name' => $val ['dever_pos_name'],
				'state' => $val ['state'],
				'limit_num' => $val ['limit_num'],
				'rate' => $val ['rate'],
				'third_party_block_id' => '',
			);
		}
		return $outData;
	}

	private function initAdsIdsList($appKey, $adSubType)
	{
		$dspAdsList = array();
		$intergrationAdsList = array();
		// 广告商参数
		$params ['ad_sub_type'] = $adSubType;
		$params ['app_key'] = $appKey;
		$adsAppRelRestut = MobgiApi_Service_AdsAppRelModel::getsBy($params, array('ads_id' => 'ASC'));
		if (!$adsAppRelRestut) {
			return array(
				$dspAdsList,
				$intergrationAdsList
			);
		}
		$adsIds = array_keys(Common::resetKey($adsAppRelRestut, 'ads_id'));
		unset ($params);
		$params ['ad_type'] = array(
			'IN',
			array(
				1,
				3
			)
		);
		$params ['ads_id'] = array(
			'IN',
			$adsIds
		);
		$adsList = MobgiApi_Service_AdsListModel::getsBy($params, array('ads_id' => 'ASC'));
		if (!$adsList) {
			return array(
				$dspAdsList,
				$intergrationAdsList
			);
		}
		foreach ($adsList as $val) {
			if ($val ['ad_type'] == 3) {
				$dspAdsList [$val ['ads_id']] = $val ['name'];
			} else {
				$intergrationAdsList [$val ['ads_id']] = $val ['name'];
			}
		}
		return array(
			$dspAdsList,
			$intergrationAdsList
		);
	}
}
