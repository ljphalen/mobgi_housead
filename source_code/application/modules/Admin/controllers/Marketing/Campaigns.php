<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/12
 * Time: 15:39
 */
class Marketing_CampaignsController extends Admin_MarketingController {

    public $resourceName = 'campaigns';

    /**
     * 创建推广计划（campaigns/add）
     */
    public function addAction(){
        $info = $this->getInput(array('campaign_name', 'campaign_type', 'product_type', 'daily_budget', 'configured_status', 'speed_mode'));
        $info = $this->checkAddParam($info);

        $data = array(
            'campaign_name'=>$info['campaign_name'],
            'campaign_type'=>$info['campaign_type'],
            'product_type'=>$info['product_type'],
            'daily_budget'=>$info['daily_budget'],
            'configured_status'=>$info['configured_status'],
            'speed_mode'=>$info['speed_mode']
        );

        $result = $this->send($data, 'add');
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0 && $resultArr['message'] == ''){
            $resultArr['message'] = '新建成功！';
        }
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查创建推广计划的参数
     * @param type $info
     * @return type
     */
    private function checkAddParam($info){
        $info['campaign_name'] = trim($info['campaign_name']);
        if(empty($info['campaign_name'])){
            $this->output(1, '推广计划名称不能为空');
        }

        $campaign_name_length = strlen($info['campaign_name']);
        if($campaign_name_length<1 || $campaign_name_length>120){
            $this->output(1, '推广计划名称字段长度最小 1 字节，长度最大 120 字节');
        }
        if(empty($info['product_type'])){
            $this->output(1, '标的物类型不能为空');
        }
        if(!isset($this->marketingConfig['PRODUCT_TYPE'][$info['product_type']])){
            $this->output(1, '标的物类型不存在');
        }
        if(empty($info['campaign_type'])){
            $this->output(1, '推广计划类型不能为空');
        }
        if(!isset($this->marketingConfig['CAMPAIGN_TYPE'][$info['campaign_type']])){
            $this->output(1, '推广计划类型不存在');
        }

        if($info['campaign_type'] == 'CAMPAIGN_TYPE_WECHAT_MOMENTS'){
            unset($info['daily_budget']);
        }
        if(isset($info['daily_budget'])){
            if(!$this->matchValue(1, $info['daily_budget'])){
                $this->output(-1, '日预算格式有误，只支持精确到小数点后两位的正数');
            }
            $info['daily_budget'] = floatval($info['daily_budget']) * 100;
            if($info['daily_budget'] <5000 || $info['daily_budget']>400000000){
                $this->output(1, '日预算需介于 50 元-4,000,000 元，单位为人民币');
            }
        }
        if(isset($info['speed_mode'])){
            if(!isset($this->marketingConfig['SPEED_MODE'][$info['speed_mode']])){
                $this->output(1, '投放速度模式参数错误');
            }
        }
        if(isset($info['configured_status'])){
            if(!isset($this->marketingConfig['AD_STATUS'][$info['configured_status']])){
                $this->output(1, '状态参数错误');
            }
        }
        return $info;
    }

