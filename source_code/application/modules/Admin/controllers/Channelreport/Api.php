<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/1
 * Time: 17:06
 */
class Channelreport_ApiController extends Admin_BaseController{

    public $actions = [
        'getAppListUrl' => '/Admin/Channelreport_Api/getAppList',
        'getActivityListUrl' => '/Admin/Channelreport_Api/getActivityList',
        'getAndroidChannelListUrl' => '/Admin/Channelreport_Api/getAndroidChannelList',
        'getChannelReportUrl' => '/Admin/Channelreport_Api/getChannelReport',
        'exportChannelReportUrl' => '/Admin/Channelreport_Api/exportChannelReport',
        'getPackageReportUrl' => '/Admin/Channelreport_Api/getPackageReport',
        'exportPackageReportUrl' => '/Admin/Channelreport_Api/exportPackageReport',
    ];

    /**
     * 获取 传参 数组
     * @return array
     */
    private function getReportParams() {
        $arrParams = array();
        foreach ($_GET as $key => $val) {
            $arrParams[$key] = strpos($val, ',') !== false ? explode(',', $val) : $val;
        }
        foreach( ['app_id', 'activity_id', 'channel_no'] as $keyVal){
            if(!empty($arrParams[$keyVal]) && !is_array($arrParams[$keyVal])){
                $arrParams[$keyVal] = [$arrParams[$keyVal]];
            }
        }
        return $arrParams;
    }

    public function getAppListAction(){
        $userId = $this->userInfo['user_id'];
        if(empty($userId)){
            $this->output(-1, '您已经被系统踢出，请重新登录');
        }
        $advertiserDetail = MobgiSpm_Service_ToolsModel::getAdvertiserDetailByUserId($userId);
        if(empty($advertiserDetail)){
            $this->output(0, '获取成功', []);
        }
        $relateApp = json_decode($advertiserDetail['relate_app'], true);
        if(!empty($relateApp)){
            $relateApp = MobgiSpm_Service_DeliveryModel::getDeliveryAppsByParams( array('app_id'=>array('IN',$relateApp)) );
        }
        $this->output(0, '获取成功', $relateApp);
    }

    public function getActivityListAction(){
        $userId = $this->userInfo['user_id'];
        if(empty($userId)){
            $this->output(-1, '您已经被系统踢出，请重新登录');
        }
        $advertiserDetail = MobgiSpm_Service_ToolsModel::getAdvertiserDetailByUserId($userId);
        if(empty($advertiserDetail)){
            $this->output(0, '获取成功', []);
        }
        $relateActivity = json_decode($advertiserDetail['relate_activity'], true);
        if(!empty($relateActivity)){
            $relateActivity = MobgiSpm_Service_DeliveryModel::getDeliveryActivitysByParams( array('id'=>array('IN',$relateActivity)) );
        }
        $this->output(0, '获取成功', $relateActivity);
    }

    public function getAndroidChannelListAction(){
        $userId = $this->userInfo['user_id'];
        if(empty($userId)){
            $this->output(-1, '您已经被系统踢出，请重新登录');
        }
        $advertiserDetail = MobgiSpm_Service_ToolsModel::getAdvertiserDetailByUserId($userId);
        if(empty($advertiserDetail)){
            $this->output(0, '获取成功', []);
        }
        $relateAndroidChannel = json_decode($advertiserDetail['relate_android_channel'], true);
        if(!empty($relateAndroidChannel)){
            $relateAndroidChannel = MobgiSpm_Service_ChannelModel::getAndroidChannelFiledsByParams( array('channel_no'=>array('IN',$relateAndroidChannel)) );
        }
        $this->output(0, '获取成功', $relateAndroidChannel);
    }

