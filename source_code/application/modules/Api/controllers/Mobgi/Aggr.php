<?php

class Mobgi_AggrController  extends Mobgi_BaseController {
    


    public function configAction()
    {

        $this->checkPostParams();
        
        $appKey = $this->getInput('appkey');
        $label  = $this->getInput('label');
        $channel = $this->getInput('channel_id');

        $params['app_key'] = $appKey;
        $params['name'] = $label;
        $info = MobgiApi_Service_PolymericAdsModel::getBy($params);
    	if(!$info){
    		exit( json_encode(array('error'=>0, 'msg'=>'Get list failures')));
    	}
    	$result['label']= $info['name'];
    	$result['third_party_appkey']= $info['third_party_appkey'];
    	$result['third_party_appsecret']= $info['secret_key'];
    	$result['blockinfos']= array();
    	
    	$adConfList = json_decode($info['position_conf'], true);
    	foreach ($adConfList['other_block_id'] as $key => $value){
    		$result['blockinfos'][]=array('block_id'=>$adConfList['pos_key'][$key],
    		                          'third_party_block_id'=>$value,
    		                          'name'=>$adConfList['pos_name'][$key],
    		                          'rate'=>$adConfList['rate'][$key],
    		    
    		);
    	}
    	echo json_encode($result);
    	exit;
    	//$this->showResult($ret);
    }
   
   private function checkPostParams(){
        if (empty($this->getInput('appkey'))) {
    		exit( json_encode(array('error'=>0, 'msg'=>'Missing required parameter')));
    	}
    	if ( empty($this->getInput('label'))) {
    	   exit( json_encode(array('error'=>0, 'msg'=>'Missing required parameter')));
    	}
   }

   
    public function reportAction()
    {
        
        echo json_encode(array('error'=>1,'msg'=>'report is ok'));
        die();
    	$data = file_get_contents('php://input');
    	$data = urldecode(html_entity_decode($data));
    	$data = "data=1.0.1|1.1.1|B4D3AF7C536AB8A2A88A|TEST0000000|1|145545455|asdfask11111|2.0.1|1111111111|127.0.0.1|device_brand|device_model|int14|1|1|0|string9|mober";
    	$data = substr($data, 5);
    	if (empty($data)) {
    		exit( json_encode(array('error'=>0, 'msg'=>'Missing required parameter')));
    	} 
    	$dataArr = explode('|', $data);
    	//判断参数个数是否正确
    	if(strlen($dataArr[2]) < 10){
    	    exit( json_encode(array('error'=>0, 'msg'=>'Missing required parameter')));
    	}
    	if(count($dataArr) < 18){
    	   exit( json_encode(array('error'=>0, 'msg'=>'Missing required parameter')));
    	}
    	//os过滤
    	if( $dataArr[15] != '0' && $dataArr[15] != '1'){
    	    exit( json_encode(array('error'=>0, 'msg'=>'os Error')));
    	}
    	//uuid过滤
    	if(empty($dataArr[6])){
    	    exit( json_encode(array('error'=>0, 'msg'=>'uuid Error')));
    	}
    	
    	//替换相应参数的值
    	$dataArr[5] = time();
    	$dataArr[9] = Common::getTime();
    	$dataArr[18] =  isset($dataArr[18])?($dataArr[18]+1):1;
    	$this->initParams($dataArr);
    	

    	$cache = Common::getQueue ( 'video_ads_stat' );
    	$ret = $cache->push ( 'RQ:video_ads_stat', json_encode ( $dataArr ) );
    	if ($ret ) {
    		echo json_encode(array('error'=>1,'msg'=>'report is ok'));
    		die();
    	}else {
    		echo json_encode(array('error'=>0,'msg'=>'report is fail'));
    		die();
    	}
    }
    
    
    private function initParams($dataArr) {
        $dataArr[0] = !empty($dataArr[0])?$dataArr[0]:'-1';
    	$dataArr[1] = !empty($dataArr[1])?$dataArr[1]:'-1';
    	$dataArr[2] = !empty($dataArr[2])?$dataArr[2]:'-1';
    	$dataArr[3] = !empty($dataArr[3])?$dataArr[3]:'-1';
    	$dataArr[4] = !empty($dataArr[4])?$dataArr[4]:'-1';
    	$dataArr[5] = !empty($dataArr[5])?$dataArr[5]:'-1';
    	$dataArr[6] = !empty($dataArr[6])?$dataArr[6]:'-1';
    	$dataArr[7] = !empty($dataArr[7])?$dataArr[7]:'-1';
    	$dataArr[8] = !empty($dataArr[8])?$dataArr[8]:'-1';
    	$dataArr[9] = !empty($dataArr[9])?$dataArr[9]:'-1';
    	$dataArr[10] = !empty($dataArr[10])?$dataArr[10]:'-1';
    	$dataArr[11] =!empty($dataArr[11])?$dataArr[11]:'-1';
    	$dataArr[12] = !empty($dataArr[12])?$dataArr[12]:'-1';
    	$dataArr[13] = !empty($dataArr[13])?$dataArr[13]:'-1';
    	$dataArr[14] = !empty($dataArr[14])?$dataArr[14]:'-1';
    	$dataArr[15] =  ($dataArr[15] == '') ? '-1': $dataArr[15] ;
    	$dataArr[16] = !empty($dataArr[16])?$dataArr[16]:'-1';
    	$dataArr[17] = !empty($dataArr[17])?$dataArr[17]:'-1';
    	return $dataArr;
    }

}