    /**
     * 更新推广计划（campaigns/update）
     */
    public function updateAction(){
        $info = $this->getInput(array('campaign_id', 'campaign_name', 'daily_budget', 'configured_status', 'speed_mode', 'product_type'));
        $data = $this->checkUpdateParam($info);

        $result = $this->send($data, 'update');
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0 && $resultArr['message'] == ''){
            $resultArr['message'] = '修改成功！';
        }
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查更新推广计划的参数
     * @param type $info
     * @return type
     */
    private function checkUpdateParam($info){
        if(empty($info['campaign_id'])){
            $this->output(1, '推广计划id不能为空');
        }

        if(isset($info['campaign_name'])  && $info['campaign_name'] ){
            $info['campaign_name'] = trim($info['campaign_name']);
            $campaign_name_length = strlen($info['campaign_name']);
            if($campaign_name_length<1 || $campaign_name_length>120){
                $this->output(1, '推广计划名称字段长度最小 1 字节，长度最大 120 字节');
            }
        }else{
            unset($info['campaign_name']);
        }


        //todo 获取指定的推广计划id的详细信息，验证daily_budget修改幅度
//        $campaign_info = array();

//        日消耗限额，单位为分，微信朋友圈广告（ campaign_type = CAMPAIGN_TYPE_WECHAT_MOMENTS ）不可使用，其他广告可使用。
//        日预算需介于 5,000 分-400,000,000 分之间（ 50 元-4,000,000 元，单位为人民币）；
//        每次修改幅度不能低于该计划今日已消耗金额加上 5,000 分（ 50 元，单位为人民币）；
//        每次修改幅度不能低于 5,000 分（ 50 元，单位为人民币）；
//        每天每计划最多修改 1,000 次；
//        最小值 5000，最大值 400000000

        if(isset($info['daily_budget']) && $info['daily_budget'] != ''){
            if(!$this->matchValue(1, $info['daily_budget'])){
                $this->output(-1, '日预算格式有误，只支持精确到小数点后两位的正数');
            }
            $info['daily_budget'] = floatval($info['daily_budget']) * 100;
            if($info['daily_budget'] <5000 || $info['daily_budget']>400000000){
                $this->output(-1, '日预算需介于 50 元-4,000,000 元');
            }
            # 每次修改幅度不能少于50元，查询该值是否修改
            $campaignData = $this->getFirstData(['campaign_id'=>$info['campaign_id']], 'get');
            if(empty($campaignData)){
                $this->output(-1, '该推广计划不存在');
            }
            if($info['daily_budget'] == $campaignData['daily_budget']){
                unset($info['daily_budget']);
            }else{
                $differNum = abs($info['daily_budget'] - $campaignData['daily_budget']); // 绝对值
                if($differNum < 5000){
                    $this->output(-1, '每次修改幅度不能低于 50元');
                }
            }

        }else{
            unset($info['daily_budget']);
        }

        if(isset($info['configured_status']) && $info['configured_status']){
            if(!isset($this->marketingConfig['AD_STATUS'][$info['configured_status']])){
                $this->output(1, '状态参数错误');
            }
        }else{
            unset($info['configured_status']);
        }

        if(isset($info['speed_mode']) && $info['speed_mode']){
            if(!isset($this->marketingConfig['SPEED_MODE'][$info['speed_mode']])){
                $this->output(1, '投放速度模式参数错误');
            }
        }else{
            unset($info['speed_mode']);
        }

        //todo 标的物类型，仅允许 product type=UNKNOWN 时更新为非 UNKNOWN 的值，其他情况不允许修改。
//        if(isset($info['product_type'])){
//            if($campaign_info['product_type'] == 'UNKNOWN ' ){
//                if(!isset($this->marketingConfig['PRODUCT_TYPE'][$info['product_type']])){
//                    $this->output(1, '标的物类型参数错误');
//                }
//            }else{
//                $this->output(1, '此标的物类型不允许被更改');
//            }
//        }else{
//            unset($info['product_type']);
//        }
        return $info;
    }

    /**
     * 获取推广计划（campaigns/get）
     */
    public function getAction(){
        $info = $this->getInput(array('campaign_id', 'campaign_type', 'product_type', 'page', 'page_size', 'start_date', 'end_date', 'configured_status', 'campaign_name'));
        $params = $this->checkGetParam($info);

        $result = $this->send($params, 'get');
        $resultArr = json_decode($result, TRUE);
        $resultArr['data'] = $this->parseGdtList($resultArr['data'], array('campaign_type'=>'CAMPAIGN_TYPE', 'speed_mode'=>'SPEED_MODE', 'product_type'=>'PRODUCT_TYPE', ));

        $report = intval($this->getInput('report'));
        if($report) {
            # 获取曝光量，点击量，点击率，点击均价，价格
            $resultArr = $this->getDailyReports($resultArr, [
                'level' => 'CAMPAIGN',
                'start_date' => $params['start_date'],
                'end_date' => $params['end_date'],
                'page' => $params['page'],
                'page_size' => $params['page_size'],
                'field' => 'campaign_id',
            ]);
            $sortParams = $this->getInput(['sort_field', 'sort_type']);
            $sortKeyList = [];
            $resultArr['data']['sum'] = $this->sumReportData($resultArr['data']['list']);
            foreach($resultArr['data']['list'] as $key => $value){
                if(isset($value[$sortParams['sort_field']])){
                    $sortKeyList[] = ($value[$sortParams['sort_field']] == '-') ? 0 : $value[$sortParams['sort_field']];
                }
            }
            if(!empty($sortKeyList)){
                $sortType = ($sortParams['sort_type'] == 'ascending') ? SORT_ASC : SORT_DESC;
                array_multisort($sortKeyList, $sortType, $resultArr['data']['list']);
            }
        }
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }


    /**
     * 检查获取推广计划的参数
     * @param type $info
     * @return type
     */
    private function checkGetParam($info){

        if(isset($info['campaign_id']) && $info['campaign_id'] ){
            $info['campaign_id'] = intval($info['campaign_id']);
            if(empty($info['campaign_id'])){
                $this->output(1, '推广计划id必须是整数');
            }
        }else{
            unset($info['campaign_id']);
        }

        //接收推广计划类型参数
        if($info['campaign_type']){
            if(!isset($this->marketingConfig['CAMPAIGN_TYPE'][$info['campaign_type']])){
                $this->output(1, '推广计划类型不存在');
            }
            $filterArr = array(
                'field'=>'campaign_type',
                'operator'=>'EQUALS',
                'values'=>array($info['campaign_type']),
            );
            $info['filtering'][] = $filterArr;
            unset($info['campaign_type']);
        }

        //接收标的物类型参数
        if($info['product_type']){
            if(!isset($this->marketingConfig['PRODUCT_TYPE'][$info['product_type']])){
                $this->output(1, '标的物类型不存在');
            }
            $filterArr = array(
                'field'=>'product_type',
                'operator'=>'EQUALS',
                'values'=>array($info['product_type']),
            );
            $info['filtering'][] = $filterArr;
            unset($info['product_type']);
        }

        //接收推广计划状态参数
        if($info['configured_status']){
            if(!isset($this->marketingConfig['AD_STATUS'][$info['configured_status']])){
                $this->output(1, '推广计划状态参数错误');
            }
            $configuredStatusArr = array(
                'field'=>'configured_status',
                'operator'=>'EQUALS',
                'values'=>array($info['configured_status']),
            );
            $info['filtering'][] = $configuredStatusArr;
            unset($info['configured_status']);
        }

        //接收推广计划名称
        if($info['campaign_name']){
            $campaignNameLength = strlen($info['campaign_name']);
            if($campaignNameLength<1 || $campaignNameLength>120){
                $this->output(1, '推广计划名称字段长度最小 1 字节，长度最大 120 字节');
            }
            $campaignNameArr = array(
                'field'=>'campaign_name',
                'operator'=>'CONTAINS',
                'values'=>array($info['campaign_name']),
            );
            $info['filtering'][] = $campaignNameArr;
            unset($info['campaign_name']);
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
//            $this->output(1, '开始日期要小于等于结束日期');
//        }
//
//        if(strtotime($info['end_date']) > strtotime(date("Y-m-d"))){
//            $this->output(1, '结束日期要小于等于今天');
//        }
        return $info;
    }

    /**
     * 删除推广计划（campaigns/delete）
     */
    public function deleteAction(){
        $campaignId = $this->getInput('campaign_id');
        if(empty($campaignId)){
            $this->output(1, '推广计划id不能为空');
        }
        $data = array(
            'campaign_id'=>$campaignId,
        );
        $result = $this->send($data, 'delete');
        $resultArr = json_decode($result, TRUE);
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 导出推广计划excel
     */
    public function exportAction(){
        $info = $this->getInput(array('campaign_id', 'page', 'page_size', 'start_date', 'end_date', 'configured_status', 'campaign_name'));
        $params = $this->checkGetParam($info);

        $result = $this->send($params, 'get');
        $campaignResultArr = json_decode($result, TRUE);
        $campaignResultArr['data'] = $this->parseGdtList($campaignResultArr['data'], array('campaign_type'=>'CAMPAIGN_TYPE',
            'speed_mode'=>'SPEED_MODE', 'product_type'=>'PRODUCT_TYPE', 'configured_status'=>'AD_STATUS' ));

        # 获取曝光量，点击量，点击率，点击均价，价格
        $campaignResultArr = $this->getDailyReports($campaignResultArr, [
            'level' => 'CAMPAIGN',
            'start_date' => $params['start_date'],
            'end_date' => $params['end_date'],
            'page' => $params['page'],
            'page_size' => $params['page_size'],
            'field' => 'campaign_id',
        ]);

        if($campaignResultArr['code']){
            $this->output($campaignResultArr['code'], $campaignResultArr['message'], $campaignResultArr['data']);
        }else{
            $campaigns = $campaignResultArr['data']['list'];
        }

        Yaf_loader::import("Util/PHPExcel/PHPExcel.php");
        $objPHPExcel = new PHPExcel();
        /*以下是一些设置，作者、标题啊之类*/
        $objPHPExcel->getProperties()->setCreator("marketing api")
            ->setLastModifiedBy("marketing api")
            ->setTitle("推广计划EXCEL导出")
            ->setSubject("推广计划EXCEL导出")
            ->setDescription("推广计划列表")
            ->setKeywords("excel")
            ->setCategory("result file");
        /*以下就是对处理Excel里的数据，横着取数据*/

        $all_field =  array(
            "campaign_id" => "推广计划id",
            "campaign_type_name" => "推广计划类型",
            "campaign_name" => "推广计划名称",
            "daily_budget" => "日消耗限额(单位为分)",
            "budget_reach_date" => "日限额到达日期",
            "created_time" => "创建时间",
            "last_modified_time" => "最后修改时间",
            "speed_mode_name" => "投放速度模式",
            "product_type_name" => "标的物类型",
            "configured_status_name" => "客户设置的状态",
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
        foreach($campaigns as $data_key => $data_val){
            $num ++;
            $char = 'A';

            foreach($data_val as $data_val_key =>$data_val_value){
                if(is_array($data_val_value)){
                    $data_val[$data_val_key] = implode(',', $data_val['site_set']);// 若是数组格式，则需要转化成字符串, 如：$data_val_key为site_set时需转成字符串
                }
            }

            $data_val['configured_status'] = $this->marketingConfig['AD_STATUS'][$data_val['configured_status']];
            foreach($all_field as $field_key => $field_val){
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$num, $data_val[$field_key]);
                $char ++;
            }
        }
        // 开始组合头
        $xml_name = "推广计划列表";
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
     * 推广计划
     * 批量修改
     */
    public function batchChangeAction(){
        $params = $this->getInput(array('ids', 'configured_status'));
        if(empty($params['ids'])){
            $this->output(-1, '请先选择要操作的推广计划');
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
            $data['campaign_id'] = $value;
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
        if(isset($params['configured_status'])){
            if(!isset($this->marketingConfig['AD_STATUS'][$params['configured_status']])){
                $this->output(-1, '状态参数错误');
            }
            $data['configured_status'] = $params['configured_status'];
        }
        return $data;
    }

    /**
     * 推广计划
     * 批量删除
     */
    public function batchDeleteAction(){
        $params = $this->getInput(array('ids'));
        if(empty($params['ids'])){
            $this->output(-1, '请先选择要删除的推广计划');
        }
        # 批量修改
        $successNum = 0;
        $failedNum = 0;
        $failedMsg = [];
        foreach($params['ids'] as $value){
            $data['campaign_id'] = $value;
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
            'level'=>'CAMPAIGN',
            'date_range'=>array('start_date'=>'2018-03-01', 'end_date'=>'2018-04-13'),
            'page'=>1,
            'page_size'=>100,
            'group_by'=> ['campaign_id'],
        );
        $dailyReport = $this->send($data, 'get', 'daily_reports');
        echo $dailyReport;die;
        $dailyReportArr = json_decode($dailyReport, TRUE);
    }

}