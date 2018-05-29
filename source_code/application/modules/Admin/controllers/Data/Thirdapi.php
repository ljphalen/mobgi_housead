<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author matt.liu
 *
 */
class Data_ThirdapiController extends Admin_BaseController {

    public $actions = array(
        'adsListUrl' => '/Admin/Data_ThirdApi/config',
        'adsMonitorListUrl' => '/Admin/Data_ThirdApi/index',
        'manageLog' => '/Admin/Data_ThirdApi/manageLog',
        'importUrl' => '/Admin/Data_ThirdApi/import',
        'importCustomDataUrl' => '/Admin/Data_ThirdApi/CustomDataImport',
        'importLogUrl' => '/Admin/Data_ThirdApi/importLog',
        'dealImportDataUrl' => '/Admin/Data_ThirdApi/dealImpotData',
        'adjustUrl'=>'/Admin/Data_ThirdApi/adjust',
        'adjustLogUrl'=>'/Admin/Data_ThirdApi/adjustLog',
        'informUrl'=>'/Admin/Data_ThirdApi/inform',
        'editInformUrl'=>'/Admin/Data_ThirdApi/editinform',
        'addInformUrl'=>'/Admin/Data_ThirdApi/addinform',
        'delInformUrl'=>'/Admin/Data_ThirdApi/delinform'
    );

    public $rmbtodoller = 6.5;

    public $perpage = 20;

    public function adjustAction() {
        $id = intval($this->getInput('id'));
        if($_POST){
            $data = array(
                'ads_id'=>$this->getPost('ads_id'),
                'amount'=>$this->getPost('amount'),
                'currency'=>$this->getPost('currency_type'),
                'date'=>$this->getPost('month'),
                'remark'=>$this->getPost('remark'),
            );
           if(!empty($this->getPost('id'))){
               $pamas['id'] = $this->getPost('id');
               $result = MobgiData_Service_ThirdApiModel::getDao('ReportIncomeAdjust')->updateBy($data,$pamas);
           }else{
               $result = MobgiData_Service_ThirdApiModel::getDao('ReportIncomeAdjust')->insert($data);
           }
           if($result){
               MobgiData_Service_ThirdApiModel::updateAdjustAdincome($data);//调整收益
               echo 1;
           }else{
               echo 0;
           }
        }else{
            if(!empty($id)){
                $data = MobgiData_Service_ThirdApiModel::getDao('ReportIncomeAdjust')->get($id);
                $this->assign('data',$data);
            }
            $ads = MobgiData_Service_ThirdApiModel::getDao('ConfigAds')->getAllByFields('identifier');
            $this->assign('ads',$ads);
            $this->assign('ad_type',Common_Service_Config::AD_TYPE);
        }
    }


    public function informAction(){
        $informs = MobgiData_Service_ThirdApiModel::getDao('ReportInform')->getAll();
        $this->assign('inform',$informs);
    }


    public function editInformAction(){
        $id =  intval($this->getInput('id'));
        if($_POST){
            $params = array('id'=>$_POST['id']);
            $data = array(
                'title'=>$_POST['title'],
                'content'=>$_POST['content'],
                'days'=>date('Y-m-d',time()),
                'status'=>$_POST['status'],
                'level'=>$_POST['level'],
            );
            $result = MobgiData_Service_ThirdApiModel::getDao('ReportInform')->updateBy($data,$params);
            if($result) echo 1;else echo 0;
        }else{
            $params = array('id'=>$id);
            $data = MobgiData_Service_ThirdApiModel::getDao('ReportInform')->getBy($params);
            $this->assign('data',$data);
        }
    }


    public function addInformAction(){
        if($_POST) {
            $data = array(
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'days' => date('Y-m-d', time()),
                'status' => $_POST['status'],
                'level' => $_POST['level'],
            );
            $result = MobgiData_Service_ThirdApiModel::getDao('ReportInform')->insert($data);
            if($result) echo 1;else echo  0;
        }
    }

    public function delInformAction(){
        $id =  intval($this->getInput('id'));
        $result = MobgiData_Service_ThirdApiModel::getDao('ReportInform')->delete($id);
        if($result) echo 1;else echo  0;
    }

