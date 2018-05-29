<?php

/**
 * 广告系统统计提取用户数据
 *
 * @author rock
 */
class UserController extends Api_BaseController {
	//用户数据提取中心接口配置
	private $mUserDataCenterConf = array( 'user' => 'soso.zhang',
				'pwd' =>'poppopp',
				'host'=>'http://edata.idreamsky.com',
				'payUserCallBackUrl'=>'http://admin.mobgi.com/Api/User/downloadPayUserData',
				'appId'=>37,
				'templateId' => 1134);
    
    private $mDataExtract = NULL;
    private $mCookiePath = './';
    const  CACHE_EXPRIE = 864000;
    const  DOWNLOAD_PAY_USER_CACHE = 'payUser';

    /**
     * 创建付费用户任务，准备提取
     */
    public function createPayUserTaskAction(){
    	
        try {
        	
            $this->initDataExtract();
            $seqId = uniqid();
            $returnData =  $this->mDataExtract->post('/user/task/createTask', array(
                                                                        'appId' => $this->mUserDataCenterConf['appId'],
                                                                        'templateId' => $this->mUserDataCenterConf['templateId'],
                                                                        'url' => $this->mUserDataCenterConf['payUserCallBackUrl'],
                                                                        'seqId'=>$seqId
            ));
           $msg = '开始创建任务:appId='.$this->mUserDataCenterConf['appId'].', templateId='.$this->mUserDataCenterConf['templateId'].', 和；回调url='.$this->mUserDataCenterConf['payUserCallBackUrl'].', seqId='.$seqId.', 任务ID='.$returnData['id'];
            Util_Log::info ( __CLASS__, 'datacerter.log', $msg);
            //记录任务id
            if($returnData['id']){           
                $msg = '创建任务成功 taskId='.$returnData['id'];
                Util_Log::info ( __CLASS__, 'datacerter.log', $msg);
                $redis = $this->getCache();
                $key = self::DOWNLOAD_PAY_USER_CACHE.$seqId;
                $result = $redis->set($key, 1, self::CACHE_EXPRIE);
                $msg = '创建任务写入redis的key='.$key.'保存的缓存时间='.self::CACHE_EXPRIE.', result='.$result;
                Util_Log::info ( __CLASS__, 'datacerter.log', $msg);
            }
        }catch(Exception $e) {
        	echo '创建付费用户任务异常:'.$e->getMessage();
        	Util_Log::info ( __CLASS__, 'datacerter.log', '创建付费用户任务异常'.$e->getMessage() );
        }
    }
 
    

    
    /**
     * 下载付费用户数据
     */
    public function downloadPayUserDataAction(){
        try{
            $downLoadUrl = $this->getGet('downloadDataUrl');
            $status =  $this->getGet('status');
            $resultCount = $this->getGet('resultCount');
            $seqId  = $this->getGet('seqId');
            $sign  = $this->getGet('sign');
            $msg = '获取回调参数:downLoadUrl='.$downLoadUrl.', status='.$status.', resultCount='.$resultCount.', seqId='.$seqId.', sign='.$sign;
            Util_Log::info ( __CLASS__, 'datacerter.log', $msg);
            if( $status != 0 || !isset($status) || !isset($downLoadUrl) || !isset($seqId) ) {
                Util_Log::info ( __CLASS__, 'datacerter.log', '检验参数不正确');
                echo json_encode(array('status' => 1, 'msg' => 'parameter error'));
                exit;
            }
            $token = md5($this->mUserDataCenterConf['pwd']);
            $encryptStr = md5($status.$resultCount.$seqId.$downLoadUrl.$token);
            if($encryptStr != $sign){
                $msg = '检验签名不正确:encryptStr='.$encryptStr.', token='.$token.', sign='.$sign.', pwd='.$this->mUserDataCenterConf['pwd'];
                Util_Log::info ( __CLASS__, 'datacerter.log', $msg);
                echo json_encode(array('status' => 1, 'msg' => 'sign error'));
                exit;
            }
            $redis = $this->getCache();
            $key = self::DOWNLOAD_PAY_USER_CACHE.$seqId;
            $value = $redis->get($key);
            $msg = '获取任务的key='.$key.'状态'.$value;
            Util_Log::info ( __CLASS__, 'datacerter.log', $msg);
            if(!$value){
                echo json_encode(array('status' => 1, 'msg' => 'taskId error'));
                exit;
            }
            //保存任务的下载链接
            $key = 'task'.date('Y-m-d');
            $value = $redis->set($key,  $downLoadUrl,  self::CACHE_EXPRIE);
            $msg = '保持下载任务key='.$key.'，保存的值downLoadUrl='.$downLoadUrl.',保存的缓存时间='.self::CACHE_EXPRIE.',状态='.$value;
            Util_Log::info ( __CLASS__, 'datacerter.log', $msg);
           // $this->initDataExtract();
            //$content = $this->mDataExtract->download( $downLoadUrl );
           // $fileName = Doo::conf()->SITE_PATH.'data/'.'payUser'.date('Y-m-d').'.csv';
           // Doo::logger()->log(' 保存的文件名：fileName'.$fileName);
           // file_put_contents($fileName, $content);
        } catch( Exception $e ) {
            echo 'Message: ' .$e->getMessage();
            Util_Log::info ( __CLASS__, 'datacerter.log', '下载付费用户异常'.$e->getMessage() );
        }
    }
	
	private function initDataExtract() {
		$this->mDataExtract = new Util_DataExtract ( array (
				'user' => $this->mUserDataCenterConf ['user'],
				'pwd' => $this->mUserDataCenterConf ['pwd'],
				'cookiePath' => $this->mCookiePath,
				'timeOut' => 0,
				'host' => $this->mUserDataCenterConf ['host'] 
		) );
		$msg = '开始登录验证：user=' . $this->mUserDataCenterConf ['user'] . ', pwd=' . $this->mUserDataCenterConf ['pwd'] . ', cookiePath=' . $this->mCookiePath . ', host=' . $this->mUserDataCenterConf ['host'];
		Util_Log::info ( __CLASS__, 'datacerter.log', $msg );
		$this->mDataExtract->login ();
	}
    
    private  function  getCache(){
      	$cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS, 'AD_USER_CACHE_REDIS_SERVER0');
        return  $cache;
    }
 
    
    

}