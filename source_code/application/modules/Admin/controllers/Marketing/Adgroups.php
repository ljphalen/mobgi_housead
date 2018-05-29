<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/12
 * Time: 15:39
 */
class Marketing_AdgroupsController extends Admin_MarketingController {

    public $resourceName = 'adgroups';

    /**
     * 创建广告组（adgroups/add）
     */
    public function addAction(){
        $info = $_POST;
//        $this->getJsonPost(array(
//            'campaign_id', 'campaign_type', 'adgroup_name', 'site_set', 'product_type', 'date_range', 'begin_date',
//            'end_date', 'time_range', 'billing_event', 'bid_amount', 'optimization_goal', 'daily_budget',
//            'product_refs_id', 'sub_product_refs_id', 'time_series',
//            'time_senior_type', 'start_time', 'end_time',
//            'configured_status', 'customized_category', 'frequency_capping','target'),'nowords');
//        $info['target'] = json_decode($_GET['target'], TRUE);
//        var_dump($info);die;
//        var_dump($_POST);die;
        $data = $this->checkAddParam($info);
        $result = $this->send($data, 'add');
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0 && $resultArr['message'] == ''){
            $resultArr['message'] = '新建成功！';
        }
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查创建广告组的参数
     * @param type $info
     * @return type
     */
    private function checkAddParam($info){
        $data = [];
        $data['campaign_id'] = intval($info['campaign_id']);
        $data['adgroup_name'] = trim($info['adgroup_name']);
        $data['product_type'] = $info['product_type'];
        $data['billing_event'] = $info['billing_event'];
        $data['optimization_goal'] = $info['optimization_goal'];

        if(empty($data['campaign_id'])){
            $this->output(1, '推广计划 id不能为空');
        }
        $adgroupNameLength = strlen($data['adgroup_name']);
        if($adgroupNameLength<1 || $adgroupNameLength>120){
            $this->output(1, '广告组名称字段长度最小 1 字节，长度最大 120 字节');
        }
        if(!isset($this->marketingConfig['SITE_SET'][$info['site_set']])){
            $this->output(1, '请选择投放站点');
        }
        $data['site_set'][] = $info['site_set'];
        if(empty($info['product_type'])){
            $this->output(1, '标的物类型不能为空');
        }
        if(!isset($this->marketingConfig['PRODUCT_TYPE'][$data['product_type']])){
            $this->output(1, '标的物类型参数错误');
        }
        $beginDateTime = strtotime($info['begin_date']);
        $endDateTime = strtotime($info['end_date']);
        if($beginDateTime > $endDateTime){
            $this->output(1, '开始投放日期要小于等于结束投放日期');
        }
        if($endDateTime < strtotime(date('Y-m-d'))){
            $this->output(1, '结束投放日期要大于等于今天');
        }
        $data['begin_date'] = date('Y-m-d',$beginDateTime);
        $data['end_date'] = date('Y-m-d',$endDateTime);

        if($data['billing_event'] == 'BILLINGEVENT_OCPA'){ // oCPA 也是 按CPC来计费的
            $data['billing_event'] = 'BILLINGEVENT_CLICK';
        }
        if(!isset($this->marketingConfig['BILLINGEVENT'][$data['billing_event']])){
            $this->output(1, '请选择计费类型');
        }
        if(empty($info['bid_amount'])){
            $this->output(1, '广告出价不能为空');
        }
        if(!$this->matchValue(1, $info['bid_amount'])){
            $this->output(-1, '出价格式有误，只支持精确到小数点后两位的正数');
        }
        $data['bid_amount'] = floatval($info['bid_amount']) * 100;
        //todo: (1)根据 campaign_id 获取推广计划的信息，得到campaign_type
        //      (2)根据广告组出价规则和campaign_type做不同的处理逻辑校验
        //
        //广告组出价规则：
        //    对于普通展示广告（campaign_type = CAMPAIGN_TYPE_NORMAL）的CPC出价而言：
        //        出价需介于10分-10,000分之间（0.1元-100元，单位为人民币）；
        //        出价不得高于所属推广计划或帐号日预算的50%。
        //    对于微信公众号广告（campaign_type = CAMPAIGN_TYPE_WECHAT_OFFICIAL_ACCOUNTS）的CPC出价而言：
        //        出价需介于50分-2,000分之间（0.5元-20元，单位为人民币）；
        //        出价不得高于所属推广计划或帐号日预算的50%。
        //    对于普通展示广告（campaign_type = CAMPAIGN_TYPE_NORMAL）的CPM出价，要求介于150分-100,000分之间（1.5元-1000元，单位为人民币）；
        //    对于微信朋友圈广告（campaign_type = CAMPAIGN_TYPE_WECHAT_MOMENTS ）的CPM出价，基于不同的地域定向有不同限制，详见 朋友圈广告地域定向及价格约束；
        //    对于某些特殊流量的CPM出价可能还会有更详细的要求，具体信息可以联系您的运营接口人。

        if(!isset($this->marketingConfig['OPTIMIZATION_GOAL'][$data['optimization_goal']])){
            $this->output(1, '请选择广告优化目标类');
        }
        if($info['campaign_type'] == 'CAMPAIGN_TYPE_WECHAT_MOMENTS'){
            if(empty($info['daily_budget'])){
                $this->output(1, '日预算不能为空值');
            }
            if(!$this->matchValue(1, $info['daily_budget'])){
                $this->output(-1, '日预算格式有误，只支持精确到小数点后两位的正数');
            }
            $data['daily_budget'] = floatval($info['daily_budget']) * 100;
            if($data['daily_budget'] <100000 || $data['daily_budget']>1000000000){
                $this->output(1, '日预算要求介于 1,000 元-10,000,000 元，单位为人民币');
            }
        }
        if($this->marketingConfig['PRODUCT_TYPE'][$data['product_type']]['product_refs_id'] == 1){
            if(empty($info['product_refs_id'])){
                $this->output(1, '请选择标的物');
            }
            $data['product_refs_id'] = $info['product_refs_id'];
            $data['sub_product_refs_id'] = $info['sub_product_refs_id'];
        }
        //todo sub_product_refs_id校验 (可不校验）

        //todo targeting_id (可不校验）

        if(empty($info['target']['targeting_id'])){ // 定向详细设置，存放所有定向条件。与 targeting_id 不能同时填写且不能同时为空，仅微信流量的广告（朋友圈和公众号广告）可使用
            if($info['campaign_type'] == 'CAMPAIGN_TYPE_NORMAL'){
                $this->output( -1, '非微信流量的广告只能使用已保存的定向包！');
            }
            $targeting = [];
            foreach ($info['target']['targeting'] as $key => $val) {
                if (!empty($val)) {
                    $targeting[$key] = in_array($key, ['gender', 'app_install_status']) ? [$val] : $val;
                }
            }
            $data['targeting'] = json_encode($targeting);
        }else{
            if($info['campaign_type'] != 'CAMPAIGN_TYPE_NORMAL'){
                $this->output( -1, '微信流量的广告只能使用定向详细设置，不能使用定向包！');
            }
            $data['targeting_id'] = $info['target']['targeting_id'];
        }
        //todo time_series (可不校验）
        if($info['time_range'] == 0){
            $data['time_series'] = Common::get_week_time_series(0, 24);
        }else{
            if($info['time_senior_type'] == 0){
                $data['time_series'] = Common::get_week_time_series(intval($info['start_time']), intval($info['end_time']));
            }else{
                // 时间区段不满336位，自动填充0
                $data['time_series'] = $info['time_series'];
            }
        }

        if(isset($info['configured_status'])){
            if(!isset($this->marketingConfig['AD_STATUS'][$info['configured_status']])){
                $this->output(1, '状态参数错误');
            }
            $data['configured_status'] = $info['configured_status'];
        }

        if($info['customized_category']){
            $customized_category_length = strlen($info['customized_category']);
            if($customized_category_length<1 || $customized_category_length>200){
                $this->output(1, '自定义分类字段长度最小 0 字节，长度最大 200 字节');
            }
        }

        //todo frequency_capping(可不校验）

        return $data;
    }


