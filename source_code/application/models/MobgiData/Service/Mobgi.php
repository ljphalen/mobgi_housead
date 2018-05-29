<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * 变现数据报表
 * @author atom.zhan
 *
 */
class MobgiData_Service_MobgiModel extends MobgiData_Service_MiddleBaseModel {

    public static $ltvFields = [
        'ad_type' => 'ad_type',
        'app_key' => 'app_key',
        'platform' => 'platform',
        'channel_gid' => 'channel_gid',
        'create_date' => 'create_date',
        'days' => 'action_date',
        'rday' => 'rday',
        'ads_id' => 'ads_id',
        'impressions' => 'sum(impressions)',
        'clicks' => 'sum(clicks)',
        'actives' => 'sum(actives)',
        'count' => 'sum(count)'
    ];

    public static $ltvLocs = [
        'ad_type' => ['name' => '广告类型', 'width' => 100],
        'app_key' => ['name' => '应用', 'width' => 100],
        'channel_gid' => ['name' => '渠道', 'width' => 100],
        'platform' => ['name' => '平台', 'width' => 100],
        'create_date' => ['name' => 'create_date', 'width' => 100],
        'action_date' => ['name' => 'action_date', 'width' => 100],
        'rday' => ['name' => '', 'width' => 100],
        'impressions' => ['name' => '展示', 'width' => 100],
        'clicks' => ['name' => '点击', 'width' => 100],
        'actives' => ['name' => '激活', 'width' => 100]
    ];

    public static $nuvFields = [
        'ad_type' => 'ad_type',
        'app_key' => 'app_key',
        'platform' => 'platform',
        'channel_gid' => 'channel_gid',
        'days' => 'days',
        'pos_key' => 'pos_key',
        'country' => 'country',
        'province' => 'province',
        'ads_id' => 'ads_id'
    ];

    public static $nuvToolBar = [
        1 => '第1次',
        2 => '第2次',
        3 => '第3次',
        4 => '第4次',
        5 => '第5次',
        6 => '第6次',
        7 => '第7-11次',
        8 => '第11-20次',
        9 => '20次以上',
    ];


    public static $watchingTimeToolBar = [
        0 => '0',
        1 => '第1次',
        2 => '第2次',
        3 => '第3次',
        4 => '第4次',
        5 => '第5次',
        6 => '第6次',
        7 => '第7-11次',
        8 => '第12-20次',
        9 => '20次以上',
    ];

    public static $watchingTimeDetailToolBar = [
        1 => '第1分钟',
        2 => '第2分钟',
        3 => '第3分钟',
        4 => '第4分钟',
        5 => '第5分钟',
        6 => '第6分钟',
        7 => '第7-11分钟',
        8 => '第12-20分钟',
        9 => '20分钟以上',
    ];


    public static function getLtvChannelNew($params, $dims) {
        $flag = false;
        if (in_array('ad_type', $dims)) {
            $flag = true;
            unset($dims[array_search('ad_type', $dims)]);
        }
        $params['dims'] = array_merge($dims, ['days']);
        $params['kpis'] = ['new_user'];
        $list = self::getDauData($params);
        if ($flag) {
            $result = [];
            $adType = array_keys(Common_Service_Config::AD_TYPE);
            foreach ($list as $item) {
                foreach ($adType as $type) {
                    $item['ad_type'] = $type;
                    $result[] = $item;
                }
            }
            return $result;
        } else {
            return $list;
        }
    }

    public static function getNuvTotalUser($params, $dims = array()) {
        if (in_array('ad_type', $dims)) {
            $flag = true;
            unset($dims[array_search('ad_type', $dims)]);
        }
        $params['dims'] = array_merge($dims, ['days']);
        $params['kpis'] = ['total_user'];
        $list = self::getDauData($params);
        $totalUsers = 0;
        foreach ($list as $key => $val) {
            $totalUsers += $val['total_user'];
        }
        return $totalUsers;
    }

