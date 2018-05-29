<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-12-8 19:02:14
 * $Id: Advertiser.php 62100 2016-12-8 19:02:14Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Sona_V1_AdvertiserController extends Api_BaseController {
    
    /**
     * 获取广告主信息
     * curl http://rock.advertiser.housead.com/v1/advertiser/read -H 'Authorization: Bearer NCwxNDgxMTk1MjQ1LDU1NmE1YTY3NzI1ZjE4ODgwMTNmYTliM2E4Y2YyMjI1ZjYxZjFkZjk='  -d 'advertiser_id=4'
     * 应答:
     * {"code":0,"msg":"","data":{"email":"782802112@qq.com","company_name":null,"address":null}}
     */
    public function readAction() {
        $advertiser_id = $this->getInput('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput('31000', 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        $userinfo = Advertiser_Service_UserModel::getUser($advertiser_id);
        $info = array();
        $info['email'] = $userinfo['email'];
        $info['company_name'] = $userinfo['company_name'];
        $info['address'] = $userinfo['address'];
        $this->sonaOutput(0, '', $info);
    }
    
    /**
     * 生成当前token
     * curl http://rock.advertiser.housead.com/v1/advertiser/token -d 'advertiser_id=4'
     * 示例应答:
     * {"code":0,"msg":"","data":{"token":"NCwxNDgxMTk3ODA4LDkzOTUzN2U1ZmQ1ZGU5YTc2MjVhZGFmNDM3NDRhY2VkMTY1NDE5MjY="}}
     */
    public function tokenAction(){
        $advertiser_id = $this->getInput('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput('31000', 'advertiser_id:lack required parameters');
        }
        $useinfo = Advertiser_Service_UserModel::getUser($advertiser_id);
        if(empty($useinfo)){
            $this->sonaOutput(31010, 'object operated not exist ');
        }
        $appkey = $useinfo['appkey'];
        $time_stamp = time();
        $sign = sha1($advertiser_id.$appkey.$time_stamp);
        $token = base64_encode($advertiser_id .',' . $time_stamp. ',' . $sign);
        $data = array('token'=>$token);
        $this->sonaOutput(0, '', $data);
    }

}

