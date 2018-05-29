<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/18
 * Time: 16:27
 */
class Marketing_ProductsController extends Admin_MarketingController {

    public $resourceName = 'products';

    /*
     * 获取标的物列表
     */
    public function getAction(){
        $info = $this->getInput(array('product_type', 'product_refs_id'));
        $params = $this->checkGetParam($info);

        $result = $this->send($params, 'get');
        $campaignResultArr = json_decode($result, TRUE);
        $campaignResultArr['data'] = $this->parseGdtList($campaignResultArr['data'], array('campaign_type'=>'CAMPAIGN_TYPE', 'speed_mode'=>'SPEED_MODE', 'product_type'=>'PRODUCT_TYPE', ));

        $this->output($campaignResultArr['code'], $campaignResultArr['message'], $campaignResultArr['data']);
    }



    /**
     * 检查获取推广计划的参数
     * @param type $info
     * @return type
     */
    private function checkGetParam($info){

        if(isset($info['product_type']) && $info['product_type'] ){
            $info['product_type'] = trim($info['product_type']);
            if(!in_array($info['product_type'],array('PRODUCT_TYPE_APP_ANDROID_OPEN_PLATFORM', 'PRODUCT_TYPE_APP_IOS', 'PRODUCT_TYPE_LBS_WECHAT'))){
                $this->output(1, '标的物类型异常');
            }
        }else{
            unset($info['product_type']);
        }

        if(isset($info['product_refs_id']) && $info['product_refs_id'] ){
            $info['product_refs_id'] = trim($info['product_refs_id']);
            if(empty($info['product_refs_id'])){
                $this->output(1, '标的物id不能为空');
            }
        }else{
            unset($info['product_refs_id']);
        }
        return $info;
    }


    /**
     * 添加标的物（products/add）
     */
    public function addAction(){
        $info = $this->getInput(array('product_name','product_type','product_refs_id'));
        $data = $this->checkAddParam($info);

        $result = $this->send($data, 'add');
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0){
            # 添加标的物
            if($this->getProductFromGdt($data)){
                $resultArr['message'] = '标的物添加成功！';
            }
        }
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查添加标的物的参数
     * @param type $info
     * @return type
     */
    private function checkAddParam($info){
        $info['product_type'] = trim($info['product_type']);
        if(!array_key_exists($info['product_type'],$this->marketingConfig['PRODUCT_REFS_TYPE'])){
            $this->output(1, '标的物类型不符合');
        }
        $info['product_refs_id'] = trim($info['product_refs_id']);
        if(empty($info['product_refs_id'])){
            $this->output(1, '标的物id不能为空');
        }
        # 校验是否存在于后台
        $productData = MobgiMarket_Service_SettingModel::getProductsByParams([
            'account_id'=>$this->getGdtAccountId(),
            'product_type'=>$info['product_type'],
            'product_refs_id'=>$info['product_refs_id']
        ]);
        if($productData){
            $this->output(1, '标的物已经存在');
        }
        # 查询广点通后台是否已经录入，已经录入则更新
        if($this->getProductFromGdt($info)){
            $this->output(0, '标的物已同步更新');
        }
        $info['product_name'] = trim($info['product_name']);
        if(empty($info['product_name'])){
            $info['product_name'] = 'unknown';
        }
        return $info;
    }