    public static function getNuvData($params) {
        $mydims = [];
        $where = [];
        if (isset($params['app_key'])) {
            array_push($mydims, 'app_key');
            $where['app_key'] = $params['app_key'][0];
        }
        if (isset($params['ad_type'])) {
            $where['ad_type'] = $params['ad_type'][0];
        }
        if (isset($params['channel_gid'])) {
            $where['channel_gid'] = $params['channel_gid'][0];
        }
        if (isset($params['platform'])) {
            array_push($mydims, 'platform');
            $where['platform'] = $params['platform'][0];
        }
        if (isset($params['pos_key'])) {
            $where['pos_key'] = $params['pos_key'][0];
        }
        if (isset($params['ads_id'])) {
            $where['ads_id'] = $params['ads_id'][0];
        }


        $kpis = "days,times,sum(people_count) as people_count,sum(total_count) as total_count";
        #$groupBy = 'event_count';
        $groupBy = 'group by times,days';#group by 条件

        if (!empty($dims)) {
            $orderBy = [];
            foreach ($dims as $dkey => $dim) {
                array_push($orderBy, $dim . ' asc');
            }
            $orderBy = implode(',', $orderBy);
        } else {
            $orderBy = NULL;
        }

        $where['days'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));

        $newUsers = self::getNuvTotalUser($params, $mydims);

        $list = self::getDao('ReportTimes')->getData($kpis, $where, $groupBy, $orderBy);
        $result = array();
        $days = [];
        $sum_people = 0;
        $sum_total = 0;

