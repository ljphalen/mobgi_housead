<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: matt.liu
 * Date: 2017/12/4
 * Time: 15:07
*/

class Spm_MaterialController extends Admin_BaseController{

    public function init() {
        parent::init();
        $module = $this->getTopModule();
        $this->assign('module', $module);
    }



    public $actions = [
        'materialIndexUrl' => '/Admin/Spm_Material/index',
    ];

    public static $materialType = [1=>'图片',2=>'视频',3=>'文字'];

    public $perpage = 20;

    public static $showAttachs = '/attachs';

    public function getAppId() {
        return isset($_COOKIE['app_id']) ? intval($_COOKIE['app_id']) : 0;
    }

    public static $mediaFileType = ['image/png'=>1,'image/jpeg'=>1,'image/jpg'=>1,'image/bmp'=>1,'image/gif'=>1];

    public static $searchInput = ['gid','type','mk_operator','label','sdate','edate','channelGid','file_size_max','file_size_min','height','width','selectLabels','video_max','video_min','name'];

    public static $channelConfig = [
        1=>array('width','height','min_time','max_time','size'),
        2=>array('width','height','size'),
        3=>array('max','min'),
    ];

    public static $ftpPath = 'ftp://wsfdupload.lxdns.com/';

    function indexAction(){
        //这里要做一个比较复杂的联合查询
        $params = $this->getInput(self::$searchInput);
        $page = intval($this->getInput('page'));
        if (empty($params['sdate']) && empty($params['edate'])) {
            $params['sdate'] = date('Y-m-d', strtotime('-60 days'));
            $params['edate'] = date("Y-m-d");
        }
        $whereResult = $this->buildSearchWhere($params);//这里获取检索条件
        $where = $whereResult['params'];
        $isOpenSearch = $whereResult['isOpenSearch'];
        if ($page < 1) $page = 1;
        if($isOpenSearch){
            //根据素材找素材组
            $materialList = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->getByOr($where, ['create_time' => "desc"]);
            $result = $this->getGroupsByMaterial($materialList,$page);
            //分页处理
            $total = $result['total'];
            $list = $result['data'];
        }else{
            //获取素材组列表
            $total = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialGroup')->oRcount($where);
            $groupList = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialGroup')->getOrList(($page-1)*$this->perpage, $this->perpage, $where, ['id' => "DESC"]);
            //根据素材组找到素材
            $list = $this->getMaterialByGroups($groupList);
        }
        //获取渠道选择
        $channelList = $this->getChannelSelect();
        //获取二级标签选择
        $labelList = $this->getLabelSelect();

        $level2Labels = MobgiSpm_Service_MaterialModel::get2LevelLabel();


        //获取制作人
        $userType = Admin_Service_UserModel::SPM_USER;
        $userList  = Admin_Service_UserModel::getsBy(array('user_type' => $userType));

        //获取素材
        $materialList = MobgiSpm_Service_MaterialModel::getMaterialList($this->getAppId());
        //获取素材组
        $materialGroups = MobgiSpm_Service_MaterialModel::getMaterialGroups($this->getAppId());

        $this->assign('ftpPath',self::$ftpPath);
        $this->assign('materialNames',$materialList);
        $this->assign('materialGroups',$materialGroups);
        $this->assign('level2Label',$level2Labels);
        $this->assign('labelList',$labelList);
        $this->assign('channels',$channelList);
        $url = $this->actions['materialIndexUrl'] . '/?' . http_build_query($params) . '&';
        $this->assign('total',$total);
        $this->assign('list',$list);
        $this->assign('params',$params);
        $this->assign('pager', $url);
        $this->assign('page',$page);
        $this->assign('userList',$userList);
        $this->assign('attachs',self::$showAttachs);
        $this->assign('materialType',self::$materialType);
    }

