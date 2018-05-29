<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: matt.liu
 * Date: 2018/3/12
 * Time: 15:15
 */
class Marketing_RuleController extends Admin_MarketingController {

    public $resourceName = '';
    public $resourceAction = '';

    public $perpage = 30;

    #是否开启远程获取，关闭为读取缓存策略
    public static $isOpenRemote = true;

    #条件字段
    public static $ruleChoose = [
        0=>[
            'label'=>'效果数据',
            'options'=>[
                'impression'=>'曝光量',
                'click'=>'点击量',
                'app_installation'=>'安装量',
                'conversion'=>'转化量',
                'cost'=>'花费(元)',
                'click_rate'=>'点击率',
                'app_installation_rate'=>'安装率',
                'conversion_rate'=>'转化率',
                'app_installation_price'=>'安装均价',
                'conversion_price'=>'转化均价',
            ],
        ],
        1=>[
            'label'=>'APP转化效果',
            'options'=>[
                'download'=>'下载量',
                'activation'=>'激活量',
                'register'=>'注册量',
                'app_payment_count'=>'付费行为量',
                'app_payment_amount'=>'付费金额',
                'download_rate'=>'下载率',
                'download_price'=>'下载均价',
                'download_activation_rate'=>'下载激活率',
                'click_activation_rate'=>'点击激活率',
                'activation_price'=>'激活均价'
            ],
        ],
    ];

    #扩展字段
    public static $expandDimType = [
        1=>array(
            'click_rate'=>'点击率',
            'app_installation_rate'=>'安装率',
            'conversion_rate'=>'转化率',
            'app_installation_price'=>'安装均价',
            'conversion_price'=>'转化均价',
        ),
        2=>array(
            'download_rate'=>'下载率',
            'download_price'=>'下载均价',
            'download_activation_rate'=>'下载激活率',
            'click_activation_rate'=>'点击激活率',
            'activation_price'=>'激活均价'
        ),
    ];

    #扩展字段算法
    public static $expandCategory = [
        'click_rate'=>array('click','impression'),
        'app_installation_rate'=>array('app_installation','click'),
        'conversion_rate'=>array('conversion','click'),
        'app_installation_price'=>array('cost','app_installation'),
        'conversion_price'=>array('cost','conversion'),
        'download_rate'=>array('download','click'),
        'download_price'=>array('cost','download'),
        'download_activation_rate'=>array('activation','download'),
        'click_activation_rate'=>array('activation','click'),
        'activation_price'=>array('cost','activation'),
    ];

//    #报表级别
//    public static $reportLevel = [
//        'default'=>'ADVERTISER',
//        'campaign_id'=>'CAMPAIGN',
//        'adgroup_id'=>'ADGROUP',
//        'ad_id'=>'AD',
//        'product_id'=>'PRODUCT'
//    ];
//
//    #两种日期类型1.天 2.小时
//    public static $dateType = [
//        1=>'daily_reports',
//        2=>'hourly_reports'
//    ];

    #规则应用对象映射
    public static $ruleObjMap = [
        'ad_plan'=>'广告计划',
        'ad_group'=>'广告组',
        'ad'=>'广告',
    ];

    #操作映射
    public static $manageMap = [
         1=>'开启广告计划',
         2=>'关闭广告计划',
         3=>'只发送通知',
         4=>'调整预算',
         5=>'调整出价',
    ];

    #运行间隔映射
    public static $dateRangeMap = [
        86400=>'每天',
        3600=>'每小时',
        900=>'每15分钟',
        1800=>'每半小时',
        10800=>'每三小时',
        604800=>'每七天',
    ];

    #时间范围映射
    public static $timeRangeMap = [
        86400=>'今天',
        172800=>'昨天',
        259200=>'过去三天',
        604800=>'过去七天',
        0=>'广告发布时间'
    ];

    #通知方式映射
    public static $informMap = [
        1=>'微信',
        2=>'邮件',
        3=>'短信'
    ];

