<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author matt.liu
 *
 */

class MobgiData_Service_SynModel extends MobgiData_Service_BaseModel {
    const AD_TYPE_VIDEO = 1;
    const AD_TYPE_PIC = 2;
    const AD_TYPE_CUSTOME = 3;
    const AD_TYPE_SPLASH = 4;
    const AD_TYPE_ENBED = 5;
    const AD_TYPE_INTERATIVE = 6;


    const channel_total_type = array(
        1 => '百度',
        2 => '华为',
        3 => '小米',
        4 => '联想',
        5 => '阿里云',
        6 => 'oppo',
        7 => '金立',
        8 => '360',
        9 => '酷派',
        10 => '豌豆荚',
        11 => '安智',
        12 => '魅族',
        13 => '4399',
        14 => 'UC',
        15 => '三星',
        16 => '2345',
        17 => '酷狗',
        18 => '力天保利',
        19 => '卓游',
        20 => '美图',
        21 => '掌星',
        22 => '掌星立意',
        23 => '连尚',
        24 => 'TCL',
        25 => '应用汇',
        26 => '拇指玩',
        27 => '掌越',
        28 => '云雀',
        29 => '走马',
        30 => '邻动',
        31 => '聚乐',
        32 => '锤子',
        33 => '7k',
        34 => '努比亚',
        35 => '酷比',
        36 => '安锋网',
        37 => '益玩',
        38 => '乐泾达',
        39 => '酷我',
        40 => '3533',
        41 => '宝软',
        42 => '福建风灵',
        43 => '迅瑞',
        44 => '游戏狗',
        45 => '移卓',
        46 => '翱海科技',
        47 => '腾讯',
        48 => '西西软件',
        49 => 'vivo',
        50 => 'PPTV',
        51 => '乐视',
        52 => '青柠',
        53 => '搜狗',
        54 => '搜狐',
        55 => '新浪',
        56 => '优酷',
        57 => '其它',
        58 => '金立奥软'
    );


    //自动化同步,用于脚本自動化同步機制
    public static function sync_auto() {
        $types = array('app', 'ad_pos', 'ads_app_id', 'third_pos', 'channel', 'ads');
        $user = 'System';
        foreach ($types as $type) {
            self::sync_init($type);
            switch (strtolower($type)) {
                case 'app':
                    $result = self::sync_app();
                    $info = "应用";
                    break;
                case 'ad_pos':
                    $result = self::sync_ad_pos();
                    $info = "广告位";
                    break;
                case  'ads_app_id':
                    $result = self::sync_third_report_id();
                    $info = "第三方APPID";
                    break;
                case  'third_pos':
                    $result = self::sync_third_pos();
                    $info = "第三方广告位";
                    break;
                case 'channel':
                    $result = self::sync_channel();
                    $info = "渠道";
                    break;
                case  'ads':
                    $result = self::sync_ads();
                    $info = "广告商";
                    break;
                default: return false;
            }
            self::sync_dest($type);
            if (empty($result)) {
                MobgiData_Service_SynModel::Syn_log($info, '-', 1, $user);
            } else {
                MobgiData_Service_SynModel::Syn_log($info, json_encode($result), 0, $user);
            }
        }
        echo 'success';
    }

    /**
     * 广告map_id同步
     */
    public static function sync_third_report_id() {
        $sql = "select * from ads_app_rel where third_party_report_id != ''";
        $apiAdsInfo = self::getApiDao('AdsAppRel')->fetcthAll($sql);
        foreach ($apiAdsInfo as $key => $value) {
            $where = array(
                'report_id' => $value['third_party_report_id'],
                'app_key' => $value['app_key'],
            );
            $data = array(
                'app_key' => $value['app_key'],
                'ads_id' => $value['ads_id'],
                'app_name' => $value['app_name'],
                'report_id' => $value['third_party_report_id'],
                'create_time' => date("Y-m-d H:i:s", time()),
                'ad_type' => $value['ad_sub_type'],
                'status' => 1,
                'syn_flag' => 1,
            );
            if (self::getDao('ConfigAdsApp')->getBy($where)) {
                self::getDao('ConfigAdsApp')->updateBy($data, $where);
            } else {
                self::getDao('ConfigAdsApp')->insert($data);
            }
        }
    }

    /**
     * 同步记录插入
     */
    public static function syn_log($eventType, $msg, $status, $username) {
        $data = array(
            'event' => $msg,
            'event_type' => $eventType,
            'username' => $username,
            'status' => $status,
            'createtime' => date('Y-m-d', time()),
        );
        return self::getDao('ReportSynLog')->insert($data);
    }

