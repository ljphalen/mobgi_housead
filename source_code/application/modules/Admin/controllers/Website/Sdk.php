<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/8/10
 * Time: 17:53
 */
class Website_SdkController extends Admin_BaseController {

    public $actions = array(
        'listUrl' => '/Admin/Website_Sdk/index',
        'addUrl' => '/Admin/Website_Sdk/add',
        'addPostUrl' => '/Admin/Website_Sdk/addPost',
        'changeStatusUrl' => '/Admin/Website_Sdk/changeStatus',
        'versionListUrl'=> '/Admin/Website_Sdk/versionList',
        'addVersionUrl' => '/Admin/Website_Sdk/addVersion',
        'addVersionPostUrl' => '/Admin/Website_Sdk/addVersionPost',
        'changeVersionStatusUrl' => '/Admin/Website_Sdk/changeVersionStatus',
        'uploadUrl' => '/Admin/Website_Sdk/uploadImg',
        'uploadPostUrl' => '/Admin/Website_Sdk/uploadImgPost',
        'uploadZipUrl' => '/Admin/Website_Sdk/uploadZip',
        'uploadZipPostUrl' => '/Admin/Website_Sdk/uploadZipPost',

    );
    public $perpage = 20;

    public $mSdkType = array(1=>'安卓',2=>'IOS');

    /**
     *
     * Enter description here ...
     */
    public function indexAction() {
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search= $this->getInput(array('sdk_type','name'));
        if ($search['sdk_type']) {
            $params['sdk_type'] = $search['sdk_type'];
        }
        if ($search['name']) {
            $params['name'] = array('LIKE', trim($search['name']));
        }
        list($total, $list) =MobgiWww_Service_SdkInfoModel::getList($page, $this->perpage, $params,array('sort'=>'DESC'));
        $url = $this->actions['listUrl'].'/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->assign('navTitle', '新增插件');

    }

    public function addAction() {
        $id = intval ( $this->getGet ( 'id'));
        $this->assign('navTitle', '添加');
        if ($id) {
            $this->assign('navTitle', '编辑');
            $info = MobgiWww_Service_SdkInfoModel::getByID ( $id );
            if (! $info) {
                $this->output ( - 1, '非法操作' );
            }
            $this->assign('info', $info);
        }
        $this->assign('sdkType', $this->mSdkType);
    }

    public function addPostAction() {
        $info = $this->getRequest ()->getPost ();
        $this->checkPostParam($info);
        $data = $this->fillData ( $info );
        if ($info ['id']) {
            $result = MobgiWww_Service_SdkInfoModel::updateByID ( $data, $info ['id'] );
        } else {
            $result = MobgiWww_Service_SdkInfoModel::add ( $data );
        }
        if (! $result) {
            $this->output ( - 1, '操作失败' );
        }
        $this->output ( 0, '操作成功');
    }

    private function fillData($info){
        $data['sdk_type'] = intval($info['sdk_type']);
        $data['name'] = trim($info['name']);
        $data['logo_url'] = trim($info['logo_url']);
        $data['sort'] = intval($info['sort']);
        return $data;
    }

    private function checkPostParam($info)
    {
        if (empty($info['sdk_type']) || empty($info['name']) || empty($info['logo_url'] || empty($info['sort']))){
            $this->output ( - 1, '请填写必填字段信息！！！' );
        }
        $params['sdk_type'] = intval($info['sdk_type']);
        $params['name'] = trim($info['name']);
        if($info['id']){
            $params['id'] =  array('<>', $info['id']);
        }
        $result=MobgiWww_Service_SdkInfoModel::getBy($params);
        if ( $result) {
            $this->output ( - 1, '此插件已经存在' );
        }


    }

    public function changeStatusAction() {
        $id = intval ( $this->getGet ( 'id'));
        $status = intval ( $this->getGet ( 'status'));
        $data = array('status'=>$status);
        $result = MobgiWww_Service_SdkInfoModel::getByID ( $id );
        if (! $result) $this->output ( - 1, '操作失败' );
        $result = MobgiWww_Service_SdkInfoModel::updateByID ( $data, $id );
        if (! $result) $this->output ( - 1, '操作失败' );
        $this->output ( 0, '操作成功' );
    }

    public function versionListAction() {
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search= $this->getInput(array('sdk_type','sdk_id'));
        if ($search['sdk_type']) {
            $params['sdk_type'] = $search['sdk_type'];
        }
        if ($search['sdk_id']) {
            $params['sdk_id'] = $search['sdk_id'];
        }
        $sdkList=MobgiWww_Service_SdkInfoModel::getsBy();
        $sdkMap = Common::resetKey($sdkList, 'id');

        list($total, $list) =MobgiWww_Service_SdkVersionModel::getList($page, $this->perpage, $params,array('sdk_update_time'=>'DESC'));
        $url = $this->actions['versionlistUrl'].'/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->assign('sdkList', $sdkList);
        $this->assign('sdkMap', $sdkMap);
        $this->assign('navTitle', '新增插件版本');

    }