    function getMaterialByGroups($groupList){
        $list = array();
        foreach ($groupList as $key=>$val){
            $list[$val['id']]['groupName'] = $val['name'];
            $tmpWhere['gid'] = $val['id'];
            $tmpList = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->getsBy($tmpWhere,array('create_time'=>'desc'));
            $list[$val['id']]['items'] = $this->filterMaterialData($tmpList);
            $list[$val['id']]['count'] = count($list[$val['id']]['items']);
            $list[$val['id']]['lastUpdate'] = $list[$val['id']]['items'][0]['create_time'];
            $list[$val['id']]['mkOperator'] = $list[$val['id']]['items'][0]['mk_operator'];//制作者暂定最新的一个
//            if(empty($list[$val['id']]['items'])){
//                unset($list[$val['id']]);
//            }
        }
        return $list;
    }

    function filterMaterialData($list){
        if(!is_array($list)) return [];
        foreach ($list as $key=>&$val){
            $tmp = Admin_Service_UserModel::getBy(array('user_id' => $val['mk_operator']));
            $val['labelName'] = '';
            if(!empty($val['label'])){
                $labelArr = explode(',',$val['label']);
                $labelMap = MobgiSpm_Service_MaterialModel::getLabelMap(false);
                foreach ($labelArr as $keys=>$id){
                    $val['labelName'] .=$labelMap[$id].',';
                }
            }
            $val['mk_operator'] = $tmp['user_name'];
        }
        return $list;
    }

    function getGroupsByMaterial($list,$page){
        if(!is_array($list)) return [];
        $limit = $page*$this->perpage;
        $start = ($page-1)*$this->perpage;
        $groupList = array();
        $filterList = $this->filterMaterialData($list);
        foreach ($filterList as $key=>$val){
            $gid = intval($val['gid']);
            $tmp = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialGroup')->get($gid);
            if(!in_array($gid,$groupList)){
                $groupList[$gid]['groupName'] = $tmp['name'];
                $groupList[$gid]['lastUpdate'] = $val['create_time'];
                $groupList[$gid]['mkOperator'] = $val['mk_operator'];
                $groupList[$gid]['count'] ++;
            }else{
                $groupList[$gid]['count'] = 1;
            }
            $groupList[$gid]['items'][]= $val;
        }
        //分页处理
        $limitResult = array();
        foreach ($groupList as $key=>$val){
            if($start <= $limit){
                $limitResult[$key] = $groupList[$key];
            }else{
                break;
            }
        }
        $result['data'] = $limitResult;
        $result['total'] = count($groupList);
        return $result;
    }

    function getGroupMaterialsAction(){
        $gid = $this->getInput('gid');
        $page = intval($this->getInput('page'));
        $whereResult = $this->buildSearchWhere(json_decode(html_entity_decode($this->getInput('params')),true));//这里获取检索条件
        $where = $whereResult['params'];
        $where['gid'] = $gid;
        $tmpList =  MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->getOrList(($page-1)*$this->perpage, $this->perpage, $where, ['id' => "DESC"]);
        $total = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->oRcount($where);
        $list['list'] = $this->filterMaterialData($tmpList);
        $list['total'] = $total;
        $this->output(0,'',$list);
    }

    function getLabelsAction(){
        $labels = $this->getLabelSelect();
        if($labels){
            $this->output(0,'',$labels);
        }else{
            $this->output(-1,'',$labels);
        }
    }

