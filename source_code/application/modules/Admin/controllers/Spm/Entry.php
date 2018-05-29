<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/4
 * Time: 15:04
 */
class Spm_EntryController extends Admin_BaseController {

    public function init() {
        parent::init();
        $module = $this->getTopModule();
        $this->assign('module', $module);
    }

    public $actions = [
        'costUrl' => '/Admin/Spm_Entry/cost',
        'costListUrl' => '/Admin/Spm_Entry/costList',
        'exportCostUrl' => '/Admin/Spm_Entry/exportCost',
        'costBatchDelUrl' => '/Admin/Spm_Entry/costBatchDel',
        'editCostUrl' => '/Admin/Spm_Entry/editCost',
        'editCostPostUrl' => '/Admin/Spm_Entry/editCostPost',
        'costAddUrl' => '/Admin/Spm_Entry/costAdd',
        'costAddPostUrl' => '/Admin/Spm_Entry/costAddPost',

        'planUrl' => '/Admin/Spm_Entry/plan',
        'planListUrl' => '/Admin/Spm_Entry/planList',
        'exportPlanUrl' => '/Admin/Spm_Entry/exportPlan',
        'planBatchDelUrl' => '/Admin/Spm_Entry/planBatchDel',
        'editPlanUrl' => '/Admin/Spm_Entry/editPlan',
        'editPlanPostUrl' => '/Admin/Spm_Entry/editPlanPost',
        'planAddUrl' => '/Admin/Spm_Entry/planAdd',
        'planAddPostUrl' => '/Admin/Spm_Entry/planAddPost',


        'staffPlanUrl' => '/Admin/Spm_Entry/staffPlan',
        'staffPlanPostUrl' => '/Admin/Spm_Entry/staffPlanAddPost',
        'staffPlanListUrl' => '/Admin/Spm_Entry/getStaffPlanList',
        'staffPlanDelUrl' => '/Admin/Spm_Entry/delStaffPlan',
        'staffPlanEditUrl' => '/Admin/Spm_Entry/editStaffPlan',
        'staffPlanSaveUrl' => '/Admin/Spm_Entry/saveStaffPlan',
        'staffPlanExportUrl' => '/Admin/Spm_Entry/exportStaffPlan',


    ];

    public function getPost($var) {
        $post = $_POST;
        if (is_string($var)) return $post[$var];
        $return = array();
        if (is_array($var)) {
            foreach ($var as $key => $value) {
                $return[$value] = $post[$value];
            }
            return $return;
        }
        return null;
    }

    /**
     * filter params
     * @param $params
     * @return array
     * @throws Exception
     */
    protected function filterParams($params) {
        foreach ($params as $field => $val) {
            if (is_array($val)) {
                list($op, $value) = $val;
                if (is_null($value) || $value === '') {
                    unset($params[$field]);
                }
            } else {
                if (is_null($val) || $val === '') {
                    unset($params[$field]);
                }
            }
        }
        return $params;
    }

    /*
     * 查询数据类型 op val msg
     */
    private function checkEntryParams($row, $data, $params, $tip) {
        $tipPrefix = '第 ' . $row . ' 行数据，';
        $tipSuffix = '<br>';
        $flag = true;
        foreach ($params as $field => $val) {
            $tip[$field] = isset($tip[$field]) ? $tip[$field] : '';
            if (is_array($val[0])) {
                foreach ($val as $v) {
                    list($op, $t, $rep) = $v;
                    $vFlag = $this->_check($op, $data[$field]);
                    if (!$vFlag) {
                        $flag = $vFlag;
                        $vTip = $t;
                        if (!empty($rep)) { // 替换参数 如 (%s) -> (activity_name)
                            $vTip = str_replace('%s', $data[$rep], $vTip);
                        }
                        $vTip = $tipPrefix . $vTip . $tipSuffix;
                        $tip[$field] .= $vTip;
                    }
                }
            } else { # 筛选特定校验
                list($op, $t, $val, $sArr) = $val;
                if ($this->_check($op, $data[$field], $val)) {
                    foreach ($sArr as $field => $v) {
                        list($op, $t, $rep) = $v;
                        $vFlag = $this->_check($op, $data[$field]);
                        if (!$vFlag) {
                            $flag = $vFlag;
                            $vTip = $t;
                            if (!empty($rep)) { // 替换参数 如 (%s) -> (activity_name)
                                $vTip = str_replace('%s', $data[$rep], $vTip);
                            }
                            $vTip = $tipPrefix . $vTip . $tipSuffix;
                            $tip[$field] .= $vTip;
                        }
                    }
                }
            }
            if (!$flag) {
                break;
            }
        }
        return array($flag, $tip);
    }

    /**
     * check 条件匹配
     * @param $op
     * @param $value
     * @param $checkValue
     * @return bool
     */
    private function _check($op, $value, $checkValue = '') {
        switch ($op) {
            case 'date':
                $flag = ($value != PHPExcel_Cell_DataType::TYPE_NUMERIC) ? false : true;
                break;
            case 'empty':
                $flag = empty($value) ? false : true;
                break;
            case '0':
                $flag = ($value == 0) ? false : true;
                break;
            case 'isset':
                $flag = isset($value) ? true : false;
                break;
            case '=':
                $flag = ($value == $checkValue) ? true : false;
                break;
        }
        return $flag;
    }

    private $checkCostAddKeys = [
        'date_type' => [['date', '日期格式错误！', '']],
        'date_of_log' => [['empty', '日期不能为空！', '']],
        'activity_id' => [['0', '活动标识有误，查无此活动！(%s)', 'activity_name']],
        'activity_data' => [['isset', '活动标识有误，查无此活动！(%s)', 'activity_name']],
        'activity_group_data' => [['isset', '活动标识有误，活动查不到对应活动组！(%s)', 'activity_name']],
        'app_data' => [['isset', '该活动无对应应用！(%s)', 'activity_name']],
        'channel_data' => [['isset', '活动查不到对应渠道！(%s)', 'activity_name']],
        'channel_group_id' => [['0', '渠道查不到对应渠道组！(%s)', 'channel_name']],
        'supplier' => [['empty', '供应商不能为空！', '']],
        'account' => [['empty', '投放账户不能为空！', '']],
        'staff' => [['empty', '活动(%s)没有对应投放人员！', 'activity_name']],
        'platform' => ['=', '安卓应用才进行下列校验', 'android', [
            'android_channel_no' => ['empty', '安卓活动组 (%s) 没配置安卓渠道号！', 'activity_group_name'],
            'android_channel_group' => ['empty', '渠道号 (%s) 查不到对应安卓渠道组！', 'android_channel_no'],
        ]]
    ];

    private $checkPlanAddKeys = [
        'start_time' => [['0', '日期格式错误！', '']],
        'end_time' => [['0', '日期格式错误！', '']],
        'start_date' => [['empty', '日期不能为空！', '']],
        'end_date' => [['empty', '日期不能为空！', '']],
        'app_data' => [['isset', '查无此应用！(%s)', 'app_name']],
        'channel_group_data' => [['isset', '查无此渠道组！(%s)', 'channel_group_name']],
    ];