    public function adjustLogAction(){
        $page = intval($this->getInput('page'));
        $search = $this->getInput(['ads_id', 'sdate', 'edate']);
        if ($page < 1) $page = 1;
        $params = [];
        if ($search['ads_id'] != NULL ) {
            if ($search['ads_id'] != -1) {
                $params['ads_id'] = $search['ads_id'];
            }
        }
        $List = MobgiData_Service_ThirdApiModel::getDao('ReportIncomeAdjust')->getList(($page-1)*$this->perpage, $this->perpage*$page, $params, ['id' => "DESC"]);
        $total = MobgiData_Service_ThirdApiModel::getDao('ReportIncomeAdjust')->count($params);
        $adslist = MobgiData_Service_ThirdApiModel::getDao('ConfigAds')->getFields('id,identifier', ['status' => 1]);
        if (empty($sdate) && empty($edate)) {
            $sdate = date("Y-m-d");
            $edate = date("Y-m-d");
        }
        $url = $this->actions['adjustLogUrl'] . '/?' . http_build_query($search) . '&';
        $this->assign('currency_type',array(1=>'美元',2=>'人民币'));
        $this->assign('params', $params);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('adslist', $adslist);
        $this->assign('total', $total);
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('list', $List);
    }

    /**
     *
     * 三方API监控首页
     */
    public function indexAction() {
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search = $this->getInput(array('ads_id', 'searchtime'));
        if ($search['ads_id']) {
            $params['ads_id'] = array('LIKE', trim($search['ads_id']));
        }
        if (!empty($search['searchtime'])) {
            $params['date'] = $search['searchtime'];
        } else {
            $params['date'] = date('Y-m-d', time());
        }
        $adsMonitorList = MobgiData_Service_ThirdApiModel::getDao('ReportMonitorLog')->getList(($page-1)*$this->perpage, $this->perpage*$page, $params, array('status' => "ASC"));
        $total = MobgiData_Service_ThirdApiModel::getDao('ReportMonitorLog')->count($params);
        if (!empty($adsMonitorList)) {
            foreach ($adsMonitorList as $key => &$val) {
                $param['identifier'] = $val['ads_id'];
                $temp = MobgiData_Service_ThirdApiModel::getDao('ConfigAds')->getBy($param);
                $val['next_time'] = $temp['next_time'];
                $val['last_time'] = $temp['last_time'];
            }
        }
        $url = $this->actions['adsMonitorListUrl'] . '/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('adsList', $adsMonitorList);
    }


    /**
     *
     * 三方API配置
     */
    public function configAction() {
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search = $this->getInput(array('ads_id'));
        if ($search['ads_id']) {
            $params['identifier'] = array('LIKE',trim($search['ads_id']));
        }
        if ($search['ads_id']) {
            $adsList = MobgiData_Service_ThirdApiModel::getDao('ConfigAds')->getsBy($params);
        } else {
            $adsList = MobgiData_Service_ThirdApiModel::getDao('ConfigAds')->getList(($page-1)*$this->perpage, $this->perpage*$page, $params);
        }
        $total = MobgiData_Service_ThirdApiModel::getDao('ConfigAds')->count($params);
        $url = $this->actions['adsListUrl'] . '/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('adsList', $adsList);
    }

    /**
     *
     * 三方数据导入
     */
    public function importAction() {
        $data = array();
        $ads = MobgiData_Service_ThirdApiModel::getDao('ConfigAds')->getAllByFields('identifier');
        $this->assign('adtype', $this->adType);
        $this->assign('ads', $ads);
        $this->assign('list', $data);
    }



    /**
     *
     * 过滤重整数据
     */
    public function fitterData($data, $ads_id, $currency_type, $pos_type) {
        foreach ($data as $key => &$val) {#检测appkey是否存在
            if (!empty($val['B'])) {
                $result_appkey = MobgiData_Service_ThirdApiModel::getDao('ConfigApp')->getBy(array('app_key' => trim($val['B'])));
                if ($result_appkey == false) {
                    MobgiData_Service_ThirdApiModel::saveImportLog($ads_id, "AppKey NOT Found APPKEY=" . $val['B'], 0, $this->userInfo['user_name'], date("Y-m-d", time()));
                    continue;
                }
                //如果有查询出平台
                $val['G'] = $result_appkey['platform'];
                if ($currency_type == 2) { //收入调整
                    $val['E'] = round($val['E'] / 6.5, 2);
                }
                if ($pos_type == 2) {
                    $val['H'] = '';
                } else {
                    //有广告位先检测广告位是否存在
                    $posname = trim($val['F']);
                    $result_pos = MobgiData_Service_ThirdApiModel::getDao('ConfigAdsPos')->getBy(array('pos_name' => $posname, 'app_key' => $val['B']));
                    if ($result_pos == false) {
                        MobgiData_Service_ThirdApiModel::saveImportLog($ads_id, "POS_NAME NOT Found POSNAME=" . $posname."APPKEY=".$val['B'], 0, $this->userInfo['user_name'], date("Y-m-d", time()));
                        $val['H'] = '';
                    } else {
                        $val['H'] = $result_pos['pos_key'];
                    }
                }
            } else {
                unset($data[$key]);
            }
        }
        return $data;
    }