    function buildSearchWhere($params){
        $where['app_id'] = $this->getAppId();
        $where['create_time'] = array(array('>=', $params['sdate']), array('<=',$params['edate']));
        $isOpenSearch = false;
        if(!empty($params['type'])){
            $where['type'] = $params['type'];
            $isOpenSearch = true;
        }
        if(!empty($params['mk_operator'])){
            $where['mk_operator'] = $params['mk_operator'];
            $isOpenSearch = true;
        }

        if(!empty($params['gid'])){
            $where['gid'] = $params['gid'];
            $isOpenSearch = true;
        }

        //素材名称检索
        if(!empty($params['name'])){
            $where['id'] = $params['name'];
            $isOpenSearch = true;
        }

        //标签检索
        if(!empty($params['label']) || !empty($params['selectLabels'][1])){
            if(!empty($params['label'])){
                $where['label'] = array('LIKE',$params['label']);
            }
            if(!empty($params['selectLabels'][1])){
                $where['label'] = array('LIKE',implode(',',$params['selectLabels']));
            }
            if(!empty($params['label']) && !empty($params['selectLabels'][1])){
                $where['label'] = array('LIKE',$params['label'].','.implode(',',$params['selectLabels']));
            }
            $isOpenSearch = true;
        }

        //渠道配置检索
        if(!empty($params['channelGid'])){
            foreach ($params['channelGid'] as $key=>$gid){
                //获取该渠道组相关配置
                if(!empty($params['type'])){
                    $sqlWhere['type'] = $params['type'];
                }
                if($gid == -1){
                    $conf = MobgiSpm_Service_ChannelModel::getSpmDao('MonitorChannelGroupSpconfig')->getAll();
                }else{
                    $sqlWhere['group_id'] = $gid;
                    $conf = MobgiSpm_Service_ChannelModel::getSpmDao('MonitorChannelGroupSpconfig')->getsBy($sqlWhere);
                }
                foreach ($conf as $kkk=>$val){
                    //读取配置信息
                    $tmp = json_decode($val['conf'],true);
                    unset($tmp['id']);
                    //做where条件替换
                    $itemWhere = array();
                    if($val['type'] == 1){
                        if($tmp['min_time'] != 0) $itemWhere['video_length'] = array('>=',$tmp['min_time']);
                        if($tmp['max_time'] != 0) $itemWhere['video_length'] = array('<=',$tmp['max_time']);
                        if($tmp['min_time'] != 0 && $tmp['max_time'] !=0) $itemWhere['video_length'] = array(array('>=',$tmp['min_time']),array('<=',$tmp['max_time']));
                        if($tmp['size'] != 0) $itemWhere['size'] = array('<=',$tmp['size']);
                        if($tmp['width'] != 0) $itemWhere['width'] = $tmp['width'];
                        if($tmp['height'] != 0) $itemWhere['heigh'] = $tmp['height'];
                    }
                    if($val['type'] == 2){
                        if($tmp['size'] != 0) $itemWhere['size'] = array('<=',$tmp['size']);
                        if($tmp['width'] != 0) $itemWhere['width'] = $tmp['width'];
                        if($tmp['height'] != 0) $itemWhere['heigh'] = $tmp['height'];
                    }
                    if($val['type'] == 3){
                        if($tmp['min'] != 0) $itemWhere['article_length'] = array('>=',$tmp['min']);
                        if($tmp['max'] != 0) $itemWhere['article_length'] = array('<=',$tmp['max']);
                        if($tmp['min'] != 0 && $tmp['max'] !=0) $itemWhere['article_length'] = array(array('>=',$tmp['min']),array('<=',$tmp['max']));
                    }
                    $where['or'][] = $itemWhere;
                }
            }
            //var_dump($where);
            //$where['channels'] = $configList;
            $isOpenSearch = true;
        }

        //额外高级检索
        if(!empty($params['file_size_max'])||!empty($params['file_size_min'])){
            if(!empty($params['file_size_max'])){
                $where['size'] = array('<=',$params['file_size_max']);
            }
            if(!empty($params['file_size_min'])){
                $where['size'] = array('>=',$params['file_size_min']);
            }
            if(!empty($params['file_size_min']) && !empty($params['file_size_max'])){
                $where['size'] = array(array('<=',$params['file_size_max']),array('>=',$params['file_size_min']));
            }
            $isOpenSearch = true;
        }

        if(!empty($params['video_max'])||!empty($params['video_min'])){
            if(!empty($params['video_max'])){
                $where['video_length'] = array('<=',$params['video_max']);
            }
            if(!empty($params['video_min'])){
                $where['video_length'] = array('>=',$params['video_min']);
            }
            if(!empty($params['video_min']) && !empty($params['video_max'])){
                $where['video_length'] = array(array('<=',$params['video_max']),array('>=',$params['video_min']));
            }
            $isOpenSearch = true;
        }

        if(!empty($params['width'])||!empty($params['height'])){
            if(!empty($params['width'])){
                $where['width'] = $params['width'];
            }
            if(!empty($params['height'])){
                $where['heigh'] = $params['height'];
            }
            $isOpenSearch = true;
        }

        return array('params'=>$where,'isOpenSearch'=>$isOpenSearch);
    }