    #op映射
    public static $opMap = [
        '>='=>'大于等于',
        '<='=>'小于等于'
    ];

    #调整预算，调整出价操作
    public static $changePriceOpMap = [
        1=>'将单日预算增加',
        2=>'将单日预算减少'
    ];

    public static $changePriceTypeMap = [
        1=>'%',
        2=>'￥',
    ];

    public static $changePriceDateRangeMap = [
        3600=>'每小时一次',
        86400=>'每天一次',
        604800=>'每周一次',
        1209600=>'每两周一次'
    ];

    public static $addParams = [
        'obj_type',
        'obj_params',
        'inform',
        'receiver',
        'obj',
        'manage',
        'change_price_op',
        'change_price_type',
        'change_price_val',
        'change_price_top',
        'change_price_date_range',
        'rule_params',
        'date_rang',
        'rule_name'
    ];
    /*
     * 获取所有规则
     */

    public function getRulesAction(){
        $account_id = $this->getGdtAccountId();
        $page = intval($this->getInput('page'));
        if($page < 1) $page = 1;
        //处理数据
        $where['account_id'] = array("IN",array($account_id,0));//0为所有权限
        $list = MobgiMarket_Service_RuleModel::getMarketDao('Rule')->getList(($page-1)*$this->perpage, $this->perpage, $where, ['id' => "DESC"]);
        foreach ($list as $key=>&$val){
            $val['obj_params_count'] = count(explode(',',$val['obj_params']));
            $val['obj_detail'] = $val['obj_params_count'].'个'.self::$ruleObjMap[$val['obj_type']];
            $val['manage_detail'] = self::$manageMap[$val['manage']];
            $val['position_detail'] = self::$dateRangeMap[$val['position']];
            $tmpRule = json_decode($val['rules'],true);
            $val['rule_detail'] =self::$timeRangeMap[$tmpRule['date_range']].self::$ruleChoose[$tmpRule['type']]['options'][$tmpRule['key']].self::$opMap[$tmpRule['op']].$tmpRule['value'];
            $receiverMap = MobgiMarket_Service_RuleModel::getReceiverMap(array('id'=>array('IN',explode(',',$val['receiver']))));
            $val['receiver_detail'] = implode(',',$receiverMap);
        }
        $this->output(0,'',$list);
    }

    /*
     * 修改规则状态
     */

    public function changestatusAction(){
        $account_id = $this->getGdtAccountId();
        $where['account_id'] = array("IN",array($account_id,0));//0为所有权限
        $where['id'] = $this->getInput('id');
        if($this->getInput('status') == 1){
            $data['status'] = 0;
        }else{
            $data['status'] = 1;
        }
        if(MobgiMarket_Service_RuleModel::getMarketDao('Rule')->updateBy($data,$where)){
            $this->output(0,'','修改成功');
        }else{
            $this->output(-1,'','修改失败');
        }
    }


    /*
     * 获取所有下拉参数
     */
    public function getAllSelectAction(){
        $account_id = $this->getGdtAccountId();
        $params = array(
            'objTypeParams'=>self::$ruleObjMap,
            'objParams'=>[],
            'manageParams'=>self::$manageMap,
            'changePriceOpParams'=>self::$changePriceOpMap,
            'changePriceTypeParams'=>self::$changePriceTypeMap,
            'changePriceDateRangeParams'=>self::$changePriceDateRangeMap,
            'informParams'=>self::$informMap,
            'dateRangParams'=>self::$dateRangeMap,
            'ruleTimeRangeParams'=>self::$timeRangeMap,
            'ruleChooseParams'=>self::$ruleChoose,
            'ruleTargetLevelParams'=>self::$ruleObjMap,
            'receiverParams'=>MobgiMarket_Service_RuleModel::getReceiverMap(array('account_id'=>$account_id)),
        );
        return $this->output(0,'',$params);
    }