    /**
     *
     * 导入
     */
    public function dealImpotDataAction() {
        $ads_id = $this->getPost('ads_id');
        $ad_type = $this->getPost('ad_type');
        $import_type = $this->getPost('import_type');
        $currency_type = $this->getPost('currency_type');
        $pos_type = $this->getPost('pos_type');#是否存在广告位
        $tmp_file = $_FILES['upload_file']['tmp_name'];//临时文件
        #如果是1的话美元不转换，如果不是1的话就要除以6.5
        if (!file_exists($tmp_file)) {
            echo -1;
            die;
        } else {
            $data = $this->readExcel($tmp_file);#读取excel文件
            $saveData = $this->fitterData($data, $ads_id, $currency_type, $pos_type);#过滤重整数据
            if ($saveData == false) {
                echo -1;
                die;
            }
            //存入第三方数据
            if($import_type == 1){
                foreach ($saveData as $key => $val) {
                    $check_select = array(
                        'app_key' => trim($val['B']),
                        'ads_id' => $ads_id,
                        'ad_type' => $ad_type,
                        'days' => date("Y-m-d",strtotime($val['A'])),
                        'platform' => $val['G'],
                        'pos_key' => $val['H']
                    );
                    $check_result = MobgiData_Service_ThirdApiModel::getDao('ReportApi')->getBy($check_select);
                    if ($check_result == false) {
                        $add_data = array(
                            'app_key' => $val['B'],
                            'ads_id' => $ads_id,
                            'ad_type' => $ad_type,
                            'days' => date("Y-m-d",strtotime($val['A'])),
                            'hours' => 0,
                            'platform' => $val['G'],
                            'pos_key' => $val['H'],
                            'third_views' => intval($val['C']),
                            'third_clicks' => intval($val['D']),
                            'ad_income' => $val['E'],
                        );

                        if (MobgiData_Service_ThirdApiModel::getDao('ReportApi')->insert($add_data) == false) {
                            MobgiData_Service_ThirdApiModel::saveImportLog($ads_id, "新增出错" . json_encode($add_data), 0, $this->userInfo['user_name'], date("Y-m-d", time()));
                            echo -1;
                            die;
                        }
                    } else {
                        $update_Data = array(
                            'third_views' => intval($val['C']),
                            'third_clicks' => intval($val['D']),
                            'ad_income' => $val['E'],
                        );
                        $paras = array('id' => $check_result['id']);
                        if (MobgiData_Service_ThirdApiModel::getDao('ReportApi')->updateBy($update_Data, $paras) == false) {
                            MobgiData_Service_ThirdApiModel::saveImportLog($ads_id, "修改出错或没有作出修改" . json_encode($update_Data), 0, $this->userInfo['user_name'], date("Y-m-d", time()));
                            echo -1;
                            die;
                        }
                    }
                }
            } else{
            $share_type = $this->getPost('share_type');
            if($share_type == 2){
                //定制渠道分成后输入比例
                $share_ratio = $this->getPost('share_ratio');
            }
            //存入定制渠道数据
            foreach ($saveData as $key => $val) {
                //查出所属渠道的gid
                $where = array(
                    'group_id'=>0,
                    'ads_id'=>$ads_id,
                );
                $channelInfo = MobgiData_Service_MobgiModel::getDao('ConfigChannels')->getBy($where);
                $channel_gid = $channelInfo['channel_id'];
                if(empty($channel_gid)){
                    MobgiData_Service_ThirdApiModel::saveImportLog($ads_id, "该渠道没有渠道组ID" . json_encode($where), 0, $this->userInfo['user_name'], date("Y-m-d", time()));
                    echo -1;
                    die;
                }
                $check_select = array(
                    'app_key' => trim($val['B']),
                    'ads_id' => $ads_id,
                    'ad_type' => $ad_type,
                    'days' => date("Y-m-d",strtotime($val['A'])),
                    'channel_gid'=> $channel_gid,
                );
                $check_result = MobgiData_Service_ThirdApiModel::getDao('ReportFinance')->getBy($check_select);
                if ($check_result == false) {
                    $add_data = array(
                        'app_key' => $val['B'],
                        'platform' => $val['G'],
                        'ads_id' => $ads_id,
                        'ad_type' => $ad_type,
                        'days' => date("Y-m-d",strtotime($val['A'])),
                        'third_views' => intval($val['C']),
                        'third_clicks' => intval($val['D']),
                        'ad_income' => isset($share_ratio)?round(floatval($val['E'])/floatval($share_ratio),2):$val['E'],
                        'channel_gid'=>$channel_gid,
                        'is_custom'=>1
                    );
                    if (MobgiData_Service_ThirdApiModel::getDao('ReportFinance')->insert($add_data) == false) {
                        MobgiData_Service_ThirdApiModel::saveImportLog($ads_id, "新增出错" . json_encode($add_data), 0, $this->userInfo['user_name'], date("Y-m-d", time()));
                        echo -1;
                        die;
                    }
                } else {
                    $update_Data = array(
                        'third_views' => intval($val['C']),
                        'third_clicks' => intval($val['D']),
                        'ad_income' => $val['E'],
                    );
                    $paras = array('id' => $check_result['id']);
                    if (MobgiData_Service_ThirdApiModel::getDao('ReportFinance')->updateBy($update_Data, $paras) == false) {
                        MobgiData_Service_ThirdApiModel::saveImportLog($ads_id, "修改出错或没有作出修改" . json_encode($update_Data), 0, $this->userInfo['user_name'], date("Y-m-d", time()));
                        echo -1;
                        die;
                    }
                }
            }
        }
        echo 1;
        die;
      }
    }


