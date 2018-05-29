<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Interative_GoodsController extends Admin_BaseController
{

	public $actions = array(
		'listUrl' => '/Admin/Interative_Goods/index',
		'addUrl' => '/Admin/Interative_Goods/add',
		'addPostUrl' => '/Admin/Interative_Goods/addPost',
		'deleteUrl' => '/Admin/Interative_Goods/delete',
		'viewUrl' => '/Admin/Interative_Goods/view',
		'getGoodsIdsUrl' => '/Admin/Interative_Goods/getGoodsIds',
		'uploadDescImgUrl' => '/Admin/Interative_Goods/uploadDescImg',
		'uploadPostUrl' => '/Admin/Baseinfo_Template/uploadPost',
		'updateStatusUrl' => '/Admin/Interative_Goods/updateStatus',
		'codeListUrl'=> '/Admin/Interative_Code/index',
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
		list($total, $goodsList) = MobgiApi_Service_InteractiveAdGoodsModel::getList($page, $this->perpage, $params);
		$url = $this->actions['listUrl'] . '/?' . http_build_query($search) . '&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
		$this->assign('search', $search);
		$this->assign('total', $total);
		$this->assign('goodsList', $goodsList);
		$this->assign('goodsType', MobgiApi_Service_InteractiveAdGoodsModel::$mGoodsType);
		$this->assign('goodsStatus', MobgiApi_Service_InteractiveAdGoodsModel::$mGoodsStatus);

	}


	public function updateStatusAction()
	{

		$id = intval($this->getInput('id'));
		$status = intval($this->getInput('status'));
		if (!$id) {
			$this->output(-1, '非法操作');
		}
		$this->checkActivityStatus($id,$status);
		$result = MobgiApi_Service_InteractiveAdGoodsModel::updateByID(['status' => $status, 'operator' => $this->userInfo['user_name']], $id);
		if (!$result) {
			$this->output(-1, '操作失败');
		}
		$this->output(0, '操作成功');
	}

	public function checkActivityStatus($goodsId,$status){
		if($status){
			return false;
		}
		$activityParams['goods_id'] = $goodsId;
		$activityInfo = MobgiApi_Service_InteractiveAdActivityRelModel::getBy($activityParams);
		if($activityInfo['activity_id']){
			$paras['id'] = $activityInfo['activity_id'];
			$paras['start_time'] = array('<=',date('Y-m-d'));
			$paras['end_time'] = array('>=',date('Y-m-d'));
			$paras['status'] = MobgiApi_Service_InteractiveAdActivityModel::OPEN_STATUS;
			$resutl = MobgiApi_Service_InteractiveAdActivityModel::getBy($paras);
			if($resutl){
				$this->output(-1, '有活动正在使用次商品，不能下架');
			}
		}

	}

	public function deleteAction()
	{
		$id = intval($this->getInput('id'));
		if (!$id) {
			$this->output(-1, '非法操作');
		}
		$result = MobgiApi_Service_InteractiveAdActivityRelModel::getBy(['goods_id'=>$id]);
		if($result){
			$this->output(-1, '有活动正在使用此商品');
		}
		$result = MobgiApi_Service_InteractiveAdGoodsModel::getByID($id);
		if($result['used_num']){
			$this->output(-1, '此商品有已经有领取,不能删除');
		}
		$result = MobgiApi_Service_InteractiveAdGoodsModel::updateByID(['del' => Common_Service_Const::DELETE_FLAG, 'operator' => $this->userInfo['user_name']], $id);
		if (!$result) {
			$this->output(-1, '操作失败');
		}
		$this->output(0, '操作成功');
	}

	public function viewAction()
	{

		$id = intval($this->getGet('id'));
		$info = MobgiApi_Service_InteractiveAdGoodsModel::getByID($id);
		$this->assign('info', $info);
		$this->assign('goodsType', MobgiApi_Service_InteractiveAdGoodsModel::$mGoodsType);
	}



	public function addAction()
	{

		$id = intval($this->getGet('id'));
		$this->assign('navTitle', '添加');
		if ($id) {
			$this->assign('navTitle', '编辑');
			$info = MobgiApi_Service_InteractiveAdGoodsModel::getByID($id);
			if (!$info) {
				$this->output(-1, '非法操作');
			}

		} else {
			$info['start_time'] = date('Y-m-d');
			$info['end_time'] = date('Y-m-d');
		}
		$this->assign('info', $info);
		$this->assign('goodsType', MobgiApi_Service_InteractiveAdGoodsModel::$mGoodsType);
	}


	public function addPostAction()
	{
		$info = $this->getPost(array(
			'id',
			'title',
			'type',
			'status',
			'start_time',
			'end_time',
			'desc',
			'big_img',
			'icon'
		));
		$info = $this->checkPostParam($info);
		if ($info['id']) {
			$result = MobgiApi_Service_InteractiveAdGoodsModel::updateByID($info, $info['id']);
		} else {
			$result = MobgiApi_Service_InteractiveAdGoodsModel::add($info);
		}
		if (!$result) {
			$this->output(-1, '操作失败');
		}
		$this->output(0, '操作成功');

	}


	private function checkPostParam($info)
	{
		if (empty(trim($info['type']))) {
			$this->output(-1, '选择商品类型');
		}
		if (empty(trim($info['title']))) {
			$this->output(-1, '商品标题不能为空');
		}

		if (empty(trim($info['big_img']))) {
			$this->output(-1, '请上传大图');
		}
		if (empty(trim($info['icon']))) {
			$this->output(-1, '请上传icon');
		}
		if ($info['status'] == 'on') {
			$info['status'] = MobgiApi_Service_InteractiveAdGoodsModel::OPEN_STATUS;
		} else {
			$info['status'] = MobgiApi_Service_InteractiveAdGoodsModel::CLOSE_STATUS;
		}
		$params['title'] = $info['title'];
		if ($info['id']) {
			$params['id'] = array('<>', $info['id']);
		}
		$result = MobgiApi_Service_InteractiveAdGoodsModel::getBy($params);
		if ($result) {
			$this->output(-1, '标题已经存在');
		}
		$info['operator'] = $this->userInfo['user_name'];

		$this->checkActivityStatus($info['id'],$info['status']);
		return $info;
	}


	public function getGoodsIdsAction()
	{
		$type = $this->getGet('type');
		$is_fill = $this->getGet('is_fill');
		if (!$type) {
			$this->output(-1, '非法操作');
		}
		$goodsList = MobgiApi_Service_InteractiveAdGoodsModel::getsBy(array('type' => $type,'del'=>Common_Service_Const::NOT_DELETE_FLAG,'status'=>MobgiApi_Service_InteractiveAdGoodsModel::OPEN_STATUS));
		if(!$goodsList){
			$this->output(0, '列表为空');
		}
		if($type !=MobgiApi_Service_InteractiveAdGoodsModel::DEAFAULT_GOODS_TYPE && $is_fill == 0){
			foreach ($goodsList as $key=>$val){
				if(!$val['stock']){
					unset($goodsList[$key]);
					continue;
				}
				if(!($val['stock']>$val['used_num'])){
					unset($goodsList[$key]);
					continue;
				}
			}
			if(!$goodsList){
				$this->output(0, '列表为空');
			}
		}
		$goodsList = common::resetKeyValue($goodsList, 'id', 'title');
		$this->output(0, 'ok', $goodsList);
	}

	public function getGoodsDetailAction()
	{
		$id = $this->getGet('id');
		if (!$id) {
			$this->output(-1, '非法操作');
		}
		$goodsInfo = MobgiApi_Service_InteractiveAdGoodsModel::getByID($id);
		$goods = array();
		$goods['goods_id'] = $goodsInfo['id'];
		$goods['title'] = $goodsInfo['title'];
		$goods['icon'] = common::getAttachPath().$goodsInfo['icon'];
		$goods['left_num'] = $goodsInfo['stock']-$goodsInfo['used_num'];
		$this->output(0, 'ok', $goods);
	}

	public function uploadDescImgAction()
	{
		$name = 'upfile';
		$ext = strtolower(strrchr($_FILES[$name]["name"], '.'));
		$result = array(
			"originalName" => $_FILES[$name]['name'],
			"name" => $_FILES[$name]['name'],
			"url" => '',
			"size" => $_FILES[$name]['size'],
			"type" => $ext,
			"state" => ''
		);
		$ret = Common::upload($name, 'interad');
		if ($ret['code'] != 0) {
			$result['state'] = '上传失败！';
			die(json_encode($result));
		}
		$attachroot = common::getAttachUrl();
		$result['url'] = $attachroot . $ret['data'];
		$result['state'] = 'SUCCESS';
		exit(json_encode($result));
	}


}