    /**
     * 广告位map_blockid同步
     */
    public static function sync_third_pos() {
        $sql = "select a.app_key,a.ads_id,a.ad_sub_type as ad_type,a.pos_key,a.third_party_block_id as third_pos_key_l,a.third_party_report_id as third_pos_key_m,b.dever_pos_name as pos_name from ads_pos_rel as a LEFT JOIN ad_dever_pos as b on a.pos_id = b.id where a.third_party_report_id != '' or a.third_party_block_id !=''";
        $apiAdsInfo = self::getApiDao('AdsPosRel')->fetcthAll($sql);
        #20180423新增流量配置第三方block_id
        $sql2 = "select app_key,ad_type,pos_key,ads_id,third_party_block_id as third_pos_key_m  from flow_pos_rel group by app_key,ad_type,pos_key,ads_id,third_party_block_id";
        $flowAdsInfo = self::getApiDao('FlowPosRel')->fetcthAll($sql2);
        $apiAdsInfo = array_merge($apiAdsInfo,$flowAdsInfo);
        foreach ($apiAdsInfo as $key => $val) {
            $where = array(
                'app_key' => $val['app_key'],
                'ads_id' => $val['ads_id'],
                'pos_key' => $val['pos_key'],
                'third_pos_key'=>empty($val['third_pos_key_m']) ? $val['third_pos_key_l'] : $val['third_pos_key_m'],
            );
            #自投过滤
            if ($val['ads_id'] == 'Mobgi' || $val['ads_id'] == 'Housead_DSP') $val['third_pos_key_m'] = $val['pos_key'];
            $data = array(
                'app_key' => $val['app_key'],
                'ads_id' => $val['ads_id'],
                'pos_key' => $val['pos_key'],
                'pos_name' => isset($val['pos_name'])?$val['pos_name']:null,
                'third_pos_key' => empty($val['third_pos_key_m']) ? $val['third_pos_key_l'] : $val['third_pos_key_m'],
                'ad_type' => $val['ad_type'],
                'syn_flag' => 1,
            );
            if (self::getDao('ConfigAdsPos')->getBy($where)) {
                self::getDao('ConfigAdsPos')->updateBy($data, $where);
            } else {
                self::getDao('ConfigAdsPos')->insert($data);
            }
        }
    }


    /**
     * 广告shang同步
     */
    public static function sync_ads() {
        $sql = "SELECT * FROM ads_list WHERE ad_type = 1 or ad_type = 3";
        $apiAdsInfo = self::getApiDao('AdsList')->fetcthAll($sql);
        foreach ($apiAdsInfo as $key => $value) {
            $where['identifier'] = $value['ads_id'];
            if (self::getDao('ConfigAds')->getBy($where)) {
                $data = array(
                    'syn_flag' => 1,
                    'update_time' => date('Y-m-d H:i:s', time()),
                    'is_foreign' => $value['is_foreign'],
                );
                self::getDao('ConfigAds')->updateBy($data, $where);
            } else {
                $data = array(
                    'identifier' => $value['ads_id'],
                    'is_reload' => 1,
                    'charge_type' => 1,
                    'time_limit' => 30,
                    'last_time' => date('Y-m-d H:i:s', time() - 86400),
                    'next_time' => date('Y-m-d H:i:s', time()),
                    'is_foreign' => $value['is_foreign'],
                    'period' => 86400,
                    'status' => 1,
                    'update_time' => date('Y-m-d H:i:s', time()),
                    'syn_flag' => 1,
                );
                self::getDao('ConfigAds')->insert($data);
            }
        }
    }




    /**
     * 应用同步
     */
    public static function sync_app() {
        $where['app_key'] = array('!=', '');
        $apiAppInfo = self::getApiDao('AdApp')->getsBy($where);
        $appkey = array();
        foreach ($apiAppInfo as $key => $value) {
            $params['app_id'] = $value['app_id'];
            $data = array(
                'app_id' => $value['app_id'],
                'app_name' => $value['app_name'],
                'app_key' => $value['app_key'],
                'platform' => $value['platform'],
                'appcate_id' => $value['appcate_id'],
                'developer' => $value['dev_id'],
                'status' => $value['state'],
                'app_type' => $value['app_type'],
                'dgc_game_id' => $value['out_game_id'],
                'syn_flag' => 1,
            );
            if (self::getDao('ConfigApp')->getBy($params)) {
                unset($data['app_id']);
                self::getDao('ConfigApp')->updateBy($data, $params);
            } else {
                self::getDao('ConfigApp')->insert($data);
            }
            if ($value['appkey'] == '8E69498B356D95CCB579') {
                array_push($appkey, 'ff15f96a336e5340a33c');
            } else {
                array_push($appkey, $value['appkey']);
            }
        }
    }