    /**
     * 获取标的物，并添加到后台数据库中
     * @param $info
     * @return bool
     */
    private function getProductFromGdt($info){
        $params = [
            'product_type' => $info['product_type'],
            'product_refs_id' => $info['product_refs_id'],
        ];

        $result = $this->send($params, 'get');
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0){
            $data = [
                'account_id' => $this->getGdtAccountId(),
                'product_refs_id' => $resultArr['data']['product_refs_id'],
                'product_name' => $resultArr['data']['product_name'],
                'product_type' => $resultArr['data']['product_type'],
            ];
            if(isset($resultArr['data']['product_info']['product_type_apple_app_store']['app_property_pkg_url'])){
                $data['product_url'] = $resultArr['data']['product_info']['product_type_apple_app_store']['app_property_pkg_url'];
            }elseif(isset($resultArr['data']['product_info']['product_type_app_android_open_platform']['app_property_pkg_url'])){
                $data['product_url'] = $resultArr['data']['product_info']['product_type_app_android_open_platform']['app_property_pkg_url'];
            }elseif(isset($resultArr['data']['product_info']['product_type_union_app_info']['app_property_pkg_url'])){
                $data['product_url'] = $resultArr['data']['product_info']['product_type_union_app_info']['app_property_pkg_url'];
            }
            MobgiMarket_Service_SettingModel::addProduct($data);
            return true;
        }else{
            return false;
        }
    }

    public function getListAction(){
        $params = $this->getInput(array('product_name', 'product_type', 'product_refs_id', 'page', 'page_size'));
        $where = $this->checkGetListParam($params);
        $page = empty($params['page']) ? 1 : intval($params['page']);
        $pageSize = empty($params['page_size']) ? 10 : intval($params['page_size']);
        list($total, $list) = MobgiMarket_Service_SettingModel::getProductList($page, $pageSize, $where);
        $totalPage = ceil($total / $pageSize);
        $data = [
            'list' => $list,
            'page_info' => [
                'page' => $page,
                'page_size' => $pageSize,
                'total_number' => intval($total),
                'total_page' => $totalPage,
            ],
        ];
        $this->output(0, '获取成功', $data);
    }

    /**
     * 检查获取标的物列表的参数
     * @param type $params
     * @return type
     */
    private function checkGetListParam($params){
        $where = array();
        $where['product_name'] = array('LIKE', trim($params['product_name']));
        $where['product_type'] = $params['product_type'];
        $where['product_refs_id'] = array('LIKE', trim($params['product_refs_id']));
        $where['account_id'] = $this->getGdtAccountId();
        $where = $this->filterParams($where);
        return $where;
    }

    /**
     * 获取标的物列表
     */
    public function listAction(){
        $params = $this->getInput(array('product_name', 'product_type', 'product_refs_id'));

        $where = [
            'product_name' => array('LIKE', trim($params['product_name'])),
            'product_type' => trim($params['product_type']),
            'product_refs_id' => trim($params['product_refs_id']),
            'account_id' => $this->getGdtAccountId()
        ];
        $where = $this->filterParams($where);
        $result = MobgiMarket_Service_SettingModel::getProductsByParams($where);
        $this->output(0, '获取成功', $result);
    }

    /**
     * 更新标的物（products/update）
     */
    public function updateAction(){
        $info = $this->getInput(array(
            'product_name', 'product_type', 'product_refs_id'
        ));
        $info = $this->checkUpdateParam($info);
        $data = $info;

        $result = $this->send($data, 'update');
        $resultArr = json_decode($result, TRUE);
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查更新标的物的参数
     * @param type $info
     * @return type
     */
    private function checkUpdateParam($info){
        if(empty($info['product_refs_id'])){
            $this->output(1, '标的物 id不能为空');
        }
        return $info;
    }

    /**
     * 获取投放应用列表
     */
    public function getAppAction(){
        $appList = MobgiSpm_Service_DeliveryModel::getDeliveryApp();
        $this->output(0, '获取成功', $appList);
    }

    /**
     * 查询该标的物是否满足oCPA条件
     */
    public function checkOcpaAction(){
        $info = $this->getInput( ['product_type', 'product_refs_id'] );
        if(empty($info['product_type']) || empty($info['product_refs_id'])){
            $this->output(-1, '标的物类型 或 标的物ID 为空');
        }
        # oCPA使用门槛介绍
        # APP类型
        # 新建\复制oCPA广告，或更新CPC广告为oCPA广告，需满足账户内推广的该APP，近3天积累了100个转化 （近3天，不包括今天，往前推3天）
        $params = [];
        $params['level'] = 'PRODUCT';
        $params['group_by'][] = 'product_refs_id';
        $params['date_range']['start_date'] = date('Y-m-d',strtotime('-3 day'));  //前三天
        $params['date_range']['end_date'] = date('Y-m-d',strtotime('-1 day'));  //前一天
//        $params['filtering'][] = [
//            'field'=>'product_type ',
//            'operator'=>'EQUALS',
//            'values'=>[ trim($info['product_type']) ],
//        ];
        $params['filtering'][] = [
            'field'=>'product_refs_id',
            'operator'=>'EQUALS',
            'values'=>[ trim($info['product_refs_id']) ],
        ];
        $result = $this->send($params, 'get', 'daily_reports');
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0){
            $conversion = 0; // 转化量
            foreach($resultArr['data']['list'] as $value){
                $conversion += $value['activation'];
            }
            if($conversion >= 100){
                $this->output(0, '该APP近3天累计了'.$conversion.'个转化，达到oCPA使用门槛');
            }else{
                $this->output(-1, '该APP近3天累计了'.$conversion.'个转化，未达到oCPA使用门槛');
            }
        }else{
            $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
        }
    }
}