    /**
     * 更新广告组（adgroups/update）
     */
    public function updateAction(){
//        $info = $this->getInput(array(
//            'adgroup_id', 'adgroup_name', 'optimization_goal', 'bid_amount', 'daily_budget',
//            'sub_product_refs_id','date_range', 'begin_date', 'end_date', 'time_range',
//            'time_series', 'time_senior_type', 'start_time', 'end_time', 'configured_status', 'customized_category', 'campaign_type'
//        ));
//        $info['target'] = json_decode($_GET['target'], TRUE);
        $info = $_POST;
//        var_dump($info);die;
        $data = $this->checkUpdateParam($info);

        $result = $this->send($data, 'update');
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0 && $resultArr['message'] == ''){
            $resultArr['message'] = '修改成功！';
        }
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查更新广告组的参数
     * @param type $info
     * @return type
     */
    private function checkUpdateParam($info){
        $data = [];
        $data['adgroup_id'] = $info['adgroup_id'];

        if(empty($info['adgroup_id'])){
            $this->output(1, '广告组 id不能为空');
        }
        if(isset($info['adgroup_name'])){
            $data['adgroup_name'] = trim($info['adgroup_name']);
            $adgroupNameLength = strlen($data['adgroup_name']);
            if($adgroupNameLength<1 || $adgroupNameLength>120){
                $this->output(1, '广告组名称字段长度最小 1 字节，长度最大 120 字节');
            }
        }
        if(isset($info['begin_date'])){
            $beginDateTime = strtotime($info['begin_date']);
            $endDateTime = strtotime($info['end_date']);
            if($beginDateTime > $endDateTime){
                $this->output(1, '开始投放日期要小于等于结束投放日期');
            }
            if($endDateTime < strtotime(date('Y-m-d'))){
                $this->output(1, '结束投放日期要大于等于今天');
            }
            $data['begin_date'] = date('Y-m-d',$beginDateTime);
            $data['end_date'] = date('Y-m-d',$endDateTime);
        }
        if(isset($info['bid_amount'])){
            if(!$this->matchValue(1, $info['bid_amount'])){
                $this->output(-1, '出价格式有误，只支持精确到小数点后两位的正数');
            }
            $data['bid_amount'] = floatval($info['bid_amount']) * 100;
        }
        if(isset($info['optimization_goal']) && !empty($info['optimization_goal'])){
            $data['optimization_goal'] = $info['optimization_goal'];
            if(!isset($this->marketingConfig['OPTIMIZATION_GOAL'][$data['optimization_goal']])){
                $this->output(1, '请选择广告优化目标类');
            }
        }
        if($info['campaign_type'] == 'CAMPAIGN_TYPE_WECHAT_MOMENTS'){
            if(isset($info['daily_budget']) && $info['daily_budget'] != ''){
                if(!$this->matchValue(1, $info['daily_budget'])){
                    $this->output(-1, '日预算格式有误，只支持精确到小数点后两位的正数');
                }
                $data['daily_budget'] = floatval($info['daily_budget']) * 100;
                if($data['daily_budget'] <100000 || $data['daily_budget']>1000000000){
                    $this->output(1, '日预算要求介于 1,000 元-10,000,000 元，单位为人民币');
                }
            }
        }

        //todo: (1)根据 adgroup_info里面的campaign_id 获取推广计划的信息，得到campaign_type
        //      (2)根据广告组出价规则和campaign_type做不同的处理逻辑校验
        //
        //广告组出价规则：
        //    对于普通展示广告（campaign_type = CAMPAIGN_TYPE_NORMAL）的CPC出价而言：
        //        出价需介于10分-10,000分之间（0.1元-100元，单位为人民币）；
        //        出价不得高于所属推广计划或帐号日预算的50%。
        //    对于微信公众号广告（campaign_type = CAMPAIGN_TYPE_WECHAT_OFFICIAL_ACCOUNTS）的CPC出价而言：
        //        出价需介于50分-2,000分之间（0.5元-20元，单位为人民币）；
        //        出价不得高于所属推广计划或帐号日预算的50%。
        //    对于普通展示广告（campaign_type = CAMPAIGN_TYPE_NORMAL）的CPM出价，要求介于150分-100,000分之间（1.5元-1000元，单位为人民币）；
        //    对于微信朋友圈广告（campaign_type = CAMPAIGN_TYPE_WECHAT_MOMENTS ）的CPM出价，基于不同的地域定向有不同限制，详见 朋友圈广告地域定向及价格约束；
        //    对于某些特殊流量的CPM出价可能还会有更详细的要求，具体信息可以联系您的运营接口人。

        if(isset($info['target'])){
            if(empty($info['target']['targeting_id'])){ // 定向详细设置，存放所有定向条件。与 targeting_id 不能同时填写且不能同时为空，仅微信流量的广告（朋友圈和公众号广告）可使用
                if($info['campaign_type'] == 'CAMPAIGN_TYPE_NORMAL'){
                    $this->output( -1, '非微信流量的广告只能使用已保存的定向包！');
                }
                $targeting = [];
                foreach ($info['target']['targeting'] as $key => $val) {
                    if (!empty($val)) {
                        $targeting[$key] = in_array($key, ['gender', 'app_install_status']) ? [$val] : $val;
                    }
                }
                $data['targeting'] = json_encode($targeting);
            }else{
                if($info['campaign_type'] != 'CAMPAIGN_TYPE_NORMAL'){
                    $this->output( -1, '微信流量的广告只能使用定向详细设置，不能使用定向包！');
                }
                $data['targeting_id'] = $info['target']['targeting_id'];
            }
        }

        //todo time_series (可不校验）
        if(isset($info['time_range'])){
            if($info['time_range'] == 0){
                $data['time_series'] = Common::get_week_time_series(0, 24);
            }else{
                if($info['time_senior_type'] == 0){
                    $data['time_series'] = Common::get_week_time_series(intval($info['start_time']), intval($info['end_time']));
                }else{
                    // 时间区段不满336位，自动填充0
                    $data['time_series'] = $info['time_series'];
                }
            }
        }

        if(isset($info['configured_status'])){
            if(!isset($this->marketingConfig['AD_STATUS'][$info['configured_status']])){
                $this->output(1, '状态参数错误');
            }
            $data['configured_status'] = $info['configured_status'];
        }

        if(isset($info['customized_category'])){
            $customized_category_length = strlen($info['customized_category']);
            if($customized_category_length<1 || $customized_category_length>200){
                $this->output(1, '自定义分类字段长度最小 0 字节，长度最大 200 字节');
            }
        }

        return $data;
    }


