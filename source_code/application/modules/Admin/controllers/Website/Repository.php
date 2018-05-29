<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/8/10
 * Time: 17:53
 */
class Website_RepositoryController extends Admin_BaseController {

    public $actions = array(
        'listUrl' => '/Admin/Website_Repository/index',
        'addUrl' => '/Admin/Website_Repository/add',
        'addPostUrl' => '/Admin/Website_Repository/addPost',
        'changeStatusUrl' => '/Admin/Website_Repository/changeStatus',
        'documentListUrl'=> '/Admin/Website_Repository/documentList',
        'addDocumentUrl' => '/Admin/Website_Repository/addDocument',
        'addDocumentPostUrl' => '/Admin/Website_Repository/addDocumentPost',
        'changeDocumentStatusUrl' => '/Admin/Website_Repository/changeDocumentStatus',
        'uploadImgUrl'=> '/Admin/Website_Repository/uploadImg',

    );
    public $perpage = 20;

    public $mLevel = array(1=>'一级菜单',2=>'二级菜单');

    /**
     *
     * Enter description here ...
     */
    public function indexAction() {
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search= $this->getInput(array('level','parent_id','name'));
        if ($search['level']) {
            $params['level'] = $search['level'];
        }
        if ($search['parent_id']) {
            $params['parent_id'] = $search['parent_id'];
        }
        if ($search['name']) {
            $params['name'] = array('LIKE', trim($search['name']));
        }
        $menuList=MobgiWww_Service_RepositoryMenuModel::getsBy(array('level'=>1));
        $menuMap = Common::resetKey($menuList, 'id');

        list($total, $list) =MobgiWww_Service_RepositoryMenuModel::getList($page, $this->perpage, $params,array('sort'=>'ASC'));
        $url = $this->actions['listUrl'].'/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->assign('menuList', $menuList);
        $this->assign('menuMap', $menuMap);
        $this->assign('navTitle', '添加菜单');

    }

    public function addAction() {
        $id = intval ( $this->getGet ( 'id'));
        $this->assign('navTitle', '添加');
        if ($id) {
            $this->assign('navTitle', '编辑');
            $info = MobgiWww_Service_RepositoryMenuModel::getByID ( $id );
            if (! $info) {
                $this->output ( - 1, '非法操作' );
            }
            $this->assign('info', $info);
        }
        $menuList=MobgiWww_Service_RepositoryMenuModel::getsBy(array('level'=>1));
        $this->assign('menuList', $menuList);

        $this->assign('level', $this->mLevel);
    }

    public function addPostAction() {
        $info = $this->getRequest ()->getPost ();
        $this->checkPostParam($info);
        $data = $this->fillData ( $info );
        if ($info ['id']) {
            $result = MobgiWww_Service_RepositoryMenuModel::updateByID ( $data, $info ['id'] );
        } else {
            $result = MobgiWww_Service_RepositoryMenuModel::add ( $data );
        }
        if (! $result) {
            $this->output ( - 1, '操作失败' );
        }
        $this->output ( 0, '操作成功');
    }

    private function fillData($info){
        $data['parent_id'] = intval($info['parent_id']);
        $data['name'] = trim($info['name']);
        $data['sort'] = intval($info['sort']);
        $data['level'] = intval($info['level']);
        return $data;
    }

    private function checkPostParam($info)
    {
        if (empty($info['name']) || empty($info['sort'] || empty($info['level']))){
            $this->output ( - 1, '请填写必填字段信息！！！' );
        }
        if($info['level'] != 1 && $info['parent_id'] == 0){
            $this->output ( - 1, '请选择所属的一级菜单！' );
        }
        $params['parent_id'] = intval($info['parent_id']);
        $params['level'] = intval($info['level']);
        $params['name'] = trim($info['name']);
        if($info['id']){
            $params['id'] =  array('<>', $info['id']);
        }
        $result=MobgiWww_Service_RepositoryMenuModel::getBy($params);
        if ( $result) {
            $this->output ( - 1, '此菜单已经存在' );
        }


    }

    public function changeStatusAction() {
        $id = intval ( $this->getGet ( 'id'));
        $status = intval ( $this->getGet ( 'status'));
        $data = array('status'=>$status);
        $result = MobgiWww_Service_RepositoryMenuModel::getByID ( $id );
        if (! $result) $this->output ( - 1, '操作失败' );
        $result = MobgiWww_Service_RepositoryMenuModel::updateByID ( $data, $id );
        if (! $result) $this->output ( - 1, '操作失败' );
        $this->output ( 0, '操作成功' );
    }

    public function documentListAction() {
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search= $this->getInput(array('menu_id','menu_parent_id','title'));
        if ($search['menu_id']) {
            $params['menu_id'] = $search['menu_id'];
        }
        if ($search['menu_parent_id']) {
            $params['menu_parent_id'] = $search['menu_parent_id'];
        }
        if ($search['title']) {
            $params['title'] = array('LIKE', trim($search['title']));
        }
        $menu1List=MobgiWww_Service_RepositoryMenuModel::getsBy(array('level'=>1));
        $menu1Map = Common::resetKey($menu1List, 'id');
        $menu2List=MobgiWww_Service_RepositoryMenuModel::getsBy(array('level'=>2));
        $menu2Map = Common::resetKey($menu2List, 'id');

        list($total, $list) =MobgiWww_Service_RepositoryDocumentModel::getList($page, $this->perpage, $params,array('update_time'=>'DESC'));
        $url = $this->actions['versionlistUrl'].'/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->assign('menu1Map', $menu1Map);
        $this->assign('menu2Map', $menu2Map);
        $this->assign('navTitle', '添加文档');

    }

