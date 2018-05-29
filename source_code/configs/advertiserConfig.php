<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-8-31 19:53:03
 * $Id: advertiserConfig.php 62100 2016-8-31 19:53:03Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');
#帐户类型
$config['Advertiser_account_type'] = array(
	'cache' => '现金帐户',
    'virtual_account1' => '虚拟帐户1',
    'virtual_account2' => '虚拟帐户2',
    'virtual_account3' => '虚拟帐户3',
    'virtual_account4' => '虚拟帐户4',
);

#交易类型
$config['Advertiser_operate_type'] = array(
	'recharge' => '充值',
    'deduction' => '扣费',
    'recovery' => '回收',
);

$config['Advertiser_cache_tip_limit'] = 500;

$config['Advertiser_operate_log'] = array(
	'advertiser' => array(
        'adver_finance'=>array('cache_recharge','set_day_consumption_limit', 'transfer_account', 'transferback_account','apply_invoice'),//资金
        'adver_delivery'=>array('add_ad', 'del_ad', 'edit_ad', 'add_unit', 'del_unit', 'edit_unit', 'add_originality', 'edit_originality', 'del_originality', 'add_direct', 'edit_direct', 'del_direct'),
        'adver_message'=>array('edit_contact_information', 'edit_tips'),
        'adver_account'=>array('edit_account'),
        ),
    'system_manager' => array(
        'sys_finance'=>array('check_invoice','draw_invoice'),
        'sys_account'=>array('check_account'),
        'sys_delivery'=>array('check_delivery'),
    ),
);

$config['Advertiser_operate_log_name'] = array(
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

$config['Advertiser_status'] = array(
    'notactive'=>'未激活',
    'actived'=>'未上传资质',
    'notchecked'=>'未审核',
    'checked_success'=>'审核通过',
    'checked_failed'=>'审核不通过',
    );

$config['Advertiser_bind_status'] = array(
    'notbind'=>'未绑定',
    'bindconfirm'=>'绑定确认中',
    'bindcheck'=>'绑定审核中',
    'binded'=>'已绑定',
    'unbindconfirm'=>'解绑确认中',
    'unbindcheck'=>'解绑审核中',
    );

//①普通 ②广点通 ③报表查看 ④广告商（实时） ⑤广告商（同步）
$config['Advertiser_type'] = array(
    1=>'普通',
    2=>'广点通',
    3=>'报表查看',
    4=>'广告商（实时）',
    5=>'广告商（同步）',
);
return $config;
