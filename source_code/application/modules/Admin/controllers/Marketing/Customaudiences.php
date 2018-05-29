<?php
/**
 * Created by PhpStorm.
 * User: atom.zhan
 * Date: 2018/4/3
 * Time: 11:24
 */


if (!defined('BASE_PATH')) exit('Access Denied!');

class Marketing_CustomAudiencesController extends Admin_MarketingController {
    const  CACHE_EXPRIE = 86400;
    public $resourceName = 'custom_audiences';
    private $targetingCond = [
        'age' => [
            ['max_len', [0, 250], '', null, true],
        ],
        'gender' => [
            ['max_len', [0, 250], '', null, true],
        ],
    ];


    //获取人群|缓存
    public function getAction() {
        $param = $this->getParams([
            'audience_id' => [
                ['int', null, '', null, true],
            ]
        ]);
        $id = $param['id'] ?: 0;

        $accountId = intval($this->getInput('account_id'));
//        $redis = $this->getCache();
//        $key = 'Audience_List_'+$accountId;
//        $result = $redis->get($key);
//        $ret = [
//            'code' => 0,
//            'message' => ''
//        ];
        if (empty($result) or ($id > 0 and empty($result[$id]))) {
            $page = 1;
            $count = 100;
            $result = [];
            $param['page_size'] = $count;
            while ($count == $param['page_size']) {
                $param['page'] = $page++;
                $ret = $this->getAudiences($param);
                if ($ret['code'] > 0) {
                    $this->output($ret['code'], $ret['message']);
                }
                $count = count($ret['data']);
                foreach ($ret['data'] as $item) {
                    if (empty($result[$item['id']])) {
                        $result[$item['id']] = $item;
                    }
                }
            }
//            $redis->set($key, $result, 3600);
        }
        $this->output($ret['code'], $ret['message'], array_values($result));
    }

    //获取人群（custom_audiences/get）
    private function getAudiences($param) {
        $result = $this->send($param, 'get');
        $ret = json_decode($result, TRUE);
        $result = [];
        if (isset($ret['data']['list'])) {
            foreach ($ret['data']['list'] as $key => $item) {
                $arr = [];
                $arr['id'] = $item['audience_id'];
                $arr['label'] = $item['name'];
                $arr['show'] = true;
                $arr['type'] = $item['type'];
                $arr['status'] = $item['status'];
                $arr['error_code'] = $item['error_code'];
                $arr['description'] = $item['description'];
                $arr['created_time'] = $item['created_time'];
                $arr['last_modified_time'] = explode(' ',$item['last_modified_time'])[0];
                if (isset($item['user_count'])) {
                    $arr['user_count'] = $item['user_count'];
                } elseif (isset($item['audience_spec']) and in_array($item['type'], ['AD', 'COMBINE'])) {
                    $param = [
                        'type' => $item['type'],
                        'audience_spec' => []
                    ];
                    isset($item['audience_spec']['ad_rule_spec']) and $spec['audience_spec']['ad_rule_spec'] = $item['audience_spec']['ad_rule_spec'];
                    isset($item['audience_spec']['combine_spec']) and $spec['audience_spec']['combine_spec'] = $item['audience_spec']['combine_spec'];
                    $arr['user_count'] = $this->getEstimation($param);
                } else {
                    $arr['user_count'] = 0;
                }
                $result[] = $arr;
            }
        }
        $ret['data'] = $result;
        return $ret;
        //        $this->output($ret['code'], $ret['message'] . count($result), $result);
    }


    //人群覆盖数预估|（custom_audience_estimations/get）
    private function getEstimation($param) {
        $result = $this->send($param, 'get', 'custom_audience_estimations');
        $ret = json_decode($result, TRUE);
        $result = [];
        if ($ret['data']['list']) {
            foreach ($ret['data']['list'] as $item) {
                if (isset($param['type']) and $param['type'] == 'REGION') {
                    if ($item['id'] == 1156) {
                        $item['parent_id'] = 0;
                    }
                    if ($item['id'] == 156) {
                        $item['parent_id'] = 1156;
                    }
                    if ($item['parent_id'] == 0 and $item['id'] > 10000) {
                        $item['parent_id'] = 1156;
                    }
                }

                $result[] = [
                    'id' => $item['id'],
                    'pid' => $item['parent_id'],
                    'label' => $item['name']

                ];
            }
            if (isset($param['type']) and $param['type'] == 'REGION') {
                $map = [];
                foreach ($result as $item) {
                    $map[$item['pid']][$item[id]] = 1;
                }

                foreach ($result as $item) {
                    if (($item['id'] > 100000 and $item['id'] < 999999 and !isset($map[$item['id']]) and $item['id'] % 10 == 0) or in_array($item['id'], [
                            110000,
                            120000,
                            310000,
                            500000
                        ])
                    ) {
                        $ret = $this->getLocation($item['id']);
                        if ($ret) {
                            $result = array_merge($result, $ret);
                        }
                    }

                }
            }
        }
        $this->output($ret['code'], $ret['message'], $result);
    }

