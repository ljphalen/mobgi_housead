<?php

if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-10-8 15:46:04
 * $Id: mailConfig.php 62100 2016-10-8 15:46:04Z hunter.fang $
 */

$config = array (
    'email_web_url'=>array(
        "@hotmail.com" => "www.hotmail.com",
        "@gmail.com" => "www.gmail.com",
        "@vip.sina.com" => "vip.sina.com.cn",
    ),
    'send' => array(
        'active_subject' => '激活您的HouseAD账号',
        'active_message' => "<p>%s,您好！</p><p>感谢您注册为HouseAD广告平台成员！请点击下面的链接，完成邮箱验证。</p>
                    <a href=%s target=_blank>%s</a>
                    <p>如果您不能点击上面链接，还可以将以下链接复制到流量器地址栏中访问：</p> %s
                    <p>在完成注册过程中，如有任何疑问或遇到问题，请发送电子邮件至mobgi@idreamksy.com，获取我们的帮助。</p>
                    <p>感谢您使用HouseAD，希望您在HouseAD体验愉快！</p><p>HouseAD广告平台 支持中心</p>",
        'pass_subject' => '重置您在HouseAD的密码',
        'pass_message' => "<div style='position:relative;font-size:14px;height:auto;padding:15px 15px 10px 15px;z-index:1;zoom:1;line-height:1.7;'><p><b>亲爱的用户</b> <span style='color:blue'>%s</span></p><p>您好！</p><p>您于 <span style='color:blue'>" . date('Y-m-d H:i:s') . "</span> 提交了找回密码申请。</p><p>请点击下面的链接，进行新密码的设置：</p><a href=%s target=_blank>%s</a><br><br>-----------------------------------------------<br> <p>如果您点击上述链接无效，请把下面的链接复制到浏览器的地址栏中访问：</p> <span style='color:blue'>%s</span><br><br><p>感谢您使用HouseAD，希望您在HouseAD有个愉快的体验！</p><p>HouseAD广告平台 支持中心</p></div>",
        'overtime' => 666600,
    ),
);

return $config;




