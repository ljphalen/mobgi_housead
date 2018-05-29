<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-10-18 14:20:15
 * $Id: gdtdirectconfig.php 62100 2016-10-18 14:20:15Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

$config = array(
    'test' => array (
        'GDT_DIRECT_URL' => 'http://sandbox.api.e.qq.com/ads/',
        'API_VERSION' => "v3",
        'RESOURCE_NAME' => array(
            "advertiser",   //广告主模块
            "campaign",     //推广计划模块
            "adgroup",      //广告组模块
            "creative",     //创意模块
            "product",      //推广标的物模块
            "targeting",    //定向模块
            "targeting_customized_audience",    //自定义人群模块
            "targeting_location",               //自定义商圈模块
            "account",      //资金账户模块
            "report",       //报表模块
            "agency",       //代理商模块
            "utility",      //工具模块
            'image',        //图片管理模块
            "media",      //工具模块
            'agency',       //代理商
        ),
        'RESOURCE_ACTION' => array(
            "create",       //创建
            "read",         //读取详细信息
            "update",       //更改
            "delete" ,      //删除
            "select",       //获取列表
            "get_app_category_list", //app类型列表
            'get_creative_template_refs',
            'get_union_media_category_list',//移动联盟媒体分类
            'create_by_url',                //url  方式 创建 图片
            'get_advertiser_list'           //获取子客帐号列表
            
        ),
        'ACTION_METHOD' => array(
            "create"=>'post',
            "read"=>'get',
            "update"=>'post',
            "delete"=>'post',
            "select"=>'get',
        ),
    ),
    'product' => array (
        'GDT_DIRECT_URL' => 'https://api.e.qq.com/ads/',
        'API_VERSION' => "v3",
        'RESOURCE_NAME' => array(
            "advertiser",   //广告主模块
            "campaign",     //推广计划模块
            "adgroup",      //广告组模块
            "creative",     //创意模块
            "product",      //推广标的物模块
            "targeting",    //定向模块
            "targeting_customized_audience",    //自定义人群模块
            "targeting_location",               //自定义商圈模块
            "account",      //资金账户模块
            "report",       //报表模块
            "agency",       //代理商模块
            "utility",      //工具模块
            'image',        //图片管理模块
            "media",      //工具模块
            'agency',       //代理商
        ),
        'RESOURCE_ACTION' => array(
            "create",       //创建
            "read",         //读取详细信息
            "update",       //更改
            "delete" ,      //删除
            "select",       //获取列表
            "get_app_category_list", //app类型列表
            'get_creative_template_refs',
            'get_union_media_category_list',//移动联盟媒体分类
            'get_region_list',//地域定向分类
            'get_business_interest_list',//商业兴趣定向分类
            'create_by_url',                //url  方式 创建 图片
            'get_advertiser_list'           //获取子客帐号列表
            
        ),
        'ACTION_METHOD' => array(
            "create"=>'post',
            "read"=>'get',
            "update"=>'post',
            "delete"=>'post',
            "select"=>'get',
        ),
    ),
    'develop' => array (
        'GDT_DIRECT_URL' => 'http://sandbox.api.e.qq.com/ads/',
        'API_VERSION' => "v3",
        'RESOURCE_NAME' => array(
            "advertiser",   //广告主模块
            "campaign",     //推广计划模块
            "adgroup",      //广告组模块
            "creative",     //创意模块
            "product",      //推广标的物模块
            "targeting",    //定向模块
            "targeting_customized_audience",    //自定义人群模块
            "targeting_location",               //自定义商圈模块
            "account",      //资金账户模块
            "report",       //报表模块
            "agency",       //代理商模块
            "utility",      //工具模块
            'image',        //图片管理模块
            "media",      //工具模块
            'agency',       //代理商
        ),
        'RESOURCE_ACTION' => array(
            "create",       //创建
            "read",         //读取详细信息
            "update",       //更改
            "delete" ,      //删除
            "select",       //获取列表
            "get_app_category_list", //app类型列表
            'get_creative_template_refs',
            'get_union_media_category_list',//移动联盟媒体分类
            'get_region_list',//地域定向分类
            'get_business_interest_list',//商业兴趣定向分类
            'create_by_url',                //url  方式 创建 图片
            'get_advertiser_list'           //获取子客帐号列表
            
        ),
        'ACTION_METHOD' => array(
            "create"=>'post',
            "read"=>'get',
            "update"=>'post',
            "delete"=>'post',
            "select"=>'get',
        ),
    )
);
return defined('ENV') ? $config[ENV] : $config['product'];