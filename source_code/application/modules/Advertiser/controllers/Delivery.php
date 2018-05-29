<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class DeliveryController extends Admin_BaseController
{

	public $actions = array(
		'uploadApkUrl' => '/Advertiser/Delivery/uploadApk',
		'uploadApkPostUrl' => '/Advertiser/Delivery/uploadApkPost',
		'breakpointuploadApkUrl' => '/Advertiser/Delivery/breakpointuploadApk',
		'breakpointuploadApkPostUrl' => '/Advertiser/Delivery/breakpointuploadApkPost',
		'uploadUrl' => '/Advertiser/Delivery/uploadImg',
		'uploadPostUrl' => '/Advertiser/Delivery/uploadImgPost',
		'uploadOtherUrl' => '/Advertiser/Delivery/uploadOther',
		'uploadOtherPostUrl' => '/Advertiser/Delivery/uploadOtherPost',
		'addAdStep1Url' => '/Advertiser/Delivery/addAdStep1',
		'addAdStep1PostUrl' => '/Advertiser/Delivery/addAdStep1Post',
		'addAdStep2Url' => '/Advertiser/Delivery/addAdStep2',
		'addAdStep2PostUrl' => '/Advertiser/Delivery/addAdStep2Post',
		'addAdStep3Url' => '/Advertiser/Delivery/addAdStep3',
		'addAdStep3PostUrl' => '/Advertiser/Delivery/addAdStep3Post',
		'addAdStep4Url' => '/Advertiser/Delivery/addAdStep4',
		'addAdStep4PostUrl' => '/Advertiser/Delivery/addAdStep4Post',
		'adManageListUrl' => '/Advertiser/Delivery/index',
		'unitListUrl' => '/Advertiser/Delivery/unitList',
		'batchUpdateUnitInfoUrl' => '/Advertiser/Delivery/batchUpdateUnitInfo',
		'batchUpdateAdInfoUrl' => '/Advertiser/Delivery/batchUpdateAdInfo',
		'origainalityListUrl' => '/Advertiser/Delivery/originalityList',
		'addOriginalityUrl' => '/Advertiser/Delivery/addOriginality',
		'copyOrigainalityUrl' => '/Advertiser/Delivery/copyOriginalityAjax',
//		'addOriginalityPostUrl'=>'/Advertiser/Delivery/addOriginalityPost',
		'exportOrigainalityUrl' => '/Advertiser/Delivery/exportOrigainality',
		'exportAdUrl' => '/Advertiser/Delivery/exportAd',
		'exportUnitUrl' => '/Advertiser/Delivery/exportUnit',
		'batchUpdateOriginalityUrl' => '/Advertiser/Delivery/batchUpdateOriginality',
		'addOriginalityPostUrl' => '/Advertiser/Delivery/addOriginalityPost',
		'saveDirecConfigPostUrl' => '/Advertiser/Delivery/saveDirecConfigtPost',
		'getDirecConfigUrl' => '/Advertiser/Delivery/getDirecConfig',
		'updateAdNameUrl' => '/Advertiser/Delivery/updateAdName',
		'updateOriginalityNameUrl' => '/Advertiser/Delivery/updateOriginalityName',
		'updateUnitNameUrl' => '/Advertiser/Delivery/updateUnitName',
		'createH5ZipUrl' => '/Advertiser/Delivery/createH5Zip',
		'previewAdUrl' => '/Advertiser/Delivery/previewAd',
		'unitListAjaxUrl' => '/Advertiser/Delivery/unitListAjax',
		'adlistAjaxUrl' => '/Advertiser/Delivery/adlistAjax',
		'originalityListAjaxUrl' => '/Advertiser/Delivery/originalityListAjax',
		'getOriginalityInfoUrl' => '/Advertiser/Delivery/getOriginalityInfo'

	);

	public $unitStatus = array('1' => '投放中', 2 => '暂停', 3 => '已删除');

	public $adStatus = array(1 => '投放中', 2 => '审核中', 3 => '审核未通过', 4 => '已暂停', 5 => '已删除', 6 => '已过期');

	public $modifyStatus = array(1 => '投放中', 4 => '暂停');

	public $perpage = 10;

	private $unitLimitAmount = 500;


	public function init()
	{
		parent::init();
		if ($this->userInfo['user_type'] == 3) {
			$this->showMsg(-1, '此用户类型不能操作');
		}
	}

	/**
	 *
	 *
	 */
	public function indexAction()
	{
		$params = array();
		$page = intval($this->getInput('page'));
		if ($page < 1) $page = 1;

		$search = $this->getInput(array('ad_name', 'status','originality_type', 'ad_target_type', 'charge_type', 'unit_id', 'start_time', 'end_time'));
		if ($search['ad_name']) {
			$params['ad_name'] = array('LIKE', trim($search['ad_name']));
		}
		if ($search['originality_type']) {
			$params['originality_type'] = $search['originality_type'];
		}
		if ($search['ad_target_type']) {
			$params['ad_target_type'] = $search['ad_target_type'];
		}
		if ($search['status'] && $search['status'] != 5) {
			$params['status'] = $search['status'];
			$params['del'] = Common_Service_Const::NOT_DELETE_FLAG;
		} elseif ($search['status'] == 5) {
			$params['del'] = Common_Service_Const::DELETE_FLAG;
		}
		if ($search['charge_type']) {
			$params['charge_type'] = $search['charge_type'];
		}
		if ($search['unit_id']) {
			$params['unit_id'] = $search['unit_id'];
		}
		if (!isset($search['start_time']) && !isset($search['end_time'])) {
			$search['start_time'] = date('Y-m-d');
			$search['end_time'] = date('Y-m-d');
		}
		$params['account_id'] = $this->userInfo['user_id'];


		list($total, $adList) = Dedelivery_Service_AdConfListModel::getList($page, $this->perpage, $params, array('del' => 'ASC', 'status' => 'ASC', 'id' => 'DESC'));

		$modeType = Common::getConfig('deliveryConfig', 'modeType');
		$adTargetType = Common::getConfig('deliveryConfig', 'adTargetType');
		$chargeTypeList = Common::getConfig('deliveryConfig', 'chargeTypeList');
		$originalityType = Common::getConfig('deliveryConfig', 'originalityType');
		$unitList = $this->getAdUnitListByDb();
		$adList = $this->fillDataToAdList($adList, $search);


		$url = $this->actions['adManageListUrl'] . '/?' . http_build_query($search) . '&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
		$this->assign('search', $search);
		$this->assign('adList', $adList);
		$this->assign('total', $total);
		$this->assign('adStatus', $this->adStatus);
		$this->assign('modifyStatus', $this->modifyStatus);
		$this->assign('modeType', $modeType);
		$this->assign('unitList', $unitList);
		$this->assign('adTargetType', $adTargetType);
		$this->assign('chargeTypeList', $chargeTypeList);
		$this->assign('originalityType', $originalityType);
		$this->assign('copyOrigainalityUrl', $this->actions['copyOrigainalityUrl']);

	}

	/**
	 * ajax拉取广告列表
	 */
	public function adlistAjaxAction()
	{
		$params = array();
		$page = intval($this->getInput('page'));
		if ($page < 1) $page = 1;
		$perpage = intval($this->getInput('limit'));
		if (empty($perpage)) {
			$perpage = $this->perpage;
		}

		$search = $this->getInput(array('ad_name', 'status', 'ad_target_type', 'charge_type', 'unit_id', 'originality_type', 'start_time', 'end_time'));
		if ($search['ad_name']) {
			$params['ad_name'] = array('LIKE', trim($search['ad_name']));
		}
		if ($search['ad_target_type']) {
			$params['ad_target_type'] = $search['ad_target_type'];
		}
		if ($search['status'] && $search['status'] != 5) {
			$params['status'] = $search['status'];
			$params['del'] = Common_Service_Const::NOT_DELETE_FLAG;
		} elseif ($search['status'] == 5) {
			$params['del'] = Common_Service_Const::DELETE_FLAG;
		}
		if ($search['charge_type']) {
			$params['charge_type'] = $search['charge_type'];
		}
		if ($search['unit_id']) {
			$params['unit_id'] = $search['unit_id'];
		}
		if ($search['originality_type']) {
			$params['originality_type'] = $search['originality_type'];
		}
		if (!isset($search['start_time']) && !isset($search['end_time'])) {
			$search['start_time'] = date('Y-m-d');
			$search['end_time'] = date('Y-m-d');
		}
		$params['account_id'] = $this->userInfo['user_id'];

		list($total, $adList) = Dedelivery_Service_AdConfListModel::getList($page, $perpage, $params, array('del' => 'ASC', 'status' => 'ASC', 'id' => 'DESC'));

		$result = array(
			'success' => 0,
			'msg' => '',
			'count' => $total,
			'data' => $adList,
		);
		exit(json_encode($result));
	}

	public function fillDataToAdList($adList, $search)
	{
		foreach ($adList as $key => $val) {
			$adId[] = $val['id'];
		}
		$origainalityList = array();
		if (!empty($adId)) {
			$origainalityResult = Dedelivery_Service_OriginalityRelationModel::getsBy(array('ad_id' => array('IN', $adId)));
			foreach ($origainalityResult as $val) {
				$origainalityList[$val['ad_id']][] = $val['id'];
			}
		}
		foreach ($adList as $key => $val) {
			if ($search['start_time'] == date('Y-m-d') && $search['end_time'] == date('Y-m-d')) {
				$todayAmount = 0;
				$todayViews = 0;
				$todayClicks = 0;
				$todayActives = 0;
				if ($origainalityList[$val['id']]) {
					foreach ($origainalityList[$val['id']] as $origainalityId) {
						$reulst = MobgiCharge_Service_AdxChargeMinuteModel::getTodayOriginalityDetailFromCache($origainalityId);
						if ($reulst) {
							$todayAmount += $reulst['amount'];
							$todayViews += $reulst['views'];
							$todayClicks += $reulst['clicks'];
							$todayActives += $reulst['actives'];
						}
					}
				}
				$totalAmount = $todayAmount;
				$totalViews = $todayViews;
				$totalClicks = $todayClicks;
				$totalActives = $todayActives;
			} else {
				if (isset($search['start_time']) && isset($search['end_time'])) {
					$dayParams['days'] = array(array('>=', $search['start_time']), array('<=', $search['end_time']));
					$dayParams['ad_id'] = $val['id'];
					$preDataResult = MobgiCharge_Service_AdxChargeDayModel::getPreDaysData($dayParams);
				}
				if (!empty($preDataResult)) {
					$totalAmount = floatval($preDataResult[0]['amount']);
					$totalViews = floatval($preDataResult[0]['views']);
					$totalClicks = floatval($preDataResult[0]['clicks']);
					$totalActives = floatval($preDataResult[0]['actives']);
				}
			}
			$adList[$key]['amount'] = sprintf("%01.4f", $totalAmount);
			$adList[$key]['views'] = sprintf("%01.4f", $totalViews);
			$adList[$key]['clicks'] = sprintf("%01.4f", $totalClicks);
			$adList[$key]['actives'] = sprintf("%01.4f", $totalActives);
			$adList[$key]['click_rate'] = ($totalViews && $totalClicks) ? sprintf("%01.4f", round(($totalClicks / $totalViews) * 100, 4)) . '%' : 0;
			$adList[$key]['amount_rate'] = ($totalClicks && $totalAmount) ? sprintf("%01.4f", round($totalAmount / $totalClicks, 4)) : 0;
			$adList[$key]['active_rate'] = ($totalActives && $totalClicks) ? sprintf("%01.4f", round(($totalActives / $totalClicks) * 100, 4) . '%') : 0;
			$adList[$key]['cpa'] = ($totalAmount && $totalActives) ? sprintf("%01.4f", round($totalAmount / $totalActives, 4)) : 0;
		}
		return $adList;
	}

	public function getOriginalityInfoAction()
	{
		$id = intval($this->getInput('id'));
		if (!$id) {
			$this->output(-1, '非法id');
		}
		$result = Dedelivery_Service_OriginalityRelationModel::getBy(array('id' => $id));
		$result['upload_content'] = json_decode($result['upload_content'], true);
		$this->output(0, 'ok', $result);
	}

	/**
	 * 创意列表
	 */
	public function originalityListAction()
	{
		$params = array();
		$page = intval($this->getInput('page'));
		if ($page < 1) $page = 1;

		$search = $this->getInput(array('title', 'originality_type', 'status', 'id', 'ad_name', 'charge_type', 'ad_target_type', 'ad_id', 'start_time', 'end_time'));
		$params['account_id'] = $this->userInfo['user_id'];

		if (!isset($search['start_time']) && !isset($search['end_time'])) {
			$search['start_time'] = date('Y-m-d');
			$search['end_time'] = date('Y-m-d');
		}

		//广告明细
		$adDetail = Dedelivery_Service_AdConfListModel::getBy(array('id' => intval($search['ad_id']), 'account_id' => $this->userInfo['user_id']));
		$adList = Dedelivery_Service_AdConfListModel::getAllByFields('id,ad_target_type,jump_type,originality_type,ad_sub_type,ad_name', array('del' => 0, 'account_id' => $this->userInfo['user_id'], 'status' => array('<>', 5)));

		$adList = Common::resetKey($adList, 'id');
		$this->assign('adList', $adList);
		$this->assign('adDetail', $adDetail);
		$sqlWhere = ' a.account_id = "' . $this->userInfo['user_id'] . '"';

		if ($search['title']) {
			$sqlWhere .= ' AND a.title like  "%' . trim($search['title']) . '%"';
		}
		if ($search['originality_type']) {
			$sqlWhere .= ' AND a.originality_type = ' . intval($search['originality_type']);
		}
		if ($search['status'] && $search['status'] != 5) {
			$sqlWhere .= ' AND a.status = ' . intval($search['status']);
			$sqlWhere .= ' AND a.del = ' . Common_Service_Const::NOT_DELETE_FLAG;
		} elseif ($search['status'] == 5) {
			$sqlWhere .= ' AND a.del = ' . Common_Service_Const::DELETE_FLAG;
		}
		if ($search['id']) {
			$sqlWhere .= ' AND a.id = ' . intval($search['id']);
		}

		if ($search['ad_name']) {
			$sqlWhere .= ' AND b.ad_name like  "%' . trim($search['ad_name']) . '%"';
		}
		if ($search['charge_type']) {
			$sqlWhere .= ' AND b.charge_type = ' . intval($search['charge_type']);
		}
		if ($search['ad_target_type']) {
			$sqlWhere .= ' AND b.ad_target_type = ' . intval($search['ad_target_type']);
		}
		if ($search['ad_id']) {
			$sqlWhere .= ' AND a.ad_id = ' . intval($search['ad_id']);
		}
		$table = 'delivery_ad_conf_list ';
		$on = 'a.ad_id = b.id';
		$field = 'a.*,b.ad_name,b.charge_type,b.ad_target_type';
		$orderBy = array('a.id' => 'desc');
		list($total, $origainalityList) = Dedelivery_Service_OriginalityRelationModel::getSearchByPageLeftJoin($table, $on, $page, $this->perpage, $sqlWhere, $orderBy, $field);

//		list($total, $origainalityList) = Dedelivery_Service_OriginalityRelationModel::getList($page, $this->perpage, $params);
		$origainalityResultType = Common::resetKey($origainalityList, 'ad_id');
		$origainalityList = $this->fillDataToOriganialityList($search, $origainalityList);


		$url = $this->actions['origainalityListUrl'] . '/?' . http_build_query($search) . '&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));

		$adTargetType = Common::getConfig('deliveryConfig', 'adTargetType');
		$chargeTypeList = Common::getConfig('deliveryConfig', 'chargeTypeList');
		$originalityType = Common::getConfig('deliveryConfig', 'originalityType');
		$strategyType = Common::getConfig('deliveryConfig', 'strategy');
		$adSubType = Common::getConfig('deliveryConfig', 'adSubType');
		$this->assign('strategyType', $strategyType);
		$this->assign('adTargetType', $adTargetType);
		$this->assign('chargeTypeList', $chargeTypeList);
		$this->assign('originalityType', $originalityType);
		$this->assign('adSubType', $adSubType);

		$this->assign('search', $search);
		$this->assign('total', $total);
		$this->assign('origainalityList', $origainalityList);
		$this->assign('adStatus', $this->adStatus);
		$this->assign('modifyStatus', $this->modifyStatus);
		$this->assign('origainalityResultType', $origainalityResultType);
	}

	/**
	 * 创意列表
	 */
	public function originalityListAjaxAction()
	{
		$params = array();
		$page = intval($this->getInput('page'));
		if ($page < 1) $page = 1;
		$perpage = intval($this->getInput('limit'));
		if (empty($perpage)) {
			$perpage = $this->perpage;
		}

		$search = $this->getInput(array('title', 'originality_type', 'status', 'id', 'ad_name', 'charge_type', 'ad_target_type', 'ad_id', 'start_time', 'end_time'));
		$params['account_id'] = $this->userInfo['user_id'];

		if (!isset($search['start_time']) && !isset($search['end_time'])) {
			$search['start_time'] = date('Y-m-d');
			$search['end_time'] = date('Y-m-d');
		}

		//广告明细
		$adDetail = Dedelivery_Service_AdConfListModel::getBy(array('id' => intval($search['ad_id']), 'account_id' => $this->userInfo['user_id']));
		$adList = Dedelivery_Service_AdConfListModel::getAllByFields('id,ad_target_type,jump_type,originality_type,ad_sub_type,ad_name, trial_package_id', array('del' => 0, 'account_id' => $this->userInfo['user_id'], 'status' => array('<>', 5)));

		$adList = Common::resetKey($adList, 'id');
		$this->assign('adList', $adList);
		$this->assign('adDetail', $adDetail);
		$sqlWhere = ' a.account_id = "' . $this->userInfo['user_id'] . '"';

		if ($search['title']) {
			$sqlWhere .= ' AND a.title like  "%' . trim($search['title']) . '%"';
		}
		if ($search['originality_type']) {
			$sqlWhere .= ' AND a.originality_type = ' . intval($search['originality_type']);
		}
		if ($search['status'] && $search['status'] != 5) {
			$sqlWhere .= ' AND a.status = ' . intval($search['status']);
			$sqlWhere .= ' AND a.del = ' . Common_Service_Const::NOT_DELETE_FLAG;
		} elseif ($search['status'] == 5) {
			$sqlWhere .= ' AND a.del = ' . Common_Service_Const::DELETE_FLAG;
		}
		if ($search['id']) {
			$sqlWhere .= ' AND a.id = ' . intval($search['id']);
		}

		if ($search['ad_name']) {
			$sqlWhere .= ' AND b.ad_name like  "%' . trim($search['ad_name']) . '%"';
		}
		if ($search['charge_type']) {
			$sqlWhere .= ' AND b.charge_type = ' . intval($search['charge_type']);
		}
		if ($search['ad_target_type']) {
			$sqlWhere .= ' AND b.ad_target_type = ' . intval($search['ad_target_type']);
		}
		if ($search['ad_id']) {
			$sqlWhere .= ' AND a.ad_id = ' . intval($search['ad_id']);
		}
		$table = 'delivery_ad_conf_list ';
		$on = 'a.ad_id = b.id';
		$field = 'a.*,b.ad_name,b.charge_type,b.ad_target_type';
		$orderBy = array('a.id' => 'desc');
		list($total, $origainalityList) = Dedelivery_Service_OriginalityRelationModel::getSearchByPageLeftJoin($table, $on, $page, $perpage, $sqlWhere, $orderBy, $field);

//		list($total, $origainalityList) = Dedelivery_Service_OriginalityRelationModel::getList($page, $this->perpage, $params);
		$origainalityResultType = Common::resetKey($origainalityList, 'ad_id');
		$origainalityList = $this->fillDataToOriganialityList($search, $origainalityList);
		$result = array(
			'success' => 0,
			'msg' => '',
			'count' => $total,
			'data' => $origainalityList,
		);
		exit(json_encode($result));
	}

	/**
	 * 创意列表
	 */
	public function addOriginalityAction()
	{

		$params = array();
		$search = $this->getInput(array('action', 'id'));
		$params['account_id'] = $this->userInfo['user_id'];

		//广告明细
		$adList = Dedelivery_Service_AdConfListModel::getAllByFields('id,ad_name,originality_conf_id', array('del' => 0, 'account_id' => $this->userInfo['user_id']));
		$adList = Common::resetKey($adList, 'id');
		$this->assign('adList', $adList);


		if ($search['id']) {
			$sqlWhere = ' AND a.id = ' . intval($search['id']);
		}

		if ($search['ad_name']) {
			$sqlWhere .= ' AND b.ad_name like  "%' . trim($search['ad_name']) . '%"';
		}
		if ($search['charge_type']) {
			$sqlWhere .= ' AND b.charge_type = ' . intval($search['charge_type']);
		}
		if ($search['ad_target_type']) {
			$sqlWhere .= ' AND b.ad_target_type = ' . intval($search['ad_target_type']);
		}
		if ($search['ad_id']) {
			$sqlWhere .= ' AND a.ad_id = ' . intval($search['ad_id']);
		}
		$table = 'delivery_ad_conf_list ';
		$on = 'a.ad_id = b.id';
		$field = 'a.*,b.ad_name,b.charge_type,b.ad_target_type';


		$adTargetType = Common::getConfig('deliveryConfig', 'adTargetType');
		$chargeTypeList = Common::getConfig('deliveryConfig', 'chargeTypeList');
		$originalityType = Common::getConfig('deliveryConfig', 'originalityType');
		$strategyType = Common::getConfig('deliveryConfig', 'strategy');
		$this->assign('strategyType', $strategyType);
		$this->assign('adTargetType', $adTargetType);
		$this->assign('chargeTypeList', $chargeTypeList);
		$this->assign('originalityType', $originalityType);

		// 获取后台设置创意
//	    $originalityConfList = $this->getOriginalityConfList($adDetail['ad_target_type']);
		$originalityConfList = $this->getOriginalityConfList(0);
		$originalityConfList = Common::resetKey($originalityConfList, 'id');
		$this->assign('originalityConfList', $originalityConfList);
		$this->assign('search', $search);
		$this->assign('adStatus', $this->adStatus);
		$this->assign('modifyStatus', $this->modifyStatus);
	}

	private function fillDataToOriganialityList($search, $origainalityList)
	{
		foreach ($origainalityList as $key => $val) {
			if ($val['originality_type'] == Common_Service_Const::PIC_AD_SUB_TYPE
				|| $val['originality_type'] == Common_Service_Const::SPLASH_AD_SUB_TYPE) {
				$img = json_decode($val['upload_content'], TRUE);
				$origainalityList[$key]['cross_img'] = $img['cross_img'];
				$origainalityList[$key]['vertical_img'] = $img['vertical_img'];
			}
			$totalAmount = 0;
			$totalViews = 0;
			$totalClicks = 0;
			$totalActives = 0;
			if ($search['start_time'] == date('Y-m-d') && $search['end_time'] == date('Y-m-d')) {
				$reulst = MobgiCharge_Service_AdxChargeMinuteModel::getTodayOriginalityDetailFromCache($val['id']);
				if ($reulst) {
					$totalAmount = $reulst['amount'];
					$totalViews = $reulst['views'];
					$totalClicks = $reulst['clicks'];
					$totalActives = $reulst['actives'];
				}
			} else {
				if (isset($search['start_time']) && isset($search['end_time'])) {
					$dayParams['days'] = array(array('>=', $search['start_time']), array('<=', $search['end_time']));
					$dayParams['originality_id'] = $val['id'];
					$preDataResult = MobgiCharge_Service_AdxChargeDayModel::getPreDaysData($dayParams);
				}
				if (!empty($preDataResult)) {
					$totalAmount = floatval($preDataResult[0]['amount']);
					$totalViews = floatval($preDataResult[0]['views']);
					$totalClicks = floatval($preDataResult[0]['clicks']);
					$totalActives = floatval($preDataResult[0]['actives']);
				}
			}
			$origainalityList[$key]['amount'] = sprintf("%01.4f", $totalAmount);
			$origainalityList[$key]['views'] = sprintf("%01.4f", $totalViews);
			$origainalityList[$key]['clicks'] = sprintf("%01.4f", $totalClicks);
			$origainalityList[$key]['actives'] = sprintf("%01.4f", $totalActives);
			$origainalityList[$key]['click_rate'] = ($totalViews && $totalClicks) ? sprintf("%01.4f", round(($totalClicks / $totalViews) * 100, 4)) . '%' : 0;
			$origainalityList[$key]['amount_rate'] = ($totalClicks && $totalAmount) ? sprintf("%01.4f", round($totalAmount / $totalClicks, 4)) : 0;
			$origainalityList[$key]['active_rate'] = ($totalActives && $totalClicks) ? sprintf("%01.4f", round(($totalActives / $totalClicks) * 100, 4) . '%') : 0;
			$origainalityList[$key]['cpa'] = ($totalAmount && $totalActives) ? sprintf("%01.4f", round($totalAmount / $totalActives, 4)) : 0;
		}
		return $origainalityList;
	}


	/**
	 * 单元列表
	 */
	public function unitListAction()
	{
		$params = array();
		$page = intval($this->getInput('page'));
		if ($page < 1) $page = 1;

		$search = $this->getInput(array('name', 'status', 'mode_type', 'start_time', 'end_time'));
		if ($search['name']) {
			$params['name'] = array('LIKE', $search['name']);
		}
		if ($search['mode_type']) {
			$params['mode_type'] = $search['mode_type'];
		}
		if ($search['status']) {
			$params['status'] = $search['status'];
		}
		$params['account_id'] = $this->userInfo['user_id'];
		if (!isset($search['start_time']) && !isset($search['end_time'])) {
			$search['start_time'] = date('Y-m-d');
			$search['end_time'] = date('Y-m-d');
		}
		//$params['del'] = Common_Service_Const::NOT_DELETE_FLAG;

		$orderBy = array('status' => 'ASC', 'update_time' => 'DESC');
		list($total, $unitList) = Dedelivery_Service_UnitConfModel::getList($page, $this->perpage, $params, $orderBy);
		$unitList = $this->fillDataToUnitList($unitList, $search);

		$url = $this->actions['unitListUrl'] . '/?' . http_build_query($search) . '&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
		$modeType = Common::getConfig('deliveryConfig', 'modeType');
		$this->assign('search', $search);
		$this->assign('unitList', $unitList);
		$this->assign('total', $total);
		$this->assign('unitStatus', $this->unitStatus);
		$this->assign('modeType', $modeType);
	}

	/**
	 * 获取计划列表
	 */
	public function unitListAjaxAction()
	{
		$params = array();
		$page = intval($this->getInput('page'));
		if ($page < 1) $page = 1;
		$perpage = intval($this->getInput('limit'));
		if (empty($perpage)) {
			$perpage = $this->perpage;
		}

		$search = $this->getInput(array('name', 'status', 'mode_type', 'start_time', 'end_time'));
		if ($search['name']) {
			$params['name'] = array('LIKE', $search['name']);
		}
		if ($search['mode_type']) {
			$params['mode_type'] = $search['mode_type'];
		}
		if ($search['status']) {
			$params['status'] = $search['status'];
		}
		$params['account_id'] = $this->userInfo['user_id'];
		if (!isset($search['start_time']) && !isset($search['end_time'])) {
			$search['start_time'] = date('Y-m-d');
			$search['end_time'] = date('Y-m-d');
		}
		$params['del'] = Common_Service_Const::NOT_DELETE_FLAG;

		list($total, $unitList) = Dedelivery_Service_UnitConfModel::getList($page, $perpage, $params);
		$unitList = $this->fillDataToUnitList($unitList, $search);

		$result = array(
			'success' => 0,
			'msg' => '',
			'count' => $total,
			'data' => $unitList,
		);
		exit(json_encode($result));

	}


	public function fillDataToUnitList($unitList, $search)
	{
		foreach ($unitList as $key => $val) {
			$unitId[] = $val['id'];
		}
		$origainalityList = array();
		if (!empty($unitId)) {
			$origainalityResult = Dedelivery_Service_OriginalityRelationModel::getsBy(array('unit_id' => array('IN', $unitId)));
			foreach ($origainalityResult as $val) {
				$origainalityList[$val['unit_id']][] = $val['id'];
			}
		}


		foreach ($unitList as $key => $val) {
			if ($search['start_time'] == date('Y-m-d') && $search['end_time'] == date('Y-m-d')) {
				$todayAmount = 0;
				$todayViews = 0;
				$todayClicks = 0;
				$todayActives = 0;
				if ($origainalityList[$val['id']]) {
					foreach ($origainalityList[$val['id']] as $origainalityId) {
						$reulst = MobgiCharge_Service_AdxChargeMinuteModel::getTodayOriginalityDetailFromCache($origainalityId);
						if ($reulst) {
							$todayAmount += $reulst['amount'];
							$todayViews += $reulst['views'];
							$todayClicks += $reulst['clicks'];
							$todayActives += $reulst['actives'];
						}
					}
				}
				$totalAmount = $todayAmount;
				$totalViews = $todayViews;
				$totalClicks = $todayClicks;
				$totalActives = $todayActives;
			} else {
				if (isset($search['start_time']) && isset($search['end_time'])) {
					$dayParams['days'] = array(array('>=', $search['start_time']), array('<=', $search['end_time']));
					$dayParams['ad_unit_id'] = $val['id'];
					$preDataResult = MobgiCharge_Service_AdxChargeDayModel::getPreDaysData($dayParams);
				}
				if (!empty($preDataResult)) {
					$totalAmount = floatval($preDataResult[0]['amount']);
					$totalViews = floatval($preDataResult[0]['views']);
					$totalClicks = floatval($preDataResult[0]['clicks']);
					$totalActives = floatval($preDataResult[0]['actives']);
				}
			}
			//$todayResult['amount'] = Advertiser_Service_UnitDayConsumeModel::getAdConsumption($val['id']);
			$unitList[$key]['amount'] = sprintf("%01.4f", $totalAmount);
			$unitList[$key]['views'] = sprintf("%01.4f", $totalViews);
			$unitList[$key]['clicks'] = sprintf("%01.4f", $totalClicks);
			$unitList[$key]['actives'] = sprintf("%01.4f", $totalActives);
			$unitList[$key]['click_rate'] = ($totalViews && $totalClicks) ? sprintf("%01.4f", round(($totalClicks / $totalViews) * 100, 4)) . '%' : 0;
			$unitList[$key]['amount_rate'] = ($totalClicks && $totalAmount) ? sprintf("%01.4f", round($totalAmount / $totalClicks, 4)) : 0;
			$unitList[$key]['active_rate'] = ($totalActives && $totalClicks) ? sprintf("%01.4f", round(($totalActives / $totalClicks) * 100, 4) . '%') : 0;
			$unitList[$key]['cpa'] = ($totalAmount && $totalActives) ? sprintf("%01.4f", round($totalAmount / $totalActives, 4)) : 0;
		}
		return $unitList;
	}

	/**
	 *新增广告参数
	 *
	 */
	public function addAdStep1Action()
	{

		$info = $this->getInput(array('id', 'action'));
		$adUnitList = $this->getAdUnitListByDb();
		$this->assign('adUnitList', $adUnitList);

		if (!$info['id']) {
			$uId = $this->userInfo['user_id'];
			$cache = Dedelivery_Service_AdConfListModel::getCache();
			$key = Dedelivery_Service_AdConfListModel::getAddAdStepKey(1, $uId, intval($info['id']));
			$result = $cache->get($key);
			if (!isset($result['start_date']) || !isset($result['end_date'])) {
				$result['start_date'] = date('Y-m-d');
				$result['end_date'] = date('Y-m-d');
			}
		} else {
			$result = Dedelivery_Service_AdConfListModel::getBy(array('id' => intval($info['id']), 'account_id' => $this->userInfo['user_id']));
			if (!empty($result)) {
				$dateRange = json_decode($result['date_range'], true);
				$result['start_date'] = $dateRange['start_date'];
				$result['end_date'] = $dateRange['end_date'];
				$timeRange = json_decode($result['time_range'], true);
				$result['start_time'] = $timeRange['start_time'];
				$result['end_time'] = $timeRange['end_time'];
				$result['action'] = $info['action'];
			}

		}

		$this->assign('result', $result);
	}

	/**
	 *post广告参数
	 *
	 */
	public function addAdStep1PostAction()
	{
		$info = $this->getInput(array('unit_id', 'ad_name', 'mode_type',
			'start_date', 'end_date',
			'time_type', 'hour_set_type',
			'start_time', 'end_time', 'time_series',
			'id', 'action',
			'frequency_type', 'frequency',
			'ad_limit_type', 'ad_limit_amount'
		));
		$info = $this->checkAdStep1Param($info);
		$uId = $this->userInfo['user_id'];

		if (!$info['id'] || $info['action'] == 'copy') {
			$params['ad_name'] = trim($info['ad_name']);
			$params['account_id'] = $uId;
			$result = Dedelivery_Service_AdConfListModel::getBy($params);
			if ($result) {
				$this->output(1, '广告名称已经存在，操作失败');
			}
		}

		$cache = Dedelivery_Service_AdConfListModel::getCache();
		$key = Dedelivery_Service_AdConfListModel::getAddAdStepKey(1, $uId, intval($info['id']));
		$result = $cache->set($key, $info, Dedelivery_Service_AdConfListModel::CACHE_EPIRE);
		if (!$result) {
			$this->output(1, '操作失败');
		}
		$this->output(0, '操作成功');
	}

	public function addAdStep2Action()
	{
		$info = $this->getInput(array('id'));
		$uId = $this->userInfo['user_id'];
		$cache = Dedelivery_Service_AdConfListModel::getCache();
		$key = Dedelivery_Service_AdConfListModel::getAddAdStepKey(1, $uId, intval($info['id']));
		$cacheData = $cache->get($key);
		if (!$cacheData) {
			$this->output(1, '操作失败');
		}


		$originalityType = Common::getConfig('deliveryConfig', 'originalityType');
		$strategyType = Common::getConfig('deliveryConfig', 'strategy');
		$h5templateType = Common::getConfig('deliveryConfig', 'h5template');
		$h5uploadType = Common::getConfig('deliveryConfig', 'h5upload');
		$adSubType = Common::getConfig('deliveryConfig', 'adSubType');
		$this->assign('originalityType', $originalityType);
		$this->assign('strategyType', $strategyType);
		$this->assign('h5templateType', $h5templateType);
		$this->assign('h5uploadType', $h5uploadType);
		$this->assign('adSubType', $adSubType);

		//广告目标
		$adTargetTypeConfig = Common::getConfig('deliveryConfig', 'adTargetType');
		$this->assign('adTargetTypeConfig', $adTargetTypeConfig);
		//点击后动作
		$jumpTypeConfig = Common::getConfig('deliveryConfig', 'jumpType');
		$this->assign('jumpTypeConfig', $jumpTypeConfig);

		if (!$info['id']) {
			$key = Dedelivery_Service_AdConfListModel::getAddAdStepKey(2, $uId, intval($info['id']));
			$result = $cache->get($key);
		} else {
			$result = Dedelivery_Service_AdConfListModel::getBy(array('id' => intval($info['id']), 'account_id' => $this->userInfo['user_id']));
			if ($result) {
				$imp_trackers_arr = json_decode($result['imp_trackers']);
				$click_trackers_arr = json_decode($result['click_trackers']);
				$result['imp_trackers'] = $imp_trackers_arr ? implode(',', $imp_trackers_arr) : '';
				$result['click_trackers'] = $click_trackers_arr ? implode(',', $click_trackers_arr) : '';
				$upload_info = json_decode($result['upload_info'], TRUE);
				if ($upload_info) {
					$result['fileName'] = $upload_info['fileName'];
					$result['fileType'] = $upload_info['fileType'];
					$result['fileSize'] = $upload_info['fileSize'];
					$result['progress'] = $upload_info['progress'];
					$result['uploadVal'] = $upload_info['uploadVal'];
				}
			}
			$originalityResult = Dedelivery_Service_OriginalityRelationModel::getBy(array('ad_id' => intval($info['id']), 'account_id' => $this->userInfo['user_id']));
			if (!empty($originalityResult)) {
				$result['originality_id'] = $originalityResult['id'];
				$result['id'] = intval($info['id']);
				$result['strategy'] = intval($originalityResult['strategy']);
				$result['title'] = $originalityResult['title'];
				$result['desc'] = $originalityResult['desc'];
				$uploadContent = json_decode($originalityResult['upload_content'], TRUE);
				$arr = array('cross_img', 'vertical_img', 'full_img', 'video', 'h5', 'icon', 'score', 'action_text', 'single_img', 'combination_img1', 'combination_img2', 'combination_img3', 'enbed_image_size');
				foreach ($arr as $val) {
					if (isset($uploadContent[$val])) {
						$result[$val] = $uploadContent[$val];
					}
				}
			}
		}

//        if($result['ad_target_type'] == 1 && in_array($result['jump_type'], array(7,0))){
//            $result['isShowApkupload'] = true;
//        }else{
//            $result['isShowApkupload'] = false;
//        }

		$this->assign('result', $result);

	}

	private function getOriginalityConfList($adTargetType)
	{
		$params['is_delete'] = Common_Service_Const::NOT_DELETE_FLAG;
		$result = Advertiser_Service_OriginalityConfModel::getsBy($params);
		if ($adTargetType) {
			foreach ($result as $key => $val) {
				$tmp = json_decode($val['ad_target_type'], true);
				if (!in_array($adTargetType, $tmp) && $adTargetType != 3) {
					unset($result[$key]);
				}
			}
		}
		return $result;
	}

	public function addAdStep2PostAction()
	{
		$info = $this->getInput(array(
			'ad_target_type', 'ad_target', 'package_name',
			'jump_type', 'imp_trackers', 'click_trackers', 'app_name',
			'originality_type', 'strategy', 'title', 'desc',
			'cross_img', 'vertical_img', 'full_img', 'video', 'h5', 'id', 'originality_id',
			'icon', 'h5upload',
			'imp_trackers', 'click_trackers',
			'fileName', 'fileType', 'fileSize', 'progress', 'uploadVal',
			'deeplink',
			'ad_sub_type', 'score', 'action_text', 'single_img', 'combination_img1', 'combination_img2', 'combination_img3', 'enbed_image_size',
		));
		$info = $this->checkStep2Param($info);
		$uId = $this->userInfo['user_id'];
		$cache = Dedelivery_Service_AdConfListModel::getCache();
		$key = Dedelivery_Service_AdConfListModel::getAddAdStepKey(2, $uId, intval($info['id']));
		$cachedata = $cache->get($key);
		if ($cachedata && is_array($cachedata)) {
			$allCacheData = array_merge($cachedata, $info);
		} else {
			$allCacheData = $info;
		}
		$result = $cache->set($key, $allCacheData, Dedelivery_Service_AdConfListModel::CACHE_EPIRE);
		$this->output(0, '操作成功');

	}


	public function addAdStep3Action()
	{
		$info = $this->getInput(array('id'));
		$uId = $this->userInfo['user_id'];
		$cache = Dedelivery_Service_AdConfListModel::getCache();
		$key = Dedelivery_Service_AdConfListModel::getAddAdStepKey(2, $uId, intval($info['id']));
		$step2CacheData = $cache->get($key);
		if (!$step2CacheData) {
			$this->output(1, '操作失败');
		}

		$this->getAppCategotyList($step2CacheData);
		$key = Dedelivery_Service_AdConfListModel::getAddAdStepKey(3, $uId, intval($info['id']));
		$result = $cache->get($key);
		if ($result === false) {
			$result = Dedelivery_Service_AdConfListModel::getBy(array('id' => intval($info['id']), 'account_id' => $this->userInfo['user_id']));
			if ($result) {
				$ditectConfData = json_decode($result['direct_config'], true);
				if ($ditectConfData) {
					$result = array_merge($result, $ditectConfData);
				}
			}
		}


		if ($result['area_type']) {
			$result['area_range'] = $this->parseAreaConf($result['area_range']);
		}

		$params['advertiser_uid'] = $this->userInfo['user_id'];
		$direcList = Advertiser_Service_DirectModel::getsBy($params);
		$direcList = Common::resetKey($direcList, 'id');


		$this->assign('direcList', $direcList);
		$this->assign('step2CacheData', $step2CacheData);
		$this->assign('result', $result);
		$config = Common::getConfig('deliveryConfig');
		foreach ($config as $key => $val) {
			$this->assign($key, $val);
		}
		//获取计费类型
		$chargeTypeList = Common::getConfig('deliveryConfig', 'chargeTypeList');
		$this->assign('chargeTypeList', $chargeTypeList);
		$provinceList = Common::getConfig('areaConfig', 'provinceList');
		$this->assign('provinceList', $provinceList);

	}

	private function getAppCategotyList($info)
	{
		//获取该创意类型的广告位
		$appCategoryList = array();
		$params = array();
		$params['originality_type'] = $info['originality_type'];
		$adpos = MobgiApi_Service_AdAppModel::getAdAppAdDeverPos($params);

		if ($adpos) {
			if ($info['ad_target_type'] == 3) {
				$appCategoryList[1] = array('id' => 1, 'name' => 'Android', 'parent_id' => 0);
				$appCategoryList[2] = array('id' => 2, 'name' => 'Ios', 'parent_id' => 0);
			} else {
				$appCategoryList[$info['ad_target_type']] = array('id' => $info['ad_target_type'], 'name' => Common_Service_Const::$mPlatformDesc[$info['ad_target_type']], 'parent_id' => 0);
			}
			foreach ($appCategoryList as $val) {
				foreach ($adpos as $item) {
					if ($val['id'] == $item['platform']) {
						$posItems[] = array('id' => $item['dever_pos_key'], 'name' => $item['dever_pos_name'], 'parent_id' => $item['app_key']);
						$appItems[$item['app_key']] = array('id' => $item['app_key'], 'name' => $item['app_name'], 'parent_id' => $item['platform']);
					}
				}
			}

			foreach ($appCategoryList as $val) {
				if (!in_array($info['originality_type'], array(Common_Service_Const::VIDEO_AD_SUB_TYPE, Common_Service_Const::PIC_AD_SUB_TYPE))) {
					foreach ($posItems as $key => $posItem) {
						$appItems[$posItem['parent_id']]['childs'][] = $posItem;
						unset($posItems[$key]);
					}
				}
				foreach ($appItems as $key => $appItem) {
					$appCategoryList[$appItem['parent_id']]['childs'][] = $appItem;
					unset($appItems[$key]);
				}
			}

		}
		$this->assign('appCategoryList', $appCategoryList);
	}


	public function addAdStep3PostAction()
	{
		$info = $this->getInput(array('area_type', 'area_range', 'age_direct_type', 'age_direct_range',
			'sex_direct_type', 'os_direct_type', 'network_direct_type', 'network_direct_range',
			'operator_direct_type', 'operator_direct_range', 'brand_direct_type', 'brand_direct_range',
			'screen_direct_type', 'screen_direct_range', 'interest_direct_type', 'interest_direct_range',
			'pay_ability_type', 'pay_ability_range', 'game_frequency_type', 'game_frequency_range',
			'app_behavior_type', 'app_behavior_range',
			'charge_type', 'price', 'id', 'direct_id'
		));
		$info = $this->checkStep3Param($info);

		$uId = $this->userInfo['user_id'];
		$cache = Dedelivery_Service_AdConfListModel::getCache();
		$key = Dedelivery_Service_AdConfListModel::getAddAdStepKey(3, $uId, intval($info['id']));
		$result = $cache->set($key, $info, Dedelivery_Service_AdConfListModel::CACHE_EPIRE);
		$this->output(0, '操作成功');
	}

	public function addAdStep4Action()
	{

		$info = $this->getInput(array('id'));
		$cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS);
		$uId = $this->userInfo['user_id'];

		$cache = Dedelivery_Service_AdConfListModel::getCache();
		$key = Dedelivery_Service_AdConfListModel::getAddAdStepKey(1, $uId, intval($info['id']));
		$step1Reslut = $cache->get($key);
		$key = Dedelivery_Service_AdConfListModel::getAddAdStepKey(2, $uId, intval($info['id']));
		$step2Reslut = $cache->get($key);
		$key = Dedelivery_Service_AdConfListModel::getAddAdStepKey(3, $uId, intval($info['id']));
		$step3Reslut = $cache->get($key);
		$this->assign('step1Reslut', $step1Reslut);
		$this->assign('step2Reslut', $step2Reslut);
		$step3Reslut['area_range'] = $this->parseAreaConf($step3Reslut['area_range']);
		$this->assign('step3Reslut', $step3Reslut);


		$unitConfList = $this->getAdUnitListByDb(array('id' => $step1Reslut['unit_id']));
		$this->assign('unitConfList', $unitConfList);
		$config = Common::getConfig('deliveryConfig');
		foreach ($config as $key => $val) {
			$this->assign($key, $val);
		}

	}

	public function addAdStep4PostAction()
	{

		$info = $this->getInput(array('id'));
		$uId = $this->userInfo['user_id'];
		$cache = Dedelivery_Service_AdConfListModel::getCache();
		$key1 = Dedelivery_Service_AdConfListModel::getAddAdStepKey(1, $uId, intval($info['id']));
		$step1Reslut = $cache->get($key1);
		$key2 = Dedelivery_Service_AdConfListModel::getAddAdStepKey(2, $uId, intval($info['id']));
		$step2Reslut = $cache->get($key2);
		$key3 = Dedelivery_Service_AdConfListModel::getAddAdStepKey(3, $uId, intval($info['id']));
		$step3Reslut = $cache->get($key3);
		if (!$step1Reslut || !$step2Reslut || !$step3Reslut) {
			$this->output(1, '操作失败，请返回，重新创建');
		}

		$confData = $this->fillAdConfData($uId, $step1Reslut, $step2Reslut, $step3Reslut);

		$isupdate = false;
		if (intval($info['id']) && $step1Reslut['action'] != 'copy') {
			$isupdate = true;
			$oldconfData = Dedelivery_Service_AdConfListModel::getBy(array('id' => $info['id']));
			$oldoriginalintData = Dedelivery_Service_OriginalityRelationModel::getBy(array('id' => $step2Reslut['originality_id']));
		}

		//处理复制功能
//	    Common_Service_Base::beginTransaction();
		if (intval($info['id']) && $step1Reslut['action'] != 'copy') {
			$adid = Dedelivery_Service_AdConfListModel::updateById($confData, intval($info['id']));
		} else {
			$confData['status'] = 2;//审核中的状态
			$adid = Dedelivery_Service_AdConfListModel::add($confData);
		}
		//更新定向配置信息
		if ($confData['direct_id']) {
			$directConfData['direct_config'] = $confData['direct_config'];
			$directConfId = Advertiser_Service_DirectModel::updateDirect($directConfData, $confData['direct_id']);
		}

		//处理编辑与添加与复制
		$adid = ($info['id'] && $step1Reslut['action'] != 'copy') ? $info['id'] : $adid;
		$step2Reslut['unit_id'] = $step1Reslut['unit_id'];
		$originalintData = $this->fillOriginalintData($uId, $step2Reslut, $adid);
		if (intval($info['id']) && $step1Reslut['action'] != 'copy') {
			$originalintId = Dedelivery_Service_OriginalityRelationModel::update($originalintData, $step2Reslut['originality_id']);
		} else {
			$originalintData['status'] = 2;//审核中的状态
			$originalintId = Dedelivery_Service_OriginalityRelationModel::add($originalintData);
		}

		if (!$originalintId || !$adid) {
//	        Common_Service_Base::rollBack();
			$this->output(1, '操作失败' . $originalintId . ':' . $originalintId);
		}
//	   Common_Service_Base::commit();
		//清理缓存
		Dedelivery_Service_AdConfListModel::deleteAdConfKey($key1, $key2, $key3);
		$this->output(0, '操作成功', array('id' => $adid));
	}

	public function getAdUnitListByDb($params = array())
	{
		$params['account_id'] = $this->userInfo['user_id'];
		$result = Dedelivery_Service_UnitConfModel::getsBy($params);
		$adUnitList = Common::resetKey($result, 'id');
		return $adUnitList;
	}


	private function fillOriginalintData($uId, $info, $adid)
	{
		$data['originality_type'] = $info['originality_type'];
		$data['ad_sub_type'] = $info['ad_sub_type'];
		$data['strategy'] = $info['strategy'];
		$data['title'] = $info['title'];
		$data['desc'] = $info['desc'];
		$tmp = array();
		if ($info['originality_type'] == Common_Service_Const::PIC_AD_SUB_TYPE) {
			$tmp['cross_img'] = $info['cross_img'];
			$tmp['vertical_img'] = $info['vertical_img'];
			$tmp['icon'] = $info['icon'];
		} elseif ($info['originality_type'] == Common_Service_Const::VIDEO_AD_SUB_TYPE) {
			$tmp['h5'] = $info['h5'];
			$tmp['video'] = $info['video'];
			$tmp['icon'] = $info['icon'];
		} elseif ($info['originality_type'] == Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
			$tmp['cross_img'] = $info['cross_img'];
			$tmp['vertical_img'] = $info['vertical_img'];
			$tmp['icon'] = $info['icon'];
		} elseif ($info['originality_type'] == Common_Service_Const::SPLASH_AD_SUB_TYPE) {
			$tmp['cross_img'] = $info['cross_img'];
			$tmp['vertical_img'] = $info['vertical_img'];
			$tmp['icon'] = $info['icon'];
		} elseif ($info['originality_type'] == Common_Service_Const::ENBED_AD_SUB_TYPE) {//原生
			if ($info['ad_sub_type'] == Common_Service_Const::SINGLE_ENBED_AD_SUB_TYPE) {//单图
				$tmp['enbed_image_size'] = $info['enbed_image_size'];
				$tmp['single_img'] = $info['single_img'];
			} else if ($info['ad_sub_type'] == Common_Service_Const::COMBINATION_ENBED_AD_SUB_TYPE) {//组图
				$tmp['combination_img1'] = $info['combination_img1'];
				$tmp['combination_img2'] = $info['combination_img2'];
				$tmp['combination_img3'] = $info['combination_img3'];
				$tmp['enbed_image_size'] = $info['enbed_image_size'];
			}
			$tmp['icon'] = $info['icon'];
			$tmp['score'] = $info['score'];
			$tmp['action_text'] = $info['action_text'];
		}
		$data['upload_content'] = json_encode($tmp);
		$data['ad_id'] = $adid;
		$data['unit_id'] = $info['unit_id'];
		$data['account_id'] = $uId;
		return $data;
	}


	private function fillAdConfData($uId, $step1Reslut, $step2Reslut, $step3Reslut)
	{
		$data['ad_name'] = $step1Reslut['ad_name'];
		$data['originality_type'] = $step2Reslut['originality_type'];
		$data['ad_sub_type'] = $step2Reslut['ad_sub_type'];
		$data['ad_target_type'] = $step2Reslut['ad_target_type'];
		$data['ad_target'] = $step2Reslut['ad_target'];
		$data['package_name'] = $step2Reslut['package_name'];
		$data['app_name'] = $step2Reslut['app_name'];
		$data['jump_type'] = $step2Reslut['jump_type'];
		$data['imp_trackers'] = json_encode(array($step2Reslut['imp_trackers']));
		$data['click_trackers'] = json_encode(array($step2Reslut['click_trackers']));
		$data['unit_id'] = $step1Reslut['unit_id'];
		$tmp = array();
		$tmp['start_date'] = $step1Reslut['start_date'];
		$tmp['end_date'] = $step1Reslut['end_date'];
		$data['date_range'] = json_encode($tmp);
		$data['time_type'] = $step1Reslut['time_type'];
		$data['hour_set_type'] = $step1Reslut['hour_set_type'];
		$tmp = array();
		$tmp['start_time'] = $step1Reslut['start_time'];
		$tmp['end_time'] = $step1Reslut['end_time'];
		$data['time_range'] = json_encode($tmp);
		$data['time_series'] = $step1Reslut['time_series'];
		$data['charge_type'] = $step3Reslut['charge_type'];
		$data['price'] = $step3Reslut['price'];
		$data['account_id'] = $uId;
		$data['direct_id'] = $step3Reslut['direct_id'];
		$data['direct_config'] = json_encode($this->fillDiRectConf($step3Reslut));
		$upload_info = array();
		if ($step2Reslut['ad_target_type'] == 1 && in_array($step2Reslut['jump_type'], array(7, 0))) {
			$upload_info['fileName'] = $step2Reslut['fileName'];
			$upload_info['fileType'] = $step2Reslut['fileType'];
			$upload_info['fileSize'] = $step2Reslut['fileSize'];
			$upload_info['progress'] = $step2Reslut['progress'];
			$upload_info['uploadVal'] = $step2Reslut['uploadVal'];
		}
		$data['upload_info'] = json_encode($upload_info);
		$data['deeplink'] = $step2Reslut['deeplink'];
		$data['frequency_type'] = $step1Reslut['frequency_type'];
		$data['frequency'] = $step1Reslut['frequency'];
		$data['ad_limit_type'] = $step1Reslut['ad_limit_type'];
		$data['ad_limit_amount'] = $step1Reslut['ad_limit_amount'];
		return $data;
	}

	private function fillDiRectConf($ditectConfData)
	{
		$data['area_type'] = $ditectConfData['area_type'];
		if (isset($ditectConfData['area_range'])) {
			$data['area_range'] = $ditectConfData['area_range'];
		}
		$data['age_direct_type'] = $ditectConfData['age_direct_type'];
		if (isset($ditectConfData['age_direct_range'])) {
			$data['age_direct_range'] = $ditectConfData['age_direct_range'];
		}
		$data['sex_direct_type'] = $ditectConfData['sex_direct_type'];
		$data['os_direct_type'] = $ditectConfData['os_direct_type'];
		$data['network_direct_type'] = $ditectConfData['network_direct_type'];
		if (isset($ditectConfData['network_direct_range'])) {
			$data['network_direct_range'] = $ditectConfData['network_direct_range'];
		}
		$data['operator_direct_type'] = $ditectConfData['operator_direct_type'];
		if (isset($ditectConfData['operator_direct_range'])) {
			$data['operator_direct_range'] = $ditectConfData['operator_direct_range'];
		}
		$data['brand_direct_type'] = $ditectConfData['brand_direct_type'];
		if (isset($ditectConfData['brand_direct_range'])) {
			$data['brand_direct_range'] = $ditectConfData['brand_direct_range'];
		}
		$data['screen_direct_type'] = $ditectConfData['screen_direct_type'];
		if (isset($ditectConfData['screen_direct_range'])) {
			$data['screen_direct_range'] = json_encode($ditectConfData['screen_direct_range']);
		}
		$data['interest_direct_type'] = $ditectConfData['interest_direct_type'];
		if (isset($ditectConfData['interest_direct_range'])) {
			$data['interest_direct_range'] = $ditectConfData['interest_direct_range'];
		}
		$data['pay_ability_type'] = $ditectConfData['pay_ability_type'];
		if (isset($ditectConfData['pay_ability_range'])) {
			$data['pay_ability_range'] = $ditectConfData['pay_ability_range'];
		}
		$data['game_frequency_type'] = $ditectConfData['game_frequency_type'];
		if (isset($ditectConfData['game_frequency_range'])) {
			$data['game_frequency_range'] = $ditectConfData['game_frequency_range'];
		}
		$data['app_behavior_type'] = $ditectConfData['app_behavior_type'];
		if (isset($ditectConfData['app_behavior_range'])) {
			$data['app_behavior_range'] = $ditectConfData['app_behavior_range'];

		}
		return $data;
	}

	private function checkStep3Param($info)
	{
		if (!isset($info['area_type'])) {
			$this->output(1, '地域没有选中');
		}
		if ($info['area_type'] == '1' && !isset($info['area_range'])) {
			$this->output(1, '地域的定向没有选中');
		}
		if (!isset($info['age_direct_type'])) {
			$this->output(1, '年龄没有选中');
		}
		if ($info['age_direct_type'] == '1' && !isset($info['age_direct_range'])) {
			$this->output(1, '年龄的定向没有选中');
		}
		if (!isset($info['sex_direct_type'])) {
			$this->output(1, '性别没有选中');
		}
		if (!isset($info['os_direct_type'])) {
			$this->output(1, '操作系统没有选中');
		}
		if (!isset($info['network_direct_type'])) {
			$this->output(1, '网络环境没有选中');
		}
		if ($info['network_direct_type'] == '1' && !isset($info['network_direct_range'])) {
			$this->output(1, '网络环境的定向没有选中');
		}
		if (!isset($info['operator_direct_type'])) {
			$this->output(1, '运营商没有选中');
		}
		if ($info['operator_direct_type'] == '1' && !isset($info['operator_direct_range'])) {
			$this->output(1, '运营商的定向没有选中');
		}
		if (!isset($info['brand_direct_type'])) {
			$this->output(1, '手机品牌没有选中');
		}
		if ($info['brand_direct_type'] == '1' && !isset($info['brand_direct_range'])) {
			$this->output(1, '手机品牌的定向没有选中');
		}
		if (!isset($info['screen_direct_type'])) {
			$this->output(1, '屏幕大小没有选中');
		}
		if ($info['screen_direct_type'] == '1' && !isset($info['screen_direct_range'])) {
			$this->output(1, '屏幕大小的定向没有选中');
		}
		if (!isset($info['interest_direct_type'])) {
			$this->output(1, '游戏兴趣没有选中');
		}
		if ($info['interest_direct_type'] == '1' && !isset($info['interest_direct_range'])) {
			$this->output(1, '游戏兴趣的定向没有选中');
		}
		if (!isset($info['pay_ability_type'])) {
			$this->output(1, '付费能力没有选中');
		}
		if ($info['pay_ability_type'] == '1' && !isset($info['pay_ability_range'])) {
			$this->output(1, '付费能力的定向没有选中');
		}
		if (!isset($info['game_frequency_type'])) {
			$this->output(1, '游戏频率没有选中');
		}
		if ($info['game_frequency_type'] == '1' && !isset($info['game_frequency_range'])) {
			$this->output(1, '游戏频率的定向没有选中');
		}
		if (!isset($info['charge_type']) || $info['charge_type'] == '') {
			$this->output(1, '计费类型没有选中');
		}
		if (!is_numeric($info['price']) || $info['price'] <= 0) {
			$this->output(1, '出价为大于零数字类型');
		}

		$info['price'] = trim($info['price']);
		return $info;
	}

	private function checkStep2Param($info)
	{
		if (intval($info['ad_target_type']) < 1) {
			$this->output(1, '广告目标没有选中');
		}
		if (!trim($info['ad_target'])) {
			$this->output(1, '链接地址不能为空');
		}
		if (!preg_match('/^(http|https)/i', $info['ad_target'])) {
			$this->output(1, '链接地址不规范,以http,https开头');
		}
		if (empty(intval($info['originality_type']))) {
			$this->output(1, '创意类型没有选中');
		}
		$info['package_name'] = trim($info['package_name']);
		if (in_array($info['ad_target_type'], array(1, 2)) && empty($info['package_name'])) {
			$this->output(1, '请输入包名');
		}
		$info['ad_target'] = trim($info['ad_target']);
		$jumpTypeConfig = Common::getConfig('deliveryConfig', 'jumpType');
		if (!isset($jumpTypeConfig[$info['ad_target_type']][$info['jump_type']])) {
			$this->output(1, '请选择正确的跳转后动作');
		}
		#jump_type为7表示安桌通知类下载
		if ($info['ad_target_type'] == 1 && empty($info['app_name'])) {
			$this->output(1, '请填写应用名称');

		}
		if (in_array($info['ad_target_type'], array(2, 3)) && in_array($info['originality_type'], array(3, 5)) && empty($info['app_name'])) {
			$this->output(1, '请填写应用名称');

		}

		if ($info['ad_target_type'] == 1 && mb_strlen($info['app_name']) > 14) {
			$this->output(1, '应用名称不超过14个汉字');
		}

		if (in_array($info['ad_target_type'], array(2, 3)) && in_array($info['originality_type'], array(3, 5)) && mb_strlen($info['app_name']) > 14) {
			$this->output(1, '应用名称不超过14个汉字');
		}


		if ($info['imp_trackers']) {
			if (!preg_match('/^(http|https)/i', $info['imp_trackers'])) {
				$this->output(1, '曝光监控地址不规范,须以http,https开头');
			}
		}

		//原生广告校验
		if ($info['originality_type'] == Common_Service_Const::ENBED_AD_SUB_TYPE) {
			if (empty($info['app_name'])) {
				$this->output(1, '原生广告请填写应用名称');
			}
			if (empty($info['ad_sub_type'])) {
				$this->output(1, '请选择展示形式');
			}
			if (!in_array($info['ad_sub_type'], array(51, 52))) {
				$this->output(1, '请选择正确的原生广告的展示形式');
			}
			if (empty($info['score'])) {
				$this->output(1, '请选择填写评分');
			}
			if (!is_numeric($info['score']) || intval($info['score']) < 0 || intval($info['score']) > 10 || $info['score'] != intval($info['score'])) {
				$this->output(1, '评分请填写1到10的正整数');
			}
			if (empty($info['action_text'])) {
				$this->output(1, '请选择填写行动语');
			}
			if (mb_strlen($info['action_text']) > 5) {
				$this->output(1, '	不超过5个汉字');
			}
			if (empty($info['desc'])) {
				$this->output(1, '原生广告请填写创意描述(推广文案)');
			}
			if ($info['ad_sub_type'] == 51) {
				if (empty($info['single_img'])) {
					$this->output(1, '请上传单图图片');
				}
			} else if ($info['ad_sub_type'] == 52) {
				if (empty($info['combination_img1']) || empty($info['combination_img2']) || empty($info['combination_img3'])) {
					$this->output(1, '请上传组图图片');
				}
			}
			if (empty($info['enbed_image_size'])) {
				$this->output(1, '原生广告尺寸参数错误');
			}
		}
		if ($info['click_trackers']) {
			if (!preg_match('/^(http|https)/i', $info['click_trackers'])) {
				$this->output(1, '点击监控地址不规范,须以http,https开头');
			}
		}

		if (intval($info['strategy']) < 1) {
			$this->output(1, '创意曝光策略没有选中');
		}
		if (!trim($info['title'])) {
			$this->output(1, '创意标题不能为空');
		}
		$info['title'] = trim($info['title']);
		if (!trim($info['desc'])) {
			$this->output(1, '创意描述不能为空');
		}

		if (mb_strlen($info['desc']) > 30) {
			$this->output(1, '创意描述不能大于30个字符');
		}

		$info['desc'] = trim($info['desc']);


		if (in_array($info['ad_target_type'], array(2, 3)) && in_array($info['originality_type'], array(3, 5)) && empty($info['icon'])) {
			$this->output(1, 'icon没有上传');
		}

		if (($info['originality_type'] == Common_Service_Const::PIC_AD_SUB_TYPE
				|| $info['originality_type'] == Common_Service_Const::CUSTOME_AD_SUB_TYPE
				|| $info['originality_type'] == Common_Service_Const::SPLASH_AD_SUB_TYPE)
			&& (!$info['cross_img'] || !$info['vertical_img'])) {
			$this->output(1, '创意横屏或竖屏图片没有上传');
		}

		if ($info['originality_type'] == Common_Service_Const::VIDEO_AD_SUB_TYPE && (!$info['video'] || !$info['h5'])) {
			$this->output(1, '创意视频文件或H5文件没有上传');
		}
		return $info;
	}


	private function checkAdStep1Param($info)
	{

		if (intval($info['unit_id']) < 1) {
			$this->output(1, '投放单元没有选中');
		}
		if ($info['time_type'] != '0' && $info['time_type'] != '1') {
			$this->output(1, '设置投放时段没有选中');
		}
		if (!trim($info['ad_name'])) {
			$this->output(1, '广告名称不能为空');
		}
		if (Common::strLength($info['ad_name']) >= 30) {
			$this->output(1, '广告名称不能太长');
		}
		$info['ad_name'] = trim($info['ad_name']);
		if (strtotime($info['start_date']) > strtotime($info['end_date'])) {
			$this->output(1, '开始时间不能小于结束时间');
		}
		if ($info['time_type'] == 1) {
			if (($info['hour_set_type'] == 0) && (strcmp($info['start_time'], $info['end_time']) > 0)) {
				$this->output(1, '开始时间不能早于结束时间');
			}
			if (($info['hour_set_type'] == 1) && $info['time_series'] == '') {
				$this->output(1, '请选择详细时间区段');
			}
		}

		if ($info['frequency_type']) {
			if (!is_numeric($info['frequency']) || intval($info['frequency'] <= 0)) {
				$this->output(1, '频次控制次数请填写正整数');
			}
			$info['frequency'] = intval($info['frequency']);
		}

		if ($info['ad_limit_type']) {
			if (!is_numeric($info['ad_limit_amount']) || intval($info['ad_limit_amount'] <= 0)) {
				$this->output(1, '广告限额请填写正整数');
			}
			$params['id'] = $info['unit_id'];
			$ret = Dedelivery_Service_UnitConfModel::getBy($params);

			if($ret['limit_range']<=$info['ad_limit_amount']){
				$this->output(1, '广告限额不能大于广告计划的限额：'.$ret['limit_range']);
			}
			$info['ad_limit_amount'] = intval($info['ad_limit_amount']);
		}

		return $info;
	}

	public function getUnitInfoAction()
	{
		$unitId = $this->getInput('unitId');
		if (!$unitId) {
			$this->output(1, '非法操作');
		}
		$params['id'] = $unitId;
		$params['account_id'] = $this->userInfo['user_id'];
		$ret = Dedelivery_Service_UnitConfModel::getBy($params);
		if (!$ret) {
			$this->output(1, '非法操作');
		}
		$this->output(0, '操作成功', $ret);
	}

	/**
	 * 增加投放单元
	 */
	public function addUnitAction()
	{
		$info = $this->getInput(array('name', 'limit_type', 'limit_range', 'mode_type', 'unit_id', 'unit_type'));

		$this->checkUnitName($info);
		$this->checkUnitLimit($info['limit_type'], $info['limit_range'], 1, $info['unit_id']);
		if (!$info['mode_type']) {
			$this->output(1, '投放方式没有选中');
		}
		if (!$info['unit_type']) {
			$this->output(1, '请选择是否内部订单');
		}
		//判断是否重复
		//编辑
		if ($info['unit_id']) {
			$params['id'] = array('<>', $info['unit_id']);
		}
		$params['name'] = trim($info['name']);
		$params['account_id'] = $this->userInfo['user_id'];
		$ret = Dedelivery_Service_UnitConfModel::getBy($params);
		if ($ret) {
			$this->output(1, '投放单元名称不能重复');
		}
		$info['account_id'] = $this->userInfo['user_id'];
		if ($info['unit_id']) {
			if ($info['limit_type'] == 0) {
				$info['limit_range'] = 0;
			}
			$result = Dedelivery_Service_UnitConfModel::updateByID($info, $info['unit_id']);
		} else {
			$result = Dedelivery_Service_UnitConfModel::add($info);
		}
		if (!$result) {
			$this->output(1, '操作失败');
		}

		/*操作日志start*/
		$this->mOperateData = $result . ',' . $info['name'] . ',' . $info['limit_type'] . ',' . $info['limit_range'] . ',' . $info['mode_type'];
		$this->addOperateLog();
		/*操作日志end*/

		$this->output(0, '操作成功', array('id' => $result, 'name' => $info['name']));
	}

	private function checkUnitName($info)
	{
		if (!trim($info['name'])) {
			$this->output(1, '投放单元名称不能为空');
		}
		if (Common::strLength($info['name']) >= 30) {
			$this->output(1, '投放单元名称长度太长');
		}
	}


	/**
	 *
	 * Enter description here ...
	 */
	public function uploadApkAction()
	{
		$apkId = $this->getInput('apkId');
		$this->assign('apkId', $apkId);
		$this->getView()->display('common/uploadApk.phtml');
		exit;
	}

	/**
	 *
	 * Enter description here ...
	 */
	public function uploadApkPostAction()
	{
		$ret = Common::upload('apk', 'delivery', array('maxSize' => 512000, 'allowFileType' => array('apk', 'APK')));
		if ($ret['code'] == 0 && $ret['data']) {
			$attachPath = Common::getConfig('siteConfig', 'attachPath');
			$filePath = $attachPath . $ret['data'];
			$info = Apk_Service_Aapt::info($filePath);
			$package = $info['package'];
			$this->assign('package', $package);
		}
		//Apk_Service_Aapt::info($file);
		$this->assign('code', $ret['data']);
		$this->assign('msg', $ret['msg']);
		$this->assign('data', $ret['data']);
		$apkId = $this->getInput('apkId');
		$this->assign('apkId', $apkId);
		$this->getView()->display('common/uploadApk.phtml');
		exit;
	}

	/**
	 *
	 * Enter description here ...
	 */
	public function breakpointuploadApkAction()
	{
		$apkId = $this->getInput('apkId');
		$fileName = $this->getInput("fileName");
		$fileType = $this->getInput("fileType");
		$fileSize = $this->getInput("fileSize");
		$progress = $this->getInput("progress");
		$uploadVal = $this->getInput("uploadVal");
		$this->assign('apkId', $apkId);
		if ($fileName && $fileType && $fileSize && $progress && $uploadVal) {
			$this->assign('isShow', 1);
			$this->assign('fileName', $fileName);
			$this->assign('fileType', $fileType);
			$this->assign('fileSize', $fileSize);
			$this->assign('progress', $progress);
			$this->assign('uploadVal', $uploadVal);
		} else {
			$this->assign('isShow', 0);
		}
		$this->getView()->display('common/breakpointupload.phtml');
		exit;
	}

	/**
	 *
	 * Enter description here ...
	 */
	public function breakpointuploadApkPostAction()
	{
//        header('Content-type: text/plain; charset=utf-8');
		$fileName = $_REQUEST['fileName'];
		$totalSize = $_REQUEST['totalSize'];
		$isLastChunk = $_REQUEST['isLastChunk'];
		$isFirstUpload = $_REQUEST['isFirstUpload'];
		$apkId = $_REQUEST['apkId'];
		$uploadParams = array();
		$uploadParams['totalSize'] = $totalSize;
		$uploadParams['isLastChunk'] = $isLastChunk;
		$uploadParams['isFirstUpload'] = $isFirstUpload;
		$uploadParams['allowFileType'] = array('apk', 'APK');
		$ret = common::breakpointupload('theFile', $fileName, 'breakpointuploadtmp', 'delivery', $uploadParams);
		if ($ret['code'] == 0 && $ret['data']['filepath']) {
			$attachPath = Common::getConfig('siteConfig', 'attachPath');
			$filePath = $attachPath . $ret['data']['filepath'];
			$info = Apk_Service_Aapt::info($filePath);
			$ret['data']['package'] = $info['package'];
			$ret['data']['urlpath'] = Common::getAttachPath() . $ret['data']['filepath'];
			$ret['data']['apkid'] = $apkId;
		}
		$this->output($ret['code'], $ret['msg'], $ret['data']);
	}


	public function uploadImgAction()
	{
		$imgId = $this->getInput('imgId');
		$originalityType = $this->getInput('originality_type');
		$this->assign('originalityType', $originalityType);
		$this->assign('imgId', $imgId);
		$this->getView()->display('common/upload.phtml');
		exit;
	}


	public function uploadImgPostAction()
	{
		$originalityType = $this->getInput('originality_type');
		$imgId = $this->getInput('imgId');
		$tmpimage = $_FILES['img']['tmp_name'];
		$isUpload = true;
		$allowFileType　 = array('gif', 'jpeg', 'jpg', 'png', 'bmp');
		$maxSize = 2048;//单位为K

		list($width, $height) = getimagesize($tmpimage);
		$size = $this->getMyImageSize($width, $height);

		//icon的尺寸验证
		if ($imgId == 'iconImg') {
			$allowFileType　 = array('png');
			$maxSize　 = 300;
			$resolution = array('150*150');
			if ($originalityType == 3) {
				$resolution = array('210*210');
			}
		}
		//横屏的尺寸验证
		if ($imgId == 'crossImg') {
			$allowFileType　 = array('png', 'jpg');
			$maxSize　 = 300;
			if ($originalityType == 2) {
				$resolution = array('960*640');
			} elseif ($originalityType == 3) {
				$resolution = array('1250*350');
			} elseif ($originalityType == 4) {
				$resolution = array('1280*720');
			}
		}
		//竖屏的尺寸验证
		if ($imgId == 'verticalImg') {
			$allowFileType　 = array('png', 'jpg');
			$maxSize　 = 300;
			if ($originalityType == 2) {
				$resolution = array('640*960');
			} elseif ($originalityType == 3) {
				$resolution = array('920*450');
			} elseif ($originalityType == 4) {
				$resolution = array('720*1280');
			}

		}
		//原生图片需要检测尺寸是否符合规定
		if (in_array($imgId, array('singleEnbedImg', 'combinationEnbedImg1', 'combinationEnbedImg2', 'combinationEnbedImg3'))) {
			$allowFileType　 = array('png', 'jpg');
			$maxSize　 = 300;
			$resolution = Common_Service_Const::$mEnbedSize;
		}
		$parmas = array(
			'allowFileType' => $allowFileType　,
			'maxSize' => $maxSize,
			'resolution' => $resolution
		);
		$ret = Common::upload('img', 'delivery', $parmas);

		$this->assign('originalityType', $originalityType);
		$this->assign('code', $ret['data']);
		$this->assign('msg', $ret['msg']);
		$this->assign('data', $ret['data']);
		$this->assign('fileType', 'img');
		$this->assign('imgId', $imgId);
		$this->assign('width', $width);
		$this->assign('height', $height);
		$this->assign('size', $size);
		$this->getView()->display('common/upload.phtml');
		exit;
	}

	/**
	 * 获取图片的比例
	 * @param type $width
	 * @param type $height
	 */
	private function getMyImageSize($width, $height)
	{
		$ret = $this->getGreatestCommonDivisorMultiple($width, $height);
		$divisor = $ret['divisor'];
		$size = $width / $divisor . ":" . $height / $divisor;
		return $size;
	}

	/**
	 * 辗转相除法求最大公约数及最小公倍数
	 * @param type $a
	 * @param type $b
	 */
	private function getGreatestCommonDivisorMultiple($a, $b)
	{
		//将初始值保存起来
		$i = $a;
		$j = $b;
		//辗转相除法求最大公约数
		while ($b <> 0) {
			$p = $a % $b;
			$a = $b;
			$b = $p;
		}
		$result = array();
		$result['divisor'] = $a;
		$result['multiple'] = $i * $j / $a;
		return $result;
//        echo "最大公约数是：" . $a . "<br />";
//        echo "最小公倍数是：" . $i * $j / $a;
	}

	public function uploadOtherAction()
	{
		$otherId = $this->getInput('otherId');
		$this->assign('otherId', $otherId);
		$this->getView()->display('common/uploadOther.phtml');
		exit;
	}


	public function uploadOtherPostAction()
	{
		$ret = Common::upload('other', 'delivery', array('maxSize' => 512000, 'allowFileType' => array('mp4', 'rar', 'zip')));
		$otherId = $this->getInput('otherId');
		$this->assign('code', $ret['data']);
		$this->assign('msg', $ret['msg']);
		$this->assign('data', $ret['data']);
		$this->assign('otherId', $otherId);
		$this->getView()->display('common/uploadOther.phtml');
		exit;
	}

	//批量单元管理操作
	function batchUpdateUnitInfoAction()
	{
		$info = $this->getPost(array('action', 'ids', 'value'));
		if (!count($info['ids'])) $this->output(-1, '没有可操作的项.');

		$unitparam = array();
		$unitparam['id'] = array('IN', $info['ids']);
		$unitsInfo = $this->getAdUnitListByDb($unitparam);
		if ($info['action'] == 'del') {
			$params['unit_id'] = array('IN', $info['ids']);
			$params['del'] = Common_Service_Const::NOT_DELETE_FLAG;
			$ret = Dedelivery_Service_AdConfListModel::getsBy($params);
			if (count($ret)) {
				$this->output(1, '选择投放单元有广告正在使用中.');
			}
			$data['del'] = Common_Service_Const::DELETE_FLAG;
			$data['status'] = 3;
			$ret = Dedelivery_Service_UnitConfModel::updateBy($data, array('id' => array('IN', $info['ids'])));
			//$ret = Dedelivery_Service_UnitConfModel::deleteBy();
			if (!$ret) $this->output('-1', '操作失败.');
			/*操作日志start*/
			$logdata['module'] = 'adver_delivery';
			$logdata['sub_module'] = 'del_unit';
			$logdata['content'] = '';
			foreach ($unitsInfo as $unit) {
				$logdata['content'] .= $unit['id'] . ',' . $unit['name'] . ';';
			}
			$this->addOperatelog($logdata);
			/*操作日志end*/
			$this->output(0, '操作成功');
		} elseif ($info['action'] == 'status') {
			$params['id'] = array('IN', $info['ids']);
			$data['status'] = intval($info['value']);
			$ret = Dedelivery_Service_UnitConfModel::updateBy($data, $params);
		} elseif ($info['action'] == 'limit') {
			$params['id'] = array('IN', $info['ids']);
			$tmp = explode('_', $info['value']);
			$data['limit_type'] = $tmp[0];
			$data['limit_range'] = $tmp[1];
			foreach ($info['ids'] as $unitId) {
				$this->checkUnitLimit($data['limit_type'], $data['limit_range'], 0, $unitId);
			}
			$ret = Dedelivery_Service_UnitConfModel::updateBy($data, $params);
		} elseif ($info['action'] == 'modeType') {
			$params['id'] = array('IN', $info['ids']);
			$data['mode_type'] = intval($info['value']);
			$ret = Dedelivery_Service_UnitConfModel::updateBy($data, $params);
		}

		/*操作日志start*/
		$logdata = array();
		$logdata['module'] = 'adver_delivery';
		$logdata['sub_module'] = 'edit_unit';
		$logdata['content'] = '';
		foreach ($unitsInfo as $unit) {
			if ($info['action'] == 'status') {
				$old = 'status:' . $unit['status'];
				$new = 'status:' . $data['status'];
			} else if ($info['action'] == 'limit') {
				$old = 'limit_type:' . $unit['limit_type'] . ',limit_range:' . $unit['limit_range'];
				$new = 'limit_range:' . $data['limit_type'] . ',limit_range:' . $data['limit_range'];
			} else if ($info['action'] == 'modeType') {
				$old = 'mode_type:' . $unit['mode_type'];
				$new = 'mode_type:' . $data['mode_type'];;
			}
			$logdata['content'] .= $unit['id'] . ',' . $unit['name'] . "," . $old . ',' . $new . ';';
		}
		$this->addOperatelog($logdata);
		/*操作日志end*/

		if (!$ret) $this->output('-1', '操作失败.');
		$this->output('0', '操作成功.');
	}

	private function checkUnitLimit($limitType, $limitRange, $isAdd = 0, $unitId)
	{
		if ($limitType == '1' && $limitRange <= $this->unitLimitAmount) {
			$this->output(1, '投放限额' . $this->unitLimitAmount . '以上');
		}
		$uId = $this->userInfo['user_id'];
		$todayConsume = Advertiser_Service_UnitDayConsumeModel::getUnitConsumption($unitId);
		if ($limitType == '1' && ($limitRange - $todayConsume <= 50) && $isAdd == 0) {
			$this->output(1, '投放限额与今日消费要大于50以上');
		}
	}


	//批量广告管理操作
	function batchUpdateAdInfoAction()
	{

		$info = $this->getPost(array('action', 'ids', 'value'));
		if (!count($info['ids'])) $this->output(-1, '没有可操作的项.');

		$adparam = array();
		$adparam['id'] = array('IN', $info['ids']);
		$adsInfo = Dedelivery_Service_AdConfListModel::getsBy($adparam);
		if ($info['action'] == 'del') {
			$params['account_id'] = $this->userInfo['user_id'];
			$params['id'] = array('IN', $info['ids']);
			$ret = Dedelivery_Service_AdConfListModel::getsBy($params);
			foreach ($ret as $val) {
				if ($val['del'] == Common_Service_Const::DELETE_FLAG) {
					$this->output('-1', '选择已经删除.');
				}
				if ($val['account_id'] != $this->userInfo['user_id']) {
					$this->output('-1', '选择投放单元非法操作.');
				}
			}
			//删除状态
			//$data['status'] = 5;
			$data['del'] = Common_Service_Const::DELETE_FLAG;
			Common_Service_Base::beginTransaction();
			$ret = Dedelivery_Service_AdConfListModel::updateBy($data, $params);
			$ret2 = Dedelivery_Service_OriginalityRelationModel::updateBy($data, array('ad_id' => array('IN', $info['ids']), 'account_id' => $this->userInfo['user_id']));
			if (!$ret || !$ret2) {
				Common_Service_Base::rollBack();
				$this->output('-1', '操作失败.');
			}
			Common_Service_Base::commit();
			/*操作日志start*/
			$logdata['module'] = 'adver_delivery';
			$logdata['sub_module'] = 'del_ad';
			$logdata['content'] = '';
			foreach ($adsInfo as $ad) {
				$unitinfo = Dedelivery_Service_UnitConfModel::getBy(array('id' => $ad['unit_id']));
				$logdata['content'] .= $ad['id'] . ',' . $ad['ad_name'] . ',unitname:' . $unitinfo['name'] . ';';
			}
			$this->addOperatelog($logdata);
			/*操作日志end*/
			$this->output('0', '操作成功.');
		} elseif ($info['action'] == 'status') {
			$params['id'] = array('IN', $info['ids']);
			$data['status'] = intval($info['value']);
			foreach ($info['ids'] as $val) {
				$result = Dedelivery_Service_AdConfListModel::getBy(array('id' => $val,
					'status' => array('IN', array_keys($this->modifyStatus)),
					'del' => Common_Service_Const::NOT_DELETE_FLAG));
				if (!$result) {
					$this->output(1, '广告状态只能在投放中，暂停才能修改');
				}
			}
			$ret = Dedelivery_Service_AdConfListModel::updateBy($data, $params);
		} elseif ($info['action'] == 'price') {
			$ret = Dedelivery_Service_AdConfListModel::getsBy(array('id' => array('IN', $info['ids']), 'status' => 5));
			if ($ret) {
				$this->output(1, '广告删除状态，不能修改价格');
			}
			$params['id'] = array('IN', $info['ids']);
			$data['price'] = $info['value'];
			if (!is_numeric($info['value']) || $info['value'] <= 0) {
				$this->output(1, '出价为大于零数字类型');
			}
			$ret = Dedelivery_Service_AdConfListModel::updateBy($data, $params);
		} elseif ($info['action'] == 'modeType') {
			$params['id'] = array('IN', $info['ids']);
			$data['mode_type'] = intval($info['value']);
			$ret = Dedelivery_Service_UnitConfModel::updateBy($data, $params);
		}
		if (!$ret) $this->output('-1', '操作失败.');

		/*操作日志start*/
		$logdata = array();
		$logdata['module'] = 'adver_delivery';
		$logdata['sub_module'] = 'edit_ad';
		$logdata['content'] = '';
		foreach ($adsInfo as $ad) {
			$unitinfo = Dedelivery_Service_UnitConfModel::getBy(array('id' => $ad['unit_id']));
			$logdata['content'] .= $ad['id'] . ',' . $ad['ad_name'] . ',unitname:' . $unitinfo['name'] . '';
			if ($info['action'] == 'status') {
				$old = 'status:' . $ad['status'];
				$new = 'status:' . $data['status'];
			} else if ($info['action'] == 'price') {
				$old = 'price:' . $ad['price'];
				$new = 'price:' . $data['price'];
			} else if ($info['action'] == 'modeType') {
				$old = 'mode_type:' . $ad['mode_type'];
				$new = 'mode_type:' . $data['mode_type'];;
			}
			$logdata['content'] .= "," . $old . ',' . $new . ';';
		}
		$this->addOperatelog($logdata);
		/*操作日志end*/

		$this->output('0', '操作成功.');
	}

	//批量创意操作
	public function batchUpdateOriginalityAction()
	{
		$info = $this->getPost(array('action', 'ids', 'value'));
		if (!count($info['ids'])) $this->output(-1, '没有可操作的项.');

		$Originalityparam = array();
		$Originalityparam['id'] = array('IN', $info['ids']);
		$Originalitys = Dedelivery_Service_OriginalityRelationModel::getsBy($Originalityparam);
		if ($info['action'] == 'del') {
			$params['id'] = array('IN', $info['ids']);
			$originalityConf = Dedelivery_Service_OriginalityRelationModel::getsBy($params);
			foreach ($originalityConf as $val) {
				if ($val['del'] == Common_Service_Const::DELETE_FLAG) {
					$this->output('-1', '选择投放单元已经删除.');
				}
				if ($val['account_id'] != $this->userInfo['user_id']) {
					$this->output('-1', '选择投放单元非法操作.');
				}
			}
			//$data['status'] = 5;

			$adIds = array_keys(Common::resetKey($originalityConf, 'ad_id'));
			if ($adIds) {
				$adConfParams['id'] = array('IN', $adIds);
				$adConfParams['status'] = Dedelivery_Service_AdConfListModel::OPEN_STATUS;
				$adConfParams['del'] = Common_Service_Const::NOT_DELETE_FLAG;
				$ret = Dedelivery_Service_AdConfListModel::getBy($adConfParams);
				if ($ret) {
					$this->output(1, '选择投放单元有广告正在使用中.');
				}
			}
			$data['del'] = Common_Service_Const::DELETE_FLAG;
			$ret = Dedelivery_Service_OriginalityRelationModel::updateBy($data, array('id' => array('IN', $info['ids'])));
			if (!$ret) $this->output('-1', '操作失败.');

			/*操作日志start*/
			$logdata['module'] = 'adver_delivery';
			$logdata['sub_module'] = 'del_originality';
			$logdata['content'] = '';
			foreach ($Originalitys as $Originality) {
				$adparams = array();
				$adparams['id'] = intval($Originality['ad_id']);
				$adinfo = Dedelivery_Service_AdConfListModel::getBy($adparams);
				$logdata['content'] .= $Originality['id'] . ',' . $Originality['title'] . ',ad_id:' . $Originality['ad_id'] . ',ad_name:' . $adinfo['ad_name'] . ';';
			}
			$this->addOperatelog($logdata);
			/*操作日志end*/

			$this->output('0', '操作成功.');

		} elseif ($info['action'] == 'status') {
			$params['id'] = array('IN', $info['ids']);
			$data['status'] = intval($info['value']);
			foreach ($info['ids'] as $val) {
				$result = Dedelivery_Service_OriginalityRelationModel::getBy(array('id' => $val, 'status' => array('IN', array_keys($this->modifyStatus)), 'del' => Common_Service_Const::NOT_DELETE_FLAG));
				if (!$result) {
					$this->output(1, '创意状态只能在投放中，暂停才能修改');
				}
			}
			$ret = Dedelivery_Service_OriginalityRelationModel::updateBy($data, $params);
			if (!$ret) $this->output('-1', '操作失败.');

			/*操作日志start*/
			$logdata = array();
			$logdata['module'] = 'adver_delivery';
			$logdata['sub_module'] = 'edit_originality';
			$logdata['content'] = '';
			foreach ($Originalitys as $Originality) {
				$logdata['content'] .= $Originality['id'] . ',' . $Originality['title'] . ',' . 'status' . ':' . $Originality['status'] . ',status:' . $data['status'] . ';';
			}
			$this->addOperatelog($logdata);
			/*操作日志end*/
		}

		$this->output('0', '操作成功.');
	}

	public function addOriginalityPostAction()
	{
		$inputInfo = $this->getInput(array('strategy', 'title', 'desc', 'cross_img', 'vertical_img', 'full_img', 'video', 'h5', 'ad_id', 'icon',
			'score', 'action_text', 'enbed_image_size', 'single_img', 'combination_img1', 'combination_img2', 'combination_img3'));
		$adInfo = Dedelivery_Service_AdConfListModel::getBy(array('id' => intval($inputInfo['ad_id'])));
		if (empty($adInfo)) {
			$this->output(1, '广告不存在');
		}
		if ($adInfo['status'] == 5) {
			$this->output(1, '广告已删除');
		}

		if (!trim($inputInfo['title'])) {
			$this->output(1, '创意标题不能为空');
		}
		$inputInfo['title'] = trim($inputInfo['title']);
		if (!trim($inputInfo['desc'])) {
			$this->output(1, '创意描述不能为空');
		}
		if (mb_strlen($inputInfo['desc']) > 30) {
			$this->output(1, '创意描述大于30个字符');
		}
		$inputInfo['desc'] = trim($inputInfo['desc']);

		if (in_array($adInfo['ad_target_type'], array(2, 3)) && in_array($adInfo['originality_type'], array(3, 5)) && empty($inputInfo['icon'])) {
			$this->output(1, 'icon没有上传');
		}
		if (($adInfo['originality_type'] == Common_Service_Const::PIC_AD_SUB_TYPE
				|| $adInfo['originality_type'] == Common_Service_Const::CUSTOME_AD_SUB_TYPE
				|| $adInfo['originality_type'] == Common_Service_Const::SPLASH_AD_SUB_TYPE)
			&& (!$inputInfo['cross_img'] || !$inputInfo['vertical_img'])) {
			$this->output(1, '横屏竖屏图片没有上传');
		}

		if ($adInfo['originality_type'] == Common_Service_Const::VIDEO_AD_SUB_TYPE && (!$inputInfo['video'] || !$inputInfo['h5'])) {
			$this->output(1, '视频文件或H5没有上传');
		}

		//原生广告校验
		if ($adInfo['originality_type'] == Common_Service_Const::ENBED_AD_SUB_TYPE) {
			if (empty($adInfo['ad_sub_type'])) {
				$this->output(1, '请选择展示形式');
			}
			if (!in_array($adInfo['ad_sub_type'], array(Common_Service_Const::SINGLE_ENBED_AD_SUB_TYPE, Common_Service_Const::COMBINATION_ENBED_AD_SUB_TYPE))) {
				$this->output(1, '请选择正确的原生广告的展示形式');
			}

			if (empty($inputInfo['score'])) {
				$this->output(1, '请选择填写评分');
			}
			if (!is_numeric($inputInfo['score']) || intval($inputInfo['score']) < 0 || intval($inputInfo['score']) > 10 || $inputInfo['score'] != intval($inputInfo['score'])) {
				$this->output(1, '评分请填写1到10的正整数');
			}

			if (empty($inputInfo['action_text'])) {
				$this->output(1, '请选择填写行动语');
			}

			if ($adInfo['ad_sub_type'] == Common_Service_Const::SINGLE_ENBED_AD_SUB_TYPE) {
				if (empty($inputInfo['single_img'])) {
					$this->output(1, '请上传单图图片');
				}
			} else if ($adInfo['ad_sub_type'] == Common_Service_Const::COMBINATION_ENBED_AD_SUB_TYPE) {
				if (empty($inputInfo['combination_img1']) || empty($inputInfo['combination_img2']) || empty($inputInfo['combination_img3'])) {
					$this->output(1, '请上传组图图片');
				}
			}
			if (empty($inputInfo['enbed_image_size'])) {
				$this->output(1, '原生广告尺寸参数错误');
			}
		}

		$uId = $this->userInfo['user_id'];
		$inputInfo['unit_id'] = $adInfo['unit_id'];
		$params['title'] = trim($inputInfo['title']);
		$params['account_id'] = $uId;
		$result = Dedelivery_Service_OriginalityRelationModel::getBy($params);
		if ($result) {
			$this->output(1, '创意标题已经存在，操作失败');
		}

		$inputInfo['strategy'] = $adInfo['strategy'];
		$inputInfo['originality_type'] = $adInfo['originality_type'];
		$inputInfo['unit_id'] = $adInfo['unit_id'];
		$inputInfo['ad_sub_type'] = $adInfo['ad_sub_type'];

		$originalintData = $this->fillOriginalintData($uId, $inputInfo, $inputInfo['ad_id']);
		$originalintData['status'] = 2;
		$originalintId = Dedelivery_Service_OriginalityRelationModel::add($originalintData);
		if (!$originalintId) $this->output('-1', '操作失败.');

		/*操作日志start*/
		$adparams['id'] = intval($inputInfo['ad_id']);
		$adinfo = Dedelivery_Service_AdConfListModel::getBy($adparams);
		$logdata = array();
		$logdata['module'] = 'adver_delivery';
		$logdata['sub_module'] = 'add_originality';
		$logdata['content'] .= $originalintId . ',' . $originalintData['title'] . ',ad_id:' . $inputInfo['ad_id'] . ', ad_name:' . $adinfo['ad_name'] . ';';
		$this->addOperatelog($logdata);
		/*操作日志end*/

		$this->output('0', '操作成功.');
	}


	/**
	 * 复制创意
	 */
	public function copyOriginalityAjaxAction()
	{
		$info = $this->getInput(array('id'));

		$uId = $this->userInfo['user_id'];
		$params = array();
		$params['account_id'] = $uId;
		$params['id'] = $info['id'];
		$params['del'] = Common_Service_Const::NOT_DELETE_FLAG;
		$originalityInfo = Dedelivery_Service_OriginalityRelationModel::getBy($params);
		if (empty($originalityInfo)) {
			$this->output(1, '创意不存在');
		}

		$originalityData = $originalityInfo;
		unset($originalityData['create_time']);
		unset($originalityData['update_time']);
		$originalityData['status'] = 1;
		$originalityData['title'] = $originalityData['title'] . "_" . date("YmdHis") . '_' . rand(1, 1000);
		$originalintId = Dedelivery_Service_OriginalityRelationModel::add($originalityData);
		if (!$originalintId) $this->output('-1', '复制失败.');

		/*操作日志start*/
		$adparams['id'] = intval($originalityInfo['ad_id']);
		$adinfo = Dedelivery_Service_AdConfListModel::getBy($adparams);
		$logdata = array();
		$logdata['module'] = 'adver_delivery';
		$logdata['sub_module'] = 'add_originality';
		$logdata['content'] .= $originalintId . ',' . $originalityData['title'] . ',ad_id:' . $originalityData['ad_id'] . ', ad_name:' . $adinfo['ad_name'] . ';';
		$this->addOperatelog($logdata);
		/*操作日志end*/

		$this->output('0', '复制成功.');
	}

	public function saveDirecConfigtPostAction()
	{
		$info = $this->getInput(array('direct_name', 'area_type', 'area_range', 'age_direct_type', 'age_direct_range',
			'sex_direct_type', 'os_direct_type', 'network_direct_type', 'network_direct_range',
			'operator_direct_type', 'operator_direct_range', 'brand_direct_type', 'brand_direct_range',
			'screen_direct_type', 'screen_direct_range', 'interest_direct_type', 'interest_direct_range',
			'pay_ability_type', 'pay_ability_range', 'game_frequency_type', 'game_frequency_range', 'charge_type', 'price'
		));
		$info = $this->checkStep3Param($info);
		$info['advertiser_uid'] = $this->userInfo['user_id'];
		$result = Advertiser_Service_DirectModel::getBy(array('direct_name' => trim($info['direct_name']), 'advertiser_uid' => $info['advertiser_uid']));
		if ($result) {
			$this->output('-1', '名称已经存在.');
		}
		$info['direct_config'] = json_encode($this->fillDiRectConf($info));
		$result = Advertiser_Service_DirectModel::addDirect($info);
		if (!result) $this->output('-1', '操作失败.');
		$this->output('0', '操作成功.', array('id' => $result, 'direct_name' => $info['direct_name']));
	}


	public function getDirecConfigAction()
	{
		$id = intval($this->getInput('id'));
		if (!$id) $this->output('1', '操作非法.');
		$result = Advertiser_Service_DirectModel::getBy(array('id' => $id, 'advertiser_uid' => $this->userInfo['user_id']));
		if (!empty($result['direct_config'])) {
			$ditectConfData = json_decode($result['direct_config'], true);
			unset($result['direct_config']);
			$result = array_merge($result, $ditectConfData);
		}
		if (!$result) $this->output('1', '操作非法.');
		$this->output(0, '成功', $result);

	}

	public function updateAdNameAction()
	{
		$info = $this->getInput(array('id', 'ad_name'));
		if (!$info['id']) {
			$this->output('1', '操作非法.');
		}
		if (!trim($info['ad_name'])) {
			$this->output(1, '广告名称不能为空');
		}
		if (Common::strLength($info['ad_name']) >= 30) {
			$this->output(1, '广告名称不能太长');
		}

		$params['id'] = intval($info['id']);
		$adinfo = Dedelivery_Service_AdConfListModel::getBy($params);
		if (!$adinfo) {
			$this->output(1, '操作非法');
		}
		if ($adinfo['ad_name'] == trim($info['ad_name'])) {
			$this->output(1, '标题没有改变');
		}
		unset($params);
		$uId = $this->userInfo['user_id'];
		$params['id'] = array('<>', intval($info['id']));
		$params['ad_name'] = trim($info['ad_name']);
		$params['account_id'] = $this->userInfo['user_id'];

		$result = Dedelivery_Service_AdConfListModel::getBy($params);
		if ($result) {
			$this->output(1, '名称已经存在');
		}
		unset($params);
		$data['ad_name'] = trim($info['ad_name']);
		$params['id'] = intval($info['id']);
		$result = Dedelivery_Service_AdConfListModel::updateBy($data, $params);
		if (!$result) {
			$this->output(1, '操作非法');
		}

		/*操作日志start*/
		$logdata = array();
		$logdata['module'] = 'adver_delivery';
		$logdata['sub_module'] = 'edit_ad';
		$logdata['content'] .= $adinfo['id'] . ',' . $data['ad_name'] . ',' . 'ad_name' . ':' . $adinfo['ad_name'] . ',ad_name:' . $data['ad_name'];
		$this->addOperatelog($logdata);
		/*操作日志end*/

		$this->output(0, '成功', $result);
	}

	public function updateOriginalityNameAction()
	{
		$info = $this->getInput(array('id', 'title'));
		if (!$info['id']) {
			$this->output('1', '操作非法.');
		}
		if (!trim($info['title'])) {
			$this->output('1', '操作非法.');
		}


		$params['id'] = intval($info['id']);
		$OriginalityInfo = Dedelivery_Service_OriginalityRelationModel::getBy($params);
		if (!$OriginalityInfo) {
			$this->output(1, '操作非法');
		}
		if ($OriginalityInfo['title'] == trim($info['title'])) {
			$this->output(1, '标题没有改变');
		}
		unset($params);
		$uId = $this->userInfo['user_id'];
		$params['id'] = array('<>', intval($info['id']));
		$params['title'] = trim($info['title']);
		$params['account_id'] = $this->userInfo['user_id'];

		$result = Dedelivery_Service_OriginalityRelationModel::getBy($params);
		if ($result) {
			$this->output(1, '名称已经存在');
		}
		unset($params);
		$data['title'] = trim($info['title']);
		$params['id'] = intval($info['id']);
		$result = Dedelivery_Service_OriginalityRelationModel::updateBy($data, $params);
		if (!$result) {
			$this->output(1, '操作非法');
		}
		/*操作日志start*/
		$logdata = array();
		$logdata['module'] = 'adver_delivery';
		$logdata['sub_module'] = 'edit_originality';
		$logdata['content'] .= $OriginalityInfo['id'] . ',' . $data['title'] . ',' . 'title' . ':' . $OriginalityInfo['title'] . ',title:' . $data['title'];
		$this->addOperatelog($logdata);
		/*操作日志end*/

		$this->output(0, '成功', $result);
	}

	public function updateUnitNameAction()
	{
		$info = $this->getInput(array('id', 'name'));
		if (!$info['id']) {
			$this->output('1', '操作非法.');
		}
		$this->checkUnitName($info);

		$params['id'] = intval($info['id']);
		$unitinfo = Dedelivery_Service_UnitConfModel::getBy($params);
		if (!$unitinfo) {
			$this->output(1, '操作非法');
		}
		if ($unitinfo['name'] == trim($info['name'])) {
			$this->output(1, '名字没有改变');
		}
		unset($params);
		$uId = $this->userInfo['user_id'];
		$params['id'] = array('<>', intval($info['id']));
		$params['name'] = trim($info['name']);
		$params['account_id'] = $this->userInfo['user_id'];

		$result = Dedelivery_Service_UnitConfModel::getBy($params);
		if ($result) {
			$this->output(1, '名称已经存在');
		}
		unset($params);
		$data['name'] = trim($info['name']);
		$params['id'] = intval($info['id']);
		$result = Dedelivery_Service_UnitConfModel::updateBy($data, $params);
		if (!$result) {
			$this->output(1, '操作非法');
		}

		/*操作日志start*/
		$logdata = array();
		$logdata['module'] = 'adver_delivery';
		$logdata['sub_module'] = 'edit_unit';
		$logdata['content'] .= $unitinfo['id'] . ',' . $data['name'] . ',' . 'name' . ':' . $unitinfo['name'] . ',name:' . $data['name'];
		$this->addOperatelog($logdata);
		/*操作日志end*/
		$this->output(0, '成功', $result);
	}

	/**
	 * 根据上传的配置生成zip包
	 */
	public function createH5ZipAction()
	{
		$info = $this->getInput(array('h5template', 'mainpic', 'carouselpic1', 'carouselpic2', 'carouselpic3', 'carouselpic4', 'iconpic', 'videotitle', 'videodesc', 'star', 'commentnum', 'buttonvalue'));
		$info = $this->checkCreateH5ZipParam($info);
		//保存缓存
		$uId = $this->userInfo['user_id'];
		$cache = Dedelivery_Service_AdConfListModel::getCache();
		$key = Dedelivery_Service_AdConfListModel::getAddAdStepKey(2, $uId, intval($info['id']));
		$cachedata = $cache->get($key);
		if ($cachedata && is_array($cachedata)) {
			$allCacheData = array_merge($cachedata, $info);
		} else {
			$allCacheData = $info;
		}
		$result = $cache->set($key, $allCacheData, Dedelivery_Service_AdConfListModel::CACHE_EPIRE);
		$info = $this->addImageAttachPath($info);
		$return = Common::createZip($info);
		if (!$return) {
			$this->output(1, 'zip包生成失败');
		}
		$this->output(0, 'zip包生成成功', $return);
	}

	/**
	 * 检查H5模板配置
	 * @param type $info
	 * @return type
	 */
	private function checkCreateH5ZipParam($info)
	{
		if (!in_array($info['h5template'], array(1, 2))) {
			$this->output(1, '模板参数错误');
		}
		if (empty($info['mainpic'])) {
			$this->output(1, '请选择主图');
		}
		if ($info['h5template'] == 1 && (empty($info['carouselpic1']) || empty($info['carouselpic2']) || empty($info['carouselpic3']) || empty($info['carouselpic4']))) {
			$this->output(1, '请上传完整的轮播图');
		}
		if (empty($info['iconpic'])) {
			$this->output(1, '请上传icon');
		}
		if (empty($info['videotitle'])) {
			$this->output(1, '请填写标题');
		}
		$info['videotitle'] = trim($info['videotitle']);
		if (empty($info['videodesc'])) {
			$this->output(1, '请填写描述');
		}
		$info['videodesc'] = trim($info['videodesc']);
		if (empty($info['star']) || !in_array($info['star'], array(0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5))) {
			$this->output(1, '请正确填写星级');
		}
		if (empty($info['commentnum']) || !is_numeric($info['commentnum'])) {
			$this->output(1, '请正确填写评论数');
		}
		if (empty($info['buttonvalue'])) {
			$this->output(1, '请填写交互方案');
		}
		return $info;
	}

	/**
	 * 补全图片地址
	 * @param type $info
	 * @return string
	 */
	private function addImageAttachPath($info)
	{
		$attachPath = common::getAttachPath();
		$info['mainpic'] = $attachPath . $info['mainpic'];
		if ($info['h5template'] == 1) {
			$info['carouselpic1'] = $attachPath . $info['carouselpic1'];
			$info['carouselpic2'] = $attachPath . $info['carouselpic2'];
			$info['carouselpic3'] = $attachPath . $info['carouselpic3'];
			$info['carouselpic4'] = $attachPath . $info['carouselpic4'];
		}
		$info['iconpic'] = $attachPath . $info['iconpic'];
		return $info;
	}

	/**
	 * 导出创意数据数据
	 * @return string
	 */
	public function exportOrigainalityAction()
	{

		$params = array();
		$page = intval($this->getInput('page'));
		if ($page < 1) $page = 1;

		$search = $this->getInput(array('title', 'originality_type', 'status', 'id', 'ad_name', 'charge_type', 'ad_target_type', 'ad_id', 'start_time', 'end_time'));
		$params['account_id'] = $this->userInfo['user_id'];

		if (!isset($search['start_time']) && !isset($search['end_time'])) {
			$search['start_time'] = date('Y-m-d');
			$search['end_time'] = date('Y-m-d');
		}

		//广告明细
		$adDetail = Dedelivery_Service_AdConfListModel::getBy(array('id' => intval($search['ad_id'])));
		$this->assign('adDetail', $adDetail);
//		$sqlWhere = 'a.is_delete = '.  Common_Service_Const::NOT_DELETE_FLAG;
		$sqlWhere = '1=1';


		if ($search['title']) {
			$sqlWhere .= ' AND a.title like  "%' . trim($search['title']) . '%"';
		}
		if ($search['originality_type']) {
			$sqlWhere .= ' AND a.originality_type = ' . intval($search['originality_type']);
		}
		if ($search['status'] && $search['status'] != 5) {
			$sqlWhere .= ' AND a.status = ' . intval($search['status']);
			$sqlWhere .= ' AND a.del = ' . Common_Service_Const::NOT_DELETE_FLAG;
		} elseif ($search['status'] == 5) {
			$sqlWhere .= ' AND a.del = ' . Common_Service_Const::DELETE_FLAG;
		}
		if ($search['id']) {
			$sqlWhere .= ' AND a.id = ' . intval($search['id']);
		}

		if ($search['ad_name']) {
			$sqlWhere .= ' AND b.ad_name like  "%' . trim($search['ad_name']) . '%"';
		}
		if ($search['charge_type']) {
			$sqlWhere .= ' AND b.charge_type = ' . intval($search['charge_type']);
		}
		if ($search['ad_target_type']) {
			$sqlWhere .= ' AND b.ad_target_type = ' . intval($search['ad_target_type']);
		}
		if ($search['ad_id']) {
			$sqlWhere .= ' AND a.ad_id = ' . intval($search['ad_id']);
		}
		$table = 'delivery_ad_conf_list ';
		$on = 'a.ad_id = b.id';
		$field = 'a.*,b.ad_name,b.charge_type,b.ad_target_type';
		$origainalityList = Dedelivery_Service_OriginalityRelationModel::getSearchLeftJoinNoLimit($table, $on, $sqlWhere, $orderBy = array(), $field);
		$origainalityList = $this->fillDataToOriganialityList($search, $origainalityList);
		$adTargetType = Common::getConfig('deliveryConfig', 'adTargetType');
		$chargeTypeList = Common::getConfig('deliveryConfig', 'chargeTypeList');
		$originalityType = Common::getConfig('deliveryConfig', 'originalityType');
		Yaf_loader::import("Util/PHPExcel/PHPExcel.php");
		$objPHPExcel = new PHPExcel();
		/*以下是一些设置 ，什么作者  标题啊之类的*/
		$objPHPExcel->getProperties()->setCreator("housead")
			->setLastModifiedBy("housead")
			->setTitle("数据EXCEL导出")
			->setSubject("数据EXCEL导出")
			->setDescription("广告创意列表")
			->setKeywords("excel")
			->setCategory("result file");
		/*以下就是对处理Excel里的数据，横着取数据*/
		$all_field = array(
			"id" => "创意ID",
			"title" => "创意标题",
			"ad_id" => "广告活动ID",
			"ad_name" => "广告活动名称",
			"ad_target_type" => "广告目标",
			"charge_type" => "计费类型",
			"originality_type" => "创意类型",
			"status" => "创意状态",
			"amount" => "消耗（元）",
			"views" => "曝光",
			"clicks" => "点击",
			"click_rate" => "点击率(%)",
			"amount_rate" => "点击均价",
			"active_rate" => "激活率",
			"cpa" => "CPA"
		);
		$num = 1;
		$char = 'A';
		foreach ($all_field as $field_key => $field_val) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($char . $num, $field_val);
			$char++;
		}
		foreach ($origainalityList as $data_key => $data_val) {
			$num++;
			$char = 'A';
			$data_val['ad_target_type'] = $adTargetType[$data_val['ad_target_type']];
			$data_val['charge_type'] = $chargeTypeList[$data_val['charge_type']];
			$data_val['originality_type'] = $originalityType[$data_val['originality_type']];
			$data_val['status'] = ($data_val['del'] == Common_Service_Const::NOT_DELETE_FLAG) ? $this->adStatus[$val['status']] : '已删除';
			foreach ($all_field as $field_key => $field_val) {
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($char . $num, $data_val[$field_key]);
				$char++;
			}
		}
		// 开始组合头
		$xml_name = "广告创意列表";
		$objPHPExcel->getActiveSheet()->setTitle('User');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $xml_name . '.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	/**
	 * 导出广告数据
	 */
	public function exportAdAction()
	{
		$params = array();
		$page = intval($this->getInput('page'));
		if ($page < 1) $page = 1;

		$search = $this->getInput(array('ad_name', 'status', 'ad_target_type', 'charge_type', 'unit_id', 'start_time', 'end_time'));
		if ($search['ad_name']) {
			$params['ad_name'] = array('LIKE', $search['ad_name']);
		}
		if ($search['ad_target_type']) {
			$params['ad_target_type'] = $search['ad_target_type'];
		}
		if ($search['status'] && $search['status'] != 5) {
			$params['status'] = $search['status'];
			$params['del'] = Common_Service_Const::NOT_DELETE_FLAG;
		} elseif ($search['status'] == 5) {
			$params['del'] = Common_Service_Const::DELETE_FLAG;
		}
		if ($search['charge_type']) {
			$params['charge_type'] = $search['charge_type'];
		}
		if ($search['unit_id']) {
			$params['unit_id'] = $search['unit_id'];
		}
		if (!isset($search['start_time']) && !isset($search['end_time'])) {
			$search['start_time'] = date('Y-m-d');
			$search['end_time'] = date('Y-m-d');
		}
		$params['account_id'] = $this->userInfo['user_id'];

		$adStatus = $this->adStatus;

		list($total, $adList) = Dedelivery_Service_AdConfListModel::getList($page, $this->perpage, $params);
//        var_dump($adList);die;
		$modeType = Common::getConfig('deliveryConfig', 'modeType');
		$adTargetType = Common::getConfig('deliveryConfig', 'adTargetType');
		$chargeTypeList = Common::getConfig('deliveryConfig', 'chargeTypeList');
		$originalityType = Common::getConfig('deliveryConfig', 'originalityType');
		$unitList = $this->getAdUnitListByDb();
		$adList = $this->fillDataToAdList($adList, $search);

		Yaf_loader::import("Util/PHPExcel/PHPExcel.php");
		$objPHPExcel = new PHPExcel();
		/*一些设置*/
		$objPHPExcel->getProperties()->setCreator("housead")
			->setLastModifiedBy("housead")
			->setTitle("数据EXCEL导出")
			->setSubject("数据EXCEL导出")
			->setDescription("广告列表")
			->setKeywords("excel")
			->setCategory("result file");
		/*以下就是对处理Excel里的数据，横着取数据*/
		$all_field = array(
			"id" => "广告ID",
			"ad_name" => "广告名称",
			"adStatus" => "广告状态",
			"ad_target_type" => "广告目标",
			"originality_type" => "创意类型",
			"charge_type" => "计费类型",
			"price" => "出价（元）",
			"amount" => "消耗（元）",
			"views" => "曝光",
			"clicks" => "点击",
			"click_rate" => "点击率(%)",
			"amount_rate" => "点击均价",
			"active_rate" => "激活率",
			"cpa" => "CPA"
		);
		$num = 1;
		$char = 'A';
		foreach ($all_field as $field_key => $field_val) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($char . $num, $field_val);
			$char++;
		}
		foreach ($adList as $data_key => $data_val) {
			$num++;
			$char = 'A';
			$data_val['ad_target_type'] = $adTargetType[$data_val['ad_target_type']];
			$data_val['charge_type'] = $chargeTypeList[$data_val['charge_type']];
			$data_val['originality_type'] = $originalityType[$data_val['originality_type']];
			//todo modify
//            $data_val['originality_type'] = $originalityType[$data_val['originality_type']];
			$data_val['adStatus'] = $data_val['del'] == Common_Service_Const::NOT_DELETE_FLAG ? $adStatus[$data_val['status']] : '已删除';
			foreach ($all_field as $field_key => $field_val) {
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($char . $num, $data_val[$field_key]);
				$char++;
			}
		}
		// 开始组合头
		$xml_name = "广告列表";
		$objPHPExcel->getActiveSheet()->setTitle('User');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $xml_name . '.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;


	}

	/**
	 * 单元列表
	 */
	public function exportUnitAction()
	{
		$params = array();
		$page = intval($this->getInput('page'));
		if ($page < 1) $page = 1;

		$search = $this->getInput(array('name', 'status', 'mode_type'));
		if ($search['name']) {
			$params['name'] = array('LIKE', $search['name']);
		}
		if ($search['mode_type']) {
			$params['mode_type'] = $search['mode_type'];
		}
		if ($search['status']) {
			$params['status'] = $search['status'];
		}
		$params['account_id'] = $this->userInfo['user_id'];
		if (!isset($search['start_time']) && !isset($search['end_time'])) {
			$search['start_time'] = date('Y-m-d');
			$search['end_time'] = date('Y-m-d');
		}
		$params['del'] = Common_Service_Const::NOT_DELETE_FLAG;

		list($total, $unitList) = Dedelivery_Service_UnitConfModel::getList($page, $this->perpage, $params);
		$unitList = $this->fillDataToUnitList($unitList, $search);
		$unitStatus = $this->unitStatus;
		$modeType = Common::getConfig('deliveryConfig', 'modeType');

		Yaf_loader::import("Util/PHPExcel/PHPExcel.php");
		$objPHPExcel = new PHPExcel();
		/*一些设置*/
		$objPHPExcel->getProperties()->setCreator("housead")
			->setLastModifiedBy("housead")
			->setTitle("数据EXCEL导出")
			->setSubject("数据EXCEL导出")
			->setDescription("计划列表")
			->setKeywords("excel")
			->setCategory("result file");
		/*以下就是对处理Excel里的数据，横着取数据*/
		$all_field = array(
			"id" => "投放单元",
			"name" => "投放单元名称",
			"unitStatus" => "状态",
			"modeType" => "投放方式",
			"limitType" => "限额（元）",
			"amount" => "消耗（元）",
			"views" => "曝光",
			"clicks" => "点击",
			"click_rate" => "点击率(%)",
			"amount_rate" => "点击均价",
			"active_rate" => "激活率",
			"cpa" => "CPA"
		);
		$num = 1;
		$char = 'A';
		foreach ($all_field as $field_key => $field_val) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($char . $num, $field_val);
			$char++;
		}
		foreach ($unitList as $data_key => $data_val) {
			$num++;
			$char = 'A';
			$data_val['unitStatus'] = $unitStatus[$data_val['status']];
			$data_val['modeType'] = $modeType[$data_val['mode_type']];
			$data_val['limitType'] = ($data_val['limit_type'] == 1) ? $data_val['limit_range'] : '无限';
			foreach ($all_field as $field_key => $field_val) {
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($char . $num, $data_val[$field_key]);
				$char++;
			}
		}
		// 开始组合头
		$xml_name = "计划列表";
		$objPHPExcel->getActiveSheet()->setTitle('User');
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $xml_name . '.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	public function parseChannelConf($channelIds)
	{
//        $channelIds = json_decode ( $channel_conf, true );
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
		$cityList = Common::getConfig('areaConfig', 'cityList');
		$tmp = array();
		foreach ($cityList as $parentId => $val) {
			foreach ($val as $ke => $va) {
				if (in_array($ke, $areaConf)) {
					$tmp [$ke] = array(
						'id' => $ke,
						'name' => $va,
						'parent_id' => $parentId,
						'level' => 2
					);
				}

			}
		}

		return $tmp;
	}

	/**
	 * app行为定向的配置
	 * @param type $originality_type
	 * @param type $app_behavior_conf
	 * @return boolean
	 */
	public function parseAppBehaviorConf($originality_type, $app_behavior_conf)
	{
		if (empty ($app_behavior_conf)) {
			return false;
		}
		$tmp = array();
		// 试玩的app行为定向定向到游戏维度
		if (in_array($originality_type, array(
			Common_Service_Const::VIDEO_AD_SUB_TYPE,
			Common_Service_Const::PIC_AD_SUB_TYPE
		))) {
			$appInfos = MobgiApi_Service_AdAppModel::getsBy(array(
				'app_key' => array(
					'in',
					$app_behavior_conf
				)
			));
			if ($appInfos) {
				foreach ($appInfos as $app) {
					$tmp [] = array(
						'app_name' => $app ['app_name'],
						'app_key' => $app ['app_key']
					);
				}
			}
		} else {
			$AdAppAdDeverParam = array();
			$AdAppAdDeverParam ['dever_pos_keys'] = $app_behavior_conf;
			$posInfos = MobgiApi_Service_AdAppModel::getAdAppAdDeverPos($AdAppAdDeverParam);
			if ($posInfos) {
				foreach ($posInfos as $pos) {
					$tmp [] = array(
						'dever_pos_name' => $pos ['dever_pos_name'],
						'dever_pos_key' => $pos ['dever_pos_key'],
						'app_name' => $pos ['app_name'],
						'app_key' => $pos ['app_key']
					);
				}
			}
		}
		return $tmp;
	}

	/**
	 *新增广告参数
	 *
	 */
	public function previewAdAction()
	{
		$adId = intval($this->getInput('id'));
		if (empty($adId)) {
			$this->output(1, '参数错误');
		}
		$result = Dedelivery_Service_AdConfListModel::getBy(array('id' => $adId));
		if (empty($result)) {
			$this->output(1, '广告不存在');
		}

		$config = Common::getConfig('deliveryConfig');

		$dateRange = json_decode($result['date_range'], true);
		$result['start_date'] = $dateRange['start_date'];
		$result['end_date'] = $dateRange['end_date'];
		$timeRange = json_decode($result['time_range'], true);
		$result['start_time'] = $timeRange['start_time'];
		$result['end_time'] = $timeRange['end_time'];
		$result['originality_type_cn'] = $config['originalityType'][$result['originality_type']];
		$direct_config = json_decode($result['direct_config'], true);
		//地域
		if ($direct_config['area_type']) {
			$tmp_cn = array();
			$provinceList = common::getConfig('areaConfig', 'provinceList');
			foreach ($provinceList as $key => $val) {
				if ($direct_config['area_range'] && in_array($key, $direct_config['area_range'])) {
					$tmp_cn[] = $val;
				}
			}
			$direct_config['area_range_cn'] = implode(',', $tmp_cn);
		} else {
			$direct_config['area_range_cn'] = '';
		}
		//年龄
		if ($direct_config['age_direct_type']) {
			$tmp_cn = array();
			foreach ($config['ageDirectList'] as $key => $val) {
				if ($direct_config['age_direct_range'] && in_array($key, $direct_config['age_direct_range'])) {
					$tmp_cn[] = $val;
				}
			}
			$direct_config['age_direct_range_cn'] = implode(',', $tmp_cn);
		} else {
			$direct_config['age_direct_range_cn'] = '';
		}
		//性别
		if ($direct_config['sex_direct_type']) {
			$tmp_cn = array();
			foreach ($config['sexTypeList'] as $key => $val) {
				if ($direct_config['sex_direct_type'] == $key) {
					$sex_direct_type_cn = $val;
				}
			}
			$direct_config['sex_direct_type_cn'] = $sex_direct_type_cn;
		} else {
			$direct_config['sex_direct_type_cn'] = '';
		}
		//操作系统
		if ($direct_config['os_direct_type']) {
			$tmp_cn = array();
			foreach ($config['osTypeList'] as $key => $val) {
				if ($direct_config['os_direct_type'] == $key) {
					$os_direct_type_cn = $val;
				}
			}
			$direct_config['os_direct_type_cn'] = $os_direct_type_cn;
		} else {
			$direct_config['os_direct_type_cn'] = '';
		}
		//网络环境
		if ($direct_config['network_direct_type']) {
			$tmp_cn = array();
			foreach ($config['netWorkList'] as $key => $val) {
				if ($direct_config['network_direct_range'] && in_array($key, $direct_config['network_direct_range'])) {
					$tmp_cn[] = $val;
				}
			}
			$direct_config['network_direct_range_cn'] = implode(',', $tmp_cn);
		} else {
			$direct_config['network_direct_range_cn'] = '';
		}
		//运营商
		if ($direct_config['operator_direct_type']) {
			$tmp_cn = array();
			foreach ($config['operatorList'] as $key => $val) {
				if ($direct_config['operator_direct_range'] && in_array($key, $direct_config['operator_direct_range'])) {
					$tmp_cn[] = $val;
				}
			}
			$direct_config['operator_direct_range_cn'] = implode(',', $tmp_cn);
		} else {
			$direct_config['operator_direct_range_cn'] = '';
		}
		//手机品牌
		if ($direct_config['brand_direct_type']) {
			$tmp_cn = array();
			foreach ($config['ostypeBrandList'] as $ostype => $brandListItem) {
				foreach ($brandListItem as $key => $val) {
					if ($direct_config['brand_direct_range'] && in_array($key, $direct_config['brand_direct_range'])) {
						$tmp_cn[] = $val;
					}
				}
			}
			$direct_config['brand_direct_range_cn'] = implode(',', $tmp_cn);
		} else {
			$direct_config['brand_direct_range_cn'] = '';
		}
		//屏幕大小
		if ($direct_config['screen_direct_type']) {
			$tmp_cn = array();
			foreach ($config['screenList'] as $key => $val) {
				if ($direct_config['screen_direct_range'] && in_array($key, $direct_config['screen_direct_range'])) {
					$tmp_cn[] = $val;
				}
			}
			$direct_config['screen_direct_range_cn'] = implode(',', $tmp_cn);
		} else {
			$direct_config['screen_direct_range_cn'] = '';
		}
		//游戏兴趣
		if ($direct_config['interest_direct_type']) {
			$tmp_cn = array();
			foreach ($config['interestList'] as $key => $val) {
				if ($direct_config['interest_direct_range'] && in_array($key, $direct_config['interest_direct_range'])) {
					$tmp_cn[] = $val;
				}
			}
			$direct_config['interest_direct_range_cn'] = implode(',', $tmp_cn);
		} else {
			$direct_config['interest_direct_range_cn'] = '';
		}
		//付费能力
		if ($direct_config['pay_ability_type']) {
			$tmp_cn = array();
			foreach ($config['payAbilityList'] as $key => $val) {
				if ($direct_config['pay_ability_range'] && in_array($key, $direct_config['pay_ability_range'])) {
					$tmp_cn[] = $val;
				}
			}
			$direct_config['pay_ability_range_cn'] = implode(',', $tmp_cn);
		} else {
			$direct_config['pay_ability_range_cn'] = '';
		}
		//游戏频率
		if ($direct_config['game_frequency_type']) {
			$tmp_cn = array();
			foreach ($config['gameFrequencyList'] as $key => $val) {
				if ($direct_config['game_frequency_range'] && in_array($key, $direct_config['game_frequency_range'])) {
					$tmp_cn[] = $val;
				}
			}
			$direct_config['game_frequency_range_cn'] = implode(',', $tmp_cn);
		} else {
			$direct_config['game_frequency_range_cn'] = '';
		}
		//媒体定向
		if ($direct_config ['app_behavior_type']) {
			$appbehavior_conf = $this->parseAppBehaviorConf($result['originality_type'], $direct_config ['app_behavior_range']);
			$tmp_cn = array();
			if ($appbehavior_conf) {
				foreach ($appbehavior_conf as $key => $val) {
					$tmp_cn[] = $val['app_name'] . '-' . $val['dever_pos_name'];
				}
			}
			$direct_config['app_behavior_range_cn'] = implode(',', $tmp_cn);
		} else {
			$direct_config['app_behavior_range_cn'] = '';
		}
		//渠道定向
		if ($direct_config ['channel_conf_type']) {
			$channel_conf = $this->parseChannelConf($direct_config ['channel_id']);
			$tmp_cn = array();
			if ($channel_conf) {
				foreach ($channel_conf as $key => $val) {
					$tmp_cn[] = $val['name'];
				}
			}
			$direct_config['channel_conf_cn'] = implode(',', $tmp_cn);
		} else {
			$direct_config['channel_conf_cn'] = '';
		}
		//区域定向
		if ($direct_config ['area_conf_type']) {
			$area_conf = $this->parseAreaConf($direct_config ['area_id']);
			$tmp_cn = array();
			if ($area_conf) {
				foreach ($area_conf as $key => $val) {
					$tmp_cn[] = $val['name'];
				}
			}
			$direct_config['area_conf_cn'] = implode(',', $tmp_cn);
		} else {
			$direct_config['area_conf_cn'] = '';
		}
		$result['direct_config'] = $direct_config;
		$this->output(0, 'ok!', $result);
	}


	public function getAreaListAction()
	{

		$provinceList = Common::getConfig('areaConfig', 'provinceList');
		$cityList = Common::getConfig('areaConfig', 'cityList');

		$outData = array();
		foreach ($cityList as $parentId => $parent) {
			$tmp = array();
			foreach ($parent as $cityId => $city) {
				$tmp[] = array(
					'id' => $cityId,
					'name' => $city,
					'parent_id' => $parentId,
					'level' => 2,
					'item' => array(),
				);
			}

			$outData[] = array(
				'id' => strval($parentId),
				'name' => $provinceList[$parentId],
				'parent_id' => '0',
				'level' => 1,
				'item' => $tmp,
			);
		}
		$callback = $this->getInput('callback');
		echo $callback . '(' . json_encode($outData) . ')';
		die();
	}

}