    function getChannelSelect(){
        $channels = MobgiSpm_Service_ChannelModel::getSpmDao('MonitorChannelGroupSpconfig')->getAll();
        $channelList = array();
        foreach ($channels as $keys=>$vals){
            if(!in_array($vals['group_id'],$channelList)){
                $channelList[$vals['group_id']]= MobgiSpm_Service_ChannelModel::getSpmDao('MonitorChannelGroup')->get($vals['group_id']);
            }
        }
        return $channelList;
    }

    function getLabelSelect(){
        $fields = "id,title,pid";
        $allLabels = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->getAllByFields($fields);
        if(!empty($allLabels)){
            return json_encode($allLabels);
        }else{
            return '';
        }
    }

    function addMaterialAction(){
        $userType = Admin_Service_UserModel::SPM_USER;
        $userList  = Admin_Service_UserModel::getsBy(array('user_type' => $userType));
        $labelData = $this->getLabelData();
        $pLabelData = MobgiSpm_Service_MaterialModel::getLabelMap();
        //获取素材组
        $materialGroups = MobgiSpm_Service_MaterialModel::getMaterialGroups($this->getAppId());
        $this->assign('userInfo',$this->userInfo);
        $this->assign('materialGroups',$materialGroups);
        $this->assign('pLabelData',$pLabelData);
        $this->assign('labelData',$labelData);
        $this->assign('userList',$userList);
        $this->assign('attachs',self::$showAttachs);
        $this->assign('materialType',self::$materialType);
    }

    #存储文件
    function addPostAction(){
        error_reporting(E_ERROR);
        $groupId = $this->getInput('groupId');
        $groupName = MobgiSpm_Service_MaterialModel::getMaterialGroupName($groupId);
        $name = 'fileToUpload';
        $signature = md5_file($_FILES[$name]['tmp_name']);
        if(MobgiSpm_Service_MaterialModel::checkRepeatFile($this->getAppId(),$signature)){
            $this->output(-1,'上传失败，存在重复上传!','');
        }
        $dir = 'materia/'.$groupName;
        $uploadParams = array('maxSize'=>102400,'allowFileType'=>array('gif','jpeg','jpg','png','bmp','mp4','rar','zip','mp3','PSD','psd'));
        $result = Common::upload($name,$dir,$uploadParams,true,true,false);
        $result['size'] = round($_FILES[$name]['size']/1024/1024,2);
        $result['signature'] = $signature;
        if($result['code'] == 0){
            $this->output(0,'上传成功',$result);
        }else{
            $this->output($result['code'],'上传失败',$result);
        }
    }


