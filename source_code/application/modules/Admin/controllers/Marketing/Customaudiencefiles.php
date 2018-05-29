<?php
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/5/14
 * Time: 16:12
 */
if (!defined('BASE_PATH')) exit('Access Denied!');

class Marketing_CustomAudienceFilesController extends Admin_MarketingController{

    public $resourceName = 'custom_audience_files';

    /**
     * 添加人群数据文件（products/add）
     */
    public function addAction(){

        $info = $this->getInput(array('user_id_type','name','description'));
        $data = $this->checkAddParam($info);
        # 新增人群
        $result = $this->send($data, 'add', 'custom_audiences');
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] != 0){
            $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
        }
        $audienceId = $resultArr['data']['audience_id'];
        $fileName = explode('.', $_FILES['file']['name'])[0];
        $saveName = date('Ymd_His') . '_' . $fileName;
        $attachPath = Common::getConfig ( 'siteConfig', 'attachPath' );
        # 保存文件到服务器，待使用
        $filePath = sprintf ( '%s/%s/%s', 'marketing', 'audience_files', date ( 'Ym' ) );
        $savePath = sprintf ( '%s/%s', $attachPath, $filePath );
        $uploader = new Util_Upload ( array('maxSize'=>1048576,'allowFileType'=>array('txt','zip')) );
        $ret = $uploader->upload('file', $saveName, $savePath);
        if(!$ret){
            $this->output(-1, '上传失败');
        }
        $fileSource = realpath($ret['source']);
        $cfile = new \CURLFile($fileSource);

        $fileData = [
            'audience_id' => $audienceId,
            'user_id_type' => $data['user_id_type'],
            'file' => $cfile,
        ];
        $fileResult = $this->send($fileData, 'add', 'custom_audience_files', 'filepost');
        $fileResultArr = json_decode($fileResult, TRUE);
        if($fileResultArr['code'] != 0){
            # 删除刚刚创建的人群
            $delResult = $this->send(['audience_id' => $audienceId], 'delete', 'custom_audiences');
            $delResultArr = json_decode($delResult, TRUE);
            if($delResultArr['code'] != 0){
                $this->output($delResultArr['code'], '人群创建失败，并且删除失败：'.$delResultArr['message'], $delResultArr['data']);
            }
            $this->output($fileResultArr['code'], $fileResultArr['message'], $fileResultArr['data']);
        }
        $this->output(0, '创建成功');

    }


    /**
     * 检查添加标的物的参数
     * @param type $info
     * @return type
     */
    private function checkAddParam($info){
        if(!isset($this->marketingConfig['USER_ID_TYPE'][$info['user_id_type']])){
            $this->output(-1, '数据类型不符合');
        }
        $info['name'] = trim($info['name']);
        if(empty($info['name'])){
            $this->output(-1, '人群名称不能为空');
        }
        if(strlen($info['name']) > 32){
            $this->output(-1, '人群名称长度最大 32 字节');
        }
        if(empty($info['description'])){
            unset($info['description']);
        }elseif(strlen($info['description']) > 100){
            $this->output(-1, '人群描述长度最大 100 字节');
        }
        $info['type'] = 'CUSTOMER_FILE';
        $ext = strtolower(substr(strrchr($_FILES['file']['name'], '.'), 1));
        if(!in_array($ext, ['txt','zip'])){
            $this->output(-1, '只支持 txt、zip 文件');
        }
        return $info;
    }
}