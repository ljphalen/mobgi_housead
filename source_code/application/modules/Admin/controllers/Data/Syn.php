<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 *
 * Enter description here ...
 * @author matt.liu
 *
 */
class Data_SynController extends Admin_BaseController {

    public $actions = array(
        'AppSynUrl' => '/Admin/Data_Syn/appsyn',
        'AdPosSynUrl' => '/Admin/Data_Syn/adpossyn',
        'AdsAppSynUrl' => '/Admin/Data_Syn/adsappsyn',
        'AdsSynUrl' => '/Admin/Data_Syn/adssyn',
        'ChannelSynUrl' => '/Admin/Data_Syn/channelsyn',
        'AppSynUrl' => '/Admin/Data_Syn/appsyn',
        'ThirdPosSynUrl'=>'/Admin/Data_Syn/thirdpossyn',
        'IndexUrl'=>'/Admin/Data_Syn/index',
        'SynUrl'=>'/Admin/Data_Syn/syncConfig',
        'SynLogUrl'=>'/Admin/Data_Syn/synlog',
    );

    public $ad_type = array(
        1=>'视频',
        2=>'插页',
        3=>'自定义',
        4=>'开屏',
        5=>'原生信息流'
    );

    public $rmbtodoller = 6.5;

    public $perpage = 20;


    public function indexAction(){

    }
    /**
     *
     * 数据同步首页
     */
    public function adpossynAction() {
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search= $this->getInput(array('pos_name','pos_key'));
        if ($search['pos_key']) {
            $page = 0;
            $params['pos_key'] = array('LIKE',$search['pos_key']);
        }
        if($search['pos_name']){
            $page = 0;
            $params['pos_name'] = array('LIKE',$search['pos_name']);
        }
        $List =MobgiData_Service_SynModel::getDao('ConfigPos')->getList(($page-1)*$this->perpage, $this->perpage*$page,$params,array('status'=>"DESC"));
        $total = MobgiData_Service_SynModel::getDao('ConfigPos')->count($params);
        $url = $this->actions['AdPosSynUrl'].'/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('List', $List);
    }


    public function adsappsynAction(){
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search= $this->getInput(array('app_key','app_name'));
        if ($search['app_key']) {
            $page = 0;
            $params['app_key'] = array('LIKE',$search['app_key']);
        }
        if($search['app_name']){
            $page = 0;
            $params['app_name'] = array('LIKE',$search['app_name']);
        }
        $List =MobgiData_Service_SynModel::getDao('ConfigAdsApp')->getList(($page-1)*$this->perpage, $this->perpage*$page, $params,array('status'=>"DESC"));
        $total = MobgiData_Service_SynModel::getDao('ConfigAdsApp')->count($params);
        foreach ($List as $key=>&$val){
            $val['ad_type'] = $this->ad_type[$val['ad_type']];
        }
        $url = $this->actions['AdsAppSynUrl'].'/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('List', $List);
    }




    //应用同步页面
    public function appsynAction(){
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search= $this->getInput(array('app_key','app_name'));
        if ($search['app_key']) {
            $page = 0;
            $params['app_key'] = array('LIKE',$search['app_key']);
        }
        if($search['app_name']){
            $page = 0;
            $params['app_name'] = array('LIKE',$search['app_name']);
        }
        $List =MobgiData_Service_SynModel::getDao('ConfigApp')->getList(($page-1)*$this->perpage, $this->perpage*$page, $params,array('status'=>"DESC"));
        $total = MobgiData_Service_SynModel::getDao('ConfigApp')->count($params);
        $url = $this->actions['AppSynUrl'].'/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('List', $List);
    }

    public function channelsynAction(){
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search= $this->getInput(array('channel_id','channel_name'));
        if ($search['channel_id']) {
            $page = 0;
            $params['channel_id'] = array('LIKE',$search['channel_id']);
        }
        if($search['channel_name']){
            $page = 0;
            $params['channel_name'] = array('LIKE',$search['channel_name']);#$search['channel_name'];
        }
        $List =MobgiData_Service_SynModel::getDao('ConfigChannels')->getList(($page-1)*$this->perpage, $this->perpage*$page, $params,array('status'=>"DESC"));
        $total = MobgiData_Service_SynModel::getDao('ConfigChannels')->count($params);
        $url = $this->actions['ChannelSynUrl'].'/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('List', $List);
    }

    public function synlogAction(){
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search= $this->getInput(array('searchtime'));
        if ($search['searchtime']) {
            $page = 0;
            $params['createtime'] = $search['searchtime'];
        }else{
            $params['createtime'] = date("Y-m-d",time());
        }

        $List =MobgiData_Service_SynModel::getDao('ReportSynLog')->getList(($page-1)*$this->perpage, $this->perpage*$page, $params,array('status'=>"DESC"));
        $total = MobgiData_Service_SynModel::getDao('ReportSynLog')->count($params);
        $url = $this->actions['SynLogUrl'].'/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('List', $List);
    }

    public function thirdpossynAction(){
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search= $this->getInput(array('app_key','pos_name','pos_key','ads_id'));
        if ($search['pos_key']) {
            $params['pos_key'] = array('LIKE',$search['pos_key']);
        }
        if($search['pos_name']){
            $params['pos_name'] = array('LIKE',$search['pos_name']);
        }
        if($search['ads_id']){
            $params['ads_id'] = array('LIKE',$search['ads_id']);
        }
        if($search['app_key']){
            $params['app_key'] = array('LIKE',$search['app_key']);
        }

        $List =MobgiData_Service_SynModel::getDao('ConfigAdsPos')->getList(($page-1)*$this->perpage, $this->perpage*$page, $params,array('ads_id'=>"DESC"));
        $total = MobgiData_Service_SynModel::getDao('ConfigAdsPos')->count($params);
        foreach ($List as $key=>&$val){
            $val['ad_type'] = $this->ad_type[$val['ad_type']];
        }
        $url = $this->actions['ThirdPosSynUrl'].'/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('List', $List);
    }



    public function syncConfigAction() {
        $params = $this->getInput('type');
        MobgiData_Service_SynModel::sync_init($params);
        switch (strtolower($params)) {
            case 'app':
                $result = MobgiData_Service_SynModel::sync_app();
                $info = "应用";
                break;
            case 'ad_pos':
                $result = MobgiData_Service_SynModel::sync_ad_pos();
                $info = "广告位";
                break;
            case  'ads_app_id':
                $result = MobgiData_Service_SynModel::sync_third_report_id();
                $info = "第三方APPID";
                break;
            case  'third_pos':
                $result = MobgiData_Service_SynModel::sync_third_pos();
                $info = "第三方广告位";
                break;
            case 'channel':
                $result = MobgiData_Service_SynModel::sync_channel();
                $info = "渠道";
                break;
            case  'ads':
                $result = MobgiData_Service_SynModel::sync_ads();
                $info = "广告商";
                break;
        }
        MobgiData_Service_SynModel::sync_dest($params);
        header("Content-type: application/json");
        if (empty($result)) {
            MobgiData_Service_SynModel::Syn_log($info,'-',1,$this->userInfo['user_name']);
            exit(json_encode(['code' => 0]));
        } else {
            MobgiData_Service_SynModel::Syn_log($info,json_encode($result),0,$this->userInfo['user_name']);
            $result['code'] = 1;
            exit(json_encode($result));
        }
    }
}