        foreach ($list as $key => $val) {
            $days[$val['days']] = $val['days'];
            $sum_people += $val['people_count'];
            $sum_total += $val['total_count'];
        }
        ksort($days);
        foreach (self::$nuvToolBar as $times => $val) {
            foreach ($days as $day) {
                $result[$times][$day] = 0;
            }
        }
        foreach ($list as $key => $val) {
            $result[$val['times']][$val['days']] = $val['people_count'];
        }
        $data = array();
        foreach ($result as $val) {
            $data[] = array_values($val);
        }
        $res['list'] = $data;
        $res['times'] = array_values(self::$nuvToolBar);
        $res['toolbar'] = array_values($days);
        $res['avg'] = ($sum_people != 0) ? round(floatval($sum_total) / $sum_people, 2) : 0;
        $res['avgtotaluser'] = ($newUsers != 0) ? round(floatval($sum_total) / $newUsers, 2) : 0;
        return $res;
    }


    /**
     * 求两个日期之间相差的天数
     * (针对1970年1月1日之后，求之前可以采用泰勒公式)
     * @param string $day1
     * @param string $day2
     * @return number
     */
    public static function diffBetweenTwoDays($day1, $day2) {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);

        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }

    public static function getLtvData($params) {
        $allFields = self::$ltvFields;
        $title = self::$ltvLocs;
        $where = [];
        $mydims = [];
        $map = [];
        if (isset($params['app_key'])) {
            $where['app_key'] = array('in', $params['app_key']);
            array_push($mydims, 'app_key');
            $map['app_key'] = self::getAppKeyMap();
        }
        if (isset($params['ad_type'])) {
            $where['ad_type'] = array('in', $params['ad_type']);
            array_push($mydims, 'ad_type');
            $map['ad_type'] = Common_Service_Config::AD_TYPE;
        }
        if (isset($params['channel_gid'])) {
            $where['channel_gid'] = array('in', $params['channel_gid']);
            array_push($mydims, 'channel_gid');
            $map['channel_gid'] = self::getChannels();
        }
        if (isset($params['platform'])) {
            $where['platform'] = array('in', $params['platform']);
            array_push($mydims, 'platform');
            $map['platform'] = Common_Service_Config::PLATFORM;
        }
        $dims = $mydims;
        $dims[] = 'rday';
        $kpis = ['impressions', 'clicks'];

        $groupBy = empty($dims) ? null : 'GROUP BY ' . implode(',', array_merge($dims, ['ads_id', 'create_date', 'action_date']));
        if (!empty($dims)) {
            $orderBy = [];
            foreach ($dims as $dkey => $dim) {
                array_push($orderBy, $dim . ' asc');
            }
            $orderBy = implode(',', $orderBy);
        } else {
            $orderBy = NULL;
        }

        $where['create_date'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));
        $where['action_date'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));
        //        $groupBy = empty($dims) ? null : 'GROUP BY ' . implode(',', array_merge($mydims, ['create_date', 'days']));
        //        $strField = self::getLtvFileds(array_merge($mydims, ['create_date', 'days', 'count']));
        //        $dauList = self::getDao("ReportDau")->getData($strField, $where, $groupBy, $orderBy);
        $dauList = self::getLtvChannelNew($params, $mydims);

        $groupBy = empty($dims) ? null : 'GROUP BY ' . implode(',', array_merge($dims, ['ads_id', 'create_date', 'days']));
        $strField = self::getLtvFileds(array_merge($dims, $kpis, ['ads_id', 'create_date', 'days']));
        $diff = date_diff(date_create($params['edate']), date_create($params['sdate']));
        $where['rday'] = array(array('>=', 0), array('<=', $diff->days));
        $list = self::getDao("ReportLtv")->getData($strField, $where, $groupBy, $orderBy);


        $incomeDims = array_merge($mydims, ['ads_id', 'days']);

        $incomes = self::getAdsIncome($params['sdate'], $params['edate'], $incomeDims);
        $chargeMap = self::getChargeMap();
        $ecp = [];

        foreach ($incomes as $income) {
            $incomeKey = '';
            foreach ($incomeDims as $dim) {
                $incomeKey .= '_' . $income[$dim];
            }
            if (isset($chargeMap[$income['ads_id']]) and intval($income['ad_income']) > 0) {
                if ($chargeMap[$income['ads_id']] == '1' and intval($income['third_views']) > 0) {
                    $ecp[$incomeKey] = $income['ad_income'] / $income['third_views'];
                } else if ($chargeMap[$income['ads_id']] == '2' and intval($income['third_clicks']) > 0) {
                    $ecp[$incomeKey] = $income['ad_income'] / $income['third_clicks'];
                } else {
                    $ecp[$incomeKey] = 0;
                }
            } else {
                $ecp[$incomeKey] = 0;
            }
        }

        $result = [];
        foreach ($list as $key => $item) {

            $incomeKey = '';
            foreach ($incomeDims as $dim) {
                $incomeKey .= '_' . $item[$dim];
            }

            if (isset($chargeMap[$item['ads_id']]) and isset($ecp[$incomeKey])) {
                $item['value'] = ($chargeMap[$item['ads_id']] == '1' ? $item['impressions'] : $item['clicks']) * $ecp[$incomeKey];
            } else {
                $item['value'] = 0;
            }

            $myKey = '';
            if (empty($mydims)) {
                $myKey = 'all';
            } else {
                foreach ($mydims as $dim) {
                    $myKey .= '_' . $item[$dim];
                }
            }
            if (empty($result[$myKey])) {
                $legend = [];
                if (empty($mydims)) {
                    $legend[] = '总体LTV';
                } else {
                    foreach ($mydims as $dim) {
                        $result[$myKey][$dim] = $map[$dim][$item[$dim]];
                        $legend[] = $map[$dim][$item[$dim]];
                    }
                }
                $result[$myKey]['legend'] = implode('_', $legend);
            }

            $ltvKey = date_diff(date_create($item['days']), date_create($params['sdate']))->days;
            $result[$myKey]['ltv'][$ltvKey] += $item['value'];
        }


        foreach ($result as $key1 => $val1) {
            ksort($val1['ltv']);
            $midVal = 0;
            foreach ($val1['ltv'] as $key2 => $val2) {
                $midVal += $val2;
                $result[$key1]['ltv'][$key2] = number_format($midVal, 3, '.', '');
            }
        }


        $dau = [];
        foreach ($dauList as $key => $item) {
            $myKey = '';
            if (empty($mydims)) {
                $myKey = 'all';
            } else {
                foreach ($mydims as $dim) {
                    $myKey .= '_' . $item[$dim];
                }
            }
            $ltvKey = date_diff(date_create($item['days']), date_create($params['sdate']))->days;
            $dau[$myKey]['ltv'][$ltvKey] = $item['new_user'];
        }

        foreach ($dau as $key1 => $val1) {
            $midVal = 0;
            ksort($val1['ltv']);
            foreach ($val1['ltv'] as $key2 => $val2) {
                $midVal += $val2;
                $dau[$key1]['ltv'][$key2] = $midVal;
            }
        }

        foreach ($result as $key1 => $val1) {
            foreach ($val1['ltv'] as $key2 => $val2) {
                if (empty($dau[$key1]['ltv'][$key2])) {
                    $result[$key1]['ltv'][$key2] = 0;
                } else {
                    $result[$key1]['ltv'][$key2] = number_format($val2 * 6.5 / $dau[$key1]['ltv'][$key2], 4, '.', '');
                }
            }
        }

        $cols = [];
        if (!empty($list)) {
            foreach ($mydims as $dim) {
                $col = [
                    'field' => $dim,
                    'title' => $title[$dim]['name'],
                    'width' => 120,
                    'fixed' => 'left'
                ];
                $cols[] = $col;
            }
            for ($i = 0; $i <= $diff->days; $i++) {
                $col = [
                    'field' => 'ltv' . $i,
                    'title' => 'ltv(' . $i . ')',
                    'width' => 100,
                    'sort' => true
                ];
                $cols[] = $col;
            }


        }
        $data['dims'] = $mydims;
        $data['data'] = $result;
        $data['cols'] = $cols;
        $data['title'] = '用户广告LTV报表';
        $data['days'] = $diff->days;
        return $data;
    }

    public static function getLtv2Data($params) {
        $allFields = self::$ltvFields;
        $title = self::$ltvLocs;
        $where = [];
        $mydims = [];
        $map = [];
        if (isset($params['app_key'])) {
            $where['app_key'] = array('in', $params['app_key']);
            array_push($mydims, 'app_key');
            $map['app_key'] = self::getAppKeyMap();
        }
        if (isset($params['ad_type'])) {
            $where['ad_type'] = array('in', $params['ad_type']);
            array_push($mydims, 'ad_type');
            $map['ad_type'] = Common_Service_Config::AD_TYPE;
        }
        if (isset($params['channel_gid'])) {
            $where['channel_gid'] = array('in', $params['channel_gid']);
            array_push($mydims, 'channel_gid');
            $map['channel_gid'] = self::getChannels();
        }
        if (isset($params['platform'])) {
            $where['platform'] = array('in', $params['platform']);
            array_push($mydims, 'platform');
            $map['platform'] = Common_Service_Config::PLATFORM;
        }
        $dims = $mydims;
        $dims[] = 'rday';
        $kpis = ['impressions', 'clicks'];

        $groupBy = empty($dims) ? null : 'GROUP BY ' . implode(',', array_merge($dims, ['ads_id', 'create_date', 'action_date']));
        if (!empty($dims)) {
            $orderBy = [];
            foreach ($dims as $dkey => $dim) {
                array_push($orderBy, $dim . ' asc');
            }
            $orderBy = implode(',', $orderBy);
        } else {
            $orderBy = NULL;
        }

        $where['create_date'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));
        $where['action_date'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));


        //        $groupBy = empty($dims) ? null : 'GROUP BY ' . implode(',', array_merge($mydims, ['create_date', 'days']));
        //        $strField = self::getLtvFileds(array_merge($mydims, ['create_date', 'days', 'count']));
        //        $dauList = self::getDao("ReportLtvDau")->getCreateDau($strField, $where, $groupBy, $orderBy);
        $dauList = self::getLtvChannelNew($params, $mydims);

        $groupBy = empty($dims) ? null : 'GROUP BY ' . implode(',', array_merge($dims, ['ads_id', 'create_date', 'days']));
        $strField = self::getLtvFileds(array_merge($dims, $kpis, ['ads_id', 'create_date', 'days']));
        $diff = date_diff(date_create($params['edate']), date_create($params['sdate']));
        $where['rday'] = array(array('>=', 0), array('<=', $diff->days));
        $list = self::getDao("ReportLtv")->getData($strField, $where, $groupBy, $orderBy);
        $incomeDims = array_merge($mydims, ['ads_id', 'days']);

        $incomes = self::getAdsIncome($params['sdate'], $params['edate'], $incomeDims);
        $chargeMap = self::getChargeMap();
        $ecp = [];

        foreach ($incomes as $income) {
            $incomeKey = '';
            foreach ($incomeDims as $dim) {
                $incomeKey .= '_' . $income[$dim];
            }
            if (isset($chargeMap[$income['ads_id']]) and intval($income['ad_income']) > 0) {
                if ($chargeMap[$income['ads_id']] == '1' and intval($income['third_views']) > 0) {
                    $ecp[$incomeKey] = $income['ad_income'] / $income['third_views'];
                } else if ($chargeMap[$income['ads_id']] == '2' and intval($income['third_views']) > 0) {
                    $ecp[$incomeKey] = $income['ad_income'] / $income['third_clicks'];
                } else {
                    $ecp[$incomeKey] = 0;
                }
            } else {
                $ecp[$incomeKey] = 0;
            }
        }

        $result = [];
        foreach ($list as $key => $item) {
            $incomeKey = '';
            foreach ($incomeDims as $dim) {
                $incomeKey .= '_' . $item[$dim];
            }
            if (isset($chargeMap[$item['ads_id']]) and isset($ecp[$incomeKey])) {
                $item['value'] = ($chargeMap[$item['ads_id']] == '1' ? $item['impressions'] : $item['clicks']) * $ecp[$incomeKey];
            } else {
                $item['value'] = 0;
            }
            $myKey = '';
            if (empty($mydims)) {
                $myKey = 'all';
            } else {
                foreach ($mydims as $dim) {
                    $myKey .= '_' . $item[$dim];
                }
            }
            if (empty($result[$myKey])) {
                $legend = [];
                if (empty($mydims)) {
                    $legend[] = '总体LTV';
                } else {
                    foreach ($mydims as $dim) {
                        $result[$myKey][$dim] = $map[$dim][$item[$dim]];
                        $legend[] = $map[$dim][$item[$dim]];
                    }
                }
                $result[$myKey]['legend'] = implode('_', $legend);
            }
            //            $ltvKey = date_diff(date_create($item['create_date']), date_create($params['sdate']))->days;
            $result[$myKey]['ltv'][$item['rday']] += $item['value'];
        }
        $dau = [];
        foreach ($dauList as $key => $item) {
            $myKey = '';
            if (empty($mydims)) {
                $myKey = 'all';
            } else {
                foreach ($mydims as $dim) {
                    $myKey .= '_' . $item[$dim];
                }
            }
            $ltvKey = date_diff(date_create($item['days']), date_create($params['sdate']))->days;
            $dau[$myKey]['ltv'][$ltvKey] = $item['new_user'];
        }

        foreach ($dau as $key1 => $val1) {
            krsort($val1['ltv']);
            $midVal = 0;
            foreach ($val1['ltv'] as $key2 => $val2) {
                $midVal += $val2;
                $dau[$key1]['ltv'][$key2] = $midVal;
            }
        }

        //        foreach ($dau as $key1 => $val1) {
        //            ksort($val1['ltv']);
        //            $new_key = $old_key = array_keys($val1['ltv']);
        //            krsort($new_key);
        //            $new_key = array_values($new_key);
        //            $keymap = array_combine($old_key, $new_key);
        //            $tmpVal = [];
        //            foreach ($val1['ltv'] as $key2 => $val2) {
        //                $tmpVal[$key2] = $val1['ltv'][$keymap[$key2]];
        //            }
        //            $dau[$key1]['ltv'] = $tmpVal;
        //        }


        foreach ($result as $key1 => $val1) {
            ksort($val1['ltv']);
            foreach ($val1['ltv'] as $key2 => $val2) {
                if (empty($dau[$key1]['ltv'][$key2])) {
                    $result[$key1]['ltv'][$key2] = 0;
                } else {
                    $result[$key1]['ltv'][$key2] = number_format($val2 * 6.5 / $dau[$key1]['ltv'][$key2], 4, '.', '');
                }

            }
        }


        $cols = [];
        if (!empty($list)) {
            foreach ($mydims as $dim) {
                $col = [
                    'field' => $dim,
                    'title' => $title[$dim]['name'],
                    'width' => 120,
                    'fixed' => 'left'
                ];
                $cols[] = $col;
            }
            for ($i = 0; $i <= $diff->days; $i++) {
                $col = [
                    'field' => 'ltv' . $i,
                    'title' => 'val(' . $i . ')',
                    'width' => 100,
                    'sort' => true
                ];
                $cols[] = $col;
            }


        }

        $data['dims'] = $mydims;
        $data['data'] = $result;
        $data['cols'] = $cols;
        $data['title'] = '用户价值报表';

        $data['days'] = $diff->days;

        return $data;
    }


    public static function getLtvFileds($params) {
        $allFields = self::$ltvFields;
        $fields = [];
        foreach ($params as $item) {
            if (isset($item, $allFields)) {
                $fields[$item] = $allFields[$item];
            }
        }
        return self::buildFields($fields);
    }


    public static function getWatchingTimeData($params) {
        $where = [];
        $mydims = [];
        $map = [];
        if (isset($params['app_key'])) {
            $where['app_key'] = array('in', $params['app_key']);
            array_push($mydims, 'app_key');
            $map['app_key'] = self::getAppKeyMap();
        }
        if (isset($params['ad_type'])) {
            $where['ad_type'] = array('in', $params['ad_type']);
            array_push($mydims, 'ad_type');
            $map['ad_type'] = Common_Service_Config::AD_TYPE;
        }

        $dims = $mydims;
        //$dims[] = 'times';
        $kpis = ['sum(people_count) as people_count', 'per_min', 'times'];
        $groupBy = empty($dims) ? 'GROUP BY per_min,times' : 'GROUP BY per_min,times,' . implode(',', array_merge($dims));#order by 条件
        //$groupBy = null;
        if (!empty($dims)) {
            $orderBy = [];
            foreach ($dims as $dkey => $dim) {
                array_push($orderBy, $dim . ' asc');
            }
            $orderBy = implode(',', $orderBy);
        } else {
            $orderBy = NULL;
        }

        $where['days'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));
        $strField = implode(',', array_merge($dims, $kpis));

        $list = self::getDao("ReportWatchingTime")->getData($strField, $where, $groupBy, $orderBy);

        $timesMap = array();

        foreach ($list as $key => $val) {
            $exchangeTime = self::watchingTimeExchnge($val['times']);
            $typeKey = '';
            if (!empty($mydims)) {
                foreach ($mydims as $keys => $dim) {
                    if (!empty($val[$dim])) {
                        $typeKey .= '_' . $val[$dim];
                    }
                }
            } else {
                $typeKey = 'all';
            }

            if (isset($timesMap[$typeKey]['per_time'][$exchangeTime])) {
                $timesMap[$typeKey]['per_time'][$exchangeTime]['sum'] += $val['per_min'] * $val['people_count'];
                $timesMap[$typeKey]['per_time'][$exchangeTime]['peoplesum'] += $val['people_count'];
            } else {
                $timesMap[$typeKey]['per_time'][$exchangeTime]['sum'] = $val['per_min'] * $val['people_count'];
                $timesMap[$typeKey]['per_time'][$exchangeTime]['peoplesum'] = $val['people_count'];
            }
            if (!empty($timesMap[$typeKey])) {
                $legend = [];
                if (empty($mydims)) {
                    $legend[] = '总体用户';
                } else {
                    foreach ($mydims as $dim) {
                        $timesMap[$typeKey][$dim] = $map[$dim][$val[$dim]];
                        $legend[] = $map[$dim][$val[$dim]];
                    }
                }
                $timesMap[$typeKey]['legend'] = implode('_', $legend);
            }


        }

        $i = 0;
        $searchMap = array();
        foreach ($timesMap as $type => $time) {#算出每次观看的时间的平均时间
            $result[$type]['legend'] = $timesMap[$type]['legend'];
            foreach ($mydims as $dim) {
                $result[$type][$dim] = $timesMap[$type][$dim];
            }
            foreach ($time["per_time"] as $item => $items) {
                if ($items['peoplesum'] > 0) {
                    $result[$type]['per_time'][$item] = round($items['sum'] / $items['peoplesum'], 2);
                } else {
                    $result[$type]['per_time'][$item] = 0;
                }
            }

            foreach (self::$watchingTimeToolBar as $time => $val) {
                if (!isset($result[$type]['per_time'][$time])) {
                    $result[$type]['per_time'][$time] = floatval(0);
                }
            }

            ksort($result[$type]['per_time']);
            $searchMap[$i]['sdate'] = $params['sdate'];
            $searchMap[$i]['edate'] = $params['edate'];
            $searchMap[$i]['info'] = $type;
            $i++;
        }


        $cols = [];
        if (!empty($list)) {
            foreach (self::$watchingTimeToolBar as $key => $val) {
                $col = [
                    'field' => 'per_time' . $key,
                    'title' => $val,
                    'width' => 100,
                    'sort' => true
                ];
                $cols[] = $col;
            }
        }

        $data['dims'] = $mydims;
        $data['data'] = $result;
        $data['cols'] = $cols;
        $data['searchMap'] = $searchMap;
        $data['title'] = '用户第几次观看广告人数-平均时间报表';
        $data['length'] = count(self::$watchingTimeToolBar);
        return $data;
    }

    public static function watchingTimeExchnge($times) {
        if ($times >= 7 && $times <= 11) {
            $exchangeTime = 7;
        } elseif ($times >= 12 && $times <= 20) {
            $exchangeTime = 8;
        } elseif ($times > 20) {
            $exchangeTime = 9;
        } else {
            $exchangeTime = $times;
        }
        return $exchangeTime;
    }

    public static function watchingTimeBack($times) {
        if ($times[0] == 7) {
            $backTime = array(7, 8, 9, 10, 11);
        } elseif ($times[0] == 8) {
            $backTime = array(12, 13, 14, 15, 16, 17, 18, 19, 20);
        } elseif ($times[0] == 9) {
            $backTime = array(20);
        } else {
            $backTime = $times;
        }
        return $backTime;
    }


    public static function getWatchingTimeDetailData($params) {
        $where = [];
        $mydims = [];
        $map = [];
        $app_name = '';
        if (isset($params['app_key'])) {
            $where['app_key'] = array('in', $params['app_key']);
            array_push($mydims, 'app_key');
            $map['app_key'] = self::getAppKeyMap();
            if (array_key_exists($params['app_key'][0], $map['app_key'])) {
                $app_name = $map['app_key'][$params['app_key'][0]] . " ";
            }
        }
        if (isset($params['ad_type'])) {
            $where['ad_type'] = array('in', $params['ad_type']);
            array_push($mydims, 'ad_type');
            $map['ad_type'] = Common_Service_Config::AD_TYPE;
        }
        if (isset($params['times'])) {
            $backTimes = self::watchingTimeBack($params['times']);
            if ($params['times'] == 9) {
                $where['times'] = array('>', $backTimes);
            } else {
                $where['times'] = array('in', $backTimes);
            }
            array_push($mydims, 'times');
        }

        $dims = $mydims;
        $groupBy = empty($dims) ? 'GROUP BY per_min' : 'GROUP BY per_min,' . implode(',', array_merge($dims));#order by 条件
        $kpis = ['sum(people_count) as people_count', 'per_min'];
        if (!empty($dims)) {
            $orderBy = [];
            foreach ($dims as $dkey => $dim) {
                array_push($orderBy, $dim . ' asc');
            }
            $orderBy = implode(',', $orderBy);
        } else {
            $orderBy = NULL;
        }

        $where['days'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));
        $strField = implode(',', array_merge($dims, $kpis));
        $list = self::getDao("ReportWatchingTime")->getData($strField, $where, $groupBy, $orderBy);
        $tmp = [];
        for ($i = 1; $i <= 60; $i++) {
            $tmp[$i] = 0;
        }
        foreach ($list as $key => $val) {
            $minute = $val['per_min'] >= 60 ? 60 : $val['per_min'];
            if ($minute > 0) {
                $tmp[$minute] += $val['people_count'];
            }
        }

        $result = $tmp;
        ksort($result);

        $cols = [];
        if (!empty($list)) {
            foreach ($result as $key => $val) {

                $title=($key<60)?$key:'>=60';
                $cols[] = [
                    'field' => 'min' . $key,
                    'title' => $title,
                    'width' => 120
                ];
            }
        }

        $data['sum'] = array_sum($result);
        $data['dims'] = $mydims;
        $data['data'] = $result;
        $data['cols'] = $cols;
        $data['lengend'] =  '人数%';
        $data['title'] = $app_name . self::$watchingTimeToolBar[$params['times'][0]] . '观看广告的时间人数分布';
        $data['detail_length'] = count($result);
        return $data;
    }

    public static function getInform() {
        $where = array('status' => 1);
        $orderBy = array('level' => 'asc');
        return self::getDao('ReportInform')->getsBy($where, $orderBy);
    }

    public static function getWeekData($type,$params,$field){
        if($type == 'dau'){
            $dao = 'ReportDau';
        }elseif($type == 'day'){
            $dao = 'ReportDay';
        }elseif($type == 'finance'){
            $dao = 'ReportFinance';
        }
        $groupBy = 'group by app_key';
        $orderBy = '';
        return self::getDao($dao)->getData($field,$params,$groupBy,$orderBy);
    }
}
