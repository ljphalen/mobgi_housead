<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Interative_CodeController extends Admin_BaseController
{

	public $actions = array(
		'listUrl' => '/Admin/Interative_Code/index',
		'addUrl' => '/Admin/Interative_Code/add',
		'addPostUrl' => '/Admin/Interative_Code/addPost',
		'deleteUrl' => '/Admin/Interative_Code/delete',
		'viewUrl' => '/Admin/Interative_Code/view',
		'goodsListUrl'=>'/Admin/Interative_Goods/index',
		'getGoodsIdsUrl'=>'/Admin/Interative_Goods/getGoodsIds',
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
		$search = $this->getInput(array('code','goods_id'));

		if ($search['code']) {
			$params['code'] = array('LIKE', $search['code']);
		}

		if ($search['goods_id']) {
			$params['goods_id'] =  $search['goods_id'];
		}
		list($total, $goodsCodelist) = MobgiApi_Service_InteractiveAdGoodsCodeModel::getList($page, $this->perpage, $params);
		$url = $this->actions['listUrl'] . '/?' . http_build_query($search) . '&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
		$this->assign('search', $search);
		$this->assign('total', $total);
		$this->assign('goodsCodelist', $goodsCodelist);

		$goodsList = MobgiApi_Service_InteractiveAdGoodsModel::getsBy(array('del'=>Common_Service_Const::NOT_DELETE_FLAG));
		$goodsList = common::resetKeyValue($goodsList,'id','title');
		$this->assign('goodsList',$goodsList);
		$this->assign('goodsType', MobgiApi_Service_InteractiveAdGoodsModel::$mGoodsType);
		$this->assign('codeStatus', MobgiApi_Service_InteractiveAdGoodsCodeModel::$mCodeStatus);

	}


	public function getCode(){
		return strtoupper(dechex(crc32(md5(uniqid(time() . mt_rand(1,1000000))))));
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
		$this->assign('goodsType', MobgiApi_Service_InteractiveAdGoodsModel::$mGoodsType);
	}



	function input_csv($handle) {
		$out = array ();
		while ($data = fgetcsv($handle, 50000)) {
			$num = count($data);
			for ($i = 0; $i < $num; $i++) {
				$out[] = $data[$i];
			}
		}
		return $out;
	}

	public function addPostAction()
	{
		$info = $this->getPost(array(
			'id',
			'type',
			'goods_id',
			'num',
			'opt_type',
			'code_file'
		));
		$info = $this->checkPostParam($info);
		$goodsInfo = MobgiApi_Service_InteractiveAdGoodsModel::getBy(array('id'=>intval($info['goods_id'])));
		if (empty($goodsInfo)){
			$this->output(-1, '商品不存在操作非法');
		}
		$data = array();
		if($info['opt_type'] == 0){
			for ($i=1;$i<=$info['num'];$i++){
				$data[]               = array(
					'type'=>$info['type'],
					'goods_id'=>$info['goods_id'],
					'status'=>MobgiApi_Service_InteractiveAdGoodsCodeModel::CODE_DEFAULT_STATUS,
					'code'=>$this->getCode(),
					'create_time'=>date('Y-m-d H:i:s'),
				);
			}
		}else{
			$ext = strtolower(strrchr($info['code_file'], '.'));
			if($ext !='.csv'){
				$this->output(-1,'请上传csv文件'.$ext);
			}

			$attachPath = Common::getConfig ( 'siteConfig', 'attachPath' );
			$filePath = $attachPath.$info['code_file'];
			$file = fopen($filePath,"r");
			$codeList = $this->input_csv($file);
			if(!$codeList){
				$this->output(-1, 'csv文件不能为空');
			}
			fclose($file);
			foreach ($codeList as $val){
				if(!$val){
					continue;
				}
				if(!preg_match('/[0-9a-zA-Z]{6,20}/',$val)){
					$this->output(-1, '兑换码只能有六位数字与字母组成：'.$val);
				}
				$codeResult = MobgiApi_Service_InteractiveAdGoodsCodeModel::getBy(['code'=>$val]);
				if($codeResult){
					$this->output(-1, '兑换码已经存在'.$val);
				}
				$data[]               = array(
					'type'=>$info['type'],
					'goods_id'=>$info['goods_id'],
					'status'=>MobgiApi_Service_InteractiveAdGoodsCodeModel::CODE_DEFAULT_STATUS,
					'code'=>$val,
					'create_time'=>date('Y-m-d H:i:s'),
				);
			}
		}
		if(!$data){
			$this->output(-1, '兑换码数据为空');
		}
		Common_Service_Base::beginTransaction('mobgiApi');
		$result1 = MobgiApi_Service_InteractiveAdGoodsCodeModel::mutiFieldInsert($data);
		$stock = $goodsInfo['stock']+count($data);
		$result2 = MobgiApi_Service_InteractiveAdGoodsModel::updateByID(array('stock'=>$stock),$info['goods_id']);
		if (!$result1||!$result2) {
			Common_Service_Base::rollBack();
			$this->output(-1, '操作失败');
		}
		Common_Service_Base::commit();
		$this->output(0, '操作成功');

	}


	private function checkPostParam($info)
	{

		if (empty(trim($info['type']))) {
			$this->output(-1, '选择商品类型');
		}
		if (empty(trim($info['goods_id']))) {
			$this->output(-1, '选择商品');
		}
		if($info['opt_type'] == 0 && intval($info['num'])<= 0){
			$this->output(-1, '库存不能小于1');
		}
		$info['num'] = intval($info['num']);
		if($info['opt_type'] == 1 && !$info['code_file']){
			$this->output(-1, '无上传文件');
		}


		return $info;
	}









}
