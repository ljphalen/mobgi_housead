<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: matt.liu
 * Date: 2018/3/12
 * Time: 15:15
 */
class Marketing_ReportController extends Admin_MarketingController {

    public $resourceName = '';
    public $resourceAction = '';


    #两种类型报表1.效果 2.APP转化效果
//    public static $dimType = [
//        1=>'date,impression,click,app_installation,conversion,cost',
//        2=>'date,download,activation,register,app_payment_count,app_payment_amount'
//    ];

    public static $dimType = [
        1=>array(
            'date'=>'日期',
            'hour'=>'小时',
            'impression'=>'曝光量',
            'click'=>'点击量',
            'app_installation'=>'安装量',
            'conversion'=>'转化量',
            'cost'=>'花费(元)',
//            'adgroup_id'=>'广告组',
//            'ad_id'=>'广告',
//            'campaign_id'=>'广告计划',
//            'product_refs_id'=>'标的物',
        ),
        2=>array(
            'date'=>'日期',
            'hour'=>'小时',
            'download'=>'下载量',
            'activation'=>'激活量',
            'register'=>'注册量',
            'app_payment_count'=>'付费行为量',
            'app_payment_amount'=>'付费金额',
//            'adgroup_id'=>'广告组',
//            'ad_id'=>'广告',
//            'campaign_id'=>'广告计划',
//            'product_refs_id'=>'标的物',
        ),
    ];

    #下拉字段
    public static $selectDims =[
        'campaign_id'=>'广告计划',
        'adgroup_id'=>'广告组',
        'ad_id'=>'广告'
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

    #报表级别
    public static $reportLevel = [
        'default'=>'ADVERTISER',
        'campaign_id'=>'CAMPAIGN',
        'adgroup_id'=>'ADGROUP',
        'ad_id'=>'AD',
        'product_id'=>'PRODUCT'
    ];

    #两种日期类型1.天 2.小时
    public static $dateType = [
        1=>'daily_reports',
        2=>'hourly_reports'
    ];


    /*
    * 获取下拉框的各类默认参数
    */
    public function getParamsAction(){
        #推广计划
        $account_id = $this->getGdtAccountId();
        $where['account_id'] = array("IN",array($account_id,0));//0为所有权限
        $map = [];
        $map['productMap'] = MobgiMarket_Service_ReportModel::getProductCacheMap($where);
        $map['campaignMap'] = $this->getCampaignRemoteMap();
        $map['adGroupMap'] = $this->getUserGroupRemoteMap();
        $map['adsMap'] = $this->getAdRemoteMap();
        $map['dateRange'] = [date('Y-m-d',time()-7*86400),date('Y-m-d',time()-86400)];
        $this->output(0, 'success', $map);
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
     * 远程获取用户组
     */
    public function getUserGroupRemoteMap($adgroup_id = NULL){
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