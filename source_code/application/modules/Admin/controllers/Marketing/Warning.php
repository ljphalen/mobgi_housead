<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: matt.liu
 * Date: 2018/3/22
 * Time: 16:27
 */
class Marketing_WarningController extends Admin_MarketingController {

    public $resourceName = 'Warning';

    /*
     * 获取当前监控参数
     */
    public function getAction(){
        $info = $this->getInput(array('product_type', 'product_refs_id'));
        $param = $this->checkGetParam($info);

        $this->resourceAction = 'get';
        $result = $this->send($param);
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
            $info['product_refs_id'] = intval($info['product_refs_id']);
            if(empty($info['product_refs_id'])){
                $this->output(1, '标的物id必须是整数');
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

        $this->resourceAction = 'add';
        $result = $this->send($data);
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0){
            MobgiMarket_Service_SettingModel::addProduct($data);
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
        $productData = MobgiMarket_Service_SettingModel::getProductsByParams(['product_type'=>$info['product_type'],'product_refs_id'=>$info['product_refs_id']]);
        if($productData){
            $this->output(1, '标的物id已经存在');
        }
        # 查询广点通后台是否已经录入，已经录入则更新
        if($this->getProductFromGdt($info)){
            $this->output(0, '标的物已同步更新');
        }
        $info['product_name'] = trim($info['product_name']);
        if(empty($info['product_name'])){
            $this->output(1, '标的物名称不能为空');
        }
        return $info;
    }

    private function getProductFromGdt($info){
        $param = [
            'product_type' => $info['product_type'],
            'product_refs_id' => $info['product_refs_id'],
        ];

        $this->resourceAction = 'get';
        $result = $this->send($param);
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0){
            $data = [
                'account_id' => 0,
                'product_refs_id' => $resultArr['data']['product_refs_id'],
                'product_name' => $resultArr['data']['product_name'],
                'product_type' => $resultArr['data']['product_type'],
            ];
            MobgiMarket_Service_SettingModel::addProduct($data);
            return true;
        }else{
            return false;
        }
    }

    public function listAction(){
        $params = $this->getInput(array('product_name', 'product_type', 'product_refs_id', 'page', 'page_size'));
        $where = $this->checkListParam($params);
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
    private function checkListParam($params){
        $where = array();
        $where['product_name'] = array('LIKE', trim($params['product_name']));
        $where['product_type'] = $params['product_type'];
        $where['product_refs_id'] = array('LIKE', trim($params['product_refs_id']));
        $where['account_id'] = array('IN', array(0,$this->getGdtAccountId()));
        $where = $this->filterParams($where);
        return $where;
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

        $this->resourceAction = 'update';
        $result = $this->send($data);
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
}