    public function costAction() {
        $channelList = MobgiSpm_Service_DeliveryModel::getDeliveryChannel();
        $this->assign('channelList', $channelList);
        $channelGroupList = MobgiSpm_Service_ChannelModel::getChannelGroup();
        $this->assign('channelGroupList', $channelGroupList);
        $androidChannelGroupList = MobgiSpm_Service_ChannelModel::getAndroidChannelGroup();
        $this->assign('androidChannelGroupList', $androidChannelGroupList);
        $userType = Admin_Service_UserModel::SPM_USER; # delivery user
        $userList = Admin_Service_UserModel::getsBy(array('user_type' => $userType));
        $this->assign('userList', $userList);
    }

    public function costListAction() {
        $params = $this->getPost(array(
            'page',
            'limit',
            'app_id',
            'channel_id',
            'channel_group_id',
            'android_channel_group_id',
            'staff',
            'supplier',
            'account',
            'sdate',
            'edate'
        ));
        $where = array(
            'app_id' => $params['app_id'],
            'channel_id' => $params['channel_id'],
            'channel_group_id' => $params['channel_group_id'],
            'android_channel_group_id' => $params['android_channel_group_id'],
            'staff' => $params['staff'],
            'supplier' => array('like', $params['supplier']),
            'account' => array('like', $params['account']),
        );
        $where = $this->filterParams($where);
        if (!empty($params['sdate']) && !empty($params['edate'])) {
            $where['date_of_log'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));
        }
        $orderBy = array('id' => 'DESC');
        list($total, $costList) = MobgiSpm_Service_EntryModel::getCostList($params['page'], $params['limit'], $where, $orderBy);
        $costList = MobgiSpm_Service_EntryModel::formatCostList($costList);
        $result = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $costList,
        );
        exit(json_encode($result));
    }

    public function exportCostAction() {
        error_reporting(E_ALL & ~E_NOTICE);
        $params = $this->getInput(array(
            'page',
            'limit',
            'app_id',
            'channel_id',
            'channel_group_id',
            'android_channel_group_id',
            'staff',
            'supplier',
            'account',
            'sdate',
            'edate'
        ));
        $where = array(
            'app_id' => $params['app_id'],
            'channel_id' => $params['channel_id'],
            'channel_group_id' => $params['channel_group_id'],
            'android_channel_group_id' => $params['android_channel_group_id'],
            'staff' => $params['staff'],
            'supplier' => array('like', $params['supplier']),
            'account' => array('like', $params['account']),
        );
        $where = $this->filterParams($where);
        if (!empty($params['sdate']) && !empty($params['edate'])) {
            $where['date_of_log'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));
            $xmlNameSuffix = '_' . $params['sdate'] . '_' . $params['edate'];
        } else {
            $xmlNameSuffix = '';
        }
        $orderBy = array('id' => 'DESC');
        $costDetail = MobgiSpm_Service_EntryModel::getCostByParams($where, $orderBy);
        $costDetail = MobgiSpm_Service_EntryModel::formatCostList($costDetail);
        Yaf_loader::import('Util/PHPExcel/PHPExcel.php');
        Yaf_loader::import('Util/PHPExcel/PHPExcel/IOFactory.php');
        $objPHPExcel = new PHPExcel();
        /*以下是一些设置 ，什么作者  标题啊之类的*/
        $objPHPExcel->getProperties()->setCreator('mobgi')->setLastModifiedBy('mobgi')->setTitle('数据EXCEL导出')->setSubject('数据EXCEL导出')->setDescription('广告渠道报表')->setKeywords('excel')->setCategory('result file');
        /*以下就是对处理Excel里的数据，横着取数据*/
        $allField = array(
            'date_of_log' => '日期',
            'app_name' => '产品',
            'activity_name' => '投放活动',
            'channel_group_name' => '渠道组',
            'android_channel_no' => '安卓渠道号',
            'android_channel_group_name' => '安卓渠道组',
            'supplier' => '供应商',
            'account' => '投放账户',
            'staff' => '投放人员',
            'account_consumption' => '账户消耗金额',
            'rebate' => '返点',
            'real_consumption' => '实际消耗金额',
            'deposit' => '充值金额',
            'impressions' => '展示数',
            'clicks' => '点击数',
        );
        $num = 1;
        $char = 'A';
        foreach ($allField as $fieldKey => $fieldVal) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char . $num, $fieldVal);
            $char++;
        }
        foreach ($costDetail as $dataKey => $dataVal) {
            $num++;
            $char = 'A';
            foreach ($allField as $fieldKey => $fieldVal) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char . $num, $dataVal[$fieldKey]);
                $char++;
            }
        }
        // 开始组合头
        $xmlName = '广告成本报表' . $xmlNameSuffix;
        $objPHPExcel->getActiveSheet()->setTitle('User');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $xmlName . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function costBatchDelAction() {
        $idArr = $this->getInput('id_arr');
        if (empty($idArr)) {
            $this->output(-1, '请选择要删除的项');
        }
        $result = MobgiSpm_Service_EntryModel::batchDelCost($idArr);
        if ($result) {
            $this->output(0, '删除成功');
        } else {
            $this->output(-1, '删除失败');
        }
    }

    public function editCostAction() {
        $id = intval($this->getInput('id'));
        $entryCost = MobgiSpm_Service_EntryModel::getEntryCostById($id);
        if ($entryCost) {
            $appData = MobgiSpm_Service_EntryModel::getAppById($entryCost['app_id']);
            $entryCost['app_name'] = $appData['app_name'];
            $activityData = MobgiSpm_Service_EntryModel::getActivityById($entryCost['activity_id']);
            $entryCost['activity_name'] = $activityData['name'];
            $channelGroupData = MobgiSpm_Service_EntryModel::getChannelGroupById($entryCost['channel_group_id']);
            $entryCost['channel_group_name'] = $channelGroupData['name'];
        }
        $this->assign('entryCost', $entryCost);
    }

    public function editCostPostAction() {
        $params = $this->getPost(array(
            'id',
            'activity_id',
            'date_of_log',
            'supplier',
            'account',
            'account_consumption',
            'rebate',
            'real_consumption',
            'deposit',
            'impressions',
            'clicks'
        ));
        $id = intval($params['id']);
        $params = $this->checkEditCostParams($params, $id);
        if ($id == 0) {
            $this->output(-1, '记录不存在');
        } else { # edit
            MobgiSpm_Service_EntryModel::updateEntryCost($params, array('id' => $id));
            $this->output(0, '修改成功');
        }
    }

    private function checkEditCostParams($params, $id) {
        $data['date_of_log'] = $params['date_of_log'];
        $data['supplier'] = trim($params['supplier']);
        $data['account'] = trim($params['account']);
        $data['account_consumption'] = floatval($params['account_consumption']);
        $data['rebate'] = floatval($params['rebate']);
        $data['real_consumption'] = floatval($params['real_consumption']);
        $data['deposit'] = floatval($params['deposit']);
        $data['impressions'] = intval($params['impressions']);
        $data['clicks'] = intval($params['clicks']);
        if (empty($data['date_of_log'])) {
            $this->output(-1, '请输入投放日期');
        }
        if (empty($data['supplier'])) {
            $this->output(-1, '请输入供应商');
        }
        if (empty($data['account'])) {
            $this->output(-1, '请输入投放账户');
        }
        $resutl = MobgiSpm_Service_EntryModel::getCostDataByParams($params, $id);
        if ($resutl) {
            $this->output(-1, '该记录已经存在');
        }
        return $data;
    }

    public function costAddAction() {

    }

    /*
     * 成本数据录入
     */
    public function costAddPostAction() {
        if (!file_exists($_FILES['file']['tmp_name'])) {
            $this->output(-1, '文件不存在！');
        } else {
            Yaf_loader::import("Util/PHPExcel/PHPExcel.php");
            Yaf_loader::import("Util/PHPExcel/PHPExcel/IOFactory.php");#引入
            $fileName = $_FILES['file']['tmp_name'];
            /**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/
            $PHPReader = new PHPExcel_Reader_Excel2007();
            if (!$PHPReader->canRead($fileName)) {
                $PHPReader = new PHPExcel_Reader_Excel5();
                if (!$PHPReader->canRead($fileName)) {
                    $this->output(-1, 'Excel文件未导入！');
                }
            }
            $PHPExcel = $PHPReader->load($fileName);
            $currentSheet = $PHPExcel->getSheet(0); /* * 读取excel文件中的第一个工作表 */
            $maxColumn = $currentSheet->getHighestColumn();
            /**取得最大的列号*/
            $columnNum = PHPExcel_Cell::columnIndexFromString($maxColumn); //字母列转换为数字列 如:AA变为27
            $rowNum = $currentSheet->getHighestRow(); /* * 取得一共有多少行 */
            $this->checkCostAdd($currentSheet, $columnNum, $rowNum);

            # 开始循环插入数据
            $this->costAddData($currentSheet, $rowNum);

            # 保存文件，核对导入信息时候用到
            $saveName = 'cost_' . $this->userInfo['user_name'] . '_' . date("YmdHis");
            $dataPath = Common::getConfig('siteConfig', 'dataPath');

            $savePath = sprintf('%s/%s/%s', $dataPath, 'spm_entry_data', date('Ym'));
            $uploader = new Util_Upload (array('maxSize' => 2048, 'allowFileType' => array('xls', 'xlsx')));
            $ret = $uploader->upload('file', $saveName, $savePath);
            $this->output(0, '导入成功');
        }

    }

    private function checkCostAdd($currentSheet, $columnNum, $rowNum) {
        if ($columnNum < 13) {
            $this->output(-1, 'Excel表格列数少于13，请检查！');
        }
        if ($rowNum < 2) {
            $this->output(-1, 'Excel表格行数少于2，请检查！');
        }
        $uniqueKeyArr = array();
        $totalKeyArr = array();
        $flag = true;
        $tip = array();
        $tip['exist'] = $tip['double'] = '';
        $activityMap = MobgiSpm_Service_EntryModel::getActivityMap();
        $activityGroupMap = MobgiSpm_Service_EntryModel::getActivityGroupMap();
        $appMap = MobgiSpm_Service_EntryModel::getAppMap();
        $channelMap = MobgiSpm_Service_EntryModel::getChannelMap();
        $androidChannelMap = MobgiSpm_Service_EntryModel::getAndroidChannelMap();
        for ($currentRow = 2; $currentRow <= $rowNum; $currentRow = $currentRow + 1) {
            /* 表格数据start */
            $data = array();
            $date = $currentSheet->getCellByColumnAndRow(0, $currentRow)->getValue(); //日期
            $data['date_type'] = $currentSheet->getCellByColumnAndRow(0, $currentRow)->getDataType();
            $data['date_of_log'] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($date) - 28800); //时间的转换
            $activityName = $currentSheet->getCellByColumnAndRow(3, $currentRow)->getValue(); //活动名
            $data['activity_id'] = $this->getActivityIdFromName($activityName);//活动id
            $data['supplier'] = $currentSheet->getCellByColumnAndRow(4, $currentRow)->getValue(); //供应商
            $data['account'] = $currentSheet->getCellByColumnAndRow(5, $currentRow)->getValue(); //投放账户
            $data['account_consumption'] = $currentSheet->getCellByColumnAndRow(7, $currentRow)->getValue(); //账户消耗金额
            $data['rebate'] = $currentSheet->getCellByColumnAndRow(8, $currentRow)->getValue(); //返点
            $data['deposit'] = $this->filterSymbol($currentSheet->getCellByColumnAndRow(10, $currentRow)->getValue()); //充值金额
            $data['impressions'] = $this->filterSymbol($currentSheet->getCellByColumnAndRow(11, $currentRow)->getValue()); //展示数
            $data['clicks'] = $this->filterSymbol($currentSheet->getCellByColumnAndRow(12, $currentRow)->getValue()); //点击数
            /* 表格数据end */
            $perKey = implode('_', $data);
            $totalKeyArr[] = $perKey;
            # 映射数据
            $data['activity_name'] = $activityName;
            $data['activity_data'] = $activityMap[$data['activity_id']];//活动记录
            $data['activity_group_data'] = $activityGroupMap[$data['activity_data']['group_id']];//活动组记录
            $data['app_data'] = $appMap[$data['activity_data']['app_id']];//应用记录
            $data['channel_data'] = $channelMap[$data['activity_data']['channel_id']];//投放渠道记录
            $data['channel_group_id'] = $data['channel_data']['group_id'];//投放渠道组id
            $data['staff'] = $data['activity_data']['operator'];

            # 安卓应用特有校验参数
            $data['platform'] = $data['app_data']['platform'];
            $data['activity_group_name'] = $data['activity_group_data']['name'];
            $data['android_channel_no'] = $data['activity_group_data']['channel_no'];
            $data['android_channel_group'] = $androidChannelMap[$data['android_channel_no']];
            list($tempFlag, $tip) = $this->checkEntryParams($currentRow, $data, $this->checkCostAddKeys, $tip);
            if (!$tempFlag) {
                $flag = $tempFlag;
                continue;
            }
            $uniqueKeyArr[] = $perKey;
            # 特殊校验方式
            if (count($uniqueKeyArr) != count(array_unique($uniqueKeyArr))) { // 校验自身重复性
                $flag = false;
                array_pop($uniqueKeyArr);
                $doubleId = array_search($perKey, $totalKeyArr) + 2;
                $tip['double'] .= '第 ' . $currentRow . ' 行数据在表格中和第 ' . $doubleId . ' 行重复！<br>';
                continue;
            }
            if (MobgiSpm_Service_EntryModel::checkCostByUniqueKey($data)) { // 校验数据库重复性
                $flag = false;
                $tip['exist'] .= '第 ' . $currentRow . ' 行数据已经存在！<br>';
                continue;
            }
        }
        if (!$flag) {
            $totalTip = implode('', $tip);
            $this->output(-1, $totalTip);
        }
    }

    private function costAddData($currentSheet, $rowNum) {// 全部数据校验通过，开始导入
        $activityMap = MobgiSpm_Service_EntryModel::getActivityMap();
        $activityGroupMap = MobgiSpm_Service_EntryModel::getActivityGroupMap();
        $appMap = MobgiSpm_Service_EntryModel::getAppMap();
        $channelMap = MobgiSpm_Service_EntryModel::getChannelMap();
        $androidChannelMap = MobgiSpm_Service_EntryModel::getAndroidChannelMap();
        $totalData = array();
        for ($currentRow = 2; $currentRow <= $rowNum; $currentRow = $currentRow + 1) {
            $data = array();
            $date_of_log = $currentSheet->getCellByColumnAndRow(0, $currentRow)->getValue(); //日期
            $data['date_of_log'] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($date_of_log) - 28800); //时间的转换
            $activity_name = $currentSheet->getCellByColumnAndRow(3, $currentRow)->getValue(); //活动名
            $data['activity_id'] = $this->getActivityIdFromName($activity_name);
            $activity_data = $activityMap[$data['activity_id']];
            $data['consumer_key'] = $appMap[$activity_data['app_id']]['consumer_key'];
            $data['app_id'] = $activity_data['app_id'];
            $data['channel_id'] = $activity_data['channel_id'];
            $data['activity_gid'] = $activity_data['group_id'];
            $data['channel_group_id'] = $channelMap[$activity_data['channel_id']]['group_id']; //渠道组
            $data['android_channel_no'] = $activityGroupMap[$activity_data['group_id']]['channel_no'];
            $data['android_channel_group_id'] = $androidChannelMap[$data['android_channel_no']]['group_id'];
            $data['supplier'] = trim($currentSheet->getCellByColumnAndRow(4, $currentRow)->getValue()); //供应商
            $data['account'] = trim(strip_tags($currentSheet->getCellByColumnAndRow(5, $currentRow)->getValue())); //投放账户
            $data['staff'] = $activity_data['operator']; //投放人员
            $data['account_consumption'] = $this->filterSymbol($currentSheet->getCellByColumnAndRow(7, $currentRow)->getValue()); //账户消耗金额
            $data['rebate'] = $this->filterSymbol($currentSheet->getCellByColumnAndRow(8, $currentRow)->getValue()); //返点（%）
            $real_consumption = round($data['account_consumption'] / (1 + $data['rebate']), 2);
            $data['real_consumption'] = $real_consumption; //实际消耗金额
            $data['deposit'] = $this->filterSymbol($currentSheet->getCellByColumnAndRow(10, $currentRow)->getValue()); //充值金额
            $data['impressions'] = $this->filterSymbol($currentSheet->getCellByColumnAndRow(11, $currentRow)->getValue()); //展示数
            $data['clicks'] = $this->filterSymbol($currentSheet->getCellByColumnAndRow(12, $currentRow)->getValue()); //点击数
            $key = $data['date_of_log'] . '_' . $data['activity_id'];
            if (array_key_exists($key, $totalData)) {
                $totalData[$key]['account_consumption'] += $data['account_consumption'];
                $totalData[$key]['real_consumption'] += $data['real_consumption'];
                $totalData[$key]['deposit'] += $data['deposit'];
                $totalData[$key]['impressions'] += $data['impressions'];
                $totalData[$key]['clicks'] += $data['clicks'];
            } else {
                $totalData[$key] = $data;
            }
        }
        foreach ($totalData as $value) {
            $id = MobgiSpm_Service_EntryModel::addDailyCost($value);
            if (!$id) {
                $this->output(-1, '第 ' . $currentRow . ' 行数据导入失败！');
            }
        }
    }

    /*
     * 根据活动名截取活动id
     */
    private function getActivityIdFromName($name) {
        $arr = explode('-N', $name);
        $count = count($arr);
        if ($count == 1) {
            return 0;
        }
        $id = $arr[$count - 1];
        if (is_numeric($id)) {
            return $id;
        } else {
            return 0;
        }
    }

    /*
     * 过滤特殊符号 ¥ , %
     */
    private function filterSymbol($string) {
        $string = str_replace("¥", "", $string);
        $string = str_replace(",", "", $string);
        $string = str_replace("%", "", $string);
        if ($string == "") {
            $string = 0;
        }
        return $string;
    }

    public function planAction() {
        $channelGroupList = MobgiSpm_Service_ChannelModel::getChannelGroup();
        $this->assign('channelGroupList', $channelGroupList);
    }

    public function staffPlanAction() {
        $staff = MobgiSpm_Service_ReportModel::getStaffMap();
        $this->assign('staff', $staff);

    }

    public function staffPlanAddAction() {
        $channelGroupList = MobgiSpm_Service_ChannelModel::getChannelGroup();
        $this->assign('channelGroupList', $channelGroupList);
    }

    public function planListAction() {
        $params = $this->getPost(array('page', 'limit', 'app_id', 'channel_group_id', 'sdate', 'edate'));
        $where = array(
            'app_id' => $params['app_id'],
            'channel_group_id' => $params['channel_group_id'],
            'start_date' => $params['sdate'],
            'end_date' => $params['edate'],
        );
        $where = $this->filterParams($where);
        $orderBy = array('id' => 'DESC');
        list($total, $planList) = MobgiSpm_Service_EntryModel::getPlanList($params['page'], $params['limit'], $where, $orderBy);
        $planList = MobgiSpm_Service_EntryModel::formatPlanList($planList);
        $result = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $planList,
        );
        exit(json_encode($result));
    }

    public function getStaffPlanListAction() {
        $params = $this->getPost(array('page', 'limit', 'app_id', 'staff', 'sdate', 'edate'));
        $where = array(
            'app_id' => $params['app_id'],
            'staff' => $params['staff'],
            'start_date' => $params['sdate'],
            'end_date' => $params['edate'],
        );
        $where = $this->filterParams($where);
        $params['limit'] = 10;
        $orderBy = array('id' => 'DESC');
        list($total, $planList) = MobgiSpm_Service_EntryModel::getStaffPlanList($params['page'], $params['limit'], $where, $orderBy);
        $planList = MobgiSpm_Service_EntryModel::formatPlanList($planList);
        $result = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $planList,
        );
        exit(json_encode($result));
    }

    public function exportDemoPlanAction() {
        error_reporting(E_ALL & ~E_NOTICE);
        $sdate = date('Y/m/01');
        $edate = date('Y/m/d', strtotime($sdate . ' +1 month -1 day'));

        $appWhere['dims'] = ['is_natural', 'app_id'];
        $appWhere['kpis'] = ['registers'];
        $appWhere['sdate'] = date('Y-m-d', strtotime(' -14 day'));
        $appWhere['edate'] = date('Y-m-d');
        $appWhere['is_natural'] = 0;
        $appWhere['order'] = ['registers' => 'desc'];
        $result = MobgiSpm_Service_ReportModel::getCommonData($appWhere);
        $top5 = [];
        foreach ($result as $key => $val) {
            if ($val['app_id'] > 0)
                $top5[$val['registers']] = $val['app_id'];
        }
        krsort($top5);
        $top5 = array_slice($top5, 0, 5);

        $appMap = MobgiSpm_Service_EntryModel::getAppNameMap();
        $xmlNameSuffix = '_' . $this->userInfo['user_name'] . '_' . $sdate . '_' . $edate;
        $list = [];
        foreach ($appMap as $name => $item) {
            if (in_array($item['app_id'], $top5)) {
                $list[] = [
                    'start_date' => $sdate,
                    'end_date' => $edate,
                    'app_name' => $name,
                    'staff' => $this->userInfo['user_name'],
                    'daily_consumption' => '1000',
                    'daily_amount' => '100',
                ];
            }
        }
        Yaf_loader::import('Util/PHPExcel/PHPExcel.php');
        Yaf_loader::import('Util/PHPExcel/PHPExcel/IOFactory.php');
        $objPHPExcel = new PHPExcel();
        /*以下是一些设置 ，什么作者  标题啊之类的*/
        $objPHPExcel->getProperties()->setCreator('mobgi')->setLastModifiedBy('mobgi')->setTitle('数据EXCEL导出')->setSubject('数据EXCEL导出')->setDescription('广告渠道报表')->setKeywords('excel')->setCategory('result file');
        /*以下就是对处理Excel里的数据，横着取数据*/
        $allField = array(
            'start_date' => '开始日期',
            'end_date' => '结束日期',
            'app_name' => '产品',
            'staff' => '投放师',
            'daily_consumption' => '日均消耗',
            'daily_amount' => '日均量',
        );
        $num = 1;
        $char = 'A';
        $activeSheet = $objPHPExcel->setActiveSheetIndex(0);
        foreach ($allField as $fieldKey => $fieldVal) {
            $activeSheet->setCellValue($char . $num, $fieldVal);
            $char++;
        }
        foreach ($list as $dataKey => $dataVal) {
            $num++;
            $char = 'A';
            foreach ($allField as $fieldKey => $fieldVal) {
                $activeSheet->setCellValue($char . $num, $dataVal[$fieldKey]);
                if (in_array($fieldKey, ['start_date', 'end_date'])) {
                    $activeSheet->getStyle($char . $num)->getNumberFormat()->setFormatCode('yyyy/mm/dd');
                }
                $char++;
            }
        }
        // 开始组合头
        $activeSheet->setTitle('Staff');
        foreach (range('A', 'F') as $columnID) {
            $activeSheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . '投放师投放计划' . $xmlNameSuffix . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function exportPlanAction() {
        error_reporting(E_ALL & ~E_NOTICE);
        $params = $this->getInput(array('page', 'limit', 'app_id', 'channel_group_id', 'sdate', 'edate'));
        $where = array(
            'app_id' => $params['app_id'],
            'channel_group_id' => $params['channel_group_id'],
            'start_date' => $params['sdate'],
            'end_date' => $params['edate'],
        );
        if (!empty($params['sdate']) && !empty($params['edate'])) {
            $xmlNameSuffix = '_' . $params['sdate'] . '_' . $params['edate'];
        } else {
            $xmlNameSuffix = '';
        }
        $where = $this->filterParams($where);
        $orderBy = array('id' => 'DESC');
        $planDetail = MobgiSpm_Service_EntryModel::getPlanByParams($where, $orderBy);
        $planDetail = MobgiSpm_Service_EntryModel::formatPlanList($planDetail);
        Yaf_loader::import('Util/PHPExcel/PHPExcel.php');
        Yaf_loader::import('Util/PHPExcel/PHPExcel/IOFactory.php');
        $objPHPExcel = new PHPExcel();
        /*以下是一些设置 ，什么作者  标题啊之类的*/
        $objPHPExcel->getProperties()->setCreator('mobgi')->setLastModifiedBy('mobgi')->setTitle('数据EXCEL导出')->setSubject('数据EXCEL导出')->setDescription('广告渠道报表')->setKeywords('excel')->setCategory('result file');
        /*以下就是对处理Excel里的数据，横着取数据*/
        $allField = array(
            'start_date' => '开始日期',
            'end_date' => '结束日期',
            'app_name' => '产品',
            'channel_group_name' => '渠道组',
            'daily_consumption' => '日均消耗金额',
            'daily_amount' => '日均量',
            'daily_cost' => '日均成本',
        );
        $num = 1;
        $char = 'A';
        foreach ($allField as $fieldKey => $fieldVal) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char . $num, $fieldVal);
            $char++;
        }
        foreach ($planDetail as $dataKey => $dataVal) {
            $num++;
            $char = 'A';
            foreach ($allField as $fieldKey => $fieldVal) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char . $num, $dataVal[$fieldKey]);
                $char++;
            }
        }
        // 开始组合头
        $xmlName = '广告计划报表' . $xmlNameSuffix;
        $objPHPExcel->getActiveSheet()->setTitle('User');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $xmlName . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }


    public function exportStaffPlanAction() {
        error_reporting(E_ALL & ~E_NOTICE);
        $params = $this->getInput(array('page', 'limit', 'app_id', 'channel_group_id', 'sdate', 'edate'));
        $where = array(
            'app_id' => $params['app_id'],
            'staff' => $params['staff'],
            'start_date' => $params['sdate'],
            'end_date' => $params['edate'],
        );
        if (!empty($params['sdate']) && !empty($params['edate'])) {
            $xmlNameSuffix = '_' . $params['sdate'] . '_' . $params['edate'];
        } else {
            $xmlNameSuffix = '';
        }
        $where = $this->filterParams($where);
        $orderBy = array('id' => 'DESC');
        $planDetail = MobgiSpm_Service_EntryModel::getStaffPlanByParams($where, $orderBy);
        $planDetail = MobgiSpm_Service_EntryModel::formatPlanList($planDetail);
        Yaf_loader::import('Util/PHPExcel/PHPExcel.php');
        Yaf_loader::import('Util/PHPExcel/PHPExcel/IOFactory.php');
        $objPHPExcel = new PHPExcel();
        /*以下是一些设置 ，什么作者  标题啊之类的*/
        $objPHPExcel->getProperties()->setCreator('mobgi')->setLastModifiedBy('mobgi')->setTitle('数据EXCEL导出')->setSubject('数据EXCEL导出')->setDescription('广告渠道报表')->setKeywords('excel')->setCategory('result file');
        /*以下就是对处理Excel里的数据，横着取数据*/
        $allField = array(
            'start_date' => '开始日期',
            'end_date' => '结束日期',
            'app_name' => '产品',
            'staff' => '投放师',
            'daily_consumption' => '预算',
            'daily_amount' => '日均量',
            'daily_cost' => '日均成本',
        );
        $num = 1;
        $char = 'A';
        foreach ($allField as $fieldKey => $fieldVal) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char . $num, $fieldVal);
            $char++;
        }
        foreach ($planDetail as $dataKey => $dataVal) {
            $num++;
            $char = 'A';
            foreach ($allField as $fieldKey => $fieldVal) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char . $num, $dataVal[$fieldKey]);
                $char++;
            }
        }
        // 开始组合头
        $xmlName = '广告计划报表' . $xmlNameSuffix;
        $objPHPExcel->getActiveSheet()->setTitle('User');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $xmlName . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }


    public function planBatchDelAction() {
        $idArr = $this->getInput('id_arr');
        if (empty($idArr)) {
            $this->output(-1, '请选择要删除的项');
        }
        Common_Service_Base::beginTransaction('mobgiSpm');
        $result = MobgiSpm_Service_EntryModel::batchDelPlan($idArr);
        if ($result) {
            Common_Service_Base::commit();
            $this->output(0, '删除成功');
        } else {
            Common_Service_Base::rollBack();
            $this->output(-1, '删除失败');
        }
    }

    public function delStaffPlanAction() {
        $idArr = $this->getInput('id_arr');
        if (empty($idArr)) {
            $this->output(-1, '请选择要删除的项');
        }
        Common_Service_Base::beginTransaction('mobgiSpm');
        $result = MobgiSpm_Service_EntryModel::delStaffPlan($idArr);
        if ($result) {
            Common_Service_Base::commit();
            $this->output(0, '删除成功');
        } else {
            Common_Service_Base::rollBack();
            $this->output(-1, '删除失败');
        }

    }

    public function editPlanAction() {
        $id = intval($this->getInput('id'));
        $entryPlan = MobgiSpm_Service_EntryModel::getEntryPlanById($id);
        if ($entryPlan) {
            $appData = MobgiSpm_Service_EntryModel::getAppById($entryPlan['app_id']);
            $entryPlan['app_name'] = $appData['app_name'];
            $channelGroupData = MobgiSpm_Service_EntryModel::getChannelGroupById($entryPlan['channel_group_id']);
            $entryPlan['channel_group_name'] = $channelGroupData['name'];
        }
        $this->assign('entryPlan', $entryPlan);
    }

    public function editStaffPlanAction() {
        $id = intval($this->getInput('id'));
        $staffPlan = MobgiSpm_Service_EntryModel::getStaffPlanById($id);
        if ($staffPlan) {
            $appData = MobgiSpm_Service_EntryModel::getAppById($staffPlan['app_id']);
            $staffPlan['app_name'] = $appData['app_name'];
        }
        $this->assign('staffPlan', $staffPlan);
    }


    public function editPlanPostAction() {
        $params = $this->getPost(array(
            'id',
            'app_id',
            'channel_group_id',
            'start_date',
            'end_date',
            'daily_consumption',
            'daily_amount',
            'daily_cost'
        ));
        $id = intval($params['id']);
        $item = MobgiSpm_Service_EntryModel::getDeliveryPlanById($id);
        if (empty($item)) {
            $this->output(-1, '记录不存在');
        } else {
            $item['start_date'] = $params['start_date'];
            $item['end_date'] = $params['end_date'];
            $item['daily_consumption'] = $params['daily_consumption'];
            $item['daily_amount'] = $params['daily_amount'];
            Common_Service_Base::beginTransaction();
            $updateRes = MobgiSpm_Service_EntryModel::updateEntryPlan($item, array('id' => $id));
            if ($updateRes) {
                MobgiSpm_Service_EntryModel::delDeliveryPlanDay($id);
                $res = MobgiSpm_Service_EntryModel::addDeliveryPlanDay($id, $item);
            }
            if ($res) {
                Common_Service_Base::commit();
            } else {
                Common_Service_Base::rollBack();
            }
            $this->output(0, '修改成功');
        }


    }

    private function checkEditPlanParams($params, $id) {
        $data['start_date'] = $params['start_date'];
        $data['end_date'] = $params['end_date'];
        $data['daily_consumption'] = floatval($params['daily_consumption']);
        $data['daily_amount'] = intval($params['daily_amount']);
        $data['daily_cost'] = floatval($params['daily_cost']);
        if (empty($data['start_date'])) {
            $this->output(-1, '请输入开始日期');
        }
        if (empty($data['end_date'])) {
            $this->output(-1, '请输入结束日期');
        }
        $resutl = MobgiSpm_Service_EntryModel::getPlanDataByParams($params, $id);
        if ($resutl) {
            $this->output(-1, '该记录已经存在');
        }
        return $data;
    }


    private function checkEditStaffPlanParams($params, $id) {
        $data['start_date'] = $params['start_date'];
        $data['end_date'] = $params['end_date'];
        $data['daily_consumption'] = floatval($params['daily_consumption']);
        $data['daily_amount'] = intval($params['daily_amount']);
        if (empty($data['start_date'])) {
            $this->output(-1, '请输入开始日期');
        }
        if (empty($data['end_date'])) {
            $this->output(-1, '请输入结束日期');
        }

        $resutl = MobgiSpm_Service_EntryModel::getStaffPlanDataById($params, $id);
        if ($resutl) {
            $this->output(-1, '该记录已经存在');
        }
        return $data;
    }

    public function freshPlanRecordAction() {
        $params = $this->getInput(array('max', 'app_id', 'channel_group_id', 'sdate', 'edate'));
        $where = [];
        if (isset($params['app_id'])) {
            $where['app_id'] = $params['app_id'];
        }
        $max = isset($params['max']) ? $params['max'] : 1;
        if (isset($params['channel_group_id'])) {
            $where['channel_group_id'] = $params['channel_group_id'];
        }
        if (isset($params['start_date'])) {
            $where['start_date'] = $params['start_date'];
        }
        if (isset($params['end_date'])) {
            $where['end_date'] = $params['end_date'];
        }
        $where = $this->filterParams($where);
        $list = MobgiSpm_Service_EntryModel::getSpmDao('MonitorDeliveryPlan')->getsBy($where);
        Common_Service_Base::beginTransaction('mobgiSpm');
        $i = 0;
        $err = '';
        foreach ($list as $item) {
            try {
                MobgiSpm_Service_EntryModel::delDeliveryPlanDay($item['id']);
                $res = MobgiSpm_Service_EntryModel::addDeliveryPlanDay($item['id'], $item);
                if ($res == false) {
                    Common_Service_Base::rollBack();
                    exit('id: ' . $item['id'] + 1 . ' 数据拆分录入失败！');
                }

            } catch (Exception $e) {
                $i++;
                $err .= 'id= ' . $item['id'] . ' 数据拆分录入失败！' . $e->getMessage() . '<br>';
                if ($max > 0 and $i > $max) {
                    break;
                }
            }
        }
        echo $err;
        if ($max > 0 and $i > $max) {
            Common_Service_Base::rollBack();
        } else {
            Common_Service_Base::commit();
        }
        exit('数据刷新成功OK');
    }

    public function freshStaffPlanRecordAction() {
        $params = $this->getInput(array('max', 'app_id', 'staff', 'sdate', 'edate'));
        $where = [];
        if (isset($params['app_id'])) {
            $where['app_id'] = $params['app_id'];
        }
        $max = isset($params['max']) ? $params['max'] : 1;
        if (isset($params['staff'])) {
            $where['staff'] = $params['staff'];
        }
        if (isset($params['start_date'])) {
            $where['start_date'] = $params['start_date'];
        }
        if (isset($params['end_date'])) {
            $where['end_date'] = $params['end_date'];
        }
        $where = $this->filterParams($where);
        $list = MobgiSpm_Service_EntryModel::getSpmDao('MonitorStaffPlan')->getsBy($where);
        Common_Service_Base::beginTransaction('mobgiSpm');
        $i = 0;
        $err = '';
        foreach ($list as $item) {
            try {
                MobgiSpm_Service_EntryModel::delStaffPlanDay($item['id']);
                $res = MobgiSpm_Service_EntryModel::addStaffPlanDay($item['id'], $item);
                if ($res == false) {
                    Common_Service_Base::rollBack();
                    exit('id: ' . $item['id'] + 1 . ' 数据拆分录入失败！');
                }

            } catch (Exception $e) {
                $i++;
                $err .= 'id= ' . $item['id'] . ' 数据拆分录入失败！' . $e->getMessage() . '<br>';
                if ($max > 0 and $i > $max) {
                    break;
                }
            }
        }
        echo $err;
        if ($max > 0 and $i > $max) {
            Common_Service_Base::rollBack();
        } else {
            Common_Service_Base::commit();
        }
        exit('数据刷新成功OK');
    }

    public function planAddAction() {

    }

    /*
     * 广告计划录入
     */
    public function planAddPostAction() {
        if (!file_exists($_FILES['file']['tmp_name'])) {
            $this->output(-1, '文件不存在！');
        } else {
            Yaf_loader::import("Util/PHPExcel/PHPExcel.php");
            Yaf_loader::import("Util/PHPExcel/PHPExcel/IOFactory.php");#引入
            $fileName = $_FILES['file']['tmp_name'];
            /**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/
            $PHPReader = new PHPExcel_Reader_Excel2007();
            if (!$PHPReader->canRead($fileName)) {
                $PHPReader = new PHPExcel_Reader_Excel5();
                if (!$PHPReader->canRead($fileName)) {
                    $this->output(-1, 'Excel文件未导入！');
                }
            }
            $PHPExcel = $PHPReader->load($fileName);
            $currentSheet = $PHPExcel->getSheet(0); /* * 读取excel文件中的第一个工作表 */
            $maxColumn = $currentSheet->getHighestColumn();
            /**取得最大的列号*/
            $columnNum = PHPExcel_Cell::columnIndexFromString($maxColumn); //字母列转换为数字列 如:AA变为27
            $rowNum = $currentSheet->getHighestRow(); /* * 取得一共有多少行 */
            $data = $this->checkPlanAdd($currentSheet, $columnNum, $rowNum);

            # 开始循环插入数据
            $this->planAddData($data);

            # 保存文件，核对导入信息时候用到
            $saveName = 'plan_' . $this->userInfo['user_name'] . '_' . date("YmdHis");
            $dataPath = Common::getConfig('siteConfig', 'dataPath');

            $savePath = sprintf('%s/%s/%s', $dataPath, 'spm_entry_data', date('Ym'));
            $uploader = new Util_Upload (array('maxSize' => 2048, 'allowFileType' => array('xls', 'xlsx')));
            $ret = $uploader->upload('file', $saveName, $savePath);
            $this->output(0, '导入成功');
        }
    }

    /*
     * 投放师计划录入
     */
    public function staffPlanAddPostAction() {
        if (!file_exists($_FILES['file']['tmp_name'])) {
            $this->output(-1, '文件不存在！');
        } else {
            Yaf_loader::import("Util/PHPExcel/PHPExcel.php");
            Yaf_loader::import("Util/PHPExcel/PHPExcel/IOFactory.php");#引入
            $fileName = $_FILES['file']['tmp_name'];
            /**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/
            $PHPReader = new PHPExcel_Reader_Excel2007();
            if (!$PHPReader->canRead($fileName)) {
                $PHPReader = new PHPExcel_Reader_Excel5();
                if (!$PHPReader->canRead($fileName)) {
                    $this->output(-1, 'Excel文件未导入！');
                }
            }
            $PHPExcel = $PHPReader->load($fileName);
            $currentSheet = $PHPExcel->getSheet(0); /* * 读取excel文件中的第一个工作表 */
            $maxColumn = $currentSheet->getHighestColumn();
            /**取得最大的列号*/
            $columnNum = PHPExcel_Cell::columnIndexFromString($maxColumn); //字母列转换为数字列 如:AA变为27
            $rowNum = $currentSheet->getHighestRow(); /* * 取得一共有多少行 */
            $data = $this->checkStaffPlanAdd($currentSheet, $columnNum, $rowNum);

            # 开始循环插入数据
            $this->staffPlanAddData($data);

            # 保存文件，核对导入信息时候用到
            $saveName = 'staff_plan_' . $this->userInfo['user_name'] . '_' . date("YmdHis");
            $dataPath = Common::getConfig('siteConfig', 'dataPath');

            $savePath = sprintf('%s/%s/%s', $dataPath, 'spm_entry_data', date('Ym'));
            $uploader = new Util_Upload (array('maxSize' => 2048, 'allowFileType' => array('xls', 'xlsx')));
            $ret = $uploader->upload('file', $saveName, $savePath);
            $this->output(0, '导入成功');
        }
    }


    private function checkPlanAdd($currentSheet, $columnNum, $rowNum) {
        if ($columnNum < 6) {
            $this->output(-1, 'Excel表格列数少于6，请检查！');
        }
        if ($rowNum < 2) {
            $this->output(-1, 'Excel表格行数少于2，请检查！');
        }
        $uniqueKeyArr = array();
        $totalKeyArr = array();
        $flag = true;
        $tip = array();
        $res = [];
        $tip['exist'] = $tip['double'] = '';
        $appMap = MobgiSpm_Service_EntryModel::getAppNameMap();
        $channelGroupMap = MobgiSpm_Service_EntryModel::getChannelGroupNameMap();
        for ($currentRow = 2; $currentRow <= $rowNum; $currentRow = $currentRow + 1) {
            /* 表格数据start */
            $data = array();
            $startDate = $currentSheet->getCellByColumnAndRow(0, $currentRow)->getValue(); //日期
            if ($currentSheet->getCellByColumnAndRow(0, $currentRow)->getDataType() == 'n') {
                $data['start_time'] = PHPExcel_Shared_Date::ExcelToPHP($startDate) - 28800;
            } else {
                $data['start_time'] = strtotime($startDate);
            }
            $data['start_date'] = date('Y-m-d', $data['start_time']); //时间的转换
            $endDate = $currentSheet->getCellByColumnAndRow(1, $currentRow)->getValue(); //日期
            if ($currentSheet->getCellByColumnAndRow(1, $currentRow)->getDataType() == 'n') {
                $data['end_time'] = PHPExcel_Shared_Date::ExcelToPHP($endDate) - 28800;
            } else if ($currentSheet->getCellByColumnAndRow(1, $currentRow)->getDataType() == 's') {
                $data['end_time'] = strtotime($endDate);
            }
            $data['end_date'] = date('Y-m-d', $data['end_time']); //时间的转换

            $data['app_name'] = $currentSheet->getCellByColumnAndRow(2, $currentRow)->getValue(); //产品名
            $data['channel_group_name'] = $currentSheet->getCellByColumnAndRow(3, $currentRow)->getValue(); //渠道组名
            $data['daily_consumption'] = $currentSheet->getCellByColumnAndRow(4, $currentRow)->getValue(); //预算
            $data['daily_amount'] = $currentSheet->getCellByColumnAndRow(5, $currentRow)->getValue(); //新增kpi
            $data['daily_cost'] = $data['daily_amount'] ? round($data['daily_consumption'] / $data['daily_amount'], 2) : 0;


            /* 表格数据end */
            $perKey = implode('_', $data);
            $totalKeyArr[] = $perKey;
            # 映射数据
            $data['app_data'] = $appMap[$data['app_name']];
            $data['app_id'] = $data['app_data']['app_id'];
            $data['consumer_key'] = $data['app_data']['consumer_key'];
            $data['channel_group_data'] = $channelGroupMap[$data['channel_group_name']];
            $data['channel_group_id'] = $data['channel_group_data']['id'];


            # 安卓应用特有校验参数
            list($tempFlag, $tip) = $this->checkEntryParams($currentRow, $data, $this->checkPlanAddKeys, $tip);
            if (!$tempFlag) {
                $flag = $tempFlag;
                continue;
            }
            $uniqueKeyArr[] = $perKey;
            # 特殊校验方式
            if (count($uniqueKeyArr) != count(array_unique($uniqueKeyArr))) { // 校验自身重复性
                $flag = false;
                array_pop($uniqueKeyArr);
                $doubleId = array_search($perKey, $totalKeyArr) + 2;
                $tip['double'] .= '第 ' . $currentRow . ' 行数据在表格中和第 ' . $doubleId . ' 行重复！<br>';
                continue;
            }
            if (MobgiSpm_Service_EntryModel::checkPlanByUniqueKey($data)) { // 校验数据库重复性
                $flag = false;
                $tip['exist'] .= '第 ' . $currentRow . ' 行数据已经存在！<br>';
                continue;
            }
            $res[] = $data;
        }

        if (!$flag) {
            $totalTip = implode('', $tip);
            $this->output(-1, $totalTip);
        } else {
            return $res;
        }
    }

    private function planAddData($data) {// 全部数据校验通过，开始导入
        Common_Service_Base::beginTransaction();
        foreach ($data as $line => $item) {
            $id = MobgiSpm_Service_EntryModel::addDailyPlan($item);
            if (!$id) {
                Common_Service_Base::rollBack();
                $this->output(-1, '第 ' . $line . ' 行数据导入失败！');
            } else {
                try {
                    $res = MobgiSpm_Service_EntryModel::addDeliveryPlanDay($id, $item);
                    if ($res == false) {
                        Common_Service_Base::rollBack();
                        $this->output(-1, '第 ' . $line + 1 . ' 行数据拆分录入失败！' . json_encode($item));
                    }
                } catch (Exception $e) {
                    Common_Service_Base::rollBack();
                    $this->output(-1, '第 ' . $line + 1 . ' 行数据拆分录入失败！' . $e->getMessage());
                }
            }
        }
        Common_Service_Base::commit();
    }


    private function checkStaffPlanAdd($currentSheet, $columnNum, $rowNum) {
        if ($columnNum < 6) {
            $this->output(-1, 'Excel表格列数少于6，请检查！');
        }
        if ($rowNum < 2) {
            $this->output(-1, 'Excel表格行数少于2，请检查！');
        }
        $uniqueKeyArr = array();
        $totalKeyArr = array();
        $flag = true;
        $tip = array();
        $res = [];
        $tip['exist'] = $tip['double'] = '';
        $appMap = MobgiSpm_Service_EntryModel::getAppNameMap();
        $staffMap = MobgiSpm_Service_EntryModel::getStaffMap();

        for ($currentRow = 2; $currentRow <= $rowNum; $currentRow = $currentRow + 1) {
            /* 表格数据start */
            $data = array();
            $startDate = $currentSheet->getCellByColumnAndRow(0, $currentRow)->getValue(); //日期
            if ($currentSheet->getCellByColumnAndRow(0, $currentRow)->getDataType() == 'n') {
                $data['start_time'] = PHPExcel_Shared_Date::ExcelToPHP($startDate) - 28800;
            } else {
                $data['start_time'] = strtotime($startDate);
            }
            $data['start_date'] = date('Y-m-d', $data['start_time']); //时间的转换
            $endDate = $currentSheet->getCellByColumnAndRow(1, $currentRow)->getValue(); //日期
            if ($currentSheet->getCellByColumnAndRow(1, $currentRow)->getDataType() == 'n') {
                $data['end_time'] = PHPExcel_Shared_Date::ExcelToPHP($endDate) - 28800;
            } else if ($currentSheet->getCellByColumnAndRow(1, $currentRow)->getDataType() == 's') {
                $data['end_time'] = strtotime($endDate);
            }
            $data['end_date'] = date('Y-m-d', $data['end_time']); //时间的转换

            $data['app_name'] = $currentSheet->getCellByColumnAndRow(2, $currentRow)->getValue(); //产品名
            $staff = $currentSheet->getCellByColumnAndRow(3, $currentRow)->getValue(); //投放师
            if (isset($staffMap[$staff])) {
                $data['staff'] = $staff;
            }

            $data['daily_consumption'] = $currentSheet->getCellByColumnAndRow(4, $currentRow)->getValue(); //预算
            $data['daily_amount'] = $currentSheet->getCellByColumnAndRow(5, $currentRow)->getValue(); //新增kpi

            /* 表格数据end */
            $perKey = implode('_', $data);
            $totalKeyArr[] = $perKey;
            # 映射数据
            if (isset($appMap[$data['app_name']])) {
                $data['app_id'] = $appMap[$data['app_name']]['app_id'];
                $data['consumer_key'] = $appMap[$data['app_name']]['consumer_key'];
            } else {
                $data['app_id'] = 0;
                $data['consumer_key'] = '';
            }

            $checkPlanAddKeys = [
                'start_time' => [['0', '日期格式错误！', '']],
                'end_time' => [['0', '日期格式错误！', '']],
                'start_date' => [['empty', '日期不能为空！', '']],
                'end_date' => [['empty', '日期不能为空！', '']],
                'app_id' => [['empty', '查无此应用！', '']],
                'consumer_key' => [['empty', '查无此应用！(%s)', 'app_name']],
                'staff' => [['isset', '查无此投放师！(%s)', 'staff']],
            ];
            # 安卓应用特有校验参数
            list($tempFlag, $tip) = $this->checkEntryParams($currentRow, $data, $checkPlanAddKeys, $tip);
            if (!$tempFlag) {
                $flag = $tempFlag;
                continue;
            }
            $uniqueKeyArr[] = $perKey;
            # 特殊校验方式
            if (count($uniqueKeyArr) != count(array_unique($uniqueKeyArr))) { // 校验自身重复性
                $flag = false;
                array_pop($uniqueKeyArr);
                $doubleId = array_search($perKey, $totalKeyArr) + 2;
                $tip['double'] .= '第 ' . $currentRow . ' 行数据在表格中和第 ' . $doubleId . ' 行重复！<br>';
                continue;
            }
            if (MobgiSpm_Service_EntryModel::checkStaffPlanByUniqueKey($data)) { // 校验数据库重复性
                $flag = false;
                $tip['exist'] .= '第 ' . $currentRow . ' 行数据已经存在！<br>';
                continue;
            }
            $res[] = $data;
        }
        if (!$flag) {
            $totalTip = implode('', $tip);
            $this->output(-1, $totalTip);
        } else {
            return $res;
        }
    }

    private function staffPlanAddData($data) {// 全部数据校验通过，开始导入
        Common_Service_Base::beginTransaction();
        foreach ($data as $line => $item) {
            $id = MobgiSpm_Service_EntryModel::addStaffPlan($item);
            if (empty($id)) {
                Common_Service_Base::rollBack();
                $this->output(-1, '第 ' . $line + 1 . ' 行数据导入失败！' . json_encode($item));
            } else {
                try {
                    $res = MobgiSpm_Service_EntryModel::addStaffPlanDay($id, $item);
                    if ($res == false) {
                        Common_Service_Base::rollBack();
                        $this->output(-1, '第 ' . $line + 1 . ' 行数据拆分录入失败！' . json_encode($item));
                    }
                } catch (Exception $e) {
                    Common_Service_Base::rollBack();
                    $this->output(-1, '第 ' . $line + 1 . ' 行数据拆分录入失败！' . $e->getMessage());

                }


            }
        }
        Common_Service_Base::commit();
    }

    public function saveStaffPlanAction() {
        $params = $this->getPost(array(
            'id',
            'start_date',
            'end_date',
            'daily_consumption',
            'daily_amount'
        ));
        $id = intval($params['id']);
        $item = MobgiSpm_Service_EntryModel::getStaffPlanDataById($id);
        if (empty($item)) {
            $this->output(-1, '记录不存在');
        } else {
            $item['start_date'] = $params['start_date'];
            $item['end_date'] = $params['end_date'];
            $item['daily_consumption'] = $params['daily_consumption'];
            $item['daily_amount'] = $params['daily_amount'];
            Common_Service_Base::beginTransaction();
            $updateRes = MobgiSpm_Service_EntryModel::updateStaffPlan($item, array('id' => $id));
            if ($updateRes) {
                MobgiSpm_Service_EntryModel::delStaffPlanDay($id);
                $res = MobgiSpm_Service_EntryModel::addStaffPlanDay($id, $item);
            }
            if ($res) {
                Common_Service_Base::commit();
            } else {
                Common_Service_Base::rollBack();
            }


            $this->output(0, '修改成功');
        }
    }

}