    /**
     * 获取广告组（adgroups/get）
     */
    public function getAction(){
        $params = $this->getInput(array('adgroup_id', 'page', 'page_size',  'start_date', 'end_date', 'configured_status', 'adgroup_name', 'system_status', 'campaign_id', 'product_type'));
        $params = $this->checkGetParam($params);

        $result = $this->send($params, 'get');
        $resultArr = json_decode($result, TRUE);
        $resultArr['data'] = $this->parseGdtList($resultArr['data'],[
            'billing_event'=>'BILLINGEVENT',
            'optimization_goal'=>'OPTIMIZATIONGOAL',
            'product_type'=>'PRODUCT_TYPE',
            'site_set'=>'SITE_SET',
            'time_series'=>'TIME_SERIES',
        ]);

        $report = intval($this->getInput('report'));
        if($report){
            # 获取曝光量，点击量，点击率，点击均价，价格
            $resultArr = $this->getDailyReports($resultArr, [
                'level' => 'ADGROUP',
                'start_date' => $params['start_date'],
                'end_date' => $params['end_date'],
                'page' => $params['page'],
                'page_size' => $params['page_size'],
                'field' => 'adgroup_id',
            ]);
            $resultArr['data']['sum'] = $this->sumReportData($resultArr['data']['list']);
            # 组合标的物
            $productList = MobgiMarket_Service_SettingModel::getProductsByParams(['account_id'=>$this->getGdtAccountId()]);
            $productKeyArr = Common::resetKey($productList, 'product_refs_id');
            $sortParams = $this->getInput(['sort_field', 'sort_type']);
            $sortKeyList = [];
            foreach($resultArr['data']['list'] as $key => $value){
                if(isset($value[$sortParams['sort_field']])){
                    $sortKeyList[] = ($value[$sortParams['sort_field']] == '-') ? 0 : $value[$sortParams['sort_field']];
                }
                $resultArr['data']['list'][$key]['product_name'] = isset($productKeyArr[$value['product_refs_id']]) ? $productKeyArr[$value['product_refs_id']]['product_name'] : $value['product_refs_id'];
            }
            if(!empty($sortKeyList)){
                $sortType = ($sortParams['sort_type'] == 'ascending') ? SORT_ASC : SORT_DESC;
                array_multisort($sortKeyList, $sortType, $resultArr['data']['list']);
            }
        }
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查获取广告组的参数
     * @param type $info
     * @return type
     */
    private function checkGetParam($info){

        if(isset($info['adgroup_id']) && $info['adgroup_id'] ){
            $info['adgroup_id'] = intval($info['adgroup_id']);
            if(empty($info['adgroup_id'])){
                $this->output(1, '广告组 id必须是整数');
            }
        }else{
            unset($info['adgroup_id']);
        }

        //接收推广计划状态参数
        if($info['configured_status']){
            if(!isset($this->marketingConfig['AD_STATUS'][$info['configured_status']])){
                $this->output(1, '推广计划状态参数错误');
            }
            $configured_status_arr = array(
                'field'=>'configured_status',
                'operator'=>'EQUALS',
                'values'=>array($info['configured_status']),
            );
            $info['filtering'][] = $configured_status_arr;
            unset($info['configured_status']);
        }

        //接收系统状态参数
        if($info['system_status']){
            if(!isset($this->marketingConfig['AD_SYSTEM_STATUS'][$info['system_status']])){
                $this->output(1, '系统状态参数错误');
            }
            $system_status_arr = array(
                'field'=>'system_status',
                'operator'=>'EQUALS',
                'values'=>array($info['system_status']),
            );
            $info['filtering'][] = $system_status_arr;
            unset($info['system_status']);
        }

        //接收推广计划参数
        if($info['campaign_id']){
            $info['campaign_id'] = intval($info['campaign_id']);
            if(empty($info['campaign_id'])){
                $this->output(1, '推广计划参数错误');
            }
            $campaign_id_arr = array(
                'field'=>'campaign_id',
                'operator'=>'EQUALS',
                'values'=>array($info['campaign_id']),
            );
            $info['filtering'][] = $campaign_id_arr;
            unset($info['campaign_id']);
        }

        //接收标的物类型参数
        if($info['product_type']){
            if(!isset($this->marketingConfig['PRODUCT_TYPE'][$info['product_type']])){
                $this->output(1, '标的物类型参数错误');
            }
            $product_type_arr = array(
                'field'=>'product_type',
                'operator'=>'EQUALS',
                'values'=>array($info['product_type']),
            );
            $info['filtering'][] = $product_type_arr;
            unset($info['product_type']);
        }

        //接收推广计划名称
        if($info['adgroup_name']){
            $adgroup_name_length = strlen($info['adgroup_name']);
            if($adgroup_name_length<1 || $adgroup_name_length>120){
                $this->output(1, '推广计划名称字段长度最小 1 字节，长度最大 120 字节');
            }
            $adgroup_name_arr = array(
                'field'=>'adgroup_name',
                'operator'=>'CONTAINS',
                'values'=>array($info['adgroup_name']),
            );
            $info['filtering'][] = $adgroup_name_arr;
            unset($info['adgroup_name']);
        }

        if(empty($info['filtering'])){
            unset($info['filtering']);
        }

        if(isset($info['page']) && $info['page']){
            $info['page'] = intval($info['page']);
            if($info['page']<1 || $info['page']>99999){
                $this->output(1, '页码最小值 1，最大值 99999');
            }
        }else{
            unset($info['page']);
        }

        if(isset($info['page_size']) && $info['page_size']){
            $info['page_size'] = intval($info['page_size']);
            if($info['page_size']<1 || $info['page_size']>100){
                $this->output(1, '每页显示的数据条数最小值 1，最大值 100');
            }
        }else{
            unset($info['page_size']);
        }

        //拉取曝光，点击数据时要用到开始日期和结束日期,默认的日期为今天日期
        if(empty($info['start_date']) && empty($info['end_date'])){
            $info['start_date'] = $info['end_date'] = date('Y-m-d');
        }

//        if(strtotime($info['start_date']) > strtotime($info['end_date'])){
//            $this->output(1, '开始日期要小于等于结束日期xx');
//        }
//
//        if(strtotime($info['end_date']) < strtotime(date("Y-m-d"))){
//            $this->output(1, '结束日期要小于等于今天');
//        }

        return $info;
    }

    /**
     * 删除广告组（adgroups/delete）
     */
    public function deleteAction(){
        $adgroupId = $this->getInput('adgroup_id');
        if(empty($adgroupId)){
            $this->output(1, '广告组id不能为空');
        }
        $data = array(
            'adgroup_id'=>$adgroupId,
        );
        $result = $this->send($data, 'delete');
        $resultArr = json_decode($result, TRUE);
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 导出广告组excel
     */
    public function exportAction(){
        $params = $this->getInput(array('adgroup_id', 'filtering', 'page', 'page_size',  'start_date', 'end_date', 'configured_status', 'adgroup_name', 'system_status'));
        $data = $this->checkGetParam($params);

        $result = $this->send($data, 'get');
        $adgroupsResultArr = json_decode($result, TRUE);

        $adgroupsResultArr['data'] = $this->parseGdtList($adgroupsResultArr['data'],
            array( 'configured_status'=>'AD_STATUS',
                'billing_event'=>'BILLINGEVENT',
                'optimization_goal'=>'OPTIMIZATIONGOAL',
                'site_set'=>'SITE_SET',
                'product_type'=>'PRODUCT_TYPE',
                'system_status'=>'AD_SYSTEM_STATUS'
            ));

        # 获取曝光量，点击量，点击率，点击均价，价格
        $adgroupsResultArr = $this->getDailyReports($adgroupsResultArr, [
            'level' => 'ADGROUP',
            'start_date' => $params['start_date'],
            'end_date' => $params['end_date'],
            'page' => $params['page'],
            'page_size' => $params['page_size'],
            'field' => 'adgroup_id',
        ]);

        if($adgroupsResultArr['code']){
            $this->output($adgroupsResultArr['code'], $adgroupsResultArr['message'], $adgroupsResultArr['data']);
        }else{
            $adgroups = $adgroupsResultArr['data']['list'];
        }

        Yaf_loader::import("Util/PHPExcel/PHPExcel.php");
        $objPHPExcel = new PHPExcel();
        /*以下是一些设置，作者、标题啊之类*/
        $objPHPExcel->getProperties()->setCreator("marketing api")
            ->setLastModifiedBy("marketing api")
            ->setTitle("广告组EXCEL导出")
            ->setSubject("广告组EXCEL导出")
            ->setDescription("广告组列表")
            ->setKeywords("excel")
            ->setCategory("result file");
        /*以下就是对处理Excel里的数据，横着取数据*/

        $all_field =  array(
            "campaign_id" => "推广计划 id",
            "adgroup_id" => "广告组 id",
            "adgroup_name" => "广告组名称",
            "site_set_name" => "投放站点集合", //数组格式
            "optimization_goal_name" => "广告优化目标类型",

            "billing_event_name" => "计费类型",
            "bid_amount" => "广告出价，单位为分",
            "daily_budget" => "日限额，单位为分",
            "product_type_name" => "标的物类型",
            "product_refs_id" => "标的物 id",

//            "sub_product_refs_id" => "子标的物 id（渠道包 id）",
//            "targeting_id" => "定向 id",
//            "targeting" => "定向详细设置",
            "begin_date" => "开始投放日期",
            "end_date" => "结束投放日期",

//            "time_series" => "投放时间段",
            "configured_status_name" => "客户设置的状态",
            "system_status_name" => "系统状态",
//            "reject_message" => "审核消息",
//            "customized_category" => "自定义分类",

            "created_time" => "创建时间",
            "last_modified_time" => "最后修改时间",
            "frequency" => "最高曝光频次",

            "impression" => "曝光",
            "click" => "点击",
            "click_rate" => "点击率",
            "cost_per_click" => "点击均价",
            "cost" => "价格",
        );
        $num = 1;
        $char = 'A';
        foreach($all_field as $field_key => $field_val){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$num, $field_val);
            $char ++;
        }
        foreach($adgroups as $data_key => $data_val){
            $num ++;
            $char = 'A';

            foreach($data_val as $data_val_key =>$data_val_value){
                if(is_array($data_val_value)){
                    $data_val[$data_val_key] = implode(',', $data_val['site_set']);// 若是数组格式，则需要转化成字符串, 如：$data_val_key为site_set时需转成字符串
                }
            }

            foreach($all_field as $field_key => $field_val){
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$num, $data_val[$field_key]);
                $char ++;
            }
        }
        // 开始组合头
        $xml_name = "广告组列表";
        $objPHPExcel->getActiveSheet()->setTitle('User');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$xml_name.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;

    }

    /**
     * 广告组
     * 批量修改
     */
    public function batchChangeAction(){
        $params = $this->getInput(array('ids', 'date_range', 'begin_date', 'end_date', 'time_range', 'time_series',
            'time_senior_type', 'start_time', 'end_time', 'configured_status', 'bid_amount'));
        if(empty($params['ids'])){
            $this->output(-1, '请先选择要操作的广告组');
        }
        $data = $this->checkBatchChangeParam($params);
        if(empty($data)){
            $this->output(-1, '请选择要操作的内容');
        }
        # 批量修改
        $successNum = 0;
        $failedNum = 0;
        $failedMsg = [];
        foreach($params['ids'] as $value){
            $data['adgroup_id'] = $value;
            $result = $this->send($data, 'update');
            $resultArr = json_decode($result, TRUE);
            if($resultArr['code'] == 0){
                $successNum ++;
            }else{
                $failedNum ++;
                $failedMsg[] = $resultArr['message'];
            }
        }
        if($failedNum == 0){
            $this->output(0, $successNum.'个修改成功');
        }elseif($successNum == 0){
            $message = implode('; ', $failedMsg);
            $this->output(-1, '修改失败，原因：'.$message);
        }else{
            $message = implode('; ', $failedMsg);
            $this->output(0, $successNum.'个修改成功，'.$failedNum.'个修改失败，原因：'.$message);
        }

    }

    /**
     * 校验批量编辑参数
     * @param $params
     * @return array
     */
    private function checkBatchChangeParam($params){
        $data = [];
        if(isset($params['begin_date'])) {
            $beginDateTime = strtotime($params['begin_date']);
            $endDateTime = strtotime($params['end_date']);
            if ($beginDateTime > $endDateTime) {
                $this->output(-1, '开始投放日期要小于等于结束投放日期');
            }
            if ($endDateTime < strtotime(date('Y-m-d'))) {
                $this->output(-1, '结束投放日期要大于等于今天');
            }
            $data['begin_date'] = date('Y-m-d', $beginDateTime);
            $data['end_date'] = date('Y-m-d', $endDateTime);
        }
        if(isset($params['time_range'])){
            if($params['time_range'] == 0){
                $data['time_series'] = Common::get_week_time_series(0, 24);
            }else{
                if($params['time_senior_type'] == 0){
                    $data['time_series'] = Common::get_week_time_series(intval($params['start_time']), intval($params['end_time']));
                }else{
                    // 时间区段不满336位，自动填充0
                    $data['time_series'] = $params['time_series'];
                }
            }
        }
        if(isset($params['configured_status'])){
            if(!isset($this->marketingConfig['AD_STATUS'][$params['configured_status']])){
                $this->output(-1, '状态参数错误');
            }
            $data['configured_status'] = $params['configured_status'];
        }
        if(isset($params['bid_amount'])){
            if(!$this->matchValue(1, $params['bid_amount'])){
                $this->output(-1, '出价格式有误，只支持精确到小数点后两位的正数');
            }
            $data['bid_amount'] = floatval($params['bid_amount']) * 100;
        }
        return $data;
    }

    /**
     * 广告组
     * 批量删除
     */
    public function batchDeleteAction(){
        $params = $this->getInput(array('ids'));
        if(empty($params['ids'])){
            $this->output(-1, '请先选择要删除的广告组');
        }
        # 批量修改
        $successNum = 0;
        $failedNum = 0;
        $failedMsg = [];
        foreach($params['ids'] as $value){
            $data['adgroup_id'] = $value;
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

    public function getReportAction(){
        $data = array(
            'level'=>'ADGROUP',
            'date_range'=>array('start_date'=>'2018-03-01', 'end_date'=>'2018-04-13'),
            'page'=>1,
            'page_size'=>100,
            'group_by'=> ['adgroup_id'],
        );
        $dailyReport = $this->send($data, 'get', 'daily_reports');
        echo $dailyReport;die;
        $dailyReportArr = json_decode($dailyReport, TRUE);
    }

    public function getAdsAction(){
        $params = $this->getInput(['adgroup_id','targeting_id','start_date','end_date']);
        $nowDate = date('Y-m-d');
        if($params['targeting_id']){ // 获取定向
            $targetResult = $this->getFirstData(['targeting_id'=>$params['targeting_id']], 'get', 'targetings');
            $resultData['targeting'] = empty($targetResult) ? [] : $targetResult['targeting'];
            if (isset($resultData['targeting']['gender'])) {
                $resultData['targeting']['gender'] = $resultData['targeting']['gender'][0];
            }
            if (isset($resultData['targeting']['app_install_status'])) {
                $resultData['targeting']['app_install_status'] = $resultData['targeting']['app_install_status'][0];
            }
        }
        $data['filtering'][] = [
            'field'=>'adgroup_id',
            'operator'=>'EQUALS',
            'values'=>[$params['adgroup_id']],
        ];
        $adResult = $this->send($data, 'get', 'ads');
        $adResultArr = json_decode($adResult, TRUE);
        $resultData['adcreative_list'] = [];
        $adIdArr = [];
        if(!empty($adResultArr['data']['list'])){
            foreach($adResultArr['data']['list'] as $adVal){
                $adIdArr[] = $adVal['ad_id'];
                # 当创意元素是 element_story 数组时候，要展示多长图片
                if(isset($adVal['adcreative']['adcreative_elements']['element_story'])){
                    foreach($adVal['adcreative']['adcreative_elements']['element_story'] as $adcreativeVal){
                        $resultData['adcreative_list'][] = [
                            'ad_name' => $adVal['ad_name'],
                            'adcreative_name' => $adVal['adcreative']['adcreative_name'],
                            'destination_url' => $adVal['adcreative']['destination_url'],
                            'title' => $adVal['adcreative']['adcreative_elements']['title'],
                            'image_url' => $adcreativeVal['image_url']
                        ];
                    }
                }else{
                    $resultData['adcreative_list'][] = [
                        'ad_name' => $adVal['ad_name'],
                        'adcreative_name' => $adVal['adcreative']['adcreative_name'],
                        'destination_url' => $adVal['adcreative']['destination_url'],
                        'title' => $adVal['adcreative']['adcreative_elements']['title'],
                        'image_url' => $adVal['adcreative']['adcreative_elements']['image_url']
                    ];
                }
            }
        }
        # 广告数据查询 日报表、小时报表
        if(empty($params['start_date']) || empty($params['end_date'])){
            $params['start_date'] = $params['end_date'] = $nowDate;
        }
        $reportData = [
            'level' => 'AD',
            'start_date' => $params['start_date'],
            'end_date' => $params['end_date'],
            'date' => $nowDate,
            'page' => $params['page'],
            'page_size' => 100,
            'field' => 'ad_id',
        ];
        $reportData['filtering'][] = [
            'field'=>'ad_id',
            'operator'=>'IN',
            'values'=>$adIdArr,
        ];

        $adResultArr = $this->getDailyReports($adResultArr, $reportData); // 日报表
        $adResultArr = $this->getHourlyReports($adResultArr, $reportData); // 小时报表
        $resultData['ad_list'] = $adResultArr['data']['list'];
        $this->output($adResultArr['code'], $adResultArr['message'], $resultData);
    }


}