    /*
     * 添加人群
     */
    public function addAction(){
        $params = $this->getInput(['name', 'type', 'description']);
        $data = $this->checkAddParam($params);
        $result = $this->send($data, 'add');
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] != 0){
            $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
        }else{
            $this->output(0, '创建成功');
        }
    }

    /**
     * 检查创建人群的参数
     * @param type $params
     * @return type
     */
    private function checkAddParam($params){
        $data = [
            'name' => trim($params['name']),
            'type' => trim($params['type']),
        ];
        if(empty($data['name'])){
            $this->output(-1, '人群名称不能为空');
        }
        if(!empty($params['description'])){
            $data['description'] = $params['description'];
        }
        if($params['type'] == 'AD'){
            $data['audience_spec']['ad_rule_spec'] = [];
            $adParams = $this->getInput([ 'campaign_id_list', 'rule_type', 'conversion_type', 'time_window']);
            if(empty($adParams['campaign_id_list'])){
                $this->output(-1, '请选择推广计划');
            }
            $data['audience_spec']['ad_rule_spec']['campaign_id_list'] = $adParams['campaign_id_list'];
            if(!isset($this->marketingConfig['RULE_TYPE'][$adParams['rule_type']])){
                $this->output(-1, '请选择广告行为');
            }
            $data['audience_spec']['ad_rule_spec']['rule_type'] = $adParams['rule_type'];
            if($adParams['rule_type'] == 'CONVERSION'){
                if(empty($adParams['conversion_type'])){
                    $this->output(-1, '请选择转化类型');
                }
                $data['audience_spec']['ad_rule_spec']['conversion_type'] = $adParams['conversion_type'];
            }
            if($adParams['time_window'] < 0 || $adParams['time_window'] > 60){
                $this->output(-1, '时间范围只允许0~60');
            }
            $data['audience_spec']['ad_rule_spec']['time_window'] = $adParams['time_window'];
        }elseif($params['type'] == 'LOOKALIKE'){
            $data['audience_spec']['lookalike_spec'] = [];
            $likeParams = $this->getInput([ 'seed_audience_id', 'expand_user_count']);
            if(empty($likeParams['seed_audience_id'])){
                $this->output(-1, '请选择拓展的种子人群');
            }
            $data['audience_spec']['lookalike_spec']['seed_audience_id'] = $likeParams['seed_audience_id'];
            if($likeParams['expand_user_count'] == 0){
                $this->output(-1, '请选择拓展的量级');
            }
            $data['audience_spec']['lookalike_spec']['expand_user_count'] = $likeParams['expand_user_count'] * 10000;
        }else{
            $this->output(-1, '该人群类型暂不支持');
        }
        return $data;
    }

    /**
     * 人群
     * 批量删除
     */
    public function batchDeleteAction(){
        $params = $this->getInput(array('ids'));
        if(empty($params['ids'])){
            $this->output(-1, '请先选择要删除的人群');
        }
        # 批量修改
        $successNum = 0;
        $failedNum = 0;
        $failedMsg = [];
        foreach($params['ids'] as $value){
            $data['audience_id'] = $value;
            $result = $this->send($data, 'delete');
            $resultArr = json_decode($result, TRUE);
            if($resultArr['code'] == 0){
                $successNum ++;
            }else{
                $failedNum ++;
                $failedMsg[] = $resultArr['message'];
            }
        }
        if($failedNum == 0){
            $this->output(0, $successNum.'个删除成功');
        }elseif($successNum == 0){
            $message = implode('; ', $failedMsg);
            $this->output(-1, '删除失败，原因：'.$message);
        }else{
            $message = implode('; ', $failedMsg);
            $this->output(0, $successNum.'个删除成功，'.$failedNum.'个删除失败，原因：'.$message);
        }

    }

    private function getCache() {
        $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS, 'AD_USER_CACHE_REDIS_SERVER0');
        return $cache;
    }
}