    #base64转文件
    function getThumb($base64){
        header('Content-type:text/html;charset=utf-8');
        if(empty($base64)) return null;
        $base64_image_content = html_entity_decode(trim($base64));
        //正则匹配出图片的格式
        //var_dump(html_entity_decode($base64_image_content));
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            $type = $result[2];//图片后缀
            $attachPath = Common::getConfig ( 'siteConfig', 'attachPath' );
            $path = $attachPath."/materia/tmp/".date("Ymd",time()).'/';
            if (!file_exists($path)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($path, 0700);
            }
            $filename = time() . '_' . uniqid() . ".{$type}"; //文件名
            $new_file = $path .'/'.$filename;
            //写入操作
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                $savePath = "/materia/tmp/".date("Ymd",time()).'/'.$filename;
                return $savePath;  //返回文件名及路径
            } else {
                return false;
            }
        }else{
           return false;
        }
    }

    function saveDataAction(){
        $params = $this->getInput(array('count','type','mk_operator','label','file_name','img_width','img_height','file_size','other_file','content','name','picture','duration','article_length','gid','signature'));
        if($params['count'] <=0){
            $this->output(-1,'写入失败,参数不能为0');
        }
        $data = array();
        $appId = $this->getAppId();
        for($i=0;$i<$params['count'];++$i){
            $data[$i]=array(
                'type'=>$params['type'],
                'mk_operator'=>$params['mk_operator'][$i],
                'label'=>html_entity_decode($params['label'][$i]),
                'file_url'=>isset($params['file_name'][$i])?$params['file_name'][$i]:null,
                'width'=>$params['img_width'][$i],
                'heigh'=>$params['img_height'][$i],
                'size'=>$params['file_size'][$i],
                'other_file_url'=>$params['other_file'][$i],
                'content'=>$params['content'][$i],
                'name'=>isset($params['name'][$i])?$params['name'][$i]:null,
                'video_length'=>$params['duration'][$i],
                'create_time'=>date('Y-m-d',time()),
                'article_length'=>0,
                'thumb'=>$this->getThumb($params['picture'][$i]),
                'app_id'=>$appId,
                'gid'=>$params['gid'],
                'up_operator'=>$this->userInfo['user_id'],
                'signature'=>$params['signature'][$i]
            );
        }
        if(MobgiSpm_Service_MaterialModel::saveMaterialData($data)){
            $this->output(0,'上传成功');
        }else{
            $this->output(-1,'上传失败');
        }
    }




    #危险的函数shell_exec
    function getVideoOrientation($video_path) {
        $cmd = "/usr/local/ffmpeg/bin/ffprobe " . $video_path . " -show_streams 2>/dev/null";
        $result = shell_exec($cmd);
        $orientation = 0;
        if(strpos($result, 'TAG:rotate') !== FALSE) {
            $result = explode("\n", $result);
            foreach($result as $line) {
                if(strpos($line, 'TAG:rotate') !== FALSE) {
                    $stream_info = explode("=", $line);
                    $orientation = $stream_info[1];
                }
            }
        }
        return $orientation;
    }


    function delMaterialAction(){
        $info = $this->getInput(array('id'));
        $delInfo = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->get($info['id']);

        if(!empty($delInfo['file_url'])){
            $delPath = dirname($_SERVER['DOCUMENT_ROOT'],2).'/attachs'.$delInfo['file_url'];
            @unlink($delPath);
        }

        if(!empty($delInfo['other_file_url'])){
            $delPath = dirname($_SERVER['DOCUMENT_ROOT'],2).'/attachs'.$delInfo['other_file_url'];
            @unlink($delPath);
        }

        if(MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->delete($info['id'])){
            $this->output(0,'删除成功');
        }else{
            $this->output(1,'删除失败');
        }
    }

    function delAllMaterialAction(){
        $ids = $this->getInput(array('ids'));
        $where['id'] = array('IN',$ids['ids']);
        $fileList = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->getsBy($where);
        foreach ($fileList as $key=>$val){
            if(!empty($val['file_url'])){
                $delPath = dirname($_SERVER['DOCUMENT_ROOT'],2).'/attachs'.$val['file_url'];
                @unlink($delPath);
            }

            if(!empty($val['other_file_url'])){
                $delPath = dirname($_SERVER['DOCUMENT_ROOT'],2).'/attachs'.$val['other_file_url'];
                @unlink($delPath);
            }
        }
        if(MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->deletes('id',$ids['ids'])){
            $this->output(0,'删除成功');
        }else{
            $this->output(1,'删除失败');
        }
    }

    function delGroupMaterialAction(){
        $gid = $this->getInput('gid');
        //求出组的素材
        $where['gid'] = $gid;
        $list = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->getsBy($where);
        foreach ($list as $key=>$val){
            if(!empty($val['file_url'])){
                $delPath = dirname($_SERVER['DOCUMENT_ROOT'],2).'/attachs'.$val['file_url'];
                @unlink($delPath);
            }
            if(!empty($val['thumb'])){
                $delPath = dirname($_SERVER['DOCUMENT_ROOT'],2).'/attachs'.$val['thumb'];
                @unlink($delPath);
            }
            if(!empty($val['other_file_url'])){
                $delPath = dirname($_SERVER['DOCUMENT_ROOT'],2).'/attachs'.$val['other_file_url'];
                @unlink($delPath);
            }
        }
        if(MobgiSpm_Service_MaterialModel::getSpmDao('MaterialGroup')->delete($gid)){
            MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->deleteBy($where);
            $this->output(0,'删除成功');
        }else{
            $this->output(1,'删除失败');
        }
    }

    function editMaterialAction(){
        $id= $this->getGet('id');
        $info = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->get($id);
        $userType = Admin_Service_UserModel::SPM_USER;
        $userList  = Admin_Service_UserModel::getsBy(array('user_type' => $userType));
        $pLabelData = MobgiSpm_Service_MaterialModel::getLabelMap();
        $labelData = $this->getLabelData();
//        //获取素材组
//        $materialGroups = MobgiSpm_Service_MaterialModel::getMaterialGroups($this->getAppId());
//        $this->assign('materialGroups',$materialGroups);
        $this->assign('labelData',$labelData);
        $this->assign('pLabelData',$pLabelData);
        $this->assign('userList',$userList);
        $this->assign('attachs',self::$showAttachs);
        $this->assign('materialType',self::$materialType);
        $this->assign('info',$info);
    }

    function saveEditAction(){
        $params = $_POST;
        if(empty($params['id'])){
            $this->output(1,'修改失败');
        }
        $data=array(
            'mk_operator'=>$params['mk_operator'],
            'file_url'=>$params['file_name'],
            'width'=>$params['img_width'],
            'heigh'=>$params['img_height'],
            'size'=>$params['file_size'],
            'other_file_url'=>$params['other_file'],
            'content'=>$params['content'],
            'video_length'=>$params['video_length'],
            'thumb'=>empty($params['picture'])?$params['thumb']:$this->getThumb($params['picture']),
            'name'=>$params['name'],
        );
        $where = array('id'=>$params['id']);
        if(MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->updateBy($data,$where)){
            $this->output(0,'修改成功');
        }else{
            $this->output(1,'修改失败');
        }
    }


    function downloadAction(){
        $ids = $this->getInput(array('ids'));
        if(empty($ids)){
            $this->output(1,'参数错误');
        }
        $where['id'] = array('IN',$ids['ids']);
//        $where['type'] = array('!=',array(3));
        $fileList = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->getFields('id,file_url',$where);
        //var_dump($fileList);

        foreach ($fileList as $key=>&$val){
            $val = dirname($_SERVER['DOCUMENT_ROOT'],2).'/attachs'.$val;
        }
        $downloadPath = '/attachs/materia/'.time().'.zip';
        $zipPath = dirname($_SERVER['DOCUMENT_ROOT'],2).$downloadPath;
        $localName = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->getFields('file_url,name',$where);
        while(list($k,$v) =each($localName)){
            $k = basename($k);
            $localName[$k] = $v;
        }
        $destination = $this->create_zip($fileList,$zipPath,false,$localName);
        if($destination){
            $this->output(0,'',$downloadPath);
        }else{
            $this->output(1,'打包失败!');
        }
    }

    function downloadMaterialGroupAction(){
        $gid = $this->getInput('gid');
        if(empty($gid)){
            $this->output(1,'参数错误');
        }
        $where['gid'] = $gid;
        $fileList = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->getFields('id,file_url',$where);
        foreach ($fileList as $key=>&$val){
            $val = dirname($_SERVER['DOCUMENT_ROOT'],2).'/attachs'.$val;
        }
        $downloadPath = '/attachs/materia/'.time().'.zip';
        $zipPath = dirname($_SERVER['DOCUMENT_ROOT'],2).$downloadPath;
        $localName = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->getFields('file_url,name',$where);
        while(list($k,$v) =each($localName)){
            $k = basename($k);
            $localName[$k] = $v;
        }
        $destination = $this->create_zip($fileList,$zipPath,false,$localName);
        if($destination){
            $this->output(0,'',$downloadPath);
        }else{
            $this->output(1,'打包失败!');
        }
    }

    function create_zip($files = array(),$destination = '',$overwrite = false,$locaName=array()) {
        //if the zip file already exists and overwrite is false, return false
        if(file_exists($destination) && !$overwrite) { return false; }
        //vars
        $valid_files = array();
        //if files were passed in...
        if(is_array($files)) {
            //cycle through each file
            foreach($files as $file) {
                //make sure the file exists
                if(file_exists($file)) {
                    $valid_files[] = $file;
                }
            }
        }

        //if we have good files...
        if(count($valid_files)) {
            //create the archive
            $zip = new ZipArchive();
            if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
                return false;
            }
            //add the files
            foreach($valid_files as $file) {
                if(!empty($locaName)){
                    $savefile = $locaName[basename($file)];
                    $zip->addFile($file,$savefile);
                }else{
                    $savefile = basename($file);
                    $zip->addFile($file,$savefile);
                }
            }
            //debug
            //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
            //close the zip -- done!
            $zip->close();
            //check to make sure the file exists
            return file_exists($destination);
        }
        else
        {
            return false;
        }
    }


    function labelManageAction(){
        $label = $this->getInput('label');
        if(empty($label)){
            $where = array('pid'=>0);
            $list = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->getsBy($where);
            foreach ($list as $key=>&$val){
                $where2 = array(
                    'pid'=>$val['id'],
                );
                $val['level2'] = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->getsBy($where2);
            }
        }else{
            $where = array('pid'=>0,'title'=>array('LIKE',$label));
            $list = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->getsBy($where);
            foreach ($list as $key=>&$val){
                $where2 = array(
                    'pid'=>$val['id'],
                );
                $val['level2'] = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->getsBy($where2);
            }
            $this->assign('label',$label);
        }
        $this->assign('list',$list);
    }

    function addLabelAction(){
        if($this->isPost()){
            $info = $this->getInput(array('id','title','pid','type'));
            $flag = true;
            if($info['type'] == 'edit'){
                $where['id'] = $info['id'];
                $updateData = array(
                    'title'=>$info['title'],
                    'pid'=>$info['pid'],
                );
                if(!MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->updateBy($updateData,$where)) $flag = false;
            }elseif($info['type'] == 'add'){
                $insertData = array(
                    'title'=>$info['title'],
                    'pid'=>$info['pid'],
                    'create_time'=>date("Y-m-d"),
                );
                if(!MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->insert($insertData)) $flag=false;
            }elseif($info['type'] == 'del'){
                if(intval($info['pid']) == 0){
                    //删除二级标签
                    $where = array(
                        'pid'=>$info['id'],
                    );
                    if(MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->delete($info['id'])){
                       MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->deleteBy($where);
                    }else{
                       $flag = false;
                    }
                }else{
                    if(!MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->delete($info['id'])) $flag = false;
                }
            }
            if($flag){
                if($info['type'] == 'add'){
                    $lastId = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->getLastInsertId();
                }else{
                    $lastId = 0;
                }
                $this->output(0,'操作成功!',$lastId);
            }else{
                $this->output(-1,'操作失敗!');
            }
        }else{
            $labels = array();
            $where['pid'] = 0;
            $level1Label = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->getsBy($where);
            foreach ($level1Label as $key=>$val){
                $labels[$key] = $val;
                $tmp = array('pid'=>$val['id']);
                $labels[$key]['level2'] = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->getsBy($tmp);
            }
            $this->assign('labels',json_encode($labels));
        }
    }

    //废弃