    public function addDocumentAction() {
        $id = intval ( $this->getGet ( 'id'));
        $this->assign('navTitle', '添加');
        if ($id) {
            $this->assign('navTitle', '编辑');
            $info = MobgiWww_Service_RepositoryDocumentModel::getByID ( $id );
            if (! $info) {
                $this->output ( - 1, '非法操作' );
            }
            $this->assign('info', $info);
            $parentId = $info['menu_parent_id'];
        }else{
            $parentId = 0;
        }
        // 一级菜单
        $menu1List=MobgiWww_Service_RepositoryMenuModel::getsBy(array('level'=>1));
        $subMenuList = array();
        $menuKey = array();
        foreach($menu1List as $key => $value){
            $id = $value['id'];
            $subMenuList[$id] = $value;
            $subMenuList[$id] = array();
            $menuKey[] = $id;
        }
        // 二级菜单
        $menu2List=MobgiWww_Service_RepositoryMenuModel::getsBy(array('level'=>2));
        foreach($menu2List as $key => $value){
            $parent_id = $value['parent_id'];
            if(in_array($parent_id,$menuKey)){
                $subMenuList[$parent_id][] = $value;
            }
        }
        $nowSubMenuList = isset($subMenuList[$parentId]) ? $subMenuList[$parentId] : array();
        $this->assign('menu1List', $menu1List);
        $this->assign('nowSubMenuList', $nowSubMenuList);
        $this->assign('subMenuList', $subMenuList);
    }

    public function addDocumentPostAction() {
        $info = $this->getRequest ()->getPost ();
        $this->checkDocumentPostParam($info);
        $data = $this->fillDocumentData ( $info );
        if ($info ['id']) {
            $result = MobgiWww_Service_RepositoryDocumentModel::updateByID ( $data, $info ['id'] );
        } else {
            $result = MobgiWww_Service_RepositoryDocumentModel::add ( $data );
        }
        if (! $result) {
            $this->output ( - 1, '操作失败' );
        }
        $this->output ( 0, '操作成功');
    }

    private function fillDocumentData($info){
        $data['menu_id'] = intval($info['menu_id']);
        $data['menu_parent_id'] = intval($info['menu_parent_id']);
        $data['title'] = trim($info['title']);
//        $data['content'] = $info['content'];
        $attachroot = Yaf_Application::app()->getConfig()->attachroot;
        $attachspath = Common::getAttachPath();
        $data['content'] = str_replace($attachroot . '/attachs', $attachspath, $info['content']);
        return $data;
    }

    private function checkDocumentPostParam($info)
    {
        if (empty($info['menu_id']) || empty($info['menu_parent_id']) || empty($info['title']) || empty($info['content'])){
            $this->output ( - 1, '请填写必填字段信息！！！' );
        }
        $params['menu_id'] = intval($info['menu_id']);
        $params['menu_parent_id'] = intval($info['menu_parent_id']);
        $params['title'] = trim($info['title']);
        if($info['id']){
            $params['id'] =  array('<>', $info['id']);
        }
        $result=MobgiWww_Service_RepositoryDocumentModel::getBy($params);
        if ( $result) {
            $this->output ( - 1, '此插件已经存在' );
        }

    }

    public function changeDocumentStatusAction() {
        $id = intval ( $this->getGet ( 'id'));
        $status = intval ( $this->getGet ( 'status'));
        $data = array('status'=>$status);
        $result = MobgiWww_Service_RepositoryDocumentModel::getByID ( $id );
        if (! $result) $this->output ( - 1, '操作失败' );
        $result = MobgiWww_Service_RepositoryDocumentModel::updateByID ( $data, $id );
        if (! $result) $this->output ( - 1, '操作失败' );
        $this->output ( 0, '操作成功' );
    }



    public function uploadImgAction() {
        $name = 'upfile';
        $ext = strtolower( strrchr( $_FILES[$name][ "name" ] , '.' ) );
        $result = array(
            "originalName" => $_FILES[$name]['name'] ,
            "name" => $_FILES[$name]['name'] ,
            "url" => '' ,
            "size" => $_FILES[$name]['size'] ,
            "type" => $ext,
            "state" => ''
        );
        $ret = Common::upload($name, 'repdoc', array (
            'allowFileType' => array (
                'gif',
                'jpeg',
                'jpg',
                'png',
                'bmp'
            )
        ));
        if ($ret['code'] != 0) {
            $result['state'] = '上传失败！';
            die(json_encode($result));
        }
        $attachroot = Yaf_Application::app()->getConfig()->attachroot;
        $result['url'] = $attachroot . '/attachs' . $ret['data'];
        $result['state'] = 'SUCCESS';
        exit(json_encode($result));
    }
}