    public function getChannelReportAction(){
        $userId = $this->userInfo['user_id'];
        if(empty($userId)){
            $this->output(-1, '您已经被系统踢出，请重新登录');
        }
        $advertiserDetail = MobgiSpm_Service_ToolsModel::getAdvertiserDetailByUserId($userId);
        $relateApp = json_decode($advertiserDetail['relate_app'], true);
        $relateActivity = json_decode($advertiserDetail['relate_activity'], true);

        $params = $this->getReportParams();
        $params['app_id'] = $_GET['app_id'];
        $params['activity_id'] = $_GET['activity_id'];
        $orderBy = empty($params['order']) ? array( 'date_of_log' => 'DESC' ) : array( $params['order'] => 'DESC' );
        $appIdArr = empty($params['app_id']) ? $relateApp : array_intersect($relateApp, $params['app_id']);
        $consumerKeyArr = MobgiSpm_Service_ChannelReportModel::getConsumerKeyList($appIdArr);
        $activityIdArr = empty($params['activity_id']) ? $relateActivity : array_intersect($relateActivity, $params['activity_id']);
        if(empty($consumerKeyArr) || empty($activityIdArr)){
            $reportDetail = array();
        }else{
            $where['consumer_key'] = array( 'IN', $consumerKeyArr);
            $where['activity_id'] = array( 'IN', $activityIdArr);
            $where['date_of_log'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));
            $reportDetail = MobgiSpm_Service_ChannelReportModel::getChannelReportByParams($where, $orderBy);
            if(!empty($reportDetail)){
                MobgiSpm_Service_ChannelReportModel::dimValueReplace($reportDetail, array('consumer_key', 'activity_id'),
                    array( 'consumer_key' => $relateApp, 'activity_id' => $relateActivity ));
                MobgiSpm_Service_ChannelReportModel::calRateFunnelForData($reportDetail);
                $data['total'] = MobgiSpm_Service_ChannelReportModel::totalCaculate($reportDetail,
                    array('clicks','effect_clicks','actives','callbacks','registers'));
            }
        }
        $data['table'] = $reportDetail;
        $this->output(0, '获取成功', $data);
    }

    public function exportChannelReportAction(){
        $userId = $this->userInfo['user_id'];
        $advertiserDetail = MobgiSpm_Service_ToolsModel::getAdvertiserDetailByUserId($userId);
        $relateApp = json_decode($advertiserDetail['relate_app'], true);
        $relateActivity = json_decode($advertiserDetail['relate_activity'], true);

        $params = $this->getReportParams();
        $orderBy = empty($params['order']) ? array( 'date_of_log' => 'DESC' ) : array( $params['order'] => 'DESC' );
        $appIdArr = empty($params['app_id']) ? $relateApp : array_intersect($relateApp, $params['app_id']);
        $consumerKeyArr = MobgiSpm_Service_ChannelReportModel::getConsumerKeyList($appIdArr);
        $activityIdArr = empty($params['activity_id']) ? $relateActivity : array_intersect($relateActivity, $params['activity_id']);
        if(empty($consumerKeyArr) || empty($activityIdArr)){
            $reportDetail = array();
        }else{
            $where['consumer_key'] = array( 'IN', $consumerKeyArr);
            $where['activity_id'] = array( 'IN', $activityIdArr);
            $where['date_of_log'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));
            $reportDetail = MobgiSpm_Service_ChannelReportModel::getChannelReportByParams($where, $orderBy);
            if(!empty($reportDetail)){
                MobgiSpm_Service_ChannelReportModel::dimValueReplace($reportDetail, array('consumer_key', 'activity_id'),
                    array( 'consumer_key' => $relateApp, 'activity_id' => $relateActivity ));
                MobgiSpm_Service_ChannelReportModel::calRateFunnelForData($reportDetail);
                $total = MobgiSpm_Service_ChannelReportModel::totalCaculate($reportDetail,
                    array('clicks','effect_clicks','actives','callbacks','registers'));
                $total['date_of_log'] = '汇总';
            }
        }
        array_push($reportDetail, $total);
        Yaf_loader::import('Util/PHPExcel/PHPExcel.php');
        Yaf_loader::import('Util/PHPExcel/PHPExcel/IOFactory.php');
        $objPHPExcel = new PHPExcel();
        /*以下是一些设置 ，什么作者  标题啊之类的*/
        $objPHPExcel->getProperties()->setCreator('mobgi')
            ->setLastModifiedBy('mobgi')
            ->setTitle('数据EXCEL导出')
            ->setSubject('数据EXCEL导出')
            ->setDescription('广告渠道报表')
            ->setKeywords('excel')
            ->setCategory('result file');
        /*以下就是对处理Excel里的数据，横着取数据*/
        $allField = array(
            'date_of_log' => '日期',
            'consumer_key' => '产品',
            'activity_id' => '投放活动',
            'clicks' => '点击数',
            'effect_clicks' => '排重点击数',
            'actives' => '激活数',
            'registers' => '注册数',
            'callbacks' => '回调数',
            'active_rate' => '激活率(%)',
        );
        $num = 1;
        $char = 'A';
        foreach($allField as $fieldKey => $fieldVal){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$num, $fieldVal);
            $char ++;
        }
        foreach($reportDetail as $dataKey => $dataVal){
            $num ++;
            $char = 'A';
            foreach($allField as $fieldKey => $fieldVal){
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$num, $dataVal[$fieldKey]);
                $char ++;
            }
        }
        // 开始组合头
        $xmlName = '广告渠道报表_' . $params['sdate'] . '_' . $params['edate'];
        $objPHPExcel->getActiveSheet()->setTitle('User');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$xmlName.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function getPackageReportAction(){
        $userId = $this->userInfo['user_id'];
        if(empty($userId)){
            $this->output(-1, '您已经被系统踢出，请重新登录');
        }
        $advertiserDetail = MobgiSpm_Service_ToolsModel::getAdvertiserDetailByUserId($userId);
        $relateAndroidChannel = json_decode($advertiserDetail['relate_android_channel'], true);

        $params = $this->getReportParams();
        $params['channel_no'] = $_GET['channel_no'];
        $orderBy = empty($params['order']) ? array( 'date_of_log' => 'DESC' ) : array( $params['order'] => 'DESC' );
        $channelNoArr = empty($params['channel_no']) ? $relateAndroidChannel : array_intersect($relateAndroidChannel, $params['channel_no']);
        if(empty($channelNoArr)){
            $reportDetail = array();
        }else{
            $where['channel_no'] = array( 'IN', $channelNoArr);
            $where['date_of_log'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));
            $reportDetail = MobgiSpm_Service_ChannelReportModel::getPackageReportByParams($where, $orderBy);
            if(!empty($reportDetail)){
                MobgiSpm_Service_ChannelReportModel::dimValueReplace($reportDetail, array('channel_no'),
                    array( 'channel_no' => $relateAndroidChannel ));
                $data['total'] = MobgiSpm_Service_ChannelReportModel::totalCaculate($reportDetail,
                    array( 'registers' ), false);
            }
        }
        $data['table'] = $reportDetail;
        $this->output(0, '获取成功', $data);
    }

    public function exportPackageReportAction(){
        $userId = $this->userInfo['user_id'];
        $advertiserDetail = MobgiSpm_Service_ToolsModel::getAdvertiserDetailByUserId($userId);
        $relateAndroidChannel = json_decode($advertiserDetail['relate_android_channel'], true);

        $params = $this->getReportParams();
        $orderBy = empty($params['order']) ? array( 'date_of_log' => 'DESC' ) : array( $params['order'] => 'DESC' );
        $channelNoArr = empty($params['channel_no']) ? $relateAndroidChannel : array_intersect($relateAndroidChannel, $params['channel_no']);
        if(empty($channelNoArr)){
            $reportDetail = array();
        }else{
            $where['channel_no'] = array( 'IN', $channelNoArr);
            $where['date_of_log'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));
            $reportDetail = MobgiSpm_Service_ChannelReportModel::getPackageReportByParams($where, $orderBy);
            if(!empty($reportDetail)){
                MobgiSpm_Service_ChannelReportModel::dimValueReplace($reportDetail, array('channel_no'),
                    array( 'channel_no' => $relateAndroidChannel ));
                $total = MobgiSpm_Service_ChannelReportModel::totalCaculate($reportDetail,
                    array( 'registers' ), false);
                $total['date_of_log'] = '汇总';
            }
        }
        array_push($reportDetail, $total);
        Yaf_loader::import('Util/PHPExcel/PHPExcel.php');
        Yaf_loader::import('Util/PHPExcel/PHPExcel/IOFactory.php');
        $objPHPExcel = new PHPExcel();
        /*以下是一些设置 ，什么作者  标题啊之类的*/
        $objPHPExcel->getProperties()->setCreator('mobgi')
            ->setLastModifiedBy('mobgi')
            ->setTitle('数据EXCEL导出')
            ->setSubject('数据EXCEL导出')
            ->setDescription('广告渠道报表')
            ->setKeywords('excel')
            ->setCategory('result file');
        /*以下就是对处理Excel里的数据，横着取数据*/
        $allField = array(
            'date_of_log' => '日期',
            'channel_no' => '安卓渠道包',
            'registers' => '新增数',
        );
        $num = 1;
        $char = 'A';
        foreach($allField as $fieldKey => $fieldVal){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$num, $fieldVal);
            $char ++;
        }
        foreach($reportDetail as $dataKey => $dataVal){
            $num ++;
            $char = 'A';
            foreach($allField as $fieldKey => $fieldVal){
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$num, $dataVal[$fieldKey]);
                $char ++;
            }
        }
        // 开始组合头
        $xmlName = '安卓渠道包报表_' . $params['sdate'] . '_' . $params['edate'];
        $objPHPExcel->getActiveSheet()->setTitle('User');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$xmlName.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}