//    function delGroupAction(){
//        $id = $this->getInput('id');
//        if(MobgiSpm_Service_MaterialModel::getSpmDao('MaterialGroup')->delete($id)){
//            $this->output(0,'删除成功!');
//        }else{
//            $this->output(-1,'删除失败!');
//        }
//    }

    function addLevel2LabelAction(){
       $info=$this->getInput(array('pid','title'));
        $data = array(
            'title'=>$info['title'],
            'pid'=>$info['pid'],
            'create_time'=>date("Y-m-d"),
        );
        if($this->filterLabel($info['title'],$info['pid'])){
            $this->output(-1,'存在重复标签!');
        }
        if(MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->insert($data)){
            $lastId = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->getLastInsertId();
            $jsonData = array(
                'title'=>$info['title'],
                'id'=>$lastId,
                'pid'=>$info['pid']
            );
            $this->output(0,'添加成功!',$jsonData);
        }else{
            $this->output(-1,'添加失敗!');
        }
    }

    function filterLabel($title,$pid){
        $where['pid'] = $pid;
        $where['title'] = $title;
        if(MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->getBy($where)){
            return true;
        }else{
            return false;
        }
    }

    function getLabelData(){
        $list = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialLabels')->getAll();
        $pidMap = MobgiSpm_Service_MaterialModel::getLabelMap();
        $result = array();
        foreach ($list as $key=>$val){
            if($val['pid']!=0){
                $tmp = array(
                    'id'=>$val['id'],
                    'disabled'=>false,
                    'groupName'=>$pidMap[$val['pid']],
                    'groupId'=>$val['pid'],
                    'selected'=>false,
                    'name'=>$val['title'],
                );
                $result[] = $tmp;
            }
        }
       return json_encode($result);
    }

    //素材组
    function GroupAction(){
        $name = $this->getInput('name');
        if(!empty($name)){
            $where = array(
                'name'=>$name,
                'app_id'=>$this->getAppId()
            );
        }else{
            $where['app_id'] = $this->getAppId();
        }
        $list = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialGroup')->getsBy($where);
        $this->assign('list',$list);
    }

    function addGroupAction(){
        if($this->isPost()){
            $info = $this->getInput(array('name','app_id','id'));
            $data = array(
                'name'=>$info['name'],
                'app_id'=>$info['app_id'],
                'create_time'=>date("Y-m-d"),
            );
            $checkWhere['name'] = $data['name'];
            if(MobgiSpm_Service_MaterialModel::getSpmDao('MaterialGroup')->getBy($checkWhere)){
                $this->output(-1,'存在重复素材组名称!');
            }
            if(!empty($info['id'])){
                $where['id']=$info['id'];
                if(MobgiSpm_Service_MaterialModel::getSpmDao('MaterialGroup')->updateBy($data,$where)){
                    $this->output(0,'修改成功!');
                }else{
                    $this->output(-1,'修改失敗!');
                }
            }else{
                if(MobgiSpm_Service_MaterialModel::getSpmDao('MaterialGroup')->insert($data)){
                    $lastId = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialGroup')->getLastInsertId();
                    $this->output(0,'添加成功!',$lastId);
                }else{
                    $this->output(-1,'添加失敗!');
                }
            }
        }
    }

    function getGroupAction(){
        $id = $this->getInput('id');
        $data = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialGroup')->get($id);
        $this->output('0',$data);
    }

    //编辑素材组内元素
    function editGroupMaterialAction(){
        $gid = $this->getInput('gid');
        $where['gid'] = $gid;
        $info = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialGroup')->get($gid);
        $tmpList = MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->getsBy($where);
        $list = $this->filterMaterialData($tmpList);

        //获取制作人
        $userType = Admin_Service_UserModel::SPM_USER;
        $userList  = Admin_Service_UserModel::getsBy(array('user_type' => $userType));
        //获取二级标签选择
        $labelList = $this->getLabelSelect();
        $materialGroups = MobgiSpm_Service_MaterialModel::getMaterialGroups($this->getAppId());
        $this->assign('materialGroups',$materialGroups);
        $this->assign('labelList',$labelList);
        $this->assign('userList',$userList);
        $this->assign('info',$info);
        $this->assign('list',$list);
    }

    function saveEditGroupMaterialAction(){
        $ids = html_entity_decode($this->getInput('ids'));
        $params = $this->getInput('params');
        foreach ($params as $key=>$val){
            if(!empty($val['value'])) $data[$val['name']] = html_entity_decode($val['value']);
        }
        if(empty($data)){
            $this->output(-1,'没有选择修改项!');
        }
        $where['id'] = array('IN',explode(',',$ids));
        if(MobgiSpm_Service_MaterialModel::getSpmDao('MaterialList')->updateBy($data,$where)){
            $this->output(0,'修改成功');
        }else{
            $this->output(-1,'修改失败');
        }
    }

    function testAction(){

    }
}