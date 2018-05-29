<?php
if (!defined('BASE_PATH')) exit ('Access Denied!');
/**
 *  stat模块
 */
return [

    'adx_stat' => new Yaf_Route_Regex ('#/adx/v1/report#', array(
        'module' => 'Stat',
        'controller' => 'Adx_V1_Stat',
        'action' => 'collect'
    )),

    'ssp_stat' => new Yaf_Route_Regex ('#^/ssp#', array(
        'module' => 'Stat',
        'controller' => 'Ssp_Stat',
        'action' => 'collect'
    )),

    'ssp_api' => new Yaf_Route_Regex ('#^/sapi#', array(
        'module' => 'Stat',
        'controller' => 'Ssp_Api',
        'action' => 'api'
    )),

    'mobgi_stat' => new Yaf_Route_Regex ('#stat#', array(
        'module' => 'Stat',
        'controller' => 'Mobgi_Stat',
        'action' => 'stat'
    )),

    'housead_stat' => new Yaf_Route_Regex ('#Stat/Data/collect#', [
        'module' => 'Stat',
        'controller' => 'Housead_Stat',
        'action' => 'collect'
    ]),

    'try_stat' => new Yaf_Route_Regex ('#^/try$#', [
        'module' => 'Stat',
        'controller' => 'Try_Stat',
        'action' => 'collect'
    ]),

    'adx_test' => new Yaf_Route_Regex ('#/adx/v1/server#', array(
        'module' => 'Stat',
        'controller' => 'Adx_V1_Test',
        'action' => 'dsp'
    )),

    'report_stat' => new Yaf_Route_Regex ('#^report_stat$#', array(
        'module' => 'Stat',
        'controller' => 'Data',
        'action' => 'collect'
    )),
];
