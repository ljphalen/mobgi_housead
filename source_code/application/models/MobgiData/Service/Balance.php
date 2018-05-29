<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/9/14
 * Time: 17:08
 */

class MobgiData_Service_BalanceModel extends MobgiData_Service_BaseModel {


    /**
     * 添加媒体分成比例全局配置
     * @param $ads_division_rate 广告商利益分成比例
     * @param $own_division_rate 自投利益分成比例
     * @param $effect_date 生效日期
     * @param $status 状态，1 有效 0 失效
     * @param $create_time 创建时间
     * @param $operator 操作人
     * @param $notice 备注
     * @return bool|int
     */
    public static function addDivisionGlobalConfig($ads_division_rate,$own_division_rate,$effect_date,$status,$create_time,$operator,$notice){
        $data = array(
            'ads_division_rate'=>$ads_division_rate,
            'own_division_rate'=>$own_division_rate,
            'effect_date'=>$effect_date,
            'status'=>$status,
            'create_time'=>$create_time,
            'operator'=>$operator,
            'notice'=>$notice,
        );
        return self::getDao('DivisionGlobalConfig')->insert($data);
    }

    public static function updateDivisionGlobalConfig($data, $params){
        return self::getDao('DivisionGlobalConfig')->updateBy($data, $params);
    }

    public static function getDivisionGlobalList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $ret = self::getDao('DivisionGlobalConfig')->getList($start, $limit, $params, $orderBy);
        $total = self::getDao('DivisionGlobalConfig')->count($params);
        return array($total, $ret);
    }

    /**
     * 添加媒体分成比例定制配置
     * @param $app_key 应用key
     * @param $ads_division_rate 广告商利益分成比例
     * @param $own_division_rate 自投利益分成比例
     * @param $effect_date 生效日期
     * @param $status 状态，1 有效 0 失效
     * @param $create_time 创建时间
     * @param $operator 操作人
     * @param $notice 备注
     * @return bool|int
     */
    public static function addDivisionCustomConfig($app_key,$ads_division_rate,$own_division_rate,$effect_date,$status,$create_time,$operator,$notice){
        $data = array(
            'app_key'=>$app_key,
            'ads_division_rate'=>$ads_division_rate,
            'own_division_rate'=>$own_division_rate,
            'effect_date'=>$effect_date,
            'status'=>$status,
            'create_time'=>$create_time,
            'operator'=>$operator,
            'notice'=>$notice,
        );
        return self::getDao('DivisionCustomConfig')->insert($data);
    }

    public static function updateDivisionCustomConfig($data, $params){
        return self::getDao('DivisionCustomConfig')->updateBy($data, $params);
    }

    public static function getDivisionCustomList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $table = 'config_app';
        $on = 'a.app_key = b.app_key';
        $cur_page = $start;
        $pagesize = $limit;
        $field = 'a.*,b.app_name,b.developer,b.platform';
        $condition_arr = array();
        foreach($params as $k => $v){
            if(!isset($v) || trim($v) ==''){
                continue;
            }
            if($k == 'status' && $v != '-1'){
                $condition_arr[] = 'a.'.$k.'='.$v;
            }
            if($k == 'developer' && $v != '-1'){
                $condition_arr[] = 'b.'.$k.'='.$v;
            }
            if($k == 'app_name' && $v != '-1'){
                $condition_arr[] = 'b.'.$k.' like "%'.$v.'%"';
            }
        }
        $condition = empty($condition_arr)? 1 : implode(' AND ',$condition_arr);
        $list = self::getDao('DivisionCustomConfig')->getSearchByPageLeftJoin(
            $table, $on, $cur_page, $pagesize, $condition, $orderBy,$field
        );
        return array($list['count'], $list['lists']);
    }
}