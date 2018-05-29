<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Interative_ActivityController extends Admin_BaseController
{

	public $actions = array(
		'listUrl' => '/Admin/Interative_Activity/index',
		'addUrl' => '/Admin/Interative_Activity/add',
		'addPostUrl' => '/Admin/Interative_Activity/addPost',
		'deleteUrl' => '/Admin/Interative_Activity/delete',
		'viewUrl' => '/Admin/Interative_Activity/view',
		'updateStatusUrl' => '/Admin/Interative_Activity/updateStatus',
		'uploadDescImgUrl' => '/Admin/Interative_Goods/uploadDescImg',
		'uploadPostUrl' => '/Admin/Baseinfo_Template/uploadPost',
		'getGoodsIdsUrl' => '/Admin/Interative_Goods/getGoodsIds',
		'getGoodsDetailUrl' => '/Admin/Interative_Goods/getGoodsDetail',
		'codeListUrl' => '/Admin/Interative_Code/index',
		'delActivityConfRelUrl' => '/Admin/Interative_Activity/delActivityConfRel',
		'activityUrl' => '/iaad/activity'
	);

	public $perpage = 20;


	/**
	 *
	 * Enter description here ...
	 */
	public function indexAction()
	{

		$params = array();
		$page = intval($this->getInput('page'));
		if ($page < 1) $page = 1;
		$search = $this->getInput(array('title', 'type'));

		if ($search['title']) {
			$params['title'] = array('LIKE', $search['title']);
		}

		if ($search['type']) {
			$params['type'] = $search['type'];
		}
		$params['del'] = Common_Service_Const::NOT_DELETE_FLAG;
		list($total, $avtivityList) = MobgiApi_Service_InteractiveAdActivityModel::getList($page, $this->perpage, $params);
		$url = $this->actions['listUrl'] . '/?' . http_build_query($search) . '&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
		$this->assign('search', $search);
		$this->assign('total', $total);
		$this->assign('avtivityList', $avtivityList);
		$this->assign('goodsType', MobgiApi_Service_InteractiveAdGoodsModel::$mGoodsType);
		$this->assign('activityStatus', MobgiApi_Service_InteractiveAdActivityModel::$mActivityStatus);
		$this->assign('limitTpe', MobgiApi_Service_InteractiveAdActivityModel::$mLimitType);

		$this->assign('activityUrl', Yaf_Application::app()->getConfig()->couponroot . $this->actions['activityUrl']);
	}


	public function updateStatusAction()
	{
		$id = intval($this->getInput('id'));
		$status = intval($this->getInput('status'));
		if (!$id) {
			$this->output(-1, '非法操作');
		}
		$this->checkGoodsInfo($id, $status);
		$result = MobgiApi_Service_InteractiveAdActivityModel::updateByID(['status' => $status, 'operator' => $this->userInfo['user_name']], $id);
		if (!$result) {
			$this->output(-1, '操作失败');
		}
		$this->output(0, '操作成功');

	}

	public function checkGoodsInfo($id, $status)
	{
		if(!$id){
			return false;
		}
		$goodsRelIds = MobgiApi_Service_InteractiveAdActivityRelModel::getsBy(['activity_id' => $id]);
		if ($goodsRelIds) {
			$goodsIds = array_keys(common::resetKey($goodsRelIds, 'goods_id'));
			if ($status) {
				$goodsInfos = MobgiApi_Service_InteractiveAdGoodsModel::getsBy(['id' => ['IN', $goodsIds]]);
				if (count($goodsRelIds) != MobgiApi_Service_InteractiveAdActivityModel::CONFIG_GOODS_COUNT) {
					$this->output(-1, '活动配置商品个数不足8个');
				}
				foreach ($goodsInfos as $val) {
					if ($val['status'] == MobgiApi_Service_InteractiveAdGoodsModel::CLOSE_STATUS) {
						$this->output(-1, '活动配置商品："' . $val['title'] . '",已下架,请先开启');
					}
					if( ($val['type'] !=  MobgiApi_Service_InteractiveAdGoodsModel::DEAFAULT_GOODS_TYPE) && ($val['stock']-$val['used_num'])<= 0){
						$this->output(-1, '活动配置商品："' . $val['title'] . '",库存不足,请添加库存');
					}
				}
			}
		}

	}

	public function deleteAction()
	{
		$id = intval($this->getInput('id'));
		if (!$id) {
			$this->output(-1, '非法操作');
		}

	}

	public function delActivityConfRelAction()
	{
		$id = intval($this->getInput('conf_rel_id'));
		if (!$id) {
			$this->output(-1, '非法操作');
		}
		$result = MobgiApi_Service_InteractiveAdActivityRelModel::getByID($id);
		if (!$result) {
			$this->output(-1, '操作失败');
		}
		$result = MobgiApi_Service_InteractiveAdActivityRelModel::deleteById($id);
		if (!$result) {
			$this->output(-1, '操作失败');
		}
		$this->output(0, '操作成功');
	}

	public function viewAction()
	{

		$id = intval($this->getGet('id'));
		$info = MobgiApi_Service_InteractiveAdActivityModel::getByID($id);
		$info['conf_rel_list'] = $this->getActivityRelConf($id);
		$this->assign('info', $info);
		$this->assign('goodsType', MobgiApi_Service_InteractiveAdGoodsModel::$mGoodsType);
		$this->assign('limitType', MobgiApi_Service_InteractiveAdActivityModel::$mLimitType);
	}


	public function addAction()
	{
		$id = intval($this->getGet('id'));
		$this->assign('navTitle', '添加');
		if ($id) {
			$this->assign('navTitle', '编辑');
			$info = MobgiApi_Service_InteractiveAdActivityModel::getByID($id);
			$info['conf_rel_list'] = $this->getActivityRelConf($id);

		} else {
			$info['start_time'] = date('Y-m-d');
			$info['end_time'] = date('Y-m-d');
		}
		$this->assign('info', $info);
		$this->assign('goodsType', MobgiApi_Service_InteractiveAdGoodsModel::$mGoodsType);
		$this->assign('limitType', MobgiApi_Service_InteractiveAdActivityModel::$mLimitType);
	}

	private function getActivityRelConf($id)
	{
		$confList = MobgiApi_Service_InteractiveAdActivityRelModel::getsBy(['activity_id' => $id], array('position' => 'ASC'));
		$tmp = array();
		if ($confList) {
			$tmp = array();
			$attach = common::getAttachPath();
			foreach ($confList as $val) {
				$goodsInfo = MobgiApi_Service_InteractiveAdGoodsModel::getByID($val['goods_id']);
				$tmp [] = array(
					'activity_rel_id' => $val['id'],
					'position' => $val['position'],
					'rate' => $val['rate'],
					'left_num' => $goodsInfo['stock'] - $goodsInfo['used_num'],
					'title' => $goodsInfo['title'],
					'goods_id' => $val['goods_id'],
					'icon' => $attach . $goodsInfo['icon'],
				);

			}
		}
		return $tmp;
	}


	public function addPostAction()
	{
		$info = $this->getPost(array(
			'id',
			'title',
			'limit_num',
			'limit_type',
			'status',
			'start_time',
			'end_time',
			'desc',
			'activity_rel_id',
			'goods_id',
			'rate',
			'position'
		));
		$info = $this->checkPostParam($info);
		$this->checkGoodsInfo($info['id'], $info['status']);
		$activityId = $this->updateActivity($info);
		$result = $this->updateActivityRel($info, $activityId);
		$this->output(0, '操作成功');

	}

	private function updateActivityRel($info, $activityId)
	{
		//删除未提交的商品配置
		$result = MobgiApi_Service_InteractiveAdActivityRelModel::getsBy(['activity_id' => $activityId]);
		if ($result) {
			$activityRelIds = array_keys(common::resetKey($result, 'id'));
			foreach ($activityRelIds as $key => $val) {
				if (in_array($val, $info['activity_rel_id'])) {
					unset($activityRelIds[$key]);
				}
			}
			if ($activityRelIds) {
				MobgiApi_Service_InteractiveAdActivityRelModel::deleteBy(['id' => ['IN', $activityRelIds]]);
			}
		}
		foreach ($info['activity_rel_id'] as $key => $confRelId) {
			$data['activity_id'] = $activityId;
			$data['goods_id'] = $info['goods_id'][$key];
			$data['rate'] = $info['rate'][$key];
			$data['position'] = $info['position'][$key];
			$data['operator'] = $this->userInfo['user_name'];
			if ($confRelId) {
				MobgiApi_Service_InteractiveAdActivityRelModel::updateByID($data, $confRelId);
			} else {
				MobgiApi_Service_InteractiveAdActivityRelModel::add($data);
			}
		}


	}

	private function updateActivity($info)
	{
		$data['title'] = trim($info['title']);
		$data['limit_num'] = $info['limit_num'];
		$data['limit_type'] = $info['limit_type'];
		$data['status'] = $info['status'];
		$data['start_time'] = $info['start_time'];
		$data['end_time'] = $info['end_time'];
		$data['desc'] = $info['desc'];
		$data['operator'] = $this->userInfo['user_name'];
		if ($info['id']) {
			$result = MobgiApi_Service_InteractiveAdActivityModel::updateByID($data, $info['id']);
		} else {
			$result = MobgiApi_Service_InteractiveAdActivityModel::add($data);
		}
		if (!$result) {
			$this->output(-1, '操作失败');
		}
		return $info['id'] ? $info['id'] : $result;
	}


	private function checkPostParam($info)
	{

		if (empty(trim($info['title']))) {
			$this->output(-1, '活动标题不能为空');
		}
		if ($info['limit_num'] < 1) {
			$this->output(-1, '活动次数正整数');
		}
		$info['limit_num'] = intval($info['limit_num']);
		if ($info['status'] == 'on') {
			$info['status'] = MobgiApi_Service_InteractiveAdActivityModel::OPEN_STATUS;
		} else {
			$info['status'] = MobgiApi_Service_InteractiveAdActivityModel::CLOSE_STATUS;
		}
		$params['title'] = $info['title'];
		if ($info['id']) {
			$params['id'] = array('<>', $info['id']);
		}
		$result = MobgiApi_Service_InteractiveAdActivityModel::getBy($params);
		if ($result) {
			$this->output(-1, '活动标题已经存在');
		}

		if (empty($info['goods_id']) || count($info['goods_id']) != MobgiApi_Service_InteractiveAdActivityModel::CONFIG_GOODS_COUNT) {
			$this->output(-1, '配置商品个数是8个');
		}
		foreach ($info ['rate'] as $postion => $va) {
			if (!is_numeric($va)) {
				$this->output(-1, '位置:"' . ($postion + 1) . '"中的一般广告商权重必须为数字');
			}
			if ($va > 1 || $va <= 0) {
				$this->output(-1, '位置:"' . ($postion + 1) . '"中的一般广告商权重范围0-1之间数字');
			}
		}
		if (strval(array_sum($info ['rate'])) != '1') {
			$this->output(-1, '中的一般广告商的权重不为１,计算结果为：' . array_sum($info ['rate']));
		}

		return $info;
	}


}