    /**
     *
     * 导入操作日志展示
     */
    public function importlogAction() {
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search = $this->getInput(array('searchtime'));
        if (!empty($search['searchtime'])) {
            $params['createtime'] = $search['searchtime'];
        } else {
            $params['createtime'] = date("Y-m-d", time());
        }
        //list($total, $logList)= Report_Service_ImportLogModel::getList($page, $this->perpage, $params,array('status'=>"ASC"));
        $logList = MobgiData_Service_ThirdApiModel::getDao('ReportImportLog')->getList(($page-1)*$this->perpage, $this->perpage*$page, $params, array('status' => "ASC"));
        $total = MobgiData_Service_ThirdApiModel::getDao('ReportImportLog')->count($params);
        $url = $this->actions['importLogUrl'] . '/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('list', $logList);
    }


    private function readExcel($file) {
        Yaf_loader::import("Util/PHPExcel/PHPExcel.php");
        Yaf_loader::import("Util/PHPExcel/PHPExcel/IOFactory.php");#引入
        $reader = PHPExcel_IOFactory::createReader('Excel5');
        $PHPExcel = $reader->load($file);// 文档名称
        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数
        $data = array();
        for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
            for ($colIndex = 'A'; $colIndex <= $highestColumm; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                if ($colIndex == 'A') {
                    $cell = gmdate("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($sheet->getCellByColumnAndRow($colIndex, $rowIndex)->getValue()));
                }else if($colIndex == 'C' || $colIndex == 'D' || $colIndex == 'E'){
                    //数值必须是float类型
                    $cell = $sheet->getCell($addr)->getValue();
                    if(is_string($cell)){
                        $cell = $sheet->getCell($addr)->getCalculatedValue();
                    }
                } else {
                    $cell = $sheet->getCell($addr)->getValue();
                }
                $data[$rowIndex][$colIndex] = $cell;
            }
        }
        return $data;
    }



    #操作日志
    public function manageLogAction() {
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $search = $this->getInput(array('searchtime'));
        if (!empty($search['searchtime'])) {
            $params['createtime'] = $search['searchtime'];
        } else {
            $params['createtime'] = date("Y-m-d", time());
        }
        $logList = MobgiData_Service_ThirdApiModel::getDao('ReportManageLog')->getList(($page-1)*$this->perpage, $this->perpage*$page, $params, array('status' => "ASC"));
        $total = MobgiData_Service_ThirdApiModel::getDao('ReportManageLog')->count($params);
        $url = $this->actions['ManageLogUrl'] . '/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('list', $logList);
    }

