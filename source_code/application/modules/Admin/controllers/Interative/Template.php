<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Interative_TemplateController extends Admin_BaseController
{

	public $actions = array(
		'listUrl' => '/Admin/Interative_Template/index',
		'addUrl' => '/Admin/Interative_Template/add',
		'addPostUrl' => '/Admin/Interative_Template/addPost',
		'deleteUrl' => '/Admin/Interative_Template/delete',
		'viewUrl' => '/Admin/Interative_Template/view',
		'uploadPostUrl' => '/Admin/Interative_Template/uploadPost',
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
		$search = $this->getInput(array('template_name'));

		if ($search['template_name']) {
			$params['name'] = array('LIKE', $search['template_name']);
		}
		list($total, $templateList) = MobgiApi_Service_InteractiveAdTemplateModel::getList($page, $this->perpage, $params);
		$url = $this->actions['listUrl'] . '/?' . http_build_query($search) . '&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
		$this->assign('search', $search);
		$this->assign('total', $total);
		$this->assign('templateList', $templateList);
	}


	public function addAction()
	{
		$id = intval($this->getGet('id'));
		$this->assign('navTitle', '添加');
		if ($id) {
			$this->assign('navTitle', '编辑');
			$info = MobgiApi_Service_InteractiveAdTemplateModel::getByID($id);
			if (!$info) {
				$this->output(-1, '非法操作');
			}
			$this->assign('info', $info);
		}
		$this->assign('adTypeList', Common_Service_Const::$mAdSubType);
	}

	public function addPostAction()
	{
		$info = $this->getPost(array(
			'id',
			'template_name',
			'url',
		));
		$info = $this->checkPostParam($info);
		if ($info ['id']) {
			$result = MobgiApi_Service_InteractiveAdTemplateModel::updateByID($info, $info ['id']);
		} else {
			$result = MobgiApi_Service_InteractiveAdTemplateModel::add($info);
		}
		if (!$result) {
			$this->output(-1, '操作失败');
		}
		$this->output(0, '操作成功');

	}


	private function checkPostParam($info)
	{

		if (empty(trim($info['template_name']))) {
			$this->output(-1, '请填写模板名称');
		}
		$info['template_name']=trim($info['template_name']);
		if (!common::checkUrl($info['url'])) {
			$this->output(-1, 'url不合法');
		}

		$result = MobgiApi_Service_InteractiveAdTemplateModel::getBy(['template_name'=>trim($info['template_name']),'id'=>array('<>',$info['id'])]);
		if($result){
			$this->output(-1, '模板名称已存在');
		}

		return $info;
	}


	/**
	 *
	 * Enter description here ...
	 */
	public function deleteAction()
	{
		$id = $this->getInput('id');
		$result = MobgiApi_Service_InteractiveAdTemplateModel::getByID($id);
		if (!$result) $this->output(-1, '操作失败');
		if (MobgiApi_Service_InteractiveAdConfModel::getBy(array('template_id' => $id))) {
			$this->output(-1, '此模板正使用');
		}
		$result = MobgiApi_Service_InteractiveAdTemplateModel::deleteById($id);
		if (!$result) $this->output(-1, '操作失败');
		$this->output(0, '操作成功');
	}

	/**
	 *
	 * Enter description here ...
	 */
	public function viewAction()
	{
		$id = intval($this->getGet('id'));
		$this->assign('navTitle', '添加');
		if ($id) {
			$this->assign('navTitle', '编辑');
			$info = MobgiApi_Service_TemplateModel::getByID($id);
			if (!$info) {
				$this->output(-1, '非法操作');
			}
			$this->assign('info', $info);
		}
		$this->assign('adTypeList', Common_Service_Const::$mAdSubType);
	}

	public function uploadPostAction()
	{
		$ret = Common::upload('file', 'template', array('maxSize' => 1048576));
		$this->output($ret['code'], $ret['msg'], $ret['data']);
	}


}
