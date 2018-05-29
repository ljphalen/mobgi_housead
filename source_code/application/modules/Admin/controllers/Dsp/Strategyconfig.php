<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Dsp_StrategyconfigController extends Admin_BaseController {
	
    public $perpage = 20;
	public $actions = array(
        'uploadUrl' => '/Admin/Dsp_Strategyconfig/uploadImg',
	    'uploadPostUrl' => '/Admin/Dsp_Strategyconfig/uploadImgPost',
        'listUrl' => '/Admin/Dsp_Strategyconfig/list',
        'saveConfigUrl'=>'/Admin/Dsp_Strategyconfig/saveConfig',
        'delUrl'=>'/Admin/Dsp_Strategyconfig/delConfig',
        'addUrl'=>'/Admin/Dsp_Strategyconfig/add',
	    'appListUrl'=>'/Admin/Dsp_Strategyconfig/appList',          
	);
    /**
     * 用户组列表
     */
    public function listAction() {
        $page = intval($this->getInput('page'));
        $name = $this->getInput('name');
		$perpage = $this->perpage;
        $params = array();
        if($name){
            $params['name']= array('like', $name);
        }
        $urlparam = "";
		list($total, $configs) = Advertiser_Service_AdAppkeyConfigModel::getList($page, $perpage, $params);
		$configs = Common::resetKey($configs, 'id');
        if($configs){
            foreach($configs as $key=>$config){
                //$configs[$key]['appkey_config_num'] = Advertiser_Service_OriginalityRelationPositionModel::getAppListCount(array('appkey_config_id'=>$config['id'],'is_delete'=>Common_Service_Const::NOT_DELETE_FLAG));
                $userinfo = Admin_Service_UserModel::getUser($config['operator']);
                $configs[$key]['operator_name'] = $userinfo['user_name'];
            }
        }
        $this->assign('name', $name);
        $this->assign('params', $params);
        $this->assign('total', $total);
        $this->assign('configs', $configs);
		$this->assign('pager', Common::getPages($total, $page, $perpage, $this->actions['listUrl'].'/?'.$urlparam));
	}
    /**
     * 
     * Enter description here ...
     */
    public function addAction() {
        $configid = $this->getInput('id');
        $customAnimationEffect = Common::getConfig('deliveryConfig','customAnimationEffect');
        if($configid){
        	$configInfo = Advertiser_Service_AdAppkeyConfigModel::getConfig($configid);
        	$this->assign('config', $configInfo);
        }
    	$this->assign('customAnimationEffect', $customAnimationEffect);
    }
	
    public function saveConfigAction() {
	    $info= $this->getInput(array('id','name','close_button_delay_show','close_button_delay_show_time','show_close_button','show_mute_button','show_download_button','show_progress_button',
            'video_jump_type','pic_jump_type','custom_jump_type',
            'border_type', 'border_color',  'border_cross_img', 'border_vertical_img',
            //交叉推广
            'custom_border_cross_img', 'custom_border_vertical_img', 'custom_close_button_url', 'custom_play_interval', 'custom_animation_effect', 'custom_boutique_label_url',
            //开屏
            'show_skip_button', 'show_countdown', 'dsp_waiting_time', 'display_time'
            ));
        if(empty($info['name'])){
            $this->output(1, '配置名称不能为空');
        }
        $info['id'] = intval($info['id']);
        if($info['id']){
            $ret = Advertiser_Service_AdAppkeyConfigModel::getBy(array('id'=>$info['id']));
            if(!$ret){
                $this->output(1, '配置不存在');
            }
            $ret = Advertiser_Service_AdAppkeyConfigModel::getBy(array('name'=>$info['name'], 'id'=>array("!=", $info['id'])));
            if($ret){
                $this->output(1, '配置名称已经存在');
            }
        }else{
            $ret = Advertiser_Service_AdAppkeyConfigModel::getBy(array('name'=>$info['name']));
            if($ret){
                $this->output(1, '配置名称已经存在');
            }
        }
        
        //检测视频配置,组织视频配置数据
        if(!isset($info['show_close_button'])){
            $this->output(1, '请选择视频配置:关闭按钮');
        }
        if(!isset($info['show_mute_button'])){
            $this->output(1, '请选择视频配置:静音按钮');
        }
        if(!isset($info['show_download_button'])){
            $this->output(1, '请选择视频配置:下载按钮');
        }
        if(!isset($info['show_progress_button'])){
            $this->output(1, '请选择视频配置:进度按钮');
        }
        if(!isset($info['video_jump_type']) || $info['video_jump_type'] == ''){
            $this->output(1, '请选择视频配置:点击后动作');
        }
        $video_tmp = array();
        $video_tmp['show_close_button'] = $info['show_close_button'];
        $video_tmp['show_mute_button'] = $info['show_mute_button'];
        $video_tmp['show_download_button'] = $info['show_download_button'];
        $video_tmp['show_progress_button'] = $info['show_progress_button'];
        $video_tmp['jump_type'] = $info['video_jump_type'];
        
        //检测插页配置,组织插页配置数据
        if(!isset($info['close_button_delay_show'])){
            $this->output(1, '请选择插页配置:延迟按钮');
        }
        if($info['close_button_delay_show'] == '1' && intval($info['close_button_delay_show_time']) <= 0 ){
            $this->output(1, '插页配置:按钮延迟时间不合法');
        }
        $pic_tmp = array();
        $pic_tmp['close_button_delay_show'] = $info['close_button_delay_show'];
        $pic_tmp['close_button_delay_show_time'] = $info['close_button_delay_show_time'];
        $pic_tmp['border_type'] = $info['border_type'];
        $pic_tmp['border'] = $info['border'];
        if($pic_tmp['border_type'] == Advertiser_Service_AdAppkeyConfigModel::BODER_TYPE_COLOR){
            $pic_tmp['border'] = $info['border_color'];
            if(!preg_match('/^#[a-f0-9]{6}$/i',$info['border_color'])){
                $this->output(1, '色值错误,示例:#aa00bb');
            }
        }else{
            $pic_tmp['border_cross_img'] = $info['border_cross_img'];
            $pic_tmp['border_vertical_img'] = $info['border_vertical_img'];
        }
        if(!isset($info['pic_jump_type']) || $info['pic_jump_type'] == ''){
            $this->output(1, '请选择插页配置:点击后动作');
        }
        $pic_tmp['jump_type'] = $info['pic_jump_type'];
        
        //检测交叉推广配置,组织交叉推广配置数据
        if(empty($info['custom_border_cross_img'])){
            $this->output(1, '请选择交叉推广配置:横屏边框');
        }
        if(empty($info['custom_border_vertical_img'])){
            $this->output(1, '请选择交叉推广配置:竖屏边框');
        }
        if(empty($info['custom_close_button_url'])){
            $this->output(1, '请选择交叉推广配置:关闭按钮图片');
        }
        if(empty($info['custom_boutique_label_url'])){
            $this->output(1, '请选择交叉推广配置:精品推荐图片');
        }
        if(empty($info['custom_play_interval'])){
            $this->output(1, '请选择交叉推广配置:轮播间隔');
        }
        
        if(intval($info['custom_play_interval'])<=0){
            $this->output(1, '交叉推广配置:轮播间隔必须是正整数');
        }
        $customAnimationEffect = Common::getConfig('deliveryConfig','customAnimationEffect');
        if(!isset($customAnimationEffect[$info['custom_animation_effect']])){
            $this->output(1, '请选择交叉推广配置:原生banner动效');
        }
        $custom_tmp = array();
        $custom_tmp['custom_border_cross_img'] = $info['custom_border_cross_img'];
        $custom_tmp['custom_border_vertical_img'] = $info['custom_border_vertical_img'];
        $custom_tmp['custom_close_button_url'] = $info['custom_close_button_url'];
        $custom_tmp['custom_boutique_label_url'] = $info['custom_boutique_label_url'];
        $custom_tmp['custom_play_interval'] = intval($info['custom_play_interval']);
        $custom_tmp['custom_animation_effect'] = $info['custom_animation_effect'];
        
        //检测开屏配置,组织开屏配置数据
        if(!isset($info['show_skip_button'])){
            $this->output(1, '请选择开屏配置:展示跳过按钮');
        }
        if(!isset($info['show_countdown'])){
            $this->output(1, '请选择开屏配置:展示倒计时');
        }
        if(intval($info['dsp_waiting_time'])<=0){
            $this->output(1, '请选择开屏配置:DSP等待时长');
        }
        if(intval($info['display_time'])<=0){
            $this->output(1, '请选择开屏配置:开屏展示时长');
        }
        $splash_tmp = array();
        $splash_tmp['show_skip_button'] = $info['show_skip_button'];
        $splash_tmp['show_countdown'] = $info['show_countdown'];
        $splash_tmp['dsp_waiting_time'] = intval($info['dsp_waiting_time']);
        $splash_tmp['display_time'] = intval($info['display_time']);
        
        $config = array();
        $config[Common_Service_Const::VIDEO_AD_SUB_TYPE] = $video_tmp;
        $config[Common_Service_Const::PIC_AD_SUB_TYPE] = $pic_tmp;
        $config[Common_Service_Const::CUSTOME_AD_SUB_TYPE] = $custom_tmp;
        $config[Common_Service_Const::SPLASH_AD_SUB_TYPE] = $splash_tmp;
        
        $data = array();
        $data['name'] = $info['name'];
        $data['config'] = $config;
        $data['operator'] = $this->userInfo['user_id'];
        if($info['id']){
            $result = Advertiser_Service_AdAppkeyConfigModel::updateConfig($data, $info['id']);
        }else{
            $result = Advertiser_Service_AdAppkeyConfigModel::addConfig($data);
        }
        if(!$result){
            $this->output(1, '操作失败');
        }
        $this->output(0, '操作成功');
	}
    /**
     * 软删除配置
     */
    public function delConfigAction(){
        $info= $this->getInput(array('id'));
        $info['id'] = intval($info['id']);
        if($info['id']){
            $ret = Advertiser_Service_AdAppkeyConfigModel::getBy(array('id'=>$info['id']));
            if(!$ret){
                $this->output(1, '配置不存在');
            }
            $appkeyconfignum = Advertiser_Service_OriginalityRelationPositionModel::getAppListCount(array('appkey_config_id'=>$info['id']));
            if($appkeyconfignum){
                $this->output(1, '该配置正在使用,不能删除');
            }
            $data = array();
            $data['del'] = 1;
            $data['operator'] = $this->userInfo['uid'];
            $result = Advertiser_Service_AdAppkeyConfigModel::updateConfig($data, $info['id']);
            if(!$result){
                $this->output(1, '操作失败');
            }
            $this->output(0, '操作成功');
        }else{
            $this->output(1, '参数错误');
        }
    }
	
	
    public function uploadImgAction() {
	    $imgId = $this->getInput('imgId');
	    $this->assign('imgId', $imgId);
	    $this->getView()->display('common/upload.phtml');
	    exit;
	}
	
	public function uploadImgPostAction() {
	    $ret = Common::upload('img', 'delivery', array('png'));
	    $imgId = $this->getInput('imgId');
	    $this->assign('code' , $ret['data']);
	    $this->assign('msg' , $ret['msg']);
	    $this->assign('data', $ret['data']);
	    $this->assign('imgId', $imgId);
	    $this->getView()->display('common/upload.phtml');
	    exit;
	}
	
}