    public function addVersionAction() {
        $id = intval ( $this->getGet ( 'id'));
        $this->assign('navTitle', '添加');
        if ($id) {
            $this->assign('navTitle', '编辑');
            $info = MobgiWww_Service_SdkVersionModel::getByID ( $id );
            if (! $info) {
                $this->output ( - 1, '非法操作' );
            }
            $this->assign('info', $info);
        }
        $sdkList = array();
        $sdkList[1]=MobgiWww_Service_SdkInfoModel::getsBy(array('sdk_type'=>1));
        $sdkList[2]=MobgiWww_Service_SdkInfoModel::getsBy(array('sdk_type'=>2));
        $nowSdkList = isset($info) ? $sdkList[$info['sdk_type']] : array();
        $this->assign('sdkList', $sdkList);
        $this->assign('nowSdkList', $nowSdkList);
        $this->assign('sdkType', $this->mSdkType);
    }

    public function addVersionPostAction() {
        $info = $this->getRequest ()->getPost ();
        $this->checkVersionPostParam($info);
        $data = $this->fillVersionData ( $info );
        if ($info ['id']) {
            $result = MobgiWww_Service_SdkVersionModel::updateByID ( $data, $info ['id'] );
        } else {
            $result = MobgiWww_Service_SdkVersionModel::add ( $data );
        }
        if (! $result) {
            $this->output ( - 1, '操作失败' );
        }
        $this->output ( 0, '操作成功');
    }

    private function fillVersionData($info){
        $data['sdk_type'] = intval($info['sdk_type']);
        $data['sdk_id'] = intval($info['sdk_id']);
        $data['version'] = trim($info['version']);
        $data['sdk_update_time'] = $info['sdk_update_time'];
        $data['update_log'] = $info['update_log'];
        if(empty($info['update_log'])){
            $this->output ( - 1, '请填写更新日志' );
        }
        $data['sdk_url'] = trim($info['sdk_url']);
        $data['document_url'] = trim($info['document_url']);
        return $data;
    }

    private function checkVersionPostParam($info)
    {
        if (empty($info['sdk_type']) || empty($info['sdk_id']) || empty($info['version']) || empty($info['sdk_update_time']) || empty($info['sdk_url']) || empty($info['document_url'])){
            $this->output ( - 1, '请填写必填字段信息！！！' );
        }
        $params['sdk_type'] = intval($info['sdk_type']);
        $params['sdk_id'] = intval($info['sdk_id']);
        $params['version'] = trim($info['version']);
        if($info['id']){
            $params['id'] =  array('<>', $info['id']);
        }
        $result=MobgiWww_Service_SdkVersionModel::getBy($params);
        if ( $result) {
            $this->output ( - 1, '此插件已经存在' );
        }

    }

    public function changeVersionStatusAction() {
        $id = intval ( $this->getGet ( 'id'));
        $status = intval ( $this->getGet ( 'status'));
        $data = array('status'=>$status);
        $result = MobgiWww_Service_SdkVersionModel::getByID ( $id );
        if (! $result) $this->output ( - 1, '操作失败' );
        $result = MobgiWww_Service_SdkVersionModel::updateByID ( $data, $id );
        if (! $result) $this->output ( - 1, '操作失败' );
        $this->output ( 0, '操作成功' );
    }

    public function uploadImgAction() {
        $imgId = $this->getInput ( 'imgId' );
        $this->assign ( 'imgId', $imgId );
        $this->getView ()->display ( 'common/upload.phtml' );
        exit ();
    }

    public function uploadImgPostAction() {
        $ret = Common::upload ( 'img', 'logo', array (
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


    public function uploadZipAction() {
        $imgId = $this->getInput('imgId');
        $this->assign('imgId', $imgId);
        $this->getView()->display('common/uploadZip.phtml');
        exit;
    }

    public function uploadZipPostAction() {
        $ret = Common::upload('img', 'sdkdowload',array('maxSize'=>2000000,'allowFileType'=>array('rar','zip')), true, true );
        $imgId = $this->getInput('imgId');
        $this->assign('code' , $ret['data']);
        $this->assign('msg' , $ret['msg']);
        $this->assign('data', $ret['data']);
        $this->assign('imgId', $imgId);
        $this->getView()->display('common/uploadZip.phtml');
        exit;
    }


}