    /*
     * 根据类型获取广告计划，广告组，广告列表
     */
    public function getSelectByTypeAction(){
        $type = $this->getInput('type');
        $id = $this->getInput('id');
        if(empty($id)) $id =null;
        if(self::$isOpenRemote){
            //远程获取
            switch (strtolower($type)){
                case 'ad_plan':$data = $this->getCampaignRemoteMap($id);break;
                case 'ad_group':$data = $this->getAdGroupRemoteMap($id);break;
                case 'ad':$data = $this->getAdRemoteMap($id);break;
                default:$data = null;break;
            }
        }else{
            //走本地缓存
        }
        $list = array();
        if(!empty($data)){
            foreach ($data as $key=>$val){
                if(strtolower($type) == 'ad_plan'){
                    $list[$val['campaign_id']] = $val['campaign_name'];
                }elseif(strtolower($type) == 'ad_group'){
                    $list[$val['adgroup_id']] = $val['adgroup_name'];
                }else{
                    $list[$val['ad_id']] = $val['ad_name'];
                }
            }
        }
        $this->output(0,'',$list);
    }


    /*
     * 添加规则
     */
    public function addAction(){
        $info = $this->getInput(self::$addParams);

    }

    /*
     * 远程获取创意
     */
    public function getCampaignRemoteMap($campaign_id = NULL){
        if(count($campaign_id) != NULL){
            $params['campaign_id'] = $campaign_id;
        }else{
            $params['page'] = 1;
            $params['page_size'] = 30;
        }
        $params['account_id'] = $this->getGdtAccountId();
        $params['page'] = 1;
        $params['page_size'] = 30;

        $this->resourceName = 'campaigns';
        $jsonResult = $this->send($params, 'get');
        $result = json_decode($jsonResult,true);
        if($result["code"] != 0){
            $this->output($result['code'],$result['message'],'');
        }
        foreach ($result['data']['list'] as $key=>$val){
            $list[$key]['campaign_id'] = $val['campaign_id'];
            $list[$key]['campaign_name'] = $val['campaign_name'];
        }
        return $list;
    }


    /*
     * 远程获取广告用用户组
     */
    public function getAdGroupRemoteMap($adgroup_id = NULL){
        if($adgroup_id != NULL){
            $params['adgroup_id'] = $adgroup_id;
        }else{
            $params['page'] = 1;
            $params['page_size'] = 30;
        }
        $params['account_id'] = $this->getGdtAccountId();
        $this->resourceName = 'adgroups';
        $jsonResult = $this->send($params, 'get');
        $result = json_decode($jsonResult,true);
        if($result["code"] != 0){
            $this->output($result['code'],$result['message'],'');
        }
        foreach ($result['data']['list'] as $key=>$val){
            $list[$key]['adgroup_id'] = $val['adgroup_id'];
            $list[$key]['adgroup_name'] = $val['adgroup_name'];
        }
        return $list;
    }

    /*
     * 远程获取广告
     */
    public function getAdRemoteMap($ad_id = NULL){
        if($ad_id != NULL){
            $params['ad_id'] = $ad_id;
        }else{
            $params['page'] = 1;
            $params['page_size'] = 30;
        }
        $params['account_id'] = $this->getGdtAccountId();
        $params['page'] = 1;
        $params['page_size'] = 50;
        $jsonResult = $this->send($params, 'get', 'ads');
        $result = json_decode($jsonResult,true);
        if($result["code"] != 0){
            $this->output($result['code'],$result['message'],'');
        }
        foreach ($result['data']['list'] as $key=>$val){
            $list[$key]['ad_id'] = $val['ad_id'];
            $list[$key]['ad_name'] = $val['ad_name'];
        }
        return $list;
    }

    /*
     * 联动选项
     */
    public function getLinkAction(){
        $campaignArr = $_GET;
        if(count($campaignArr)){
            $where['campaign_id'] = array('IN',$campaignArr);
            $where['account_id'] = $this->getGdtAccountId();
            $map['adsMap'] = MobgiMarket_Service_ReportModel::getAdsCacheMap($where);
            $map['adgroupMap'] = MobgiMarket_Service_ReportModel::getAdGroupCacheMap($where);
        }else{
            $map['adsMap'] = null;
            $map['adgroupMap'] = null;
        }
        $this->output(0,'success',$map);
    }

