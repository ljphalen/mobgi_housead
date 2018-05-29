<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class RegisterController extends Common_BaseController {
	
	public $actions = array(
		'loginUrl' => '/Advertiser/Login/login',
        'loginindexUrl' => '/Advertiser/Login/index',
		'registerUrl' => '/Advertiser/Register/register',
        'registerindexUrl' => '/Advertiser/Register/index',
        'registerVerifycodeUrl' => '/Advertiser/Register/verigycode',
        'checkVerifycodeUrl' => '/Advertiser/Register/checkverifycode',
        'registerActiveUrl' => '/Advertiser/Register/active',
        'pwdsendemailUrl' => '/Advertiser/Register/pwdsendemail',
        'resetpwdUrl' => '/Advertiser/Register/resetpwd',
        'checkemailUrl' => '/Advertiser/Register/checkemail',
	);
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {
		$this->assign('loginUrl', $this->actions['loginUrl']);
		$this->assign('registerUrl', $this->actions['registerUrl']);
        $this->assign('registerVerifycodeUrl', $this->actions['registerVerifycodeUrl']);
        $this->assign('registerActiveUrl', $this->actions['registerActiveUrl']);
        $this->assign('checkVerifycodeUrl', $this->actions['checkVerifycodeUrl']);
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function registerAction() {	   
        Yaf_Session::getInstance()->start();
//		$register = $this->getRequest()->getPost();
        $register = $this->getPost(array('account_type','advertiser_name','confirm_password','email', 'password', 'verify_code'));

        if(!in_array($register['account_type'], array('advertiser', 'agent'))){
            return $this->output(-1, '请选择正确的帐号类型.');
        }
        
		if (empty($register['advertiser_name']) || empty($register['password']) || empty($register['confirm_password'])) {
			return $this->output(-1, '用户名或者密码不能为空.');
		}
        
        if(empty(Util_Filter::post('email', '*email'))){
            return $this->output(-1, '邮箱格式不正确');    
        }
        
        if($register['password'] != $register['confirm_password']){
            return $this->output(-1, '两次输入密码不一致');
        }
        
        
        if(strtoupper($register['verify_code'])!=strtoupper($_SESSION['verify_py']['code'])){
            return $this->output(-1, '验证码错误');
        }

        if(Advertiser_Service_UserModel::getUserByEmail($register['email'])){
            return $this->output(-1, '邮箱已经被注册');
        }
        $register['groupid']=1;
        $add_uid = Advertiser_Service_UserModel::addUser($register);
        Advertiser_Service_UserModel::setAppkey($add_uid);
        $account_result= Advertiser_Service_AccountDetailModel::addAccountdetail(array('uid'=>$add_uid, 'account_type'=>'cache'));
		if (!$add_uid || !$account_result) $this->output(-1, '操作失败.');
        $this->output(0, '注册成功.');
//        $this->redirect($this->actions['loginindexUrl']);
//		$this->redirect($this->actions['registerActiveUrl']. "?email=". $register['email']);
	}
    
    /**
     * 检测邮箱
     * @return type
     */
    public function checkemailAction(){
        $email = $this->getInput('email');
        
        if(empty(Util_Filter::post('email', '*email'))){
            return $this->output(-1, '邮箱格式不正确');    
        }
        
        if(Advertiser_Service_UserModel::getUserByEmail($email)){
            return $this->output(-1, '邮箱已经被注册');
        }
        return $this->output(0, '邮箱格式正确');
    }
    
    /**
     * 输出验证码
     */
    public function verigycodeAction(){
        $secoderObj = new Util_Secoder();
        $secoderObj->entry();
    }
    /**
     * ajax验证验证码是否正确
     */
    public function checkverifycodeAction(){
        Yaf_Session::getInstance()->start();
        $register = $this->getRequest()->getPost();
        if(strtoupper($register['verify_code'])!=$_SESSION['verify_py']['code']){
            $this->output(1, '验证码错误');
        }else{
            $this->output(0, '');
        }
    }
    
    /**
     * 重置密码展示
     */
    public function pwdeditemailAction(){
        
    }
    
    /**
     * 重置密码
     */
    public function pwdsendemailAction(){
        $email = $this->getInput('email');
        
        if(empty(Util_Filter::post('email', '*email'))){
            $this->output(-1, '邮箱格式不正确');    
        }
        
        if(!(Advertiser_Service_UserModel::getUserByEmail($email))){
            $this->output(-1, '邮箱未被注册');
        }
        
        // 检测防刷邮件，一分钟之内，只能提交一次
        $session = Common::getSession();
        $sendover = $session->get('send_over');
        if (empty($sendover)) {
            $sendover = array(0, '');
        }
        if ((time() - $sendover[0]) < 60) {
            $this->output(-1, '60秒内只能提交一次');
        }
        
        $where['where'] = array("`email` = '" . $email . "'");
        $nonce = rand(1, 1000000000);
        Advertiser_Service_UsernonceModel::addNonce(array('email' => $email, 'nonce' => $nonce));

        // send email
        $url = Common::getWebRoot() . '/Advertiser/Register/passport?data=' . base64_encode(json_encode(array('email' => $email, 'time' => time(), 'nonce' => $nonce, 'type' => 2)));
        $mailSendconfig = Common::getConfig("mailConfig", "send");
        $mailbody = sprintf($mailSendconfig['pass_message'], $email, $url, $url, $url);
        $data = array();
        $data['receiver'] = $email;
        $data['url'] = $url;
        $data['mailbody'] = $mailbody;
        $data['subject'] = '重置您在HouseAD的密码';
        // active_message 激活验证； pass_message 重置密码验证
        $data['type'] = 'pass_message';
        $redis = Common::getQueue('report');
        $write = $redis->push('RQ:housead_admin_email', $data);
        if ($write <= 0) {
            $this->errlog('saveChargeData');
        }
        $this->output(0, '发送成功,请登录邮箱重置密码', array('redirect_url'=>common::return_email_web_url($email)));
//        $this->output(0, '发送成功,请登录邮箱重置密码');

//        $sendemailresult = Util_PHPMailer_SendMail::postEmail($email, $mailSendconfig['pass_subject'], $mailbody);
//        if ($sendemailresult) {
//            $session->set('send_over', array(time(), $email));
//            $this->output(0, '发送成功,请登录邮箱重置密码', array('redirect_url'=>common::return_email_web_url($email)));
//        } else {
//            $this->output(-1, '发送邮件失败');
//        }
    }
    
    /**
     * 邮箱激活验证
     */
    public function passportAction(){
        $data = $this->getInput('data');
        $dataArr = json_decode(base64_decode($data), true);
        
        if (empty($dataArr['email']) || empty($dataArr['time']) || empty($dataArr['nonce'])) {
            Advertiser_Service_UsernonceModel::deleteBy(array('email'=>$dataArr['email']));
            $this->showMsg(10001, '链接失效');
        }
        
        if($dataArr['type'] == 1){
//            $UserModel = new Model_User();
//            $userinfo=$UserModel->getUser(array("email"=>$dataArr["email"]));
//            if(!empty($userinfo["isactive"]) && $userinfo["isactive"]==1){
//                Common::show_alert("你已经激活,请勿重复操作","location.href='/report/'");
//            }
        }
        
        $nonceInfo = Advertiser_Service_UsernonceModel::getBy(array('email'=>$dataArr['email'], 'nonce'=>$dataArr['nonce']), array('create_time'=>'desc'));
        if(empty($nonceInfo)){
            Advertiser_Service_UsernonceModel::deleteBy(array('email'=>$dataArr['email']));
            $this->showMsg(10001, '链接失效');
        }
        
        if ($dataArr['type'] == 1) { // 激活帐号成功
//            if ($RegisterModel->upd(array('isactive' => 1), $where)) {
//                $userData = $RegisterModel->findOne($where);
//                Session::instance()->set('session_id', md5($userData['email'] . time()));
//                Session::instance()->set('dev_id', $userData['dev_id']);
//                Session::instance()->set('email', $userData['email']);
//                $RegisterModel->del($where); //激活成功删除noce
//                $msg=new Model_Msg();
//                $letter=  Kohana::$config->load("email.letter.newuser");
//                $msg->sendLetter($userData['dev_id'], $letter["title"], $letter["msg"]);
//                Common::show_alert("您的邮箱已成功验证，感谢您的支持！","location.href='/user/user_info'");
//                
//                //$redirect = "/user/user_info";
//            } else {
//                header('Content-Type: text/html; charset=utf-8');
//                $RegisterModel->del($where);
//                echo "链接失效";
//                die;
//            }
        } else if ($dataArr['type'] == 2) { // 修改密码
            $redirect = "/Advertiser/Register/setpassword?data=" . $data;
        }
        $this->redirect($redirect);
    }
    
    public function setpasswordAction(){
        $data = $this->getInput('data');
        $dataArr = json_decode(base64_decode($data), true);
        
        if($dataArr["type"] != 2){
            $this->showMsg(10001, '非法请求');
        }
        
        if (empty($dataArr['email']) || empty($dataArr['time']) || empty($dataArr['nonce'])) {
            Advertiser_Service_UsernonceModel::deleteBy(array('email'=>$dataArr['email']));
            $this->showMsg(10001, '非法请求');
        }
        
        $nonceInfo = Advertiser_Service_UsernonceModel::getBy(array('email'=>$dataArr['email'], 'nonce'=>$dataArr['nonce']), array('create_time'=>'desc'));
        if(empty($nonceInfo)){
            Advertiser_Service_UsernonceModel::deleteBy(array('email'=>$dataArr['email']));
            $this->showMsg(10001, '非法请求');
        }
        $this->assign('dataArr', $dataArr);
        $this->assign('data', $data);
    }
    
    /**
     * 重置密码
     */
    public function resetpwdAction(){
        $data = $this->getInput('data');
        $dataArr = json_decode(base64_decode($data), true);
        
        if($dataArr["type"] != 2){
            $this->output(10001, '非法请求');
        }
        
        if (empty($dataArr['email']) || empty($dataArr['time']) || empty($dataArr['nonce'])) {
            Advertiser_Service_UsernonceModel::deleteBy(array('email'=>$dataArr['email']));
            $this->output(10001, '非法请求');
        }
        
        $nonceInfo = Advertiser_Service_UsernonceModel::getBy(array('email'=>$dataArr['email'], 'nonce'=>$dataArr['nonce']), array('create_time'=>'desc'));
        if(empty($nonceInfo)){
            Advertiser_Service_UsernonceModel::deleteBy(array('email'=>$dataArr['email']));
            $this->output(10001, '非法请求');
        }
        
        $register = $this->getPost(array('email','password','confirm_password'));
        
		if (empty($register['email']) || empty($register['password']) || empty($register['confirm_password'])) {
			return $this->output(-1, '邮箱或者密码不能为空.');
		}
        
        if(empty(Util_Filter::post('email', '*email'))){
            return $this->output(-1, '邮箱格式不正确');    
        }
        
        if($register['password'] != $register['confirm_password']){
            return $this->output(-1, '两次输入密码不一致');
        }
        
        $userInfo = Advertiser_Service_UserModel::getUserByEmail($register['email']);
        if (strlen($register['password']) < 5 || strlen($register['password']) > 16) $this->output(-1, '用户密码长度5-16位之间');
		if ($register['password'] !== $register['confirm_password']) $this->output(-1, '两次密码输入不一致');
		$result = Advertiser_Service_UserModel::updateUser($register, intval($userInfo['advertiser_uid']));
		if (!$result) $this->output(-1, '编辑失败');
        Advertiser_Service_UsernonceModel::deleteBy(array('email'=>$register['email']));
        $this->output(0, '重置密码成功,请登录',array('redirect_url'=>$this->actions['loginindexUrl']));
    }
    
    
    public function activeAction(){
        echo 'active';die;
    }
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function logoutAction() {
		Advertiser_Service_UserModel::logout();
		$this->redirect("/Advertiser/Login/index");
	}
}