    //更新重导设置
    public function update_reloadAction() {
        $type = trim(strtolower($_GET['type']));
        switch ($type) {
            case 'reload':
                $event = "修改是否允许重导";
                $infoArr = explode('|', $_GET['data']);
                $data = array(
                    'is_reload' => $infoArr[0],
                );
                $where = array(
                    'identifier' => $infoArr[1],
                );
                break;
            case 'msg':
                $event = "修改备注";
                $data = array(
                    'remark' => $_GET['remark'],
                );
                $where = array(
                    'identifier' => trim($_GET['identifier']),
                );
                break;
            case 'time':
                $event = "修改重导时间限制";
                $data = array(
                    'time_limit' => $_GET['time_limit'],
                );
                $where = array(
                    'identifier' => trim($_GET['identifier']),
                );
                break;
            case 'changenexttime':
                //查询是否允许重导
                $event = "重导操作";
                $msg = $_GET['identifier'];
                $where['identifier'] = $msg;
                //$check = Report_Service_ThirdAdsListModel::getsBy($where);
                $check = MobgiData_Service_ThirdApiModel::getDao('ConfigAds')->getsBy($where);
                if ($check[0]['is_reload'] != '1') {
                    die(json_encode(array("errorCode" => "0", "msg" => "该广告商不允许被重导！")));
                }
                $data = array(
                    'next_time' => $_GET['nexttime'],
                    'last_time' => date('Y-m-d H:i:s', strtotime($_GET['nexttime']) - 86400),
                );
                $where = array(
                    'identifier' => trim($_GET['identifier']),
                );
                break;
            case 'changenexttimedel':
                $event = "重导并清除数据操作";
                //重设重导日期并且清除数据
                $msg = $_GET['identifier'];
                $where['identifier'] = $msg;
                $check = MobgiData_Service_ThirdApiModel::getDao('ConfigAds')->getsBy($where);
                if ($check[0]['is_reload'] != '1') {
                    die(json_encode(array("errorCode" => "0", "msg" => "该广告商不允许被重导！")));
                }
                $data = array(
                    'next_time' => $_GET['nexttime'],
                    'last_time' => date('Y-m-d H:i:s', strtotime($_GET['nexttime']) - 86400),
                );
                #清除数据操作
                $delParams = array(
                    'ads_id' => trim($_GET['identifier']),
                    'days' => array('>', date("Y-m-d", strtotime($_GET['nexttime']) - 86400)),
                );
                MobgiData_Service_ThirdApiModel::getDao('ReportApi')->deleteBy($delParams);
                $where = array(
                    'identifier' => trim($_GET['identifier']),
                );
                break;
            //重设脚本周期
            case 'period':
                $event = "重设脚本周期操作";
                $data = array(
                    'period' => $_GET['period'],
                );
                $where = array(
                    'identifier' => trim($_GET['identifier']),
                );
                break;
            //脚本开关
            case 'status':
                $event = "脚本开关操作";
                $infoArr = explode('|', $_GET['data']);
                $data = array(
                    'status' => $infoArr[0],
                );
                $where = array(
                    'identifier' => $infoArr[1],
                );
                break;
        }
        $res = MobgiData_Service_ThirdApiModel::getDao('ConfigAds')->updateBy($data, $where);
        if ($res) {
            MobgiData_Service_ThirdApiModel::getDao('ReportManageLog')->insert(array(
                'identifier' => $where['identifier'],
                'event' => $event,
                'status' => 1,
                'createtime' => date('Y-m-d', time()),
                'username' => $this->userInfo['user_name']
            ));
            echo json_encode(array("errorCode" => "1", "msg" => "更新成功！"));
        } else {
            MobgiData_Service_ThirdApiModel::getDao('ReportManageLog')->insert(array(
                'identifier' => $where['identifier'],
                'event' => $event,
                'status' => 0,
                'createtime' => date('Y-m-d', time()),
                'username' => $this->userInfo['user_name']
            ));
            echo json_encode(array("errorCode" => "0", "msg" => "更新失败！"));
        }
    }
}