    /**
     * 获取报表数据（ads/get）
     */
    public function getDataAction(){
        $info = $this->getInput(array('type', 'date_type', 'page_size','page','start_date','end_date','campaign_id','adgroup_id','product_id','ad_id','order_by','group_by'));
        $remoteData = $this->checkGetParam($info);
        $dims = self::$dimType[$info['type']];#需要展示的字段
        $expandDims = self::$expandDimType[$info['type']];#需要展示的扩展字段
        $list = [];
        //显示字段过滤
        //天数据不需要显示小时数据
        if($info['date_type'] == 1) unset($dims['hour']);
        foreach ($remoteData['data']['list'] as $key=>$val){
            foreach (array_keys($dims) as $keys=>$dim){
                if($dim == 'hour'){
                    $list[$key]['hour'] = $val['hour'];
                    $list[$key]['date'] = $info['start_date'];
                }
                if($val[$dim]!== null){
                    $list[$key][$dim] = $val[$dim];
                }
            }

            //select字段产生
            foreach ($remoteData['group_by'] as $k=>$selectDim){
                $list[$key][$selectDim] = $this->getRealName($selectDim,$val[$selectDim]);
            }
        }


        //扩展字段过滤
        foreach ($list as $key=>&$val){
            foreach (array_keys($expandDims) as $keys=>$expandDim){
                $val[$expandDim] = $this->calRule($expandDim,$val);
            }
        }

        $selectBanner = [];
        foreach ($remoteData['group_by'] as $key=>$type){
            $selectBanner[$type] = self::$selectDims[$type];
        }
       $banner = array_merge($selectBanner,$dims,$expandDims);

       $returnData['data'] = $list;
       $returnData['page_info'] = $remoteData['data']['page_info'];
       $returnData['table_banner'] = $banner;
       $this->output(0,'',$returnData);
    }

    public function getRealName($type,$id){
        switch ($type){
            case 'campaign_id':
                $info = $this->getCampaignRemoteMap($id);
                $name = $info[0]['campaign_name'];
                break;
            case 'adgroup_id':
                $info = $this->getUserGroupRemoteMap($id);
                $name = $info[0]['adgroup_name'];
                break;
            case 'ad_id':
                $info = $this->getAdRemoteMap($id);
                $name = $info[0]['ad_name'];
                break;
        }
        return $name;
    }

    public function calRule($expandDim,$data){
        $expRule = self::$expandCategory[$expandDim];
        return $data[$expRule[1]]==0?0:round($data[$expRule[0]]/$data[$expRule[1]],2);
    }


    public function checkGetParam($info){
       if(empty($info['start_date']) || empty($info['end_date'])) {
           $info['start_date'] = date('Y-m-d', time() - 86400 * 7);
           $info['end_date'] = date('Y-m-d', time() - 86400);
       }else{
           $info['start_date'] = date("Y-m-d",strtotime($info['start_date']));
           $info['end_date'] = date("Y-m-d",strtotime($info['end_date']));
       }

       if($info['date_type'] == 1){
            $dataParams = array(
               'date_range'=>array(
                   'start_date'=>$info['start_date'],
                   'end_date'=>$info['end_date']
               ),
           );
       }else{
           $dataParams = array('date'=>$info['start_date']);
       }

       $baseParams = array(
           'account_id'=>$this->getGdtAccountId(),
           'page'=>$info['page'],
           'page_size'=>$info['page_size'],
       );
       $exandParams = $this->getExpandParams($info);
       $params = array_merge($baseParams,$exandParams,$dataParams);
       $this->resourceName = self::$dateType[$info['date_type']];
       $jsonResult = $this->send($params, 'get');
       $result = json_decode($jsonResult,true);
       if($result['code'] == 18011){
           $this->output($result['code'],'目前拉取数据只支持两个维度，请重新选择!','');
       }elseif($result['code'] != 0){
           $this->output($result['code'],$result['message'],'');
       }
       $result['group_by'] = array_intersect($params['group_by'],array_keys(self::$selectDims));
       return $result;
    }


