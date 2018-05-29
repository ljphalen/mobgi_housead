<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-8-31 19:53:03
 * $Id: advertiserConfig.php 62100 2016-8-31 19:53:03Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

//$config['Admin_uid'] = 1;

$config['Admin_advertiser_operate_log'] = array(
        'adver_finance'=>array('cache_recharge','set_day_consumption_limit', 'transfer_account', 'transferback_account','apply_invoice'),//资金
//        'adver_advertiser_management'=>array('add_advertiser', 'unbind_advertiser','login_advertiser', 'edit_advertiser'),//子客管理
        'adver_delivery'=>array('add_ad', 'del_ad', 'edit_ad', 'add_unit', 'del_unit', 'edit_unit', 'add_originality', 'edit_originality', 'del_originality', 'add_direct', 'edit_direct', 'del_direct'),
        'adver_message'=>array('edit_contact_information', 'edit_tips'),
        'adver_account'=>array('edit_account'),
    
        'sys_finance'=>array('check_invoice','draw_invoice'),
//        'sys_advertiser_management'=>array('check_advertiser'),
        'sys_account'=>array('check_account'),
        'sys_delivery'=>array('check_delivery')
);

$config['Admin_advertiser_operate_log_name'] = array(
	'advertiser' => '本帐号',
    'system_manager'=>'系统管理员',
    
    'adver_finance'=>'资金',
    'adver_advertiser_management'=>'子客管理',
    'adver_delivery'=>'投放管理',
    'adver_message'=>'消息设置',
    'adver_account'=>'帐号信息',
    'sys_finance'=>'资金',
    'sys_advertiser_management'=>'子客管理',
    'sys_account'=>'帐号信息',
    'sys_delivery'=>'投放审核',
    
    'cache_recharge'=>'现金充值', 
    'set_day_consumption_limit'=>'设置日限额',
    'transfer_account'=>'向子客划账', 
    'transferback_account'=>'从子客回划',
    'apply_invoice'=>'申请发票',
    
    'add_advertiser'=>'新增子客', 
    'unbind_advertiser'=>'解绑子客',
    'login_advertiser'=>'登录子客', 
    'edit_advertiser'=>'编辑子客信息',
    
    'add_ad'=>'新增广告', 
    'del_ad'=>'删除广告',
    'edit_ad'=>'编辑广告',
    'add_unit'=>'新增投放单元',
    'del_unit'=>'删除投放单元',
    'edit_unit'=>'编辑投放单元',
    'add_originality'=>'新增创意',
    'edit_originality'=>'编辑创意',
    'del_originality'=>'删除创意',
    'add_direct'=>'新增定向',
    'edit_direct'=>'编辑定向',
    'del_direct'=>'删除定向',
    
    'edit_contact_information'=>'编辑联系方式', 
    'edit_tips'=>'编辑tips设置',
    
    'edit_account'=>'编辑账号信息',
    
    'check_invoice'=>'发票审核',
    'draw_invoice'=>'开票',
    'check_advertiser'=>'子客审核',
    'check_account'=>'帐号审核',
    'check_delivery'=>'投放广告审核'
);

$config['Admin_operate_log'] = array(
        'admin_usergroup'=>array(
            'change_usergroup','add_advertisergroup', 'del_advertisergroup', 'modify_advertisergroup',//用户管理
            'change_systemusergroup','add_systemgroup', 'del_systemgroup', 'modify_systemgroup',//系统用户管理
        ),
        'admin_account'=>array('add_accounttask', 'audit_accounttask'),
        'admin_ad'=>array('audit_originality'),
        'admin_ad_position'=>array('add_originality_conf', 'modify_originality_conf', 'del_originality_conf','del_ad_position','change_ad_switch','change_ad_weight'),
);

$config['Admin_operate_log_name'] = array(
    //一级
    'admin_usergroup'=>'用户管理',
    'admin_account'=>'账务管理',
    'admin_ad'=>'广告管理',
    'admin_ad_position'=>'广告位管理',
    
    //二级
    'change_usergroup'=>'修改用户权限', 
    'add_advertisergroup'=>'新增权限套餐',
    'del_advertisergroup'=>'删除权限套餐', 
    'modify_advertisergroup'=>'修改权限套餐',
    'change_systemusergroup'=>'修改系统用户权限', 
    'add_systemgroup'=>'新增系统权限套餐',
    'del_systemgroup'=>'删除系统权限套餐', 
    'modify_systemgroup'=>'修改系统权限套餐',
    
    'add_accounttask'=>'新建任务', 
    'audit_accounttask'=>'审核任务',
    
    'audit_originality'=>'创意审核', 
    
    'add_originality_conf'=>'新增创意类型',
    'modify_originality_conf'=>'编辑创意类型',
    'del_originality_conf'=>'删除创意类型',
    'del_ad_position'=>'删除广告位',
    'change_ad_switch'=>'调整广告开关',
    'change_ad_weight'=>'调整广告权重',
);


$config['Admin_account_opertype'] = array(
    'recharge'=>'充值',
    'recovery'=>'回收'
);

$config['Admin_account_auditstate'] = array(
    'not_check'=>'未审核',
    'checked_success'=>'审核成功',
    'checked_failed'=>'审核失败'
);

$config['Admin_account_taskstate'] = array(
    'not_check'=>'未审核',
    'checked_failed'=>'审核失败',
    'sending'=>'发放中',
    'sended_success'=>'发放成功',
    'sended_failed'=>'发放失败',
    'sended_partial_success'=>'部分发放成功',
);

$config['Admin_account_taskdetailstate'] = array(
    'not_check'=>'未审核',
    'checked_failed'=>'审核失败',
    'sending'=>'发放中',
    'sended_success'=>'发放成功',
    'sended_failed'=>'发放失败',
);

//虚拟金帐户类型
$config['Admin_virtualaccount_type'] = array(
    'virtual_account1' => '虚拟帐户1',
    'virtual_account2' => '虚拟帐户2',
    'virtual_account3' => '虚拟帐户3',
    'virtual_account4' => '虚拟帐户4',
);
$config['Admin_division_config_status'] = array(
    '1'=>array('value'=>'有效','color'=>'green'),
    '0'=>array('value'=>'已失效','color'=>'red')
);
return $config;
