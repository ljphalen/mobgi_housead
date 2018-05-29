<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Dsp_AdmanageController extends Admin_BaseController
{

	public $actions = array('origainalityListUrl' => '/Admin/Dsp_Admanage/index',
		'batchUpdateCheckUrl' => '/Admin/Dsp_Admanage/batchUpdateCheck',
		'getAppListUrl' => '/Admin/Dsp_Admanage/retAppList',
		'getAppTypeUrl' => '/Admin/Dsp_Admanage/retAppType',
		'getFiterAppUrl' => '/Admin/Dsp_Admanage/getFiterAppById',
		'origainalityListAjaxUrl' => '/Admin/Dsp_Admanage/origainalityListAjax',
		'getOriginalityInfoUrl' => '/Advertiser/Delivery/getOriginalityInfo'
	);

	// array(1=>'投放中',2=>'审核中',3=>'审核未通过',4=>'已暂停',5=>'已删除',6=>'已过期');用户对应的状态
	public $status = array(2 => '待审核', 3 => '审核未通过', -1 => '审核通过', 5 => '已删除');
	public $perpage = 10;

	/**
	 *
	 * Enter description here ...
	 */
	public function indexAction()
	{

		$page = intval($this->getInput('page'));
		if ($page < 1) $page = 1;

		$sqlWhere = '1';
		$search = $this->getInput(array('title', 'status', 'id', 'ad_id', 'account_id', 'originality_type', 'charge_type', 'ad_target_type'));
		if ($search['title']) {
			$sqlWhere .= " AND a.title like  '%" . trim($search['title']) . "%'";
		}
		if ($search['id']) {
			$sqlWhere .= " AND a.id =" . intval($search['id']);
		}
		if ($search['account_id']) {
			$sqlWhere .= " AND a.account_id =" . intval($search['account_id']);
		}
		if ($search['ad_id']) {
			$sqlWhere .= " AND a.ad_id =" . intval($search['ad_id']);
		}
		if ($search['originality_type']) {
			$sqlWhere .= " AND a.originality_type =" . $search['originality_type'];
		}
		if ($search['charge_type']) {
			$sqlWhere .= " AND b.charge_type =" . $search['charge_type'];
		}
		if ($search['ad_target_type']) {
			$sqlWhere .= " AND b.ad_target_type =" . $search['ad_target_type'];
		}

		if ($search['status'] == '2' || $search['status'] == '3' || $search['status'] == '5') {
			$sqlWhere .= " AND a.status =" . $search['status'];
		} elseif ($search['status'] == '-1') {
			$sqlWhere .= " AND a.status in(1,4) ";
		}

		$table = 'delivery_ad_conf_list';
		$on = 'a.ad_id = b.id';

		$orderBy = array('a.id' => 'DESC');
		$field = 'a.*,b.ad_target_type, b.ad_target,b.charge_type,b.price';

		list($total, $origainalityList) = Dedelivery_Service_OriginalityRelationModel::getSearchByPageLeftJoin($table, $on, $page, $this->perpage, $sqlWhere, $orderBy, $field);
		$url = $this->actions['origainalityListUrl'] . '/?' . http_build_query($search) . '&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));


		$adTargetType = Common::getConfig('deliveryConfig', 'adTargetType');
		$chargeTypeList = Common::getConfig('deliveryConfig', 'chargeTypeList');
		$originalityType = Common::getConfig('deliveryConfig', 'originalityType');
		$strategyType = Common::getConfig('deliveryConfig', 'strategy');

		$accountList = $this->getAccountList();
		$appList = $this->getAppList();
		$origainalityList = $this->fillDataToOrigainalityList($origainalityList, $appList);

		$this->assign('appList', $appList);
		$this->assign('accountList', $accountList);
		$this->assign('strategyType', $strategyType);
		$this->assign('search', $search);
		$this->assign('total', $total);
		$this->assign('origainalityList', $origainalityList);
		$this->assign('status', $this->status);
		$this->assign('adTargetType', $adTargetType);
		$this->assign('chargeTypeList', $chargeTypeList);
		$this->assign('originalityType', $originalityType);

	}

	public function origainalityListAjaxAction()
	{
		$params = array();
		$page = intval($this->getInput('page'));
		$perpage = intval($this->getInput('limit'));
		if (empty($perpage)) {
			$perpage = $this->perpage;
		}
		if ($page < 1) $page = 1;

		$sqlWhere = '1';
		$search = $this->getInput(array('title', 'status', 'id', 'ad_id', 'account_id', 'originality_type', 'charge_type', 'ad_target_type'));
		if ($search['title']) {
			$sqlWhere .= " AND a.title like  '%" . trim($search['title']) . "%'";
		}
		if ($search['id']) {
			$sqlWhere .= " AND a.id =" . intval($search['id']);
		}
		if ($search['account_id']) {
			$sqlWhere .= " AND a.account_id =" . intval($search['account_id']);
		}
		if ($search['ad_id']) {
			$sqlWhere .= " AND a.ad_id =" . intval($search['ad_id']);
		}
		if ($search['originality_type']) {
			$sqlWhere .= " AND a.originality_type =" . $search['originality_type'];
		}
		if ($search['charge_type']) {
			$sqlWhere .= " AND b.charge_type =" . $search['charge_type'];
		}
		if ($search['ad_target_type']) {
			$sqlWhere .= " AND b.ad_target_type =" . $search['ad_target_type'];
		}

		if ($search['status'] == '2' || $search['status'] == '3' || $search['status'] == '5') {
			$sqlWhere .= " AND a.status =" . $search['status'];
		} elseif ($search['status'] == '-1') {
			$sqlWhere .= " AND a.status in(1,4) ";
		}

		$table = 'delivery_ad_conf_list';
		$on = 'a.ad_id = b.id';

		$orderBy = array('a.id' => 'DESC');
		$field = 'a.*,b.ad_target_type, b.ad_target,b.charge_type,b.price';

		list($total, $origainalityList) = Dedelivery_Service_OriginalityRelationModel::getSearchByPageLeftJoin($table, $on, $page, $perpage, $sqlWhere, $orderBy, $field);
		$url = $this->actions['origainalityListUrl'] . '/?' . http_build_query($search) . '&';

		$appList = $this->getAppList();
		$origainalityList = $this->fillDataToOrigainalityList($origainalityList, $appList);
		$result = array(
			'success' => 0,
			'msg' => '',
			'count' => $total,
			'data' => $origainalityList,
		);
		exit(json_encode($result));
	}

	private function getAccountList()
	{
		$params['user_type'] = array('NOT IN', array(Admin_Service_UserModel::DEVERLOPER_USER));
		$accountList = Admin_Service_UserModel::getsBy($params);
		$accountList = Common::resetKey($accountList, 'user_id');
		return $accountList;
	}


	private function getAppList($platform = NULL)
	{
		//应用列表
		//$params['platform'] = $platform;
		$params['state'] = 1;
		$params['is_check'] = 1;
		if ($platform) {
			$params['platform'] = $platform;
		}
		$appResult = MobgiApi_Service_AdAppModel::getsBy($params);
		$appList = Common::resetKey($appResult, 'app_key');
		return $appList;
	}

	private function getAppListByAppKey($appKey)
	{
		if (empty($appKey)) {
			return false;
		}
		//应用列表
		$params['app_key'] = array('IN', $appKey);
		$appResult = MobgiApi_Service_AdAppModel::getsBy($params);
		$appList = Common::resetKey($appResult, 'app_key');
		return $appList;
	}

	private function fillDataToOrigainalityList($origainalityList, $appList)
	{

		foreach ($origainalityList as $key => $val) {
			if ($val['originality_type'] == Common_Service_Const::PIC_AD_SUB_TYPE) {
				$img = json_decode($val['upload_content'], TRUE);
				$origainalityList[$key]['cross_img'] = $img['cross_img'];
				$origainalityList[$key]['vertical_img'] = $img['vertical_img'];
			}
			$conAppList = explode(',', html_entity_decode($val['filter_app_conf']));
			$temp = array();
			foreach ($conAppList as $va) {
				$temp[] = $appList[$va]['app_name'];
			}
			$origainalityList[$key]['filter_app_conf'] = implode(',', $temp);
		}
		return $origainalityList;
	}


	//批量广告审核
	public function batchUpdateCheckAction()
	{
		$info = $this->getPost(array('action', 'ids', 'content', 'editId'));
		if ($info['action'] == 'editFiterApp') {
			$info['ids'] = array(0 => $info['editId']);
		}
		if (!count($info['ids'])) $this->output(-1, '没有可操作的项.');
		$oldOriginalObjs = array();
		foreach ($info['ids'] as $val) {
			$oldOriginalObjs[$val] = Dedelivery_Service_OriginalityRelationModel::getBy(array('id' => $val));
		}

		//审核通过array(1=>'投放中',2=>'审核中',3=>'审核未通过',4=>'已暂停',5=>'已删除',6=>'已过期');用户对应的状态
		if ($info['action'] == 'checkPass') {
			foreach ($info['ids'] as $val) {
				$result = Dedelivery_Service_OriginalityRelationModel::getBy(array('id' => $val, 'status' => array('IN', array(2, 3, 1))));
				if (!$result) {
					$this->output(1, '创意状态只能在审核中，审核未通过才能修改');
				}
			}
			//投放状态
			$params['id'] = array('IN', $info['ids']);
			$data['status'] = 1;
			$ret = Dedelivery_Service_OriginalityRelationModel::updateBy($data, $params);
			if (!$ret) {
				$this->output('-1', '操作失败.');
			}
			foreach ($info['ids'] as $val) {
				$result = Dedelivery_Service_OriginalityRelationModel::getBy(array('id' => $val));
				$ret2 = Dedelivery_Service_AdConfListModel::updateBy($data, array('id' => $result['ad_id']));
			}
		} elseif ($info['action'] == 'checkNoPass') {
			foreach ($info['ids'] as $val) {
				$result = Dedelivery_Service_OriginalityRelationModel::getBy(array('id' => $val, 'status' => array('IN', array(2, 3))));
				if (!$result) {
					$this->output(1, '创意状态只能在审核中，审核未通过才能修改');
				}
			}
			//审核不通过
			$params['id'] = array('IN', $info['ids']);
			$data['status'] = 3;
			$ret = Dedelivery_Service_OriginalityRelationModel::updateBy($data, $params);
			if (!$ret) {
				$this->output('-1', '操作失败.');
			}
			//当所有的创意都是不通过的，对应的广告也是不通过
			$tmpAdId = array();
			foreach ($info['ids'] as $val) {
				$result = Dedelivery_Service_OriginalityRelationModel::getBy(array('id' => $val));
				$tmpAdId[$result['ad_id']] = $result['ad_id'];
			}

			foreach ($tmpAdId as $val) {
				$result = Dedelivery_Service_OriginalityRelationModel::getsBy(array('ad_id' => $val));
				$noPassCount = 0;
				foreach ($result as $va) {
					if ($va['status'] == 3) {
						$noPassCount++;
					}
				}
				if (count($result) == $noPassCount) {
					Dedelivery_Service_AdConfListModel::updateBy(array('status' => 3), array('id' => $val));
				}
			}
		} elseif ($info['action'] == 'fiterApp' || $info['action'] == 'editFiterApp') {
			// if (!$info['content']) $this->output(-1, '没有可操作的应用.');
			$params['id'] = array('IN', $info['ids']);
			$data['filter_app_conf'] = $info['content'];
			$ret = Dedelivery_Service_OriginalityRelationModel::updateBy($data, $params);
		} elseif ($info['action'] == 'modifyWeight') {
			if ($info['content'] < 1 || $info['content'] > 1.2) {
				$this->output(-1, '权重范围1-1.2之间.');
			}
			$params['id'] = array('IN', $info['ids']);
			$data['weight'] = trim($info['content']);
			$ret = Dedelivery_Service_OriginalityRelationModel::updateBy($data, $params);
		}
		if (!$ret) $this->output('-1', '操作失败.');

		/*操作日志start*/
		foreach ($info['ids'] as $val) {
			$originalObj = Dedelivery_Service_OriginalityRelationModel::getBy(array('id' => $val));
			$statusarray = array(1 => '投放中', 2 => '审核中', 3 => '审核未通过', 4 => '已暂停', 5 => '已删除', 6 => '已过期');
			if (in_array($info['action'], array('checkPass', 'checkNoPass'))) {
				/*用户操作日志start*/
				$this->mOperateData = '创意id:' . $val . ',' . $statusarray[$data['status']];
				$this->addOperateLog();
				/*用户操作日志end*/
				/*管理端操作日志start*/
				$this->mOperateData = '创意id:' . $val . ',' . $statusarray[$data['status']];
				$this->addOperateLog();
				/*管理端操作日志end*/
			} else if ($info['action'] == 'fiterApp') {
				/*管理端操作日志start*/
				$this->mOperateData = '创意id:' . $val . ',广告id:' . $originalObj['ad_id'] . ',filter_app_conf:' . $oldOriginalObjs[$val]['filter_app_conf'] . ',filter_app_conf:' . $data['filter_app_conf'];
				$this->addOperateLog();
				/*管理端操作日志end*/
			} else if ($info['action'] == 'modifyWeight') {
				/*管理端操作日志start*/
				$this->mOperateData = '创意id:' . $val . ',广告id:' . $originalObj['ad_id'] . ',weight:' . $oldOriginalObjs[$val]['weight'] . ',weight:' . $data['weight'];
				$this->addOperateLog();
				/*管理端操作日志end*/
			}
		}

		/*操作日志end*/

		$this->output('0', '操作成功.');
	}

	public function retAppTypeAction()
	{
		$info = $this->getInput(array('callbackparam', 'callbackparam'));
		if (!isset($info['callbackparam']) || !$info['callbackparam']) {
			die("error");
		}
		$result = array();
		$result[] = array('id' => '1', 'name' => '安卓');
		$result[] = array('id' => '2', 'name' => 'Ios');

		$jsonp = $info['callbackparam'];
		echo $jsonp . '(' . json_encode($result) . ')';
		exit;
	}

	public function retAppListAction()
	{
		$info = $this->getInput(array('callbackparam', 'classIds'));
		if (!isset($info['callbackparam']) || !$info['callbackparam']) {
			die("error");
		}
		$jsonp = $info['callbackparam'];
		$countryList = array();
		if (isset($info['classIds']) && $info['classIds']) {
			$ids = explode(',', $info['classIds']);
			foreach ($ids as $key => $val) {
				if ($val != '-999') {
					$appList = $this->getAppList($val);
					foreach ($appList as $ke => $va) {
						$countryList[]['app_key'] = array('code_id' => $va['app_key'], 'code_name' => $va['app_name']);
					}
				}
			}
		}
		if (!empty($countryList)) {
			echo $jsonp . '(' . json_encode($countryList) . ')';
		} else {
			echo $jsonp . '(null)';
		}
		exit;
	}


	public function getFiterAppByIdAction()
	{
		$id = intval($this->getInput('id'));
		if (!$id) {
			$this->output(1, '操作非法.');
		}
		$result = Dedelivery_Service_OriginalityRelationModel::getBy(array('id' => $id));
		$conAppList = explode(',', html_entity_decode($result['filter_app_conf']));
		$appList = $this->getAppListByAppKey($conAppList);
		$this->output(0, '操作成功.', $appList);
	}


}