    public function getExpandParams($info){
        $flag = true;
        $params = array();
        if($info['date_type'] == 1){
            $params['group_by'] = array('date');
        }else{
            $params['group_by'] = array('hour');
        }
        if(!empty($info['product_id'])&&$flag){
            $params['level'] = self::$reportLevel['product_id'];
            $where['product_refs_id'] = array('IN',$info['product_id']);
            $tmp = MobgiMarket_Service_ReportModel::getMarketDao('Product')->getBy($where);
            $params['filtering'][0] = array(
                'field'=>'product_type',
                'operator'=>'IN',
                'values'=>array($tmp['product_type']),
            );
            $params['filtering'][1] = array(
                'field'=>'product_refs_id',
                'operator'=>'IN',
                'values'=>$info['product_id'],
            );
            if(!empty($info['ad_id'])){
                $adgroup =  array(
                    'field'=>'ad_id',
                    'operator'=>'IN',
                    'values'=>$info['ad_id'],
                );
                array_push($params['filtering'],$adgroup);
            }
            if(!empty($info['adgroup_id'])){
                $adgroup =  array(
                    'field'=>'adgroup_id',
                    'operator'=>'IN',
                    'values'=>$info['adgroup_id'],
                );
                array_push($params['filtering'],$adgroup);
            }
            if(!empty($info['campaign_id'])){
                $campaign = array(
                    'field'=>'campaign_id',
                    'operator'=>'IN',
                    'values'=>$info['campaign_id'],
                );
                array_push($params['filtering'],$campaign);
            }
            array_push($params['group_by'],'product_refs_id');
            $flag = false;
        }
        if(!empty($info['ad_id'])&&$flag){
            $params['level'] = self::$reportLevel['ad_id'];//ad_id, adgroup_id, campaign_id
            $params['filtering'][0] = array(
                'field'=>'ad_id',
                'operator'=>'IN',
                    'values'=>$info['ad_id'],
                );
                array_push($params['group_by'],'ad_id');
                if(!empty($info['adgroup_id'])){
                    $adgroup =  array(
                        'field'=>'adgroup_id',
                        'operator'=>'IN',
                        'values'=>$info['adgroup_id'],
                    );
                    array_push($params['filtering'],$adgroup);
                    array_push($params['group_by'],'adgroup_id');
                }
                if(!empty($info['campaign_id'])){
                    $campaign = array(
                    'field'=>'campaign_id',
                    'operator'=>'IN',
                    'values'=>$info['campaign_id'],
                );
                array_push($params['filtering'],$campaign);
                array_push($params['group_by'],'campaign_id');
            }
            $flag = false;
        }
        if(!empty($info['adgroup_id'])&&$flag){
            $params['level'] = self::$reportLevel['adgroup_id'];
            $params['filtering'][0] = array(
                'field'=>'adgroup_id',
                'operator'=>'IN',
                'values'=>$info['adgroup_id'],
            );
            array_push($params['group_by'],'adgroup_id');
            if(!empty($info['campaign_id'])){
                $campaign = array(
                    'field'=>'campaign_id',
                    'operator'=>'IN',
                    'values'=>$info['campaign_id'],
                );
                array_push($params['filtering'],$campaign);
                array_push($params['group_by'],'campaign_id');
            }
            $flag = false;
        }
        if(!empty($info['campaign_id'])&&$flag){
            $params['level'] = self::$reportLevel['campaign_id'];
            $params['filtering'][0] = array(
                'field'=>'campaign_id',
                'operator'=>'IN',
                'values'=>$info['campaign_id'],
            );
            array_push($params['group_by'],'campaign_id');
            $flag = false;
        }
        if($flag){
            $params['level'] = self::$reportLevel['default'];
        }
        return $params;
    }
}