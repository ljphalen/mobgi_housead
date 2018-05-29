<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * 定制渠道数据报表
 * @author atom.zhan
 *
 */
class MobgiData_Service_CustomModel extends MobgiData_Service_MiddleBaseModel {

    /**
     * 获取用户报表配置
     * @param $accountId
     * @return string
     */
    public static function getChartConf($accountId, $params = []) {
        $conf = [
            'api' => [
                "data" => '/Admin/Data_Report/getCustomData',
                "conf" => '/Admin/Data_Report/updateCustomKpi',
            ],
            'box' => [
                "app_key" => [],
                "pos_key" => [],
                "ad_type" => [],
                "platform" => [],
                "channel_gid" => [],
                "ads_id" => [],
                "ssp_id" => [],
                "country" => [],
                "province" => [],
            ],
            'conf' => self::$conf,
            'kpi' => [],
            'dim' => [
                "default_dim_dom" => "#dim",
                "default_dim_fields" => [
                    "days" => "日期",
                    "hours" => "小时",
                    "hr1" => "-",
                    "app_key" => "应用",
                    "pos_key" => "广告位",
                    "ad_type" => "广告类型",
                    "platform" => "平台",
                    "channel_gid" => "渠道",
                    "hr2" => "-",
                    "ads_id" => "广告商",
                    "ssp_id" => "流量主",
                    "hr3" => "-",
                    "app_version" => "游戏版本",
                    "sdk_version" => "SDK版本",
                    "hr4" => "-",
                    "country" => "国家",
                    "province" => "省份",
                ],
                "default_dim_value" => ["days" => []],
                "dims" => [],
            ],
        ];
        if (isset($params['dims'])) {
            $mydims = [];
            foreach ($params['dims'] as $dim) {
                $mydims[$dim] = $params[$dim] ?: [];
            }
            if (!empty($mydims)) {
                $conf['dim']['default_dim_value'] = $mydims;
            }
        }
        $kpis = $params['kpis'] ?: [];

        $conf['kpi'] = self::getChartKpis($accountId, $kpis);
        $conf['dim']['dims'] = self::getChartDims($accountId);
        return $conf;
    }

}
