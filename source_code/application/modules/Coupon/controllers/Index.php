<?php
if (!defined('BASE_PATH'))
exit ('Access Denied!');

class IndexController extends Coupon_BaseController
{
    /**
     * Enter description here ...
     */

    //神庙优惠券
    public function indexAction() {
        $deviceType = $this->get_device_type();
        if($deviceType == 'android'){
            if(!empty($_COOKIE['code1'])){
                $randCode = base64_decode($_COOKIE['code1']);
            }else{
                $randCode = $this->getRandCode(1);
                if($randCode){
                    setcookie('code1',base64_encode($randCode));
                    $this->updateCode($randCode);
                }else{
                    $randCode = '兑换码已经发放完毕!';
                }
            }
        }elseif($deviceType == 'ios'){
            if(!empty($_COOKIE['code2'])){
                $randCode = base64_decode($_COOKIE['code2']);
            }else{
                $randCode = $this->getRandCode(2);
                if($randCode){
                    setcookie('code2',base64_encode($randCode));
                    $this->updateCode($randCode);
                }else{
                    $randCode = '兑换码已经发放完毕!';
                }

            }
        }else{
            $randCode = '请使用手机打开!';
        }
        $this->assign('code',$randCode);
    }


    //好时光优惠券详情1九折
    public function couponDetail1Action(){
        if(!empty($_COOKIE['code3'])){
            $randCode = base64_decode($_COOKIE['code3']);
        }else{
            $randCode = $this->getRandCode(3);
            if($randCode){
                setcookie('code3',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }




    //好时光优惠券详情2八折
    public function couponDetail2Action(){
        if(!empty($_COOKIE['code4'])){
            $randCode = base64_decode($_COOKIE['code4']);
        }else{
            $randCode = $this->getRandCode(4);
            if($randCode){
                setcookie('code4',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }

    //好时光优惠券详情3免费2小时券
    public function couponDetail3Action(){
        if(!empty($_COOKIE['code5'])){
            $randCode = base64_decode($_COOKIE['code5']);
        }else{
            $randCode = $this->getRandCode(5);
            if($randCode){
                setcookie('code5',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }

    //兑换码使用规则腾讯视频好时光磨砂蘑菇杯
    public function couponDetail4Action(){
        if(!empty($_COOKIE['code6'])){
            $randCode = base64_decode($_COOKIE['code6']);
        }else{
            $randCode = $this->getRandCode(6);
            if($randCode){
                setcookie('code6',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }


    //兑换码使用规则腾讯视频好时光礼品餐具套餐
    public function couponDetail5Action(){
        if(!empty($_COOKIE['code7'])){
            $randCode = base64_decode($_COOKIE['code7']);
        }else{
            $randCode = $this->getRandCode(7);
            if($randCode){
                setcookie('code7',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }

    //兑换码使用规则腾讯视频好时光观影包厢1小时券
    public function couponDetail6Action(){
        if(!empty($_COOKIE['code8'])){
            $randCode = base64_decode($_COOKIE['code8']);
        }else{
            $randCode = $this->getRandCode(8);
            if($randCode){
                setcookie('code8',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }


    //兑换码使用规则腾讯视频好时光全场小食7折券
    public function couponDetail7Action(){
        if(!empty($_COOKIE['code9'])){
            $randCode = base64_decode($_COOKIE['code9']);
        }else{
            $randCode = $this->getRandCode(9);
            if($randCode){
                setcookie('code9',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }


    //兑换码使用规则腾讯视频好时光全场饮品8折券
    public function couponDetail8Action(){
        if(!empty($_COOKIE['code10'])){
            $randCode = base64_decode($_COOKIE['code10']);
        }else{
            $randCode = $this->getRandCode(10);
            if($randCode){
                setcookie('code10',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }

    //兑换码使用规则神庙逃亡精美抱枕
    public function couponDetail9Action(){
        if(!empty($_COOKIE['code11'])){
            $randCode = base64_decode($_COOKIE['code11']);
        }else{
            $randCode = $this->getRandCode(11);
            if($randCode){
                setcookie('code11',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }

    //兑换码使用规则梦幻花园Q版狗狗抱枕
    public function couponDetail10Action(){
        if(!empty($_COOKIE['code12'])){
            $randCode = base64_decode($_COOKIE['code12']);
        }else{
            $randCode = $this->getRandCode(12);
            if($randCode){
                setcookie('code12',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }

    //兑换码使用规则QQ限量定制萌宠公仔套装
    public function couponDetail11Action(){
        if(!empty($_COOKIE['code13'])){
            $randCode = base64_decode($_COOKIE['code13']);
        }else{
            $randCode = $this->getRandCode(13);
            if($randCode){
                setcookie('code13',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }


    //兑换码使用规则战队照片
    public function couponDetail12Action(){
        if(!empty($_COOKIE['code14'])){
            $randCode = base64_decode($_COOKIE['code14']);
        }else{
            $randCode = $this->getRandCode(14);
            if($randCode){
                setcookie('code14',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }

    //兑换码使用规则观影包厢2小时券
    public function couponDetail13Action(){
        if(!empty($_COOKIE['code15'])){
            $randCode = base64_decode($_COOKIE['code15']);
        }else{
            $randCode = $this->getRandCode(15);
            if($randCode){
                setcookie('code15',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }

    //兑换码使用规则Q版公仔
    public function couponDetail14Action(){
        if(!empty($_COOKIE['code16'])){
            $randCode = base64_decode($_COOKIE['code16']);
        }else{
            $randCode = $this->getRandCode(16);
            if($randCode){
                setcookie('code16',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }

    //兑换码使用规则气质丝巾
    public function couponDetail15Action(){
        if(!empty($_COOKIE['code17'])){
            $randCode = base64_decode($_COOKIE['code17']);
        }else{
            $randCode = $this->getRandCode(17);
            if($randCode){
                setcookie('code17',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }

        //兑换码使用规则精美淑女发夹
        public function couponDetail16Action(){
            if(!empty($_COOKIE['code18'])){
                $randCode = base64_decode($_COOKIE['code18']);
            }else{
                $randCode = $this->getRandCode(18);
                if($randCode){
                    setcookie('code18',base64_encode($randCode));
                    $this->updateCode($randCode);
                }else{
                    $randCode = '兑换码已经发放完毕!';
                }
            }
        $this->assign('code',$randCode);
    }

    //兑换码使用规则爱心大礼包
    public function couponDetail17Action(){
        if(!empty($_COOKIE['code19'])){
            $randCode = base64_decode($_COOKIE['code19']);
        }else{
            $randCode = $this->getRandCode(19);
            if($randCode){
                setcookie('code19',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }

    //兑换码使用规则精美耳钉
    public function couponDetail18Action(){
        if(!empty($_COOKIE['code20'])){
            $randCode = base64_decode($_COOKIE['code20']);
        }else{
            $randCode = $this->getRandCode(20);
            if($randCode){
                setcookie('code20',base64_encode($randCode));
                $this->updateCode($randCode);
            }else{
                $randCode = '兑换码已经发放完毕!';
            }
        }
        $this->assign('code',$randCode);
    }


    //地铁兑换码
    public function couponDetail19Action(){
        $deviceType = $this->get_device_type();
        if($deviceType == 'android'){
            if(!empty($_COOKIE['code21'])){
                $randCode = base64_decode($_COOKIE['code21']);
            }else{
                $randCode = $this->getRandCode(21);
                if($randCode){
                    setcookie('code21',base64_encode($randCode));
                    $this->updateCode($randCode);
                }else{
                    $randCode = '兑换码已经发放完毕!';
                }
            }
        }elseif($deviceType == 'ios'){
            if(!empty($_COOKIE['code22'])){
                $randCode = base64_decode($_COOKIE['code22']);
            }else{
                $randCode = $this->getRandCode(22);
                if($randCode){
                    setcookie('code22',base64_encode($randCode));
                    $this->updateCode($randCode);
                }else{
                    $randCode = '兑换码已经发放完毕!';
                }

            }
        }else{
            $randCode = '请使用手机打开!';
        }
        $this->assign('code',$randCode);
    }

    //
    private function getRandCode($type){
        $where['type'] = $type;
        $where['status'] = 0;
        $list = Coupon_Service_CodeModel::getsBy($where);
        if(!empty($list)){
            return $list[array_rand($list,1)]['code'];
        }else{
            return false;
        }
    }

    #改变查看状态
    private function updateCode($code){
        $where['code'] = $code;
        $data['status'] = 1;
        return Coupon_Service_CodeModel::updateBy($data,$where);
    }

    #外显后台
    public function jdiojwoandahiwguq123Action(){
        $code = $this->getInput('code');
        if(!empty($code)){
            $where['code'] = array('LIKE',$code);
            $list = Coupon_Service_CodeModel::getsBy($where);
        }else{
            $tmp = Coupon_Service_CodeModel::getAll();
            $list = $tmp[1];
        }
        if(!empty($list)){
        foreach ($list as $key=>&$val){
            switch($val['type']){
                case 1:$val['typeName'] = '神庙安卓';break;
                case 2:$val['typeName'] = '神庙IOS';break;
                case 3:$val['typeName'] = '好时光九折券';break;
                case 4:$val['typeName'] = '好时光八折券';break;
                case 5:$val['typeName'] = '好时光2小时房费';break;
                case 6:$val['typeName'] = '好时光磨砂蘑菇杯';break;
                case 7:$val['typeName'] = '好时光礼品餐具套餐';break;
                case 8:$val['typeName'] = '观影包厢1小时券';break;
                case 9:$val['typeName'] = '全场小食7折券';break;
                case 10:$val['typeName'] = '全场小食8折券';break;
                case 11:$val['typeName'] = '神庙逃亡精美抱枕';break;
                case 12:$val['typeName'] = '梦幻花园Q版狗狗抱枕';break;
                case 13:$val['typeName'] = 'QQ限量定制萌宠公仔套装';break;
                case 14:$val['typeName'] = '战队签名照';break;
                case 15:$val['typeName'] = '观影包厢2小时券';break;
                case 16:$val['typeName'] = 'Q版公仔';break;
                case 17:$val['typeName'] = '气质丝巾';break;
                case 18:$val['typeName'] = '精美淑女发夹';break;
                case 19:$val['typeName'] = '爱心大礼包';break;
                case 20:$val['typeName'] = '精美耳钉';break;
                case 21:$val['typeName'] = '地铁安卓';break;
                case 22:$val['typeName'] = '地铁IOS';break;
            }
            if($val['status'] == 2) $val['checkMsg'] = '已校核';else $val['checkMsg'] = '未校核';
        }}
        $this->assign('codelist',$list);
    }

    #改变校核状态
    public function changgeijjdiisdwAction(){
        $id = $this->getInput('id');
        $where['id'] = $id;
        $data['status'] = 2;
        if(Coupon_Service_CodeModel::updateBy($data,$where)){
            echo 1;
        }else{
            echo 0;
        }
    }

    function get_device_type()
    {
        //全部变成小写字母
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $type = 'other';
        //分别进行判断
        if(strpos($agent, 'iphone') || strpos($agent, 'ipad'))
        {
            $type = 'ios';
        }

        if(strpos($agent, 'android'))
        {
            $type = 'android';
        }
        return $type;
    }

    //随机数发生器
    function rand5211441wahhusgygdawnAction(){
        $map = array(
            15=>300,
            16=>50,
            17=>50,
            18=>200,
            19=>400,
            20=>90,
        );
        //var_dump($this->getRandomString(8));
        //die();
        foreach ($map as $type=>$count){
            $rands = $this->makeRandCode($count);
            foreach ($rands as $key=>$code){
                $data = array(
                    'code'=>$code,
                    'status'=>0,
                    'type'=>$type,
                );
                Coupon_Service_CodeModel::insert($data);
            }
        }
        echo "更新完毕,本次更新总共4100条随机码";
        die();
    }

    private function makeRandCode($count){
        $rands = array();
        while($count){
            $rands[$count] = $this->getRandomString(8);
            $count--;
        }
        return $rands;
    }


    function getRandomString($len, $chars=null)
    {
        if (is_null($chars)){
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        }
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }

}