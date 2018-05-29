<?php
if (!defined('BASE_PATH')) exit ('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/28
 * Time: 15:11
 */
return [
    // 投放单元模块
    'track_index' => new Yaf_Route_Regex ( '#^/track/index$#', [
        'module' => 'Spm',
        'controller' => 'Track',
        'action' => 'index'
    ]),
    'track_common' => new Yaf_Route_Regex ( '#^/track/common$#', [
        'module' => 'Spm',
        'controller' => 'Track',
        'action' => 'common'
    ]),
    'track_gdt' => new Yaf_Route_Regex ( '#^/track/gdt$#', [
        'module' => 'Spm',
        'controller' => 'Track',
        'action' => 'gdt'
    ]),
    'track_gdtcgi' => new Yaf_Route_Regex ( '#^/track/gdtcgi$#', [
        'module' => 'Spm',
        'controller' => 'Track',
        'action' => 'gdtcgi'
    ]),
    'track_wechat' => new Yaf_Route_Regex ( '#^/track/wechat$#', [
        'module' => 'Spm',
        'controller' => 'Track',
        'action' => 'wechat'
    ]),
    'track_baidu' => new Yaf_Route_Regex ( '#^/track/baidu$#', [
        'module' => 'Spm',
        'controller' => 'Track',
        'action' => 'baidu'
    ]),
    'track_active' => new Yaf_Route_Regex ( '#^/track/active$#', [
        'module' => 'Spm',
        'controller' => 'Track',
        'action' => 'active'
    ]),
    'abroad_active' => new Yaf_Route_Regex ( '#^/abroad/active$#', [
        'module' => 'Spm',
        'controller' => 'Abroad',
        'action' => 'active'
    ]),
    'appsflyer_get_install_report' => new Yaf_Route_Regex ( '#^/appsflyer/get_install_report$#', [
        'module' => 'Spm',
        'controller' => 'Abroad',
        'action' => 'getInstallReport'
    ]),
    'abroad_get_install_report' => new Yaf_Route_Regex ( '#^/abroad/get_install_report$#', [
        'module' => 'Spm',
        'controller' => 'Abroad',
        'action' => 'getInstallReport'
    ]),
    'appsflyer_get_install_report_android' => new Yaf_Route_Regex ( '#^/appsflyer/get_install_report_android$#', [
        'module' => 'Spm',
        'controller' => 'Abroad',
        'action' => 'getInstallReportAndroid'
    ]),
    'abroad_get_install_report_android' => new Yaf_Route_Regex ( '#^/abroad/get_install_report_android$#', [
        'module' => 'Spm',
        'controller' => 'Abroad',
        'action' => 'getInstallReportAndroid'
    ]),
    'tools_gdtaccesstoken' => new Yaf_Route_Regex ( '#^/tools/gdtaccesstoken#', [
        'module' => 'Spm',
        'controller' => 'Tools',
        'action' => 'gdtAccessToken'
    ]),
    'tools_gdtrefreshtoken' => new Yaf_Route_Regex ( '#^/tools/gdtrefreshtoken#', [
        'module' => 'Spm',
        'controller' => 'Tools',
        'action' => 'gdtRefreshToken'
    ]),
];