    //同步广告位
    public static function sync_ad_pos() {
        $sql = "SELECT pos_key_type AS ad_type,dever_pos_key AS pos_key, dever_pos_name AS pos_name, ad_dever_pos.state as 
        status, ad_app.app_key as app_key FROM ad_dever_pos LEFT JOIN ad_app ON ad_dever_pos.app_id = ad_app.app_id WHERE ad_app.app_key != ''AND dever_pos_name != ''";
        $apiAppInfo = self::getApiDao('AdDeverPos')->fetcthAll($sql);
        $map_ad_type = array(
            'VIDEO_INTERGRATION' => self::AD_TYPE_VIDEO,
            'PIC_INTERGRATION' => self::AD_TYPE_PIC,
            'CUSTOME_INTERGRATION' => self::AD_TYPE_CUSTOME,
            'SPLASH_INTERGRATION' => self::AD_TYPE_SPLASH,
            'ENBED_INTERGRATION' => self::AD_TYPE_ENBED,
            'INTERATIVE_AD' => self::AD_TYPE_INTERATIVE
        );
        foreach ($apiAppInfo as $key => $value) {
            //$where = "pos_key='{$value['pos_key']}' and app_key='{$value['app_key']}'";
            $where = array(
                'pos_key' => $value['pos_key'],
                'app_key' => $value['app_key'],
            );
            $value['ad_type'] = trim($value['ad_type']);
            if (array_key_exists($value['ad_type'], $map_ad_type)) {
                $value['ad_type'] = $map_ad_type[$value['ad_type']];
            } else {
                $value['ad_type'] = 0;
            }
            $value['syn_flag'] = 1;
            if (self::getDao('ConfigPos')->getBy($where)) {
                self::getDao('ConfigPos')->updateBy($value, $where);
            } else {
                self::getDao('ConfigPos')->insert($value);
            }
        }
    }
    //同步渠道
    public static function sync_channel() {
        $where['channel_id'] = array('!=', '');
        $apiAppInfo = self::getApiDao('Channel')->getsBy($where);
        foreach ($apiAppInfo as $key => $value) {
            $where['id'] = $value['id'];
            $data = array(
                'id' => $value['id'],
                'channel_id' => $value['channel_id'],
                'channel_name' => $value['channel_name'],
                'group_id' => $value['group_id'],
                'group_name' => self::channel_total_type[$value['group_id']],
                'ads_id' => $value['ads_id'],
                'is_custom' => $value['is_custom'],
                'status' => 1,
                'syn_flag' => 1,
            );
            if (self::getDao('ConfigChannels')->getBy($where)) {
                self::getDao('ConfigChannels')->updateBy($data, $where);
            } else {
                self::getDao('ConfigChannels')->insert($data);
            }
        }
    }


    //同步初始化检查,修正syn_flag
    public static function sync_init($type) {
        switch (strtolower($type)) {
            case 'app':
                $table = "ConfigApp";
                break;
            case 'ad_pos':
                $table = "ConfigPos";
                break;
            case  'ads_app_id':
                $table = "ConfigAdsApp";
                break;
            case  'third_pos':
                $table = "ConfigAdsPos";
                break;
            case 'channel':
                $table = "ConfigChannels";
                break;
            case  'ads':
                $table = "ConfigAds";
                break;
        }
        $data = array('syn_flag' => 0);
        return self::getDao($table)->updateByNoWhere($data);
    }

    //同步结束后检查,delete无效数据段
    public static function sync_dest($type) {
        switch (strtolower($type)) {
            case 'app':
                $table = "ConfigApp";
                break;
            case 'ad_pos':
                $table = "ConfigPos";
                break;
            case  'ads_app_id':
                $table = "ConfigAdsApp";
                break;
            case  'third_pos':
                $table = "ConfigAdsPos";
                break;
            case 'channel':
                $table = "ConfigChannels";
                break;
            case  'ads':
                $table = "ConfigAds";
                break;
        }
        //删除无效数据段
        $where = array('syn_flag' => 0);
        return self::getDao($table)->deleteBy($where